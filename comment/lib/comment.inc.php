<?php

/*
 * Copyright (C) 2006, 2007, 2008 Alex Lance, Clancy Malcolm, Cybersource
 * Pty. Ltd.
 * 
 * This file is part of the allocPSA application <info@cyber.com.au>.
 * 
 * allocPSA is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or (at
 * your option) any later version.
 * 
 * allocPSA is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public
 * License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with allocPSA. If not, see <http://www.gnu.org/licenses/>.
*/

class comment extends db_entity {
  public $classname = "comment";
  public $data_table = "comment";
  public $key_field = "commentID";
  public $data_fields = array("commentMaster"
                             ,"commentMasterID"
                             ,"commentType"
                             ,"commentLinkID"
                             ,"commentCreatedUser"
                             ,"commentCreatedTime"
                             ,"commentModifiedTime"
                             ,"commentModifiedUser"
                             ,"commentCreatedUserClientContactID"
                             ,"commentCreatedUserText"
                             ,"commentEmailRecipients"
                             ,"commentEmailUID"
                             ,"comment"
                             );

  function save() {
    if ($this->get_value("commentType") == "comment") {
      $parent_comment = new comment();
      $parent_comment->set_id($this->get_value("commentLinkID"));
      $parent_comment->select();
      $this->set_value("commentMaster",$parent_comment->get_value("commentType"));
      $this->set_value("commentMasterID",$parent_comment->get_value("commentLinkID"));
    } else {
      $this->set_value("commentMaster",$this->get_value("commentType"));
      $this->set_value("commentMasterID",$this->get_value("commentLinkID"));
    }
    parent::save();
  }

  function delete() {
  
    if ($this->get_id()) {
      $dir = ATTACHMENTS_DIR."comment".DIRECTORY_SEPARATOR.$this->get_id();
      if (is_dir($dir)) {
        $handle = opendir($dir);
        clearstatcache();
        while (false !== ($file = readdir($handle))) {
          if ($file != "." && $file != ".." && file_exists($dir.DIRECTORY_SEPARATOR.$file)) {
            unlink($dir.DIRECTORY_SEPARATOR.$file);
            clearstatcache();
          }
        }
        is_dir($dir) && rmdir($dir);
      }
    }
    parent::delete();
  }

  function is_owner() {
    global $current_user;
    $entity = $this->get_value("commentType");
    $e = new $entity;
    $e->set_id($this->get_value("commentLinkID"));
    $e->select();
    
    // If the parent is a comment, then go up one more level
    if ($entity == "comment") {
      $entity = $e->get_value("commentType");
      $pe = new $entity;
      $pe->set_id($e->get_value("commentLinkID"));
      $pe->select();
      $e = $pe;
    }
    return $e->is_owner($current_user);
  }

  function has_attachment_permission($person) {
    return $this->is_owner();
  }

  function has_attachment_permission_delete($person) {
    return $this->is_owner();
  }

  function get_comments($commentType="",$commentLinkID="") {
    $rows = array();
    if ($commentType && $commentLinkID) {
      $q = sprintf("SELECT commentID, commentLinkID, commentType,
                           commentCreatedUser as personID, 
                           commentCreatedTime as date, 
                           commentModifiedTime, 
                           commentModifiedUser, 
                           comment, 
                           commentCreatedUserClientContactID as clientContactID,
                           commentCreatedUserText,
                           commentEmailRecipients,
                           commentEmailUID
                      FROM comment 
                     WHERE commentType = '%s' AND commentLinkID = %d 
                  ORDER BY commentCreatedTime"
                  ,$commentType, $commentLinkID);
      $db = new db_alloc;
      $db->query($q);
      while ($row = $db->row()) {
        $rows[] = $row;
      }
    }
    return $rows;
  }

  function util_get_comments_array($entity, $id, $options=array()) {
    global $TPL, $current_user;
    $rows = array();
    $new_rows = array();
    // Need to get timeSheet comments too for task comments
    if ($entity == "task") {
      $rows = comment::get_comments($entity,$id);
      $rows2 = timeSheetItem::get_timeSheetItemComments($id);
      $rows or $rows = array();
      $rows2 or $rows2 = array();
      $rows = array_merge($rows,$rows2);
      if (is_array($rows)) {
        usort($rows, array("comment","sort_task_comments_callback_func"));
      }
    } else {
      $rows = comment::get_comments($entity,$id);
    }

    foreach ($rows as $v) {
      $new = $v;

      if (!$v["comment"])
        continue ;
  
      unset($children);
      $children = comment::util_get_comments_array("comment", $v["commentID"], $options);
      is_array($children) && count($children) and $new["children"] = $children;


      $token = new token;
      if ($token->select_token_by_entity_and_action("comment",$new["commentID"],"add_comment_from_email")) {
        if ($token->get_value("tokenHash")) {
          $new["hash"] = $token->get_value("tokenHash");
          $new["hashKey"] = "{Key:".$new["hash"]."}";
          $new["hashHTML"] = " <em class=\"faint\">".$new["hashKey"]."</em>";
        }
  
        $ip = interestedParty::get_interested_parties("comment",$new["commentID"]);
        if (is_array($ip)) {
          foreach($ip as $email => $info) {
            if ($info["external"]) {
              $new["external"] = " loud";
              $new["external_label"] = "<em class='faint warn'>[&nbsp;External&nbsp;Conversation&nbsp;]</em>";
            }
          }
        }
        $new["external_label"] or $new["external_label"] = "<em class='faint'>[&nbsp;Internal&nbsp;Conversation&nbsp;]</em>";

      #} else if ($token->select_token_by_entity_and_action($entity,$id,"add_comment_from_email")) {
        #$token->get_value("tokenHash") and $new["hash"] = " <em class=\"faint\">{Key:".$token->get_value("tokenHash")."}</em>";
      }


      if ($v["timeSheetID"]) {
        $timeSheet = new timeSheet();
        $timeSheet->set_id($v["timeSheetID"]);
        $v["ts_label"] = " (Time Sheet #".$timeSheet->get_id().")";

      } else if (($v["personID"] == $current_user->get_id()) && $options["showEditButtons"] && $new["hash"]) {
        //$new["comment_buttons"] = "<input type=\"submit\" name=\"comment_edit\" value=\"Edit\">";
      //<input type=\"submit\" name=\"comment_delete\" value=\"Delete\" class=\"delete_button\">";
      }

      $new["attribution"] = comment::get_comment_attribution($v);
      $new["commentCreatedUserEmail"] = comment::get_comment_author_email($v);
      $s = commentTemplate::populate_string(config::get_config_item("emailSubject_taskComment"), $entity, $id);
      $new["commentEmailSubject"] = $s." ".$new["hashKey"];

      if (!$_GET["commentID"] || $_GET["commentID"] != $v["commentID"]) {

        if ($options["showEditButtons"] && $new["comment_buttons"]) {
          $new["form"] = '<form action="'.$TPL["url_alloc_comment"].'" method="post">';
          $new["form"].= '<input type="hidden" name="entity" value="'.$v["commentType"].'">';
          $new["form"].= '<input type="hidden" name="entityID" value="'.$v["commentLinkID"].'">';
          $new["form"].= '<input type="hidden" name="commentID" value="'.$v["commentID"].'">';
          $new["form"].= '<input type="hidden" name="comment_id" value="'.$v["commentID"].'">';
          $new["form"].= $new["comment_buttons"];
          $new["form"].= '</form>';
        }
    
        if ($new["commentEmailUID"] && config::get_config_item("allocEmailHost")) { 
          $new['downloadEmail'] = '<a class="noprint" href="'.$TPL["url_alloc_downloadEmail"].'msg_uid='.$new["commentEmailUID"].'">';
          #$new['downloadEmail'].= '<img border="0" title="Download Email" src="'.$TPL["url_alloc_images"].'download_email.gif">';
          $new['downloadEmail'].= 'Download</a>';
        }

        $files = get_attachments("comment",$v["commentID"],array("sep"=>"<br>"));
        if (is_array($files)) {
          foreach($files as $key => $file) {
            $new["files"].= '<div align="center" style="float:left; display:inline; margin-right:14px;">'.$file["file"].'</div>';
          }
        }

        $v["commentEmailRecipients"] and $new["emailed"] = 'Emailed to '.page::htmlentities($v["commentEmailRecipients"]);

        $new_rows[] = $new;
      }
    }

    return $new_rows;
  }

  function util_get_comments($entity, $id, $options=array()) {
    global $TPL, $current_user;
    $rows = comment::util_get_comments_array($entity, $id, $options);
    $rows or $rows = array();
    foreach ($rows as $row) {
      $rtn.= comment::get_comment_html_table($row);
    }
    return $rtn;
  }

  function get_comment_html_table($row=array()) {
    global $TPL;
    $comment = comment::add_shrinky_divs(page::to_html($row["comment"]),$row["commentID"]);
    $onClick = "return set_grow_shrink('comment_".$row["commentID"]."','button_comment_".$row["commentID"]."','true');";
    $rtn[] = '<table width="100%" cellspacing="0" border="0" class="panel'.$row["external"].'">';
    $rtn[] = '<tr>';
    $rtn[] = '  <td style="width:75%; padding-bottom:0px; white-space:normal" onClick="'.$onClick.'">'.$row["attribution"].$row["hashHTML"].'</td>';
    $rtn[] = '  <td align="right" style="padding-bottom:0px;">'.$row["form"].$row["downloadEmail"].$row["external_label"].'</td>';
    $rtn[] = '</tr>';
    $rtn[] = '<tr>';
    $rtn[] = '  <td colspan="2" style="padding-top:0px; white-space:normal;">'.preg_replace("/<[^>]>/","",$row["emailed"])."</td>";
    $rtn[] = '</tr>';
    $rtn[] = '<tr>';
    $rtn[] = '  <td colspan="2" onClick="'.$onClick.'"><div style="overflow:auto">'.$comment.'</div></td>';
    $rtn[] = '</tr>';
    $row["children"] and $rtn[] = comment::get_comment_children($row["children"]);
    $row["files"] and $rtn[] = '<tr>';
    $row["files"] and $rtn[] = '  <td valign="bottom" align="left" colspan="2">'.$row["files"].'</td>';
    $row["files"] and $rtn[] = '</tr>';
    $rtn[] = '</table>';
    return implode("\n",$rtn);
  }

  function get_comment_attribution($comment=array()) {
    $str = '<b>'.comment::get_comment_author($comment).'</b> <span class="comment_date">'.$comment["date"]."</span>";
      if ($comment["commentModifiedTime"] || $comment["commentModifiedUser"]) {
        $str.= ", last modified by <b>".person::get_fullname($comment["commentModifiedUser"])."</b> ".$comment["commentModifiedTime"];
      }
      $str.= $comment["ts_label"];
    return $str;
  }

  function add_shrinky_divs($html="", $commentID) {
    if ($_GET["media"] == "print") return $html;
    $class = "comment_".$commentID;
    $lines = explode("\n","\n".$html."\n");
    foreach ($lines as $k => $line) {
      if (!$started && preg_match("/^&gt;/",$line)) {
        $started = true;
        $start_position = $k;
        $new_lines[$k] = $line;

      } else if ($started && !preg_match("/^&gt;/",$line)){
    
        $num_lines_hidden = $k-$start_position;

        if ($num_lines_hidden > 3) {
          $new_lines[$start_position-1].= "<div style=\"display:inline;\" class=\"hidden_text button_".$class."\"> --- ".$num_lines_hidden." lines hidden --- <br></div>";
          $new_lines[$start_position-1].= "<div style=\"display:none;\" class=\"hidden_text ".$class."\">";
          $new_lines[$k] = "</div>".$line;
    
        } else {
          $new_lines[$start_position-1].= "<div style=\"display:inline;\" class=\"hidden_text\">";
          $new_lines[$k] = "</div>".$line;
        }

        $started = false;

      } else {
        $new_lines[$k] = $line;
      }
    }

    // Hide signature too
    foreach ($new_lines as $k => $line) {
      if (!$sig_started && preg_match("/^--(\s|\n|\r|<br>|<br \/>)*$/",$line)) {
        $sig_started = true;
        $sig_start_position = $k;
      } 
      $new_lines2[$k] = $line;
    }

    $sig_num_lines_hidden = count($new_lines2)-1-$sig_start_position;
  
    if ($sig_started && $sig_num_lines_hidden > 3){
      $new_lines2[$sig_start_position-1].= "<div style=\"display:inline;\" class=\"hidden_text button_".$class."\"> --- ".$sig_num_lines_hidden." lines hidden (signature) --- <br></div>";
      $new_lines2[$sig_start_position-1].= "<div style=\"display:none;\" class=\"hidden_text ".$class."\">"; 
      $new_lines2[count($new_lines2)].= "</div>";
    } else if ($sig_started) {
    }

    return rtrim(implode("\n",$new_lines2));
  }

