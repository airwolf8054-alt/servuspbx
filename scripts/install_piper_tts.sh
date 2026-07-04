#!/usr/bin/env bash
# ServusPBX Piper TTS Installer
# Wird aus dem Webinterface via sudo -n aufgerufen.
set -Eeuo pipefail

export DEBIAN_FRONTEND=noninteractive

log() { echo "[servuspbx-tts] $*"; }
fail() { echo "[servuspbx-tts][FEHLER] $*" >&2; exit 1; }

if [[ "${EUID}" -ne 0 ]]; then
  fail "Dieses Script muss als root laufen. Bitte sudoers für www-data prüfen."
fi

ARCH="$(uname -m)"
case "$ARCH" in
  x86_64|amd64)
    PIPER_ARCHIVE="piper_linux_x86_64.tar.gz"
    ;;
  aarch64|arm64)
    PIPER_ARCHIVE="piper_linux_aarch64.tar.gz"
    ;;
  armv7l|armhf)
    PIPER_ARCHIVE="piper_linux_armv7l.tar.gz"
    ;;
  *)
    fail "Nicht unterstützte CPU-Architektur: ${ARCH}"
    ;;
esac

PIPER_VERSION="2023.11.14-2"
BASE_DIR="/usr/local/servuspbx/tts"
PIPER_DIR="${BASE_DIR}/piper"
VOICE_DIR="${BASE_DIR}/voices"
TMP_DIR="$(mktemp -d /tmp/servuspbx-piper.XXXXXX)"
SOUNDS_DIR="/var/lib/asterisk/sounds/custom/tts"
VOICES_ONLY=0
if [[ "${1:-}" == "--voices-only" ]]; then
  VOICES_ONLY=1
fi

cleanup() { rm -rf "$TMP_DIR"; }
trap cleanup EXIT

log "Installiere Systempakete ..."
apt-get update -y
apt-get install -y --no-install-recommends ca-certificates curl tar gzip ffmpeg espeak-ng-data

log "Erstelle Verzeichnisse ..."
mkdir -p "$BASE_DIR" "$VOICE_DIR" "$SOUNDS_DIR"

if [[ "$VOICES_ONLY" -eq 0 ]]; then
  log "Lade Piper (${PIPER_VERSION}, ${ARCH}) ..."
  curl -fL --retry 3 --connect-timeout 20 \
    "https://github.com/rhasspy/piper/releases/download/${PIPER_VERSION}/${PIPER_ARCHIVE}" \
    -o "${TMP_DIR}/${PIPER_ARCHIVE}"

  tar -xzf "${TMP_DIR}/${PIPER_ARCHIVE}" -C "$TMP_DIR"
  if [[ ! -x "${TMP_DIR}/piper/piper" ]]; then
    fail "Piper Binary wurde im Archiv nicht gefunden."
  fi

  rm -rf "$PIPER_DIR"
  mv "${TMP_DIR}/piper" "$PIPER_DIR"
  chmod +x "${PIPER_DIR}/piper"
else
  log "Nur Stimmen-Nachinstallation wurde angefordert."
  if [[ ! -x "${PIPER_DIR}/piper" ]]; then
    fail "Piper ist noch nicht installiert. Bitte zuerst Piper installieren."
  fi
fi

download_voice() {
  local lang_path="$1"
  local speaker="$2"
  local quality="$3"
  local file="$4"
  if [[ -s "${VOICE_DIR}/${file}.onnx" && -s "${VOICE_DIR}/${file}.onnx.json" ]]; then
    log "Stimme ${file} ist bereits vorhanden."
    return 0
  fi
  log "Lade Stimme ${file} ..."
  curl -fL --retry 3 --connect-timeout 20 \
    "https://huggingface.co/rhasspy/piper-voices/resolve/v1.0.0/${lang_path}/${speaker}/${quality}/${file}.onnx?download=true" \
    -o "${VOICE_DIR}/${file}.onnx"
  curl -fL --retry 3 --connect-timeout 20 \
    "https://huggingface.co/rhasspy/piper-voices/resolve/v1.0.0/${lang_path}/${speaker}/${quality}/${file}.onnx.json?download=true" \
    -o "${VOICE_DIR}/${file}.onnx.json"
}

log "Lade vordefinierte deutsche Stimmen ..."
download_voice "de/de_DE" "thorsten" "medium" "de_DE-thorsten-medium"
download_voice "de/de_DE" "thorsten" "low" "de_DE-thorsten-low"
download_voice "de/de_DE" "eva_k" "x_low" "de_DE-eva_k-x_low"
download_voice "de/de_DE" "karlsson" "low" "de_DE-karlsson-low"
download_voice "de/de_DE" "kerstin" "low" "de_DE-kerstin-low"

# Sinnvolle Rechte: Webserver darf Status lesen, Asterisk darf MP3 abspielen.
log "Setze Berechtigungen ..."
chown -R root:root "$BASE_DIR"
chmod -R a+rX "$BASE_DIR"
chmod 755 "${PIPER_DIR}/piper"

if id asterisk >/dev/null 2>&1; then
  chown -R asterisk:asterisk "$SOUNDS_DIR"
else
  chown -R www-data:www-data "$SOUNDS_DIR" || true
fi
chmod -R 775 "$SOUNDS_DIR"

log "Installation abgeschlossen."
log "Piper: ${PIPER_DIR}/piper"
log "Stimmen: ${VOICE_DIR}"
log "Ausgabe: ${SOUNDS_DIR}"
"${PIPER_DIR}/piper" --help >/dev/null 2>&1 || true
exit 0
