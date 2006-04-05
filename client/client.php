<?php
include("alloc.inc");

$client = new client;
if (isset($save)) {
  if ($clientName == "") {
    $TPL["error"] = "Please enter a company name.";
  }
  $client->read_globals();
  $client->set_value("clientModifiedTime", date("Y-m-d"));
  if (!$client->get_id()) {
    // New client.

    $TPL["createGeneralSupportProject"] = "<tr> <td colspan=\"2\"><b>Don't Create General Support Project</b></td>
        <td><input type=\"checkbox\" name=\"dontCreateProject\" checked=\"yes\"/></td></tr>";
					     
    
    $client->set_value("clientCreatedTime", date("Y-m-d"));
    $new_client = true;
 }
  $client->save();
  $clientID = $client->get_id();
  $client->set_tpl_values(DST_HTML_ATTRIBUTE, "client_");
  
  if ($new_client == true && $dontCreateProject != "on") {
     // Create the <Client Name> - General Support
    $project = new project;
    $project->set_value("projectName", $client->get_value("clientName")." - General Support");
    $project->set_value("clientID", $clientID);
    $project->set_value("projectType", "contract");
    $project->set_value("projectStatus", "current");
    //TODO: Find out what other stuff to add.
    $project->save();

    //now, add current user as a projectperson
    $projectperson = new projectperson;
    $projectperson->set_value("personID", $auth->auth['uid']);
    $projectperson->set_value("projectID", $project->get_id());
    $projectperson->set_value("emailEmptyTaskList", true);
    $projectperson->set_value_role("isManager");
    $projectperson->save();

  }    
  
} else if (isset($save_attachment)) {

  if ($attachment != "none") {
    is_uploaded_file($attachment) || die("Uploaded document error.  Please try again.");

    if (!is_dir($TPL["url_alloc_clientDocs_dir"].$clientID)) {
      mkdir($TPL["url_alloc_clientDocs_dir"].$clientID, 0777);
    }

    if (!move_uploaded_file($attachment, $TPL["url_alloc_clientDocs_dir"].$clientID."/".$attachment_name)) {
      die("could not move attachment to: ".$TPL["url_alloc_clientDocs_dir"].$clientID."/".$attachment_name);
    } else {
      chmod($TPL["url_alloc_clientDocs_dir"].$clientID."/".$attachment_name, 0777);
      header("Location: ".$TPL["url_alloc_client"]."&clientID=".$clientID);
    }
  }


} else {

  if (isset($delete)) {
    $client->read_globals();
    // delete all contacts and comments linked with this client as well
    $db = new db_alloc;
    $query = sprintf("SELECT * FROM clientContact WHERE clientID=%d", $client->get_id());
    $db->query($query);
    while ($db->next_record()) {
      $clientContact = new clientContact;
      $clientContact->read_db_record($db);
      $clientContact->delete();
    }
    $query = sprintf("SELECT * FROM comment WHERE commentLinkID=%d", $client->get_id());
    $db->query($query);
    while ($db->next_record()) {
      $comment = new comment;
      $comment->read_db_record($db);
      $comment->delete();
    }
    $client->delete();
    header("location: ".$TPL["url_alloc_clientList"]);
  } else {
    $client->set_id($clientID);
    $client->select();
  }

  $client->set_tpl_values(DST_HTML_ATTRIBUTE, "client_");
}

$clientStatus_array = array("current"=>"Current", "potential"=>"Potential", "archived"=>"Archived");
$TPL["clientStatusOptions"] = get_options_from_array($clientStatus_array, $client->get_value("clientStatus"));

  // client contacts
if (isset($clientContact_save) || isset($clientContact_add) || isset($clientContact_addpc)
    || isset($clientContact_makepc) || isset($clientContact_delete)) {
  $clientContact = new clientContact;
  $clientContact->read_globals();
  if (isset($clientContact_add) || isset($clientContact_addpc)) {
    $clientContact->set_value('clientID', $clientID);


    if ($clientContact->get_value('clientContactEmail') && preg_match("/<(.*)>/",$clientContact->get_value('clientContactEmail'),$matches)) {
      $clientContact->set_value('clientContactEmail',$matches[1]);
    }

    $clientContact->save();
    if (isset($clientContact_addpc)) {
      $client->set_value('clientPrimaryContactID', $clientContact->get_id());
      $client->save();
    }
  }
  if (isset($clientContact_save)) {
    $clientContact->save();
  }
  if (isset($clientContact_delete)) {
    $clientContact->delete();
  }
  if (isset($clientContact_makepc)) {
    $client->set_value('clientPrimaryContactID', $clientContact->get_id());
    $client->save();
  }
}
  // client comments