  function get_comment_children($children=array(), $padding=1) {
    $rtn = array();
    foreach($children as $child) {
      // style=\"padding:0px; padding-left:".($padding*15+5)."px; padding-right:6px;\"
      $rtn[] = "<tr><td colspan=\"2\" style=\"padding:0px; padding-left:6px; padding-right:6px;\">".comment::get_comment_html_table($child)."</td></tr>";
      if (is_array($child["children"]) && count($child["children"])) {
        $padding += 1;
        $rtn[] = comment::get_comment_children($child["children"],$padding);
        $padding -= 1;
      } 
    } 
    return implode("\n",$rtn);
  }

  function get_comment_author($comment=array()) {
    if ($comment["commentCreatedUserText"]) {
      $author = page::htmlentities($comment["commentCreatedUserText"]);
    } else if ($comment["clientContactID"]) {
      $cc = new clientContact;
      $cc->set_id($comment["clientContactID"]);
      $cc->select();
      #$author = " <a href=\"".$TPL["url_alloc_client"]."clientID=".$cc->get_value("clientID")."\">".$cc->get_value("clientContactName")."</a>";
      $author = $cc->get_value("clientContactName");
    } else {
      $person = new person;
      $person->set_id($comment["personID"]);
      $person->select();
      $author = $person->get_name();
    }
    return $author;
  }

  function get_comment_author_email($comment=array()) {
    if ($comment["commentCreatedUser"]) {
      $personID = $comment["commentCreatedUser"];
      $p = new person;
      $p->set_id($personID);
      $p->select();
      $email = $p->get_from();
    } else if ($comment["clientContactID"]) {
      $cc = new clientContact;
      $cc->set_id($comment["clientContactID"]);
      $cc->select();
      $email = $cc->get_value("clientContactEmail");
    } else {
      $p= new person;
      $p->set_id($comment["personID"]);
      $p->select();
      $email = $p->get_from();
    }
    return $email;
  }

  function sort_task_comments_callback_func($a, $b) {
    return strtotime($a["date"]) > strtotime($b["date"]);
  }

  function make_token_add_comment_from_email() {
    global $current_user;
    $token = new token;
    $token->set_value("tokenEntity","comment");
    $token->set_value("tokenEntityID",$this->get_id());
    $token->set_value("tokenActionID",2);
    $token->set_value("tokenActive",1);
    $token->set_value("tokenCreatedBy",$current_user->get_id());
    $token->set_value("tokenCreatedDate",date("Y-m-d H:i:s"));
    $hash = $token->generate_hash();
    $token->set_value("tokenHash",$hash);
    $token->save();
    return $hash;
  }

