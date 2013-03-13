<?php

// This nightmare of a patch just captures any comments created using the alloc-cli
// that didn't get stuck into the correct imap folder

function e($str) {
  echo "<br>".htmlentities($str);
}

$db = new db_alloc();

$row = $db->qr("SELECT count(*) AS sum FROM sentEmailLog WHERE sentEmailLogCreatedTime >= '2012-05-12 00:00:00'");
//e(sprintf("Found %d emails sent.",$row["sum"]));

$row = $db->qr("SELECT count(*) AS sum FROM comment WHERE commentCreatedTime >= '2012-05-12 00:00:00'");
//e(sprintf("Found %d comments created.",$row["sum"]));

$info["host"] = config::get_config_item("allocEmailHost");
$info["port"] = config::get_config_item("allocEmailPort");
$info["username"] = config::get_config_item("allocEmailUsername");
$info["password"] = config::get_config_item("allocEmailPassword");
$info["protocol"] = config::get_config_item("allocEmailProtocol");


$qid = $db->query("SELECT * FROM comment WHERE commentCreatedTime >= '2012-05-12 00:00:00'");

while ($row = $db->row($qid)) {

  // For each comment, ascertain the taskID
  $entity = $row["commentMaster"];
  $entityID = $row["commentMasterID"];

  // Use the taskID to query the mbox.taskID for all its emails
  $mail = new email_receive($info);

  $mail->open_mailbox("INBOX");
  $created = $mail->create_mailbox("INBOX/".$entity.$entityID);
  $created or $created = $mail->create_mailbox("INBOX.".$entity.$entityID);
  $opened = $mail->open_mailbox("INBOX/".$entity.$entityID);
  $uids = $mail->get_all_email_msg_uids();
  //e("Entity: ".$entity.$entityID." comment: ".$row["commentID"]." uids: ".print_r($uids,1));
  $found = false;
  foreach ((array)$uids as $uid) {
    // If there's one that looks like the current one, forgeddaboutit
    list($header,$body) = $mail->get_raw_email_by_msg_uid($uid);
    similar_text(preg_replace("/\s+/","",trim($row["comment"])),preg_replace("/\s+/","",trim($body)),$percent);
    if ($percent > 97) {
      $found = true;
      #e("percent: ".$percent);
      #e("1-------------------");
      #e($row["comment"]);
      #e("2-------------------");
      #e($body);
      #e("E-------------------");
    }
  }

  // If not found, append the comment to the mailbox
  if (!$found) {
    e("Appending this comment: ".$row["comment"]);
    $people_cache =& get_cached_table("person");
    $name = $people_cache[$row["commentCreatedUser"]]["name"];
    $email = add_brackets($people_cache[$row["commentCreatedUser"]]["emailAddress"]);

    $eml = array();
    $eml[] = "From: ".$name." ".$email;
    $eml[] = "Date: ".date('D M  j G:i:s Y',strtotime($row["commentCreatedTime"]));

    $e = new $entity;
    $e->set_id($entityID);
    $e->select();

    $tpl = config::get_config_item("emailSubject_".$entity."Comment");
    $tpl and $subject = commentTemplate::populate_string($tpl, $entity, $entityID);
    $entity != "task" and $prefix = ucwords($entity)." Comment: ";
    $subject or $subject = $prefix.$entityID." ".$e->get_name(DST_VARIABLE);

    $r = $db->qr("SELECT tokenHash FROM token WHERE tokenEntity = 'comment' AND tokenEntityID = %d",$row["commentID"]);
    $subject_header = "Subject: ".$subject." {Key:".$r["tokenHash"]."}";
    $eml[] = $subject_header;

    $recipients = comment::get_email_recipients(array("interested"),$entity,$entityID);
    list($to_address,$bcc,$successful_recipients) = comment::get_email_recipient_headers($recipients, $people_cache[$row["commentCreatedUser"]]["emailAddress"]);

    $eml[] = "To: ".$to_address;
    $eml[] = "";
    $eml[] = $row["comment"];

    $eml = implode("\n",$eml);

    //$eml = str_replace("\n","<br>",htmlentities($eml)); //TODO: remove
    //echo("<br><b style='font-size:70%'>".$eml."</b>");

    $mail->mail_headers["subject"] = $subject_header; // hack
    $mail->msg_text = $eml;
    $mail->archive("INBOX/".$entity.$entityID);
  }
  $mail->expunge();
  $mail->close();
}

?>