if (isset($clientComment_save) || isset($clientComment_update)) {
  $comment = new comment;
  $comment->set_value('commentType', 'client');
  $comment->set_value('commentLinkID', $clientID);
  $comment->set_modified_time();
  $comment->set_value('commentModifiedUser', $auth->auth["uid"]);

  if (isset($clientComment_update)) {
    $comment->set_id($clientComment_id);
  }
  if (isset($clientComment)) {
    $comment->set_value('comment', $clientComment);
    $comment->save();
  }
}
if (isset($clientComment_delete) && isset($clientComment_id)) {
  $comment = new comment;
  $comment->set_id($clientComment_id);
  $comment->delete();
}
if (isset($clientComment_cancel)) {
  unset($commentID);
}

global $clientComment_edit;
if (isset($commentID) && $clientComment_edit) {
  $comment = new comment();
  $comment->set_id($commentID);
  $comment->select();
  $TPL["client_clientComment"] = $comment->get_value('comment');
  $TPL["client_clientComment_buttons"] =
    sprintf("<input type=\"hidden\" name=\"clientComment_id\" value=\"%d\">", $commentID)
           ."<input type=\"submit\" name=\"clientComment_update\" value=\"Save Comment\">";
} else {
  $TPL["client_clientComment_buttons"] = "<input type=\"submit\" name=\"clientComment_save\" value=\"Save Comment\">";
}