  function add_comment_from_email($email) {

    // Skip over emails that are from alloc. These emails are kept only for
    // posterity and should not be parsed and downloaded and re-emailed etc.
    if (same_email_address($email->mail_headers->fromaddress, ALLOC_DEFAULT_FROM_ADDRESS)) {
      $email->mark_seen();
      return;
    }

    // Make a new comment
    $comment = new comment;
    $comment->set_value("commentType","comment");
    $comment->set_value("commentLinkID",$this->get_id());
    $comment->set_value("commentEmailUID",$email->msg_uid);
    $comment->save();
    $commentID = $comment->get_id();

    $c = new comment();
    $c->set_id($comment->get_value("commentLinkID"));
    $c->select();
    if ($c->get_value("commentType") == "task" && $c->get_value("commentLinkID")) {
      $t = new task;
      $t->set_id($c->get_value("commentLinkID"));
      $t->select();
      $projectID = $t->get_value("projectID");
    }

    // Save the email attachments into a directory
    $dir = ATTACHMENTS_DIR."comment".DIRECTORY_SEPARATOR.$comment->get_id();
    if (!is_dir($dir)) {
      mkdir($dir, 0777);
    }
    $file = $dir.DIRECTORY_SEPARATOR."mail.eml";
    $decoded = $email->save_email($file);

    // Try figure out and populate the commentCreatedUser/commentCreatedUserClientContactID fields
    list($from_address,$from_name) = parse_email_address($decoded[0]["Headers"]["from:"]);


    $person = new person;
    $personID = $person->find_by_email($from_address);
    $personID or $personID = $person->find_by_name($from_name);

    if ($personID && (!is_object($current_user) || (is_object($current_user) && !$current_user->get_id()))) {
      global $current_user;
      $current_user = new person;
      $current_user->load_current_user($personID);
    }

    $cc = new clientContact();
    $clientContactID = $cc->find_by_email($from_address, $projectID);
    $clientContactID or $clientContactID = $cc->find_by_name($from_name, $projectID);

    if ($personID) {
      $comment->set_value('commentCreatedUser', $personID);
    } else if ($clientContactID) {
      $comment->set_value('commentCreatedUserClientContactID', $clientContactID);
    }

    // If we don't have a $from_name, but we do have a personID or clientContactID, get proper $from_name
    if (!$from_name && $personID) {
      $from_name = person::get_fullname($personID);

    } else if (!$from_name && $clientContactID) {
      $cc = new clientContact;
      $cc->set_id($clientContactID);
      $cc->select();
      $from_name = $cc->get_value("clientContactName");

    } else if (!$from_name) {
      $from_name = $from_address;
    }

    // Load up some variables for later in send_emails()
    $from["email"] = $from_address;
    $from["name"] = $from_name;
    $from["references"] = $decoded[0]["Headers"]["references:"];
    $from["in-reply-to"] = $decoded[0]["Headers"]["in-reply-to:"];
    $from["precedence"] = $decoded[0]["Headers"]["precedence:"];

    // Don't update last modified fields...
    $comment->skip_modified_fields = true;

    // Update comment with the text body and the creator
    $body = trim(mime_parser::get_body_text($decoded));
    $comment->set_value("comment",$body);
    $comment->set_value("commentCreatedUserText",trim($decoded[0]["Headers"]["from:"]));
    $comment->save();
    $from["commentID"] = $comment->get_id();


    // If user wants to un/subscribe to this comment
    $subject = $decoded[0]["Headers"]["subject:"];
    $ip_action = interestedParty::adjust_by_email_subject($subject,"comment",$this->get_id(),$from_name,$from_address,$personID,$clientContactID,$body,$email->msg_uid);

    $recipients[] = "interested";

    $class = $c->get_value("commentType");
    if (class_exists($class)) {
      $obj = new $class;
      $obj->set_id($c->get_value("commentLinkID"));
      $obj->select();
      $from["parentCommentID"] = $c->get_id();
      $from["entity"] = "comment";
      $from["entityID"] = $c->get_id();

      $token = new token;
      if ($token->select_token_by_entity_and_action("comment",$comment->get_value("commentLinkID"),"add_comment_from_email")) {
        $from["hash"] = $token->get_value("tokenHash");
      }

      if ($ip_action["interestedParty"]) {
        $comment->set_value("comment",$ip_action["interestedParty"]."\n\n".$comment->get_value("comment"));
        $comment->save();
      }
      if ($ip_action["timeSheet"]) {
        $comment->set_value("comment",$ip_action["timeSheet"]."\n\n".$comment->get_value("comment"));
        $comment->save();
      }

      if (!$ip_action["quiet"]) { // only resend email if quiet hasn't been put in the subject line
        $successful_recipients = $obj->send_emails($recipients, $c->get_value("commentType")."_comments", $comment->get_value("comment"), $from);
      }

      if ($successful_recipients) {
        $comment->set_value("commentEmailRecipients",$successful_recipients);
        $comment->save();
      }
    }

  }

  function get_email_recipients($options=array(),$from=array()) {
    $recipients = array();
    $people = get_cached_table("person");

    foreach ($options as $selected_option) {

      // Determine recipients 
      if ($selected_option == "interested") {
        $db = new db_alloc;
        if ($from["entity"] && $from["entityID"]) {
          $q = sprintf("SELECT * FROM interestedParty WHERE entity = '%s' AND entityID = %d",$from["entity"],$from["entityID"]);
        }
        $db->query($q);
        while($row = $db->next_record()) {
          $row["isCC"] = true;
          $row["name"] = $row["fullName"];
          $recipients[] = $row;
        }
      } else if (is_int($selected_option)){
        $recipients[] = $people[$selected_option];

      } else if (is_string($selected_option) && preg_match("/@/",$selected_option)) {
        list($email, $name) = parse_email_address($selected_option);
        $email and $recipients[] = array("name"=>$name,"emailAddress"=>$email);
      }
    }
    return $recipients;
  }

