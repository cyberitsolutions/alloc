<?php
include("alloc.inc");

global $needle, $sel, $TPL;

function search_projects($template) {
  global $TPL, $search, $needle, $category;

  if (!$search) {
    print "<BR><BR>
	   <B>Searching Announcements</B> looks for a match in each <BR>
	   Announcement's Heading and Body.<BR><BR>";
    print "<B>Searching Clients</B> looks for a match in each Client's<BR> 
	   Client Name, Contact Name, and Comments. <BR><BR>";
    print "<B>Searching Items</B> looks for a matching Item Name.<BR><BR>";
    print "<B>Searching Projects</B> looks for a match in each Project's <BR>
	   Project Name, Client Name, and Comments.	<BR><BR>";
    print "<B>Searching Tasks</B> looks for a match in each Task's <BR>
	   Task Name, Task Description, and Comments. <BR><BR>";
  }

  if (isset($search) && $needle != "" && $category == "Projects") {
    // need to search tables: project, comment;
    $db = new db_alloc;

    $query = "SELECT *, count(*) AS rank 
                FROM project 
           LEFT JOIN comment ON project.projectID=comment.commentLinkID 
               WHERE (project.projectName LIKE '%".$needle."%'"." OR project.projectComments LIKE '%".$needle."%'"." OR project.projectClientName LIKE '%".$needle."%')
                  OR (comment.comment LIKE '%".$needle."%' AND comment.commentType='project') 
            GROUP BY project.projectID 
            ORDER BY rank DESC,project.projectName";

    $db->query($query);
    while ($db->next_record()) {
      $details = "";
      $project = new project;
      $project->read_db_record($db);
      $project->set_tpl_values(DST_HTML_ATTRIBUTE, "project_");

      $projectName = get_trimmed_description($project->get_value('projectName'), $needle);
      if ($projectName != "") {
        $details.= "<b>Project Name:</b> ".$projectName."<br>\n";
      }
      $projectComments = get_trimmed_description($project->get_value('projectComments'), $needle);
      if ($projectComments != "") {
        $details.= "<b>Project Comments:</b> ".$projectComments."<br>\n";
      }
      $projectClientName = get_trimmed_description($project->get_value('projectClientName'), $needle);
      if ($projectClientName != "") {
        $details.= "<b>Project Client Name:</b> ".$projectClientName."<br>\n";
      }

      if ($project->get_id() != "") {
        // recursively search comments
        $db2 = new db_alloc;
        $query = "SELECT * FROM comment 
                   WHERE commentType='project' 
                     AND commentLinkID = ".$project->get_id()."  
                     AND comment LIKE '%".$needle."%'";

        $db2->query($query);
        while ($db2->next_record()) {
          $comment = new comment;
          $comment->read_db_record($db2);
          $commentText = get_trimmed_description($comment->get_value('comment'), $needle);
          if ($commentText != "") {
            $details.= "<b>Modification History:</b> ".$commentText."<br>\n";
          }
        }
        $TPL["project_searchDetails"] = $details;

        include_template($template);
      }
    }
  }
}