include_template("templates/clientM.tpl");

  function check_optional_client_exists() {
    global $clientID;
    return isset($clientID);
  }

  function show_client_details_edit($template) {
    global $TPL, $clientID, $client_edit;

    $TPL["clientContactItem_buttons"] = "<input type=\"submit\" name=\"clientContact_save\" value=\"Save\">"."<input type=\"submit\" name=\"clientContact_delete\" value=\"Delete\">";

    if (!isset($clientID) || isset($client_edit)) {
      // if new client
      if (!isset($clientID)) {
        $TPL["clientDetails_buttons"] = "<input type=\"submit\" name=\"save\" value=\"&nbsp;&nbsp;&nbsp;Add&nbsp;&nbsp;&nbsp;\">";
        // new support project text box should be visible.
        $TPL["createGeneralSupportProject"] = "<tr> <td colspan=\"2\"><b>Don't Create General Support Project</b></td>
                <td><input type=\"checkbox\" name=\"dontCreateProject\" checked=\"yes\"/></td></tr>";
          
        // else if just editing
      } else {
        $TPL["clientDetails_buttons"] =
          "<input type=\"submit\" name=\"save\" value=\"&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;\">".
          "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".
          "<input type=\"submit\" name=\"cancel\" value=\"&nbsp;&nbsp;&nbsp;Cancel&nbsp;&nbsp;&nbsp;\">"."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"."<input type=\"submit\" name=\"delete\" value=\"Delete Record\" onClick=\"return confirm('Are you sure you want to delete this record?')\">";
        // new support project check box should NOT be visible.
        $TPL["createGeneralSupportProject"] = "";
      
      }
      include_template($template);
    }
  }

  function show_client_details($template) {
    global $TPL, $client, $clientID, $client_edit;
    if (isset($clientID) && !isset($client_edit)) {
      // setup formatted address output

      // postal address
      $TPL["client_clientPostalAddress"] = $client->get_value('clientName')."<br>".format_address($client->get_value('clientStreetAddressOne'), $client->get_value('clientSuburbOne'), $client->get_value('clientStateOne'), $client->get_value('clientPostcodeOne'), $client->get_value('clientCountryOne'));
      // street address
      $TPL["client_clientStreetAddress"] = $client->get_value('clientName')."<br>".format_address($client->get_value('clientStreetAddressTwo'), $client->get_value('clientSuburbTwo'), $client->get_value('clientStateTwo'), $client->get_value('clientPostcodeTwo'), $client->get_value('clientCountryTwo'));
      include_template($template);
    }
  }

  function format_address($address, $suburb, $state, $postcode, $country) {
    if ($address != "") {
      $a = $address;
      if ($suburb != "") {
        $a.= "<br>".$suburb;
      }
      if ($state != "") {
        $a.= "<br>".$state;
        if ($postcode != "") {
          $a.= ", ".$postcode;
        }
      }
      if ($country != "") {
        $a.= "<br>".$country;
      }
    } else {
      $a = "(no address)";
    }
    return $a;
  }

  function show_client_contacts($template) {
    global $TPL, $clientID;

    $TPL["clientContactItem_buttons"] = "<input type=\"submit\" name=\"clientContact_save\" value=\"Save\">"."<input type=\"submit\" name=\"clientContact_delete\" value=\"Delete\">";

    // get primary contact first
    $client = new client;
    $client->set_id($clientID);
    $client->select();
    if ($client->get_id('clientPrimaryContactID') != "NULL") {
      $clientContact = new clientContact;
      $clientContact->set_id($client->get_value('clientPrimaryContactID'));
      $clientContact->select();
      if ($clientContact->get_value('clientContactName') != "") {
        $clientContact->set_tpl_values(DST_HTML_ATTRIBUTE, "client_");
        $TPL["client_clientTitle"] = "Primary: ".$clientContact->get_value('clientContactName');
        if ($TPL["client_clientContactEmail"]) {
          $TPL["client_clientTitle"] .= "&nbsp;&nbsp;<a href=\"mailto:".$TPL["client_clientContactEmail"]."\">".$TPL["client_clientContactEmail"]."</a>";
        }
        $TPL["client_clientContactID"] = $clientContact->get_id();

        include_template($template);
      }
    }

    $TPL["clientContactItem_buttons"] = "<input type=\"submit\" name=\"clientContact_save\" value=\"Save\">"."<input type=\"submit\" name=\"clientContact_makepc\" value=\"Make Primary Contact\">"."<input type=\"submit\" name=\"clientContact_delete\" value=\"Delete\">";

    // other contacts
    $query = "SELECT * FROM clientContact";
    $query.= sprintf(" WHERE clientID=%d AND clientContactID!=%d", $clientID, $client->get_value('clientPrimaryContactID'));
    $query.= " ORDER BY clientContactName";
    $db = new db_alloc;
    $db->query($query);
    while ($db->next_record()) {
      $clientContact = new clientContact;
      $clientContact->read_db_record($db);
      $clientContact->set_tpl_values(DST_HTML_ATTRIBUTE, "client_");
      $TPL["client_clientTitle"] = $clientContact->get_value('clientContactName');
      if ($TPL["client_clientContactEmail"]) {
        $TPL["client_clientTitle"] .= "&nbsp;&nbsp;<a href=\"mailto:".$TPL["client_clientContactEmail"]."\">".$TPL["client_clientContactEmail"]."</a>";
      }
      $TPL["client_clientContactID"] = $clientContact->get_id();

      include_template($template);
    }

    // place blank client contact form at the end with add button for adding new contacts

    $TPL["clientContactItem_buttons"] = "<input type=\"submit\" name=\"clientContact_addpc\" value=\"Add as Primary Contact\">"."<input type=\"submit\" name=\"clientContact_add\" value=\"Add\">";

    $clientContact = new clientContact;
    $clientContact->set_tpl_values(DST_HTML_ATTRIBUTE, "client_");
    $TPL["client_clientID"] = $clientID;
    $TPL["client_clientTitle"] = "Add New Contact";
    include_template($template);
  }

  function show_reminders($template) {
    global $TPL, $clientID, $reminderID, $auth;

    // show all reminders for this project
    $reminder = new reminder;
    $db = new db_alloc;
    $permissions = explode(",", $auth->auth["perm"]);
    if (in_array("admin", $permissions) || in_array("manage", $permissions)) {
      $query = sprintf("SELECT * FROM reminder WHERE reminderType='client' AND reminderLinkID=%d", $clientID);
    } else {
      $query = sprintf("SELECT * FROM reminder WHERE reminderType='client' AND reminderLinkID=%d AND personID='%s'", $clientID, $auth->auth["uid"]);
    }
    $db->query($query);
    while ($db->next_record()) {
      $reminder->read_db_record($db);
      $reminder->set_tpl_values(DST_HTML_ATTRIBUTE, "reminder_");
      if ($reminder->get_value('reminderRecuringInterval') == "No") {
        $TPL["reminder_reminderRecurence"] = "&nbsp;";
      } else {
        $TPL["reminder_reminderRecurence"] = "Every ".$reminder->get_value('reminderRecuringValue')
          ." ".$reminder->get_value('reminderRecuringInterval')."(s)";
      }
      $person = new person;
      $person->set_id($reminder->get_value('personID'));
      $person->select();
      $TPL["reminder_reminderRecipient"] = $person->get_value('username');
      $TPL["returnToParent"] = "t";

      include_template($template);
    }
  }

  function show_projects() {

    global $clientID;

    if (!isset($clientID)) {
      return;
    }

    $db_projects = new db_alloc;
    $query = sprintf("SELECT * from project where clientID=%d", $clientID);
    $db_projects->query($query);

    while ($db_projects->next_record()) {

      $project = new project;
      $project->read_db_record($db_projects);
      $project->set_tpl_values(DST_HTML_ATTRIBUTE, "project_");

      include_template("templates/projectListR.tpl");

    }
  }

  function show_attachments($template) {
    global $TPL, $clientID;
    $TPL["clientID"] = $clientID;
    include_template($template);
  }

  function list_attachments($template) {

    global $TPL, $clientID;

    if (is_dir($TPL["url_alloc_clientDocs_dir"].$clientID)) {
      $handle = opendir($TPL["url_alloc_clientDocs_dir"].$clientID);

      while (false !== ($file = readdir($handle))) {

        if ($file != "." && $file != "..") {
          $size = filesize($TPL["url_alloc_clientDocs_dir"].$clientID."/".$file);
          $TPL["filename"] = "<a href=\"".$TPL["url_alloc_clientDoc"]."&clientID=".$clientID."&file=".urlencode($file)."\">".$file."</a>";
          $TPL["size"] = sprintf("%dk",$size/1024);
          include_template($template);
        }
      }
    }
  }

  function show_comments($template) {
    global $TPL, $clientID, $commentID, $view, $clientCommentTemplateID, $current_user, $auth;

    
    // setup add/edit comment section values
    $TPL["client_clientID"] = $clientID;
    $TPL["client_clientComment"] = "";

    // Init
    $rows = array();


    // Get list of comments
    $query = sprintf("SELECT commentID, commentLinkID, commentModifiedTime AS date, comment, commentModifiedUser AS personID
                        FROM comment 
                       WHERE comment.commentType = 'client' AND comment.commentLinkID = %d
                    ORDER BY comment.commentModifiedTime", $clientID);
    $db = new db_alloc;
    $db->query($query);
    while ($db->next_record()) {
      $comment = new comment;
      $comment->read_db_record($db);
      $comment->set_tpl_values(DST_HTML_ATTRIBUTE, "client_");
      $person = new person;
      $person->set_id($db->f("personID"));
      $person->select();
      $person->set_tpl_values(DST_HTML_ATTRIBUTE, "client_");

      $TPL["comment_buttons"] = "";
      if ($db->f("personID") == $auth->auth["uid"]) {
        $TPL["comment_buttons"] = "<nobr><input type=\"submit\" name=\"clientComment_edit\" value=\"Edit\">
                                         <input type=\"submit\" name=\"clientComment_delete\" value=\"Delete\"></nobr>";
      }

      $TPL["client_username"] = $person->get_username(1);

      // trim comment to 128 characters
      if (strlen($comment->get_value("comment")) > 3000 && $view != "printer") {
        $TPL["client_comment_trimmed"] = nl2br(sprintf("%s...", substr($comment->get_value("comment"), 0, 3000)));
      } else {
        $TPL["client_comment_trimmed"] = str_replace("\n", "<br>", htmlentities($comment->get_value("comment")));
      }

      if (!$commentID || $commentID != $comment->get_id()) {
        include_template($template);
      }
    }
  }

page_close();



?>