  function get_email_recipient_headers($recipients, $from) {
    global $current_user;

    $emailMethod = config::get_config_item("allocEmailAddressMethod");

    // Build up To: and Bcc: headers
    foreach ($recipients as $recipient) {
      unset($recipient_full_name);

      if ($recipient["firstName"] && $recipient["surname"]) {
        $recipient_full_name = $recipient["firstName"]." ".$recipient["surname"];
      } else if ($recipient["fullName"]) {
        $recipient_full_name = $recipient["fullName"];
      } else if ($recipient["name"]) {
        $recipient_full_name = $recipient["name"];
      }

      if ($recipient["emailAddress"] && !$done[$recipient["emailAddress"]]) {

        // If the person does *not* want to receive their own emails, skip adding them as a recipient
        if ($current_user->prefs["receiveOwnTaskComments"] == 'no' && same_email_address($recipient["emailAddress"],$from["email"])) {
          continue;
        }

        $done[$recipient["emailAddress"]] = true;

        $name = $recipient_full_name or $name = $recipient["emailAddress"];
        $email_without_name = $recipient["emailAddress"];
        if ($recipient_full_name) {
          $name_and_email = $recipient_full_name." <".$recipient["emailAddress"].">";
        } else {
          $name_and_email = $recipient["emailAddress"];
        }

        if ($emailMethod == "to") {
          $to_address.= $commar.$name_and_email;
          $successful_recipients.= $commar.$name_and_email;
          $commar = ", ";

        } else if ($emailMethod == "bcc") {
          $bcc.= $commar.$email_without_name;
          $successful_recipients.= $commar.$name_and_email;
          $commar = ", ";

        // The To address contains no actual email addresses, ie "Alex Lance": ; all the real recipients are in the Bcc.
        } else if ($emailMethod == "tobcc") {
          $to_address.= $commar.'"'.$name.'": ;';
          $bcc.= $commar.$email_without_name;
          $successful_recipients.= $commar.$name_and_email;
          $commar = ", ";
        }

      }
    }
    return array($to_address, $bcc, $successful_recipients);
  }

  function get_list($_FORM=array()) {
    if ($_FORM["entity"] && in_array($_FORM["entity"],array("project","client","task","timeSheet")) && $_FORM["entityID"]) {
      $e = new $_FORM["entity"];
      $e->set_id($_FORM["entityID"]);
      if ($e->select()) { // this ensures that the user can read the entity
        return comment::util_get_comments_array($_FORM["entity"],$_FORM["entityID"],$_FORM);
      }
    }
  }

  function get_list_vars() {
    return array("entity"            => "The entity whose comments you want to fetch, eg: project | client | task | timeSheet"
                ,"entityID"          => "The ID of the particular entity"
                ,"showEditButtons"   => "Will fetch a form with edit comment buttons"
                );
  }

  function get_parent_object() {
    if (class_exists($this->get_value("commentType"))) {
      $parent_type = $this->get_value("commentType");
    
      $o = new $parent_type;
      $o->set_id($this->get_value("commentLinkID"));
      $o->select();

      if ($parent_type == "comment") {
        return $o->get_parent_object();
      } else {
        return $o;
      }
    }
  }