function search_clients($template) {
  global $TPL, $search, $needle, $category;

  print "";

  if (isset($search) && $needle != "" && $category == "Clients") {
    // need to search tables: client, clientContact, comment;
    $db = new db_alloc;
    $query = "SELECT *,count(*) AS rank, client.clientID as clientID 
                FROM client 
           LEFT JOIN clientContact ON client.clientID=clientContact.clientID 
           LEFT JOIN comment ON client.clientID=comment.commentLinkID 
               WHERE (client.clientName LIKE '%".$needle."%')
                  OR (clientContact.clientContactName LIKE '%".$needle."%' OR clientContact.clientContactOther LIKE '%".$needle."%') 
                  OR (comment.comment LIKE '%".$needle."%' AND comment.commentType='client') 
            GROUP BY client.clientID 
            ORDER BY rank DESC,client.clientName";

    $db->query($query);

      // . " OR client.clientStreetAddressOne LIKE '%" . $needle . "%'"
      // . " OR client.clientStreetAddressTwo LIKE '%" . $needle . "%'"
      // . " OR client.clientSuburbOne LIKE '%" . $needle . "%'"
      // . " OR client.clientSuburbTwo LIKE '%" . $needle . "%'"
      // . " OR client.clientStateOne LIKE '%" . $needle . "%'"
      // . " OR client.clientStateTwo LIKE '%" . $needle . "%'"
      // . " OR client.clientPostcodeOne LIKE '%" . $needle . "%'"
      // . " OR client.clientPostcodeTwo LIKE '%" . $needle . "%'"
      // . " OR client.clientPhoneOne LIKE '%" . $needle . "%'"
      // . " OR client.clientFaxOne LIKE '%" . $needle . "%'"
      // . " OR client.clientCountryOne LIKE '%" . $needle . "%'"
      // . " OR client.clientCountryTwo LIKE '%" . $needle . "%')"
      // . " OR clientContact.clientContactStreetAddress LIKE '%" . $needle . "%'"
      // . " OR clientContact.clientContactSuburb LIKE '%" . $needle . "%'"
      // . " OR clientContact.clientContactState LIKE '%" . $needle . "%'"
      // . " OR clientContact.clientContactPostcode LIKE '%" . $needle . "%'"
      // . " OR clientContact.clientContactPhone LIKE '%" . $needle . "%'"
      // . " OR clientContact.clientContactMobile LIKE '%" . $needle . "%'"
      // . " OR clientContact.clientContactFax LIKE '%" . $needle . "%'"
      // . " OR clientContact.clientContactEmail LIKE '%" . $needle . "%'"
    while ($db->next_record()) {
      $details = array();
      $client = new client;
      $client->read_db_record($db);
      $client->set_tpl_values(DST_HTML_ATTRIBUTE, "client_");

      $clientName = get_trimmed_description($client->get_value('clientName'), $needle);
      if ($clientName != "") {
        $details[] = "<b>Client Name: </b>".$clientName;
      }

      $db2 = new db_alloc;
      // recursively search contacts
      $query = "SELECT * FROM clientContact WHERE clientID = ".$client->get_id();
/*((clientContactName LIKE '%".$needle."%' OR clientContactOther LIKE '%".$needle."%') AND clientID = ".$client->get_id().") */

      $db2->query($query);
      while ($db2->next_record()) {
        $str = "";
        $clientContact = new clientContact;
        $clientContact->read_db_record($db2);

        $clientContactName = get_trimmed_description($clientContact->get_value('clientContactName'), $needle);
        #$clientContactName = $clientContact->get_value('clientContactName');
        if ($clientContactName != "") {
          $str = "<b>Contact Name: </b>".$clientContactName;
          if ($clientContact->get_value("clientContactEmail")) {
            $str .= "&nbsp;&nbsp;<a href=\"mailto:".$clientContact->get_value("clientContactEmail")."\">".$clientContact->get_value("clientContactEmail")."</a>";
          }
        }
        $clientContactOther = get_trimmed_description($clientContact->get_value('clientContactOther'), $needle);
        if ($clientContactOther != "") {
          $str.= "&nbsp;&nbsp;".$clientContactOther;
        }
        $details[] = $str;
      }

      if ($client->get_id() != "") {
        // recursively search comments
        $query = "SELECT * from comment WHERE commentType='client' AND commentLinkID = ".$client->get_id()." AND comment LIKE '%".$needle."%'";
        $db2->query($query);
        while ($db2->next_record()) {
          $comment = new comment;
          $comment->read_db_record($db2);
          $commentText = get_trimmed_description($comment->get_value('comment'), $needle);
          if ($commentText != "") {
            $details[] = "<b>Comment: </b>".$commentText;
          }
        }
        if (count($details)) 
        $TPL["client_searchDetails"] = implode("<br/>",$details)."<br/>";

        include_template($template);
      }
    }
  }
}

function search_taskID($template) {
  global $TPL, $search, $needle, $category;
  if (isset($search) && $needle != "" && $category == "TaskID") {
    $db = new db_alloc;
    $query = sprintf("SELECT *,count(*) AS rank FROM task WHERE taskID = %d GROUP BY task.taskID"
                    ,$needle);
    $db->query($query);
    while ($db->next_record()) {
       $task = new task;
      $task->read_db_record($db);
      $task->set_tpl_values(DST_HTML_ATTRIBUTE, "task_");
      $project = new project;
      $project->set_id($task->get_value('projectID'));
      $project->select();
      $project->set_tpl_values(DST_HTML_ATTRIBUTE, "project_");
      $taskName = get_trimmed_description($task->get_value('taskName'), $needle);
      if ($taskName != "") {
        $details.= "<b>Task Name:</b> ".$taskName."<br>\n";
      }
      $taskDescription = get_trimmed_description($task->get_value('taskDescription'), $needle);
      if ($taskDescription != "") {
        $details.= "<b>Task Description:</b> ".$taskDescription."<br>\n";
      }
      $TPL["task_searchDetails"] = $details;

      include_template($template);


    }
  }
}

