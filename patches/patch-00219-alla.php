<?php

function printorlog($str,$color="") {
  #$str == "\n" and $br = "<br>";
  #$str = $br.htmlentities($str);
  #$color and $str = "<b style='color:".$color."'>".$str."</b>";
  #print $str."&nbsp;";
}

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

$mail = new alloc_email_receive($info);
$mail->open_mailbox(config::get_config_item("allocEmailFolder"),OP_HALFOPEN+OP_READONLY);
$mail->check_mail();

$msg_nums = $mail->get_all_email_msg_uids(); 

printorlog("\n");
printorlog(date("Y-m-d H:i:s")." Found ".count($msg_nums)." emails.");

$db = new db_alloc();

// Get list of tokens and the task that they are related to
// This query may need to be optimized
// Try: ALTER TABLE comment ADD INDEX typelinkidx (commentType,commentLinkID);
$q = "SELECT tokenHash, tokenEntity, tokenEntityID, commentMaster, commentMasterID
        FROM token
   LEFT JOIN comment ON (tokenEntity = commentType AND tokenEntityID = commentLinkID)
                     OR (tokenEntity = 'comment' and tokenEntityID = commentID)
       GROUP by tokenHash;";
$db->query($q);

while ($row = $db->row()) {
  if ($row["tokenEntity"] && $row["tokenEntity"] != "comment") {
    $entityIDs[$row["tokenHash"]] = $row["tokenEntity"].$row["tokenEntityID"];

  } else if ($row["commentMaster"] && $row["commentMaster"] != "comment") {
    $entityIDs[$row["tokenHash"]] = $row["commentMaster"].$row["commentMasterID"];
  }
}

printorlog("\n");
printorlog("Gathered this many tokens:".count($entityIDs));

// fetch and parse email
foreach ($msg_nums as $num) {
  // this will stream output
  flush();

  // So we don't kill the server
  usleep(2500);

  $mail->set_msg($num);
  $mail->get_msg_header($num);
  $keys = $mail->get_hashes();
  $entityID = $entityIDs[$keys[0]];

  if (!$entityID) {
    continue;
  }
  printorlog("INBOX.".$entityID);
  $mail->create_mailbox("INBOX.".$entityID);
  $mail->move_mail($num,"INBOX.".$entityID);
}
$mail->expunge();
$mail->close();
printorlog(date("Y-m-d H:i:s")." DONE.");
?>