  function update_search_index_doc(&$index) {
    $arr["commentCreatedUserText"] = $this->get_value("commentCreatedUserText");
    $arr["clientContactID"]        = $this->get_value("commentCreatedUserClientContactID");
    $arr["personID"]               = $this->get_value("commentCreatedUser");

    $name = $this->get_value("commentCreatedTime")." ";
    $author = comment::get_comment_author($arr);
    $name.= $author;

    $entity = $this->get_value("commentType");
    $entity_id = $this->get_value("commentLinkID");
    $e = new $entity;
    $e->set_id($entity_id);
    $e->select();
    $entity_name = $e->get_name();
    // If the parent is a comment, then go up one more level
    if ($entity == "comment") {
      $entity = $e->get_value("commentType");
      $entity_id = $e->get_value("commentLinkID");
      $f = new $entity;
      $f->set_id($entity_id);
      $f->select();
      $entity_name = $f->get_name();
    }

    $doc = new Zend_Search_Lucene_Document();
    $doc->addField(Zend_Search_Lucene_Field::Keyword('id'   ,$this->get_id()));
    $doc->addField(Zend_Search_Lucene_Field::Text('name'    ,$name));
    $doc->addField(Zend_Search_Lucene_Field::Text('type'    ,$entity));
    $doc->addField(Zend_Search_Lucene_Field::Text('typeid'  ,$entity_id));
    $doc->addField(Zend_Search_Lucene_Field::Text('typename',$entity_name));
    $doc->addField(Zend_Search_Lucene_Field::Text('desc'    ,$this->get_value("comment")));
    $doc->addField(Zend_Search_Lucene_Field::Text('creator' ,$author));
    $doc->addField(Zend_Search_Lucene_Field::Text('dateCreated',str_replace("-","",$this->get_value("commentCreatedTime"))));
    $index->addDocument($doc);
  }

  function delete_search_index_doc(&$index) {
    return $index;
  }

  function get_list_summary_filter($filter=array()) {
    
    // This takes care of projectID singular and plural
    $projectIDs = project::get_projectID_sql($filter,"task");
    $projectIDs and $sql1["projectIDs"] = $projectIDs;
    $projectIDs and $sql2["projectIDs"] = $projectIDs;
    $filter['taskID'] and $sql1[] = sprintf("(task.taskID = %d)", db_esc($filter["taskID"]));
    $filter['taskID'] and $sql2[] = sprintf("(task.taskID = %d)", db_esc($filter["taskID"]));
    $filter["fromDate"] and $sql1[] = sprintf("(date(commentCreatedTime) >= '%s')", db_esc($filter["fromDate"]));
    $filter["fromDate"] and $sql2[] = sprintf("(dateTimeSheetItem >= '%s')", db_esc($filter["fromDate"]));
    $filter["toDate"] and $sql1[] = sprintf("(date(commentCreatedTime) < '%s')", db_esc($filter["toDate"]));
    $filter["toDate"] and $sql2[] = sprintf("(dateTimeSheetItem < '%s')", db_esc($filter["toDate"]));
    $filter["personID"] and $sql1["personID"] = "(comment.commentCreatedUser IN (".implode(",",(array)$filter["personID"])."))";
    $filter["personID"] and $sql2[] = "(timeSheetItem.personID IN (".implode(",",(array)$filter["personID"])."))";
    $filter["clients"] or $sql1[] = "(commentCreatedUser IS NOT NULL)";
    $filter["clients"] && $filter["personID"] and $sql1["personID"] = "(comment.commentCreatedUser IN (".implode(",",(array)$filter["personID"]).") OR comment.commentCreatedUser IS NULL)";

    $filter["taskStatus"] and $sql1[] = task::get_taskStatus_sql($filter["taskStatus"]);
    $filter["taskStatus"] and $sql2[] = task::get_taskStatus_sql($filter["taskStatus"]);

    return array($sql1,$sql2);
  }