function search_tasks($template) {
  global $TPL, $search, $needle, $category;
  if (isset($search) && $needle != "" && $category == "Tasks") {
    // need to search tables: task;
    $db = new db_alloc;
    $query = "SELECT *,count(*) AS rank FROM task"." LEFT JOIN comment ON task.taskID=comment.commentLinkID"." WHERE ("."taskName LIKE '%".$needle."%'"
      // . " OR taskComments LIKE '%" . $needle . "%'"
      ." OR taskDescription LIKE '%".$needle."%')"." OR (comment.comment LIKE '%".$needle."%' AND comment.commentType='task')"." GROUP BY task.taskID"." ORDER BY rank DESC,task.taskName";
    $db->query($query);
    while ($db->next_record()) {
      $details = "";
      $task = new task;
      $task->read_db_record($db);
      $task->set_tpl_values(DST_HTML_ATTRIBUTE, "task_");
      $project = new project;
      $project->set_id($task->get_value('projectID'));
      $project->select();
      $project->set_tpl_values(DST_HTML_ATTRIBUTE, "project_");

      $taskName = get_trimmed_description($task->get_value('taskName'), $needle);
      if ($taskName != "") {
        $details.= "<b>Task Name:</b> ".$taskName."<br>\n";
      }
      $taskDescription = get_trimmed_description($task->get_value('taskDescription'), $needle);
      if ($taskDescription != "") {
        $details.= "<b>Task Description:</b> ".$taskDescription."<br>\n";
      }
      // $taskComment = get_trimmed_description($task->get_value('taskComment'), $needle);
      // if($taskComment != "") {
      // $details .= "<b>Task Comment:</b> " . $taskComment . "<br>\n";
      // }

      if ($task->get_id() != "") {
        // recursively search comments
        $db2 = new db_alloc;
        $query = "SELECT * from comment WHERE commentType='task' AND commentLinkID = ".$task->get_id()." AND comment LIKE '%".$needle."%'";
        $db2->query($query);
        while ($db2->next_record()) {
          $comment = new comment;
          $comment->read_db_record($db2);
          $commentText = get_trimmed_description($comment->get_value('comment'), $needle);
          if ($commentText != "") {
            $details.= "<b>Comment:</b> ".$commentText."<br>\n";
          }
        }
        $TPL["task_searchDetails"] = $details;

        include_template($template);
      }
    }
  }
}

function search_announcements($template) {
  global $TPL, $search, $needle, $category;

  if (isset($search) && $needle != "" && $category == "Announcements") {
    // need to search tables: announcement;
    $db = new db_alloc;
    $query = "SELECT * FROM announcement WHERE ("."heading LIKE '%".$needle."%'"." OR body LIKE '%".$needle."%')";
    $db->query($query);
    while ($db->next_record()) {
      $announcement = new announcement;
      $announcement->read_db_record($db);
      $announcement->set_tpl_values(DST_HTML_ATTRIBUTE, "announcement_");

      $heading = get_trimmed_description($announcement->get_value('heading'), $needle);
      if ($heading != "") {
        $TPL["announcement_heading"] = $heading;
      }
      $body = get_trimmed_description($announcement->get_value('body'), $needle);
      if ($body != "") {
        $TPL["announcement_body"] = $body;
      }

      if ($announcement->get_id() != "") {
        include_template($template);
      }
    }
  }
}

