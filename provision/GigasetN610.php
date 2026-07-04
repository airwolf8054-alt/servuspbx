<?php
require_once __DIR__ . '/../inc/db.php';
error_reporting(E_ALL);
ini_set('display_errors','Off');
header('Content-Type: text/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';
function gx($v){return htmlspecialchars((string)$v,ENT_XML1|ENT_QUOTES,'UTF-8');}
function gmac($v){return strtoupper(preg_replace('/[^A-Fa-f0-9]/','',(string)$v));}
function gs($k,$d=''){ $db=spbx_db(); $s=$db->prepare("SELECT setting_value FROM spbx_settings WHERE setting_key=? LIMIT 1"); if(!$s)return $d; $s->bind_param('s',$k); $s->execute(); $r=$s->get_result()->fetch_assoc(); return $r?(string)$r['setting_value']:$d; }
$db=spbx_db(); $mac=isset($_GET['mac'])?gmac($_GET['mac']):'000000000000'; $server=$_SERVER['SERVER_ADDR']??'127.0.0.1'; $ip=$_SERVER['REMOTE_ADDR']??'';
$s=$db->prepare("UPDATE spbx_dect_bases SET ip_address=? WHERE mac_address=? AND base_type='GigasetN610'"); if($s){$s->bind_param('ss',$ip,$mac);$s->execute();}
$base=null; $s=$db->prepare("SELECT * FROM spbx_dect_bases WHERE mac_address=? AND base_type='GigasetN610' AND active=1 LIMIT 1"); if($s){$s->bind_param('s',$mac);$s->execute();$base=$s->get_result()->fetch_assoc();}
$slots=[]; for($i=1;$i<=8;$i++) $slots[$i]=['active'=>'0','user'=>'','name'=>'','pass'=>''];
if($base){$res=$db->query("SELECT h.idx_number,e.extension,e.display_name,a.password FROM spbx_dect_base_handsets h JOIN spbx_extensions e ON e.endpoint_id=h.endpoint_id LEFT JOIN ps_auths a ON a.id=e.endpoint_id WHERE h.base_id=".(int)$base['id']." AND h.idx_number BETWEEN 1 AND 8 AND e.active=1 ORDER BY h.idx_number"); if($res){while($r=$res->fetch_assoc()){ $idx=(int)$r['idx_number']; $slots[$idx]=['active'=>'1','user'=>(string)$r['extension'],'name'=>(string)($r['display_name']?:$r['extension']),'pass'=>(string)($r['password']??'')];}}}
echo '<settings>';
echo '<device>';
echo '<provisioning_server>http://'.gx($server).'/provision/GigasetN610.php?mac='.gx($mac).'</provisioning_server>';
echo '<phonebook_url>http://'.gx($server).'/provision/dectdirectory.php?vendor=gigaset</phonebook_url>';
$fw=gs('fw_gigaset_dect_n610_url',''); if($fw!=='') echo '<firmware_url>'.gx($fw).'</firmware_url>';
echo '</device><sip><registrar>'.gx($server).'</registrar><proxy>'.gx($server).'</proxy><transport>udp</transport></sip><handsets>';
for($i=1;$i<=8;$i++){ $x=$slots[$i]; echo '<handset idx="'.$i.'"><active>'.gx($x['active']).'</active><extension>'.gx($x['user']).'</extension><display_name>'.gx($x['name']).'</display_name><auth_name>'.gx($x['user']).'</auth_name><auth_password>'.gx($x['pass']).'</auth_password></handset>'; }
echo '</handsets></settings>';
?>