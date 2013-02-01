<?php

// Trying to fix: select * from comment where commentID = 57585\G
// The emailUID should be corrected and the commentCreatedUser should not be Matt Cen, but rather client Mark OOi.
// Update commentCreatedUser, commentEmailUID, commentCreatedUserClientContactID, commentCreatedUserText, 

function printorlog($str,$color="") {
  global $fp;
  $txt = $str;
  $txt != "\n" and $txt = str_replace("\n","",$txt);
  $color and $txt = strtoupper($color).": ".$txt;
  fputs($fp,$txt." ");

  $str == "\n" and $br = "<br>";
  $str = $br.htmlentities($str);
  $color and $str = "<b style='color:".$color."'>".$str."</b>";
  print $str."&nbsp;";
}

function fix_this_comment($r,$num,$from,$messageid) {
  global $db;
  global $alloc_from_addresses2;
 
  if ($r["commentEmailUIDORIG"] != $num) {
    unset($projectID);
    if ($r["commentMaster"]=="task" && $r["commentMasterID"]) {
      $q = prepare("select projectID from task where taskID = %d",$r["commentMasterID"]);
      $db->query($q);
      $task_row = $db->row();
      $projectID = $task_row["projectID"];
    }

    // Try figure out and populate the commentCreatedUser/commentCreatedUserClientContactID fields
    list($from_address,$from_name) = parse_email_address($from);

    $person = new person();
    $personID = $person->find_by_email($from_address);
    $personID or $personID = $person->find_by_name($from_name);

    $sql = array();
    $sql[] = prepare("commentEmailUID = '%s'",trim($num));
    if ($personID) {
      $sql[] = prepare("commentCreatedUser = %d",$personID);
      $sql[] = "commentCreatedUserClientContactID = NULL";
    } else {
      $sql[] = "commentCreatedUser = NULL";
      $cc = new clientContact();
      $clientContactID = $cc->find_by_email($from_address, $projectID);
      $clientContactID or $clientContactID = $cc->find_by_name($from_name, $projectID);
      $clientContactID and $sql[] = prepare("commentCreatedUserClientContactID = %d",$clientContactID);
    }

    $sql[] = prepare("commentCreatedUserText = '%s'",trim($from));
    $sql[] = prepare("commentEmailMessageID = '%s'",trim($messageid));

    if (!in_array($from_address,$alloc_from_addresses2)) { // don't update items that are from alloc
      $q = prepare("UPDATE comment SET ".implode(",",$sql)." WHERE commentID = %d",$r["commentID"]);
      $db->query($q);
      printorlog("FIXED: ".$q." (old uid: ".$r["commentEmailUIDORIG"].")","blue");
    }

  } else {
    // Try figure out and populate the commentCreatedUser/commentCreatedUserClientContactID fields
    list($from_address,$from_name) = parse_email_address($from);
    if (!in_array($from_address,$alloc_from_addresses2)) { // don't update items that are from alloc
      $sql = array();
      $sql[] = prepare("commentEmailUID = '%s'",trim($num));
      $sql[] = prepare("commentEmailMessageID = '%s'",trim($messageid));
      $q = prepare("UPDATE comment SET ".implode(",",$sql)." WHERE commentID = %d",$r["commentID"]);
      $db->query($q);
      printorlog("GOOD: ".$q,"green");
    }
  }
}


global $fp;


// nuke output buffering
@apache_setenv('no-gzip', 1);
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
for ($i = 0; $i < ob_get_level(); $i++) { ob_end_flush(); }
ob_implicit_flush(1);

ini_set('max_execution_time',180000); 
ini_set('memory_limit',"256M");

global $alloc_from_addresses2;
$alloc_from_addresses2[] = config::get_config_item("AllocFromEmailAddress");
$alloc_from_addresses2[] = "alloc@cybersource.com.au";
$alloc_from_addresses2[] = "alloc@cyber.com.au";
$alloc_from_addresses2[] = "allocdev@cybersource.com.au";
$alloc_from_addresses2[] = "allocdev@cyber.com.au";

$lockfile = ATTACHMENTS_DIR."mail.lock.patch179";

$info["host"] = config::get_config_item("allocEmailHost");
$info["port"] = config::get_config_item("allocEmailPort");
$info["username"] = config::get_config_item("allocEmailUsername");
$info["password"] = config::get_config_item("allocEmailPassword");
$info["protocol"] = config::get_config_item("allocEmailProtocol");