  function get_list_summary($_FORM=array()) {

    //$_FORM["fromDate"] = "2010-08-20";
    //$_FORM["projectID"] = "22";

    $_FORM["maxCommentLength"] or $_FORM["maxCommentLength"] = 500;

    list($filter1,$filter2) = comment::get_list_summary_filter($_FORM);

    is_array($filter1) && count($filter1) and $filter1 = " AND ".implode(" AND ",$filter1);
    is_array($filter2) && count($filter2) and $filter2 = " AND ".implode(" AND ",$filter2);

    if ($_FORM["clients"]) {
      $client_join = " LEFT JOIN clientContact on comment.commentCreatedUserClientContactID = clientContact.clientContactID";
      $client_fields = " , clientContact.clientContactName";
    }

    $q = sprintf("SELECT commentID as id
                       , commentCreatedUser as personID
                       , date(commentCreatedTime) as date
                       , commentMasterID as taskID
                       , task.taskName
                       , SUBSTRING(comment.comment,1,%d) AS comment_text
                       , commentCreatedUserText
                         ".$client_fields."
                    FROM comment
               LEFT JOIN task on comment.commentMasterID = task.taskID
                         ".$client_join."
                   WHERE commentMaster = 'task'
                         ".$filter1."
                ORDER BY commentCreatedTime, commentCreatedUser"
                ,$_FORM["maxCommentLength"]);
    $q.= " ";
              
    $people = get_cached_table("person");

    $db = new db_alloc();
    $db->query($q);
    while ($row = $db->row()) {
      $row["id"] = "comment_".$row["id"];
      $row["personID"] and $row["person"] = $people[$row["personID"]]["name"];
      $row["clientContactName"] and $row["person"] = $row["clientContactName"];
      $row["person"] or list($e,$row["person"]) = parse_email_address($row["commentCreatedUserText"]);
      if (!$tasks[$row["taskID"]]) {
        $t = new task;
        $t->set_id($row["taskID"]);
        $t->set_value("taskName",$row["taskName"]);
        $tasks[$row["taskID"]] = $t->get_task_link(array("prefixTaskID"=>true));
      }
      $rows[$row["taskID"]][$row["date"]][] = $row;
    }

    // Note that timeSheetItemID is selected twice so that the perms checking can work
    // timeSheetID is also used by the perms checking.
    $q2 = sprintf("SELECT timeSheetItemID as id
                         ,timeSheetItemID
                         ,timeSheetID
                         ,timeSheetItem.personID
                         ,dateTimeSheetItem as date
                         ,timeSheetItem.taskID
                         ,task.taskName
                         ,timeSheetItemDuration as duration
                         ,SUBSTRING(timeSheetItem.comment,1,%d) AS comment_text
                     FROM timeSheetItem
                LEFT JOIN task on timeSheetItem.taskID = task.taskID
                    WHERE 1
                          ".$filter2."
                 ORDER BY dateTimeSheetItem"
                 ,$_FORM["maxCommentLength"]);

    $db->query($q2);
    while ($row = $db->row()) {
      $timeSheetItem = new timeSheetItem();
      if (!$timeSheetItem->read_row_record($row,false))
        continue;
      $row["id"] = "timeitem_".$row["id"];
      $row["person"] = $people[$row["personID"]]["name"];
      if (!$tasks[$row["taskID"]]) {
        $t = new task;
        $t->set_id($row["taskID"]);
        $t->set_value("taskName",$row["taskName"]);
        $tasks[$row["taskID"]] = $t->get_task_link(array("prefixTaskID"=>true));
      }
      $totals[$row["taskID"]] += $row["duration"];
      $rows[$row["taskID"]][$row["date"]][] = $row;
    }

    foreach ((array)$rows as $taskID => $dates) {
      $rtn.= comment::get_list_summary_header($tasks[$taskID],$totals[$taskID],$_FORM);
      foreach ($dates as $date => $more_rows) {
        foreach ($more_rows as $row) {
          $rtn.= comment::get_list_summary_body($row);
        }
      }
      $rtn.= comment::get_list_summary_footer($rows,$tasks);
    }
    return $rtn;
  }

  function get_list_summary_header($task,$totals,$_FORM=array()) {
  
    if ($_FORM["showTaskHeader"]) {
      $rtn[] = "<table class='list' style='border-bottom:0;'>";
      $rtn[] = "<tr>";
      $rtn[] = "<td style='font-size:130%'>".$task."</td>";
      $rtn[] = "<td class='right bold'>".sprintf("%0.2f",$totals)."</td>";
      $rtn[] = "</tr>";
      $rtn[] = "</table>";
    }
    $rtn[] = "<table class=\"list sortable\" style='margin-bottom:10px'>";
    $rtn[] = "<tr>";
    $rtn[] = "<th>Date</th>";
    $rtn[] = "<th>Person</th>";
    $rtn[] = "<th>Comment</th>";
    $rtn[] = "<th>Hours</th>";
    $rtn[] ="</tr>";
    return implode("\n",$rtn);
  }

  function get_list_summary_body($row) {
    global $TPL;
    $TPL["row"] = $row;
    return include_template(dirname(__FILE__)."/../templates/summaryR.tpl", true);
  }

  function get_list_summary_footer($rows,$tasks) {
    $rtn[] = "</table>";
    return implode("\n",$rtn);
  }



}



?>
