<?php

function printorlog($str,$color="") {
  // un comment this to see what's happening!
  //$str == "\n" and $br = "<br>";
  //$str = $br.htmlentities($str);
  //$color and $str = "<b style='color:".$color."'>".$str."</b>";
  //print $str."&nbsp;";
}

function hash_to_entity($hash="") {
  global $db;
  if ($hash) {
    $q = prepare("select * from token WHERE tokenHash = '%s'",$hash);
    $row = $db->qr($q);
    if ($row["tokenEntity"] == "comment") {
      $q = prepare("SELECT commentMaster,commentMasterID FROM comment WHERE commentID = %d",$row["tokenEntityID"]);
      $r = $db->qr($q);
      return $r["commentMaster"].$r["commentMasterID"];
    } else {
      return $row["tokenEntity"].$row["tokenEntityID"];
    }
  }
}

$db = new db_alloc();

// nuke output buffering
@apache_setenv('no-gzip', 1);
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
for ($i = 0; $i < ob_get_level(); $i++) { ob_end_flush(); }
ob_implicit_flush(1);
ini_set('max_execution_time',180000); 
ini_set('memory_limit',"256M");

$info["host"] = config::get_config_item("allocEmailHost");
$info["port"] = config::get_config_item("allocEmailPort");
$info["username"] = config::get_config_item("allocEmailUsername");
$info["password"] = config::get_config_item("allocEmailPassword");
$info["protocol"] = config::get_config_item("allocEmailProtocol");

$mail = new email_receive($info);
$mail->open_mailbox(config::get_config_item("allocEmailFolder"), CL_EXPUNGE);
$mail->check_mail();

$msg_nums = $mail->get_all_email_msg_uids(); 

printorlog("\n");
printorlog(date("Y-m-d H:i:s")." Found ".count($msg_nums)." emails.");

// fetch and parse email
foreach ($msg_nums as $num) {
  $x++;

  // this will stream output
  flush();

  $mail->set_msg($num);
  $mail->get_msg_header($num);
  $keys = $mail->get_hashes();
  $mailbox = hash_to_entity($keys[0]);

  if (!$mailbox) {
    printorlog("\n");
    printorlog("keys[0] not found. Trying keys[1]: ");
    $mailbox = hash_to_entity($keys[1]);

    if (!$mailbox) {
      printorlog("Failed: ".print_r($keys,1));
      continue;
    }
  }
  printorlog("\n");
  printorlog("INBOX.".$mailbox);
  $mail->create_mailbox("INBOX/".$mailbox);
  $mail->move_mail($num,"INBOX/".$mailbox);

  if ($x % 100 == 0) {
    printorlog("\n");
    printorlog("expunging at ".$x);
    $mail->expunge();
  }
}
printorlog("\n");
printorlog("Done at ".$x);
$mail->expunge();
$mail->close();
printorlog(date("Y-m-d H:i:s")." DONE.");
?>