function search_items($template) {
  global $TPL, $search, $needle, $category;

  if (isset($search) && $needle != "" && $category == "Items") {

    $today = date("Y")."-".date("m")."-".date("d");

    // need to search tables: item;
    $db = new db_alloc;
    $query = "SELECT * FROM item WHERE ("."itemName LIKE '%".$needle."%'"." OR itemNotes LIKE '%".$needle."%')";
    $db->query($query);
    while ($db->next_record()) {
      $details = "";
      $item = new item;
      $item->read_db_record($db);
      $item->set_tpl_values(DST_HTML_ATTRIBUTE, "item_");

      $itemName = get_trimmed_description($item->get_value('itemName'), $needle);
      if ($itemName != "") {
        $details.= "<b>Item Name:</b> ".$itemName."<br>\n";
      }
      $itemNotes = get_trimmed_description($item->get_value('itemNotes'), $needle);
      if ($itemNotes != "") {
        $details.= "<b>Item Notes:</b> ".$itemNotes."<br>\n";
      }
      $TPL["item_searchDetails"] = $details;

      // get availability of loan
      $db2 = new db_alloc;
      $query = "SELECT * FROM loan WHERE itemID=".$item->get_id()." AND dateReturned='0000-00-00'";
      $db2->query($query);
      if ($db2->next_record()) {
        $loan = new loan;
        $loan->read_db_record($db2);

        if ($loan->have_perm(PERM_READ_WRITE)) {
          // if item is OVERDUE!!
          if ($loan->get_value("dateToBeReturned") < $today) {
            $status = "Overdue";
            $ret = "Return Now!";
          } else {
            $status = "Due on ".$loan->get_value("dateToBeReturned");
            $color = "yellow";
            $ret = "Return";
          }

          $TPL["loan_status"] = $status." <a href=\"".$TPL["url_alloc_item"]
            ."&itemID=".$TPL["item_itemID"]
            ."&return=true\">$ret</a>";
        } else {
          // you dont have permission to loan or return so just show status
          // get username
          $dbUsername = new db_alloc;
          $query = "SELECT username FROM person WHERE personID=".$loan->get_value("personID");
          $dbUsername->query($query);
          $dbUsername->next_record();

          if ($loan->get_value("dateToBeReturned") < $today) {
            $TPL["loan_status"] = "Overdue from ".$dbUsername->f("username");
          } else {
            $TPL["loan_status"] = "Due from ".$dbUsername->f("username")
              ." on ".$loan->get_value("dateToBeReturned");
          }
        }
      } else {
        $TPL["loan_status"] = "Available <a href=\"".$TPL["url_alloc_item"]
          ."&itemID=".$TPL["item_itemID"]."&borrow=true\">Borrow</a>";
      }

      include_template($template);
    }
  }
}

function get_trimmed_description($haystack, $needle) {


  $position = strpos(strtolower($haystack),strtolower($needle));
  if ($position) {
    $prefix = "...";
    $suffix = "...";

    // Nuke trailing ellipses if the string ends in the match
    if (strlen(substr($haystack,$position)) == strlen($needle)) {
      $suffix = "";
    }


    $buffer = 30;
    $position = $position - $buffer;
    // Reset position to zero cause negative number will make it wrap around, 
    // and nuke ellipses prefix because the string begins with the match
    if ($position < 0) {
      $position = 0;
      $prefix = "";
    }
    
    preg_match("/".$needle."/i",$haystack,$matches); // This gets the actual casse insensive match for search and replace

    $str = substr($haystack,$position,strlen($needle)+100);
    $str = str_replace($matches[0],"<em class=\"highlighted\">".$matches[0]."</em>",$str);
    $str = $prefix.$str.$suffix;
    return $str;
  }

  return $haystack;

  #$return_string = $haystack;
  #$strcount = spliti($needle, $haystack);
  #if (count($strcount) - 1 > 0) {
    #while ($temp = stristr($haystack, $needle)) {
      #$return_string.= htmlspecialchars(substr($haystack, 0, (strlen($haystack) - strlen($temp))), ENT_QUOTES)
                    #."<em class=\"highlighted\">".htmlspecialchars(substr($temp, 0, strlen($needle)), ENT_QUOTES)."</em>";
      #$haystack = substr($temp, strlen($needle), (strlen($temp) - strlen($needle)));
    #}
    #$return_string.= $haystack;
  #}
  #return $return_string;
}

  // setup generic values
$category_options = array("Tasks"=>"Tasks", "TaskID"=>"Task ID", "Announcements"=>"Announcements", "Clients"=>"Clients", "Items"=>"Items", "Projects"=>"Projects");
$TPL["category_options"] = get_options_from_array($category_options, $category);
$TPL["needle"] = $needle;

include_template("templates/searchM.tpl");
page_close();



?>