if (true) {
  // regular users probably won't need to apply this patch, so let them skip it
  define("FORCE_PATCH_SUCCEED_patch-00179-alla.php",1);
} else {

  $fp = fopen(ATTACHMENTS_DIR."updatecommentpatch179.log","a+");
  $mail = new email_receive($info,$lockfile);
  $mail->open_mailbox(config::get_config_item("allocEmailFolder"),OP_HALFOPEN+OP_READONLY);
  $mail->check_mail();

  $msg_nums = $mail->get_all_email_msg_uids(); 

  # foreach ($msg_nums as $n) {
  #   $n == 326 and $go = true;
  #   $go and $remaining[] = $n;
  # }
  # unset($msg_nums);
  # $msg_nums = $remaining;

  printorlog("\n");
  printorlog(date("Y-m-d H:i:s")." Found ".count($msg_nums)." emails.");

  $db = new db_alloc();

  // fetch and parse email
  foreach ($msg_nums as $num) {

    // this will stream output
    flush();

    $dir = ATTACHMENTS_DIR."tmp";
    if (!is_dir($dir)) mkdir($dir);

    usleep(25000);

    $x++; 
    #$x>20 and alloc_error(fclose($fp).$mail->close()."\n"."Stopped."."\n");

    $mail->set_msg($num);
    $decoded = $mail->save_email();
    $body = trim(mime_parser::get_body_text($decoded));
    $from = $decoded[0]["Headers"]["from:"];
    $subject = $decoded[0]["Headers"]["subject:"];
    $messageid = $decoded[0]["Headers"]["message-id:"];
    $keys = $mail->get_hashes();
    
    printorlog("\n");
    printorlog($x.". ".date("Y-m-d H:i:s")." Keys:".print_r($keys,1)." IMAP UID: ".$num);

    // Get a bunch of comments that look like they might belong to this email...

    unset($q);

    // First try and get a Key: from the subject line, or message-id
    $key = current($keys);
    if ($key && strlen($key) == 8) {
      $db->query("SELECT * FROM token WHERE tokenHash = '%s'",$key);
      $row = $db->row();
      printorlog("Using key: ".$key." relates to: ".$row["tokenEntity"].":".$row["tokenEntityID"]);
      // lookup the entity for that key eg: comment 1234 or task 123
      if ($row) {
        $q = prepare("SELECT * 
                        FROM comment 
                       WHERE commentEmailUID IS NULL 
                         AND ((commentID = %d AND '%s'='comment') OR (commentLinkID = %d AND commentType='%s'))"
                    ,$row["tokenEntity"],$row["tokenEntityID"],$row["tokenEntityID"],$row["tokenEntity"]);
      }
    
    // Failing a key, try and get a "Task Comment: 213" from the email subject
    } else if (preg_match("/Task Comment: (\d+)/", $subject, $matches)) {
      printorlog("Using subject line: ".$matches[1]." relates to task:".$matches[1]);
      $q = prepare("SELECT *
                      FROM comment 
                     WHERE commentEmailUID IS NULL 
                       AND commentMaster = 'task' 
                       AND commentMasterID = '%d'",$matches[1]);
    } else {
      printorlog("WTF:".$num." ".$from.": ".print_r($mail->mail_headers,1).$body);
    }


    if ($q) {
      $db->query($q);
      $hits = array();
      while($row = $db->row()) {
        if (trim($body) == trim($row["comment"])) {
          $hits[] = $row;
        }
      }

      if (count($hits) == 1) {
        fix_this_comment(current($hits),$num,$from,$messageid);

      } else {
        printorlog("Found ".count($hits),"orange");
        // perform more checks to determine which email relates to which comment ...
        unset($this_is_the_girl);
        foreach ($hits as $hit) {
          $from == $hit["commentCreatedUserText"] and $this_is_the_girl = $hit;
        }

        if ($this_is_the_girl) {
          fix_this_comment($this_is_the_girl,$num,$from,$messageid);
        } else {
          list($from_address,$from_name) = parse_email_address($from);
          if (!in_array($from_address,$alloc_from_addresses2)) { // don't update items that are from alloc
            printorlog("Unable to find comment for email with UID: ".$num." From: ".$from." Subject: ".$subject,"red");
          } else {
            printorlog("Skipping UID: ".$num." because it looks to be from alloc: ".$from, "#cccccc");
          }
        }
      }
      
    }
    
  }

  printorlog("\n");
  printorlog(date("Y-m-d H:i:s")." DONE.");

  fclose($fp);
  $mail->close();

  // This patch can have output, and still proceed as though it has applied cleanly.
  define("FORCE_PATCH_SUCCEED_patch-00179-alla.php",1);
}






?>
