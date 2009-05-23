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

require_once("../alloc.php");

global $TPL;


$noRedirect = $_POST["idRedirect"] or $_GET["idRedirect"];
$search = $_POST["search"] or $search = $_GET["search"];
$category = $_POST["category"] or $category = $_GET["category"];
$needle = trim($_POST["needle"]) or $needle = trim(urldecode($_GET["needle"]));
$needle_esc = db_esc($needle);

if (!$search) {
  $str = "<br/><br/>";
  $str.= "<b>Searching Tasks</b> looks for a match in each Task's Name, Description and Comments.<br /><br />";
  $str.= "<b>Searching Projects</b> looks for a match in each Project's Name, Client and Comments.<br /><br />";  
  $str.= "<b>Searching Time Sheets</b> looks for a match in each Time Sheets Billing Note, Comment and Project.<br /><br />";
  $str.= "<b>Searching Items</b> looks for a matching Item Name.<br /><br />";
  $str.= "<b>Searching Clients</b> looks for a match in each Client's Name, Contact Name, and Comments.<br /><br />";
  $str.= "<b>Redirection by ID</b> will cause a redirection to the task, project, etc. if the search key is a valid ID. Disable this to search for numerical strings.";
  $TPL["search_results"] = $str;


// Project Search (will search through project, client and comment)
} else if ($search && $needle && $category == "Projects") {

  $db = new db_alloc;

  if (!$noRedirect && is_numeric($needle)) {
    $query = sprintf("SELECT projectID FROM project WHERE projectID = %d",$needle);
    $db->query($query);
    if ($db->next_record()) {
      alloc_redirect($TPL["url_alloc_project"]."projectID=".$db->f("projectID"));
    } 

  } else {
    $query = "SELECT *, count(*) AS rank 
                FROM project 
           LEFT JOIN comment ON project.projectID = comment.commentLinkID 
               WHERE (project.projectName LIKE '%".$needle_esc."%' OR project.projectComments LIKE '%".$needle_esc."%' OR project.projectClientName LIKE '%".$needle_esc."%')
                  OR (comment.comment LIKE '%".$needle_esc."%' AND comment.commentType='project') 
            GROUP BY project.projectID 
            ORDER BY rank DESC,project.projectName";

    $db->query($query);
    while ($db->next_record()) {
      $details = "";
      $project = new project;
      $project->read_db_record($db);
      if ($project->have_perm(PERM_READ)) {
        $project->set_tpl_values(DST_HTML_ATTRIBUTE, "project_");

        $projectName = search::get_trimmed_description($project->get_value('projectName'), $needle, $category);
        $projectName and $details.= "<b>Project Name:</b> ".page::htmlentities($projectName)."<br>\n";

        $projectComments = search::get_trimmed_description($project->get_value('projectComments'), $needle, $category);
        $projectComments and $details.= "<b>Project Comments:</b> ".page::htmlentities($projectComments)."<br>\n";

        $projectClientName = search::get_trimmed_description($project->get_value('projectClientName'), $needle, $category);
        $projectClientName and $details.= "<b>Project Client Name:</b> ".page::htmlentities($projectClientName)."<br>\n";

        // Recursively search comments
        if ($project->get_id() != "") {
          $db2 = new db_alloc;
          $query = "SELECT * FROM comment 
                     WHERE commentType = 'project' 
                       AND commentLinkID = ".$project->get_id()."  
                       AND comment LIKE '%".$needle_esc."%'";

          $db2->query($query);
          while ($db2->next_record()) {
            $comment = new comment;
            $comment->read_db_record($db2);
            $commentText = search::get_trimmed_description($comment->get_value('comment'), $needle, $category);
            $commentText and $details.= "<b>Modification History:</b> ".page::htmlentities($commentText)."<br>\n";
          }

          $TPL["search_results"] .= "<b><a href=\"".$TPL["url_alloc_project"]."projectID=".$TPL["project_projectID"]."\">".page::htmlentities($TPL["project_projectName"])."</b></a><br>".$details."<br>";
        }
      }
    }
  }

// Clients Search (will search through client, clientContact and comment)
} else if ($search && $needle && $category == "Clients") {

  $db = new db_alloc;

  if (!$noRedirect && is_numeric($needle)) {
    $query = sprintf("SELECT clientID FROM client WHERE clientID = %d",$needle);
    $db->query($query);
    if ($db->next_record()) {
      alloc_redirect($TPL["url_alloc_client"]."clientID=".$db->f("clientID"));
    } 
    
  } else {
    $query = "SELECT *,count(*) AS rank, client.clientID as clientID 
                FROM client 
           LEFT JOIN clientContact ON client.clientID=clientContact.clientID 
           LEFT JOIN comment ON client.clientID=comment.commentLinkID 
               WHERE (client.clientName LIKE '%".$needle_esc."%')
                  OR (clientContact.clientContactName LIKE '%".$needle_esc."%' OR clientContact.clientContactOther LIKE '%".$needle_esc."%') 
                  OR (comment.comment LIKE '%".$needle_esc."%' AND comment.commentType='client') 
            GROUP BY client.clientID 
            ORDER BY rank DESC,client.clientName";

    $db->query($query);

    while ($db->next_record()) {
      $details = array();
      $client = new client;
      $client->read_db_record($db);

      if ($client->have_perm(PERM_READ)) {
        $client->set_tpl_values(DST_HTML_ATTRIBUTE, "client_");

        $clientName = search::get_trimmed_description($client->get_value('clientName'), $needle, $category);
        $clientName and $details[] = "<b>Client Name: </b>".page::htmlentities($clientName);

        $db2 = new db_alloc;
        $query = sprintf("SELECT * FROM clientContact WHERE clientID = %d",$client->get_id());

        $db2->query($query);
        while ($db2->next_record()) {
          $str = "";
          $clientContact = new clientContact;
          $clientContact->read_db_record($db2);

          $clientContactName = search::get_trimmed_description($clientContact->get_value('clientContactName'), $needle, $category);
          #$clientContactName = $clientContact->get_value('clientContactName');
          if ($clientContactName != "") {
            $str = "<b>Contact Name: </b>".page::htmlentities($clientContactName);
            if ($clientContact->get_value("clientContactEmail")) {
              $str .= "&nbsp;&nbsp;<a href=\"mailto:".$clientContact->get_value("clientContactEmail")."\">".page::htmlentities($clientContact->get_value("clientContactEmail"))."</a>";
            }
          }
          $clientContactOther = search::get_trimmed_description($clientContact->get_value('clientContactOther'), $needle, $category);
          if ($clientContactOther != "") {
            $str.= "&nbsp;&nbsp;".page::htmlentities($clientContactOther);
          }
          $str and $details[] = $str;
        }

        if ($client->get_id() != "") {
          // recursively search comments
          $query = "SELECT * from comment WHERE commentType='client' AND commentLinkID = ".$client->get_id()." AND comment LIKE '%".$needle_esc."%'";
          $db2->query($query);
          while ($db2->next_record()) {
            $comment = new comment;
            $comment->read_db_record($db2);
            $commentText = search::get_trimmed_description($comment->get_value('comment'), $needle, $category);
            if ($commentText != "") {
              $details[] = "<b>Comment: </b>".page::htmlentities($commentText);
            }
          }
          if (count($details)) {
            $TPL["search_results"] .= "<b><a href=\"".$TPL["url_alloc_client"]."clientID=".$TPL["client_clientID"]."\">".page::htmlentities($TPL["client_clientName"]);
            $TPL["search_results"] .= "</a></b><br>".implode("<br/>",$details)."<br/>"."<br>";
          }

        }
      }
    }
  }

// Tasks Search (will search through task, comments)
} else if ($search && $needle && $category == "Tasks") {

  // need to search tables: task;
  $db = new db_alloc;

  if (!$noRedirect && is_numeric($needle)) {
    $query = sprintf("SELECT taskID FROM task WHERE taskID = %d",$needle);
    $db->query($query);
    if ($db->next_record()) {
      alloc_redirect($TPL["url_alloc_task"]."taskID=".$db->f("taskID"));
    } 

  } else {
    $query = "SELECT *,count(*) AS rank 
                FROM task 
           LEFT JOIN comment ON task.taskID = comment.commentLinkID 
               WHERE (taskName LIKE '%".$needle_esc."%' OR taskDescription LIKE '%".$needle_esc."%')
                  OR (comment.comment LIKE '%".$needle_esc."%' AND comment.commentType='task') 
            GROUP BY task.taskID 
            ORDER BY rank DESC,task.taskName";

    $db->query($query);

    while ($db->next_record()) {
      $details = "";
      $task = new task;
      $task->read_db_record($db);
      if ($task->have_perm(PERM_READ)) {
        $task->set_tpl_values(DST_HTML_ATTRIBUTE, "task_");
        $project = new project;
        $project->set_id($task->get_value('projectID'));
        $project->select();
        $project->set_tpl_values(DST_HTML_ATTRIBUTE, "project_");

        $taskName = search::get_trimmed_description($task->get_value('taskName'), $needle, $category);
        $taskName and $details.= "<b>Task Name:</b> ".page::htmlentities($taskName)."<br>\n";

        $taskDescription = search::get_trimmed_description($task->get_value('taskDescription'), $needle, $category);
        $taskDescription and $details.= "<b>Task Description:</b> ".page::htmlentities($taskDescription)."<br>\n";

        if ($task->get_id() != "") {
          $db2 = new db_alloc;
          $query = "SELECT * from comment WHERE commentType='task' AND commentLinkID = ".$task->get_id()." AND comment LIKE '%".$needle_esc."%'";
          $db2->query($query);
          while ($db2->next_record()) {
            $comment = new comment;
            $comment->read_db_record($db2);
            $commentText = search::get_trimmed_description($comment->get_value('comment'), $needle, $category);
            $commentText and $details.= "<b>Comment:</b> ".page::htmlentities($commentText)."<br>\n";
          }

          $TPL["search_results"] .= "<b><a href=\"".$TPL["url_alloc_task"]."taskID=".$TPL["task_taskID"]."\">".page::htmlentities($TPL["task_taskName"])."</b></a> (belongs to project: ";
          $TPL["search_results"] .= "<a href=\"".$TPL["url_alloc_project"]."projectID=".$TPL["project_projectID"]."\">".page::htmlentities($TPL["project_projectName"])."</a>)<br>".$details."<br>";
        }
      }
    }
  }


// Announcements Search (will search through announcements, comments)
} else if ($search && $needle && $category == "Announcements") {

  $db = new db_alloc;

  if (!$noRedirect && is_numeric($needle)) {
    $query = sprintf("SELECT announcementID FROM announcement WHERE announcementID = %d",$needle);
    $db->query($query);
    if ($db->next_record()) {
      alloc_redirect($TPL["url_alloc_announcement"]."announcementID=".$db->f("announcementID"));
    }

  } else {
    $query = "SELECT * FROM announcement WHERE (heading LIKE '%".$needle_esc."%' OR body LIKE '%".$needle_esc."%')";
    $db->query($query);
    while ($db->next_record()) {
      $announcement = new announcement;
      $announcement->read_db_record($db);
      if ($announcement->have_perm(PERM_READ)) {
        $announcement->set_tpl_values(DST_HTML_ATTRIBUTE, "announcement_");

        $heading = search::get_trimmed_description($announcement->get_value('heading'), $needle, $category);
        $body = search::get_trimmed_description($announcement->get_value('body'), $needle, $category);

        if ($announcement->get_id() != "") {
          $TPL["search_results"] .=  "<b>".page::htmlentities($heading)."</b><br>".page::htmlentities($body)."<br><br>";
        }
      }
    }
  }

// Item Search (will search through item) 
} else if ($search && $needle && $category == "Items") {

  $today = date("Y")."-".date("m")."-".date("d");

  // need to search tables: item;
  $db = new db_alloc;

  if (!$noRedirect && is_numeric($needle)) {
    $query = sprintf("SELECT itemID FROM item WHERE itemID = %d",$needle);
    $db->query($query);
    if ($db->next_record()) {
      alloc_redirect($TPL["url_alloc_item"]."itemID=".$db->f("itemID"));
    }

  } else {
    $query = "SELECT * FROM item WHERE (itemName LIKE '%".$needle_esc."%' OR itemNotes LIKE '%".$needle_esc."%')";
    $db->query($query);
    while ($db->next_record()) {
      $details = "";
      $item = new item;
      $item->read_db_record($db);

      if ($item->have_perm(PERM_READ)) {
        $item->set_tpl_values(DST_HTML_ATTRIBUTE, "item_");

        $itemName = search::get_trimmed_description($item->get_value('itemName'), $needle, $category);
        $itemName and $details.= "<b>Item Name:</b> ".page::htmlentities($itemName)."<br>\n";

        $itemNotes = search::get_trimmed_description($item->get_value('itemNotes'), $needle, $category);
        $itemNotes and $details.= "<b>Item Notes:</b> ".page::htmlentities($itemNotes)."<br>\n";

        $TPL["item_searchDetails"] = $details;

        // get availability of loan
        $db2 = new db_alloc;
        $query = sprintf("SELECT * FROM loan WHERE itemID = %d AND dateReturned='0000-00-00'",$item->get_id());
        $db2->query($query);
        if ($db2->next_record()) {
          $loan = new loan;
          $loan->read_db_record($db2);

          if ($loan->have_perm(PERM_READ_WRITE)) {
            // if item is overdue
            if ($loan->get_value("dateToBeReturned") < $today) {
              $status = "Overdue";
              $ret = "Return Now!";
            } else {
              $status = "Due on ".$loan->get_value("dateToBeReturned");
              $color = "yellow";
              $ret = "Return";
            }
            $TPL["loan_status"] = $status." <a href=\"".$TPL["url_alloc_item"]."itemID=".$TPL["item_itemID"]."&return=true\">$ret</a>";

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
              $TPL["loan_status"] = "Due from ".$dbUsername->f("username")." on ".$loan->get_value("dateToBeReturned");
            }
          }

        } else {
          $TPL["loan_status"] = "Available <a href=\"".$TPL["url_alloc_item"]."itemID=".$TPL["item_itemID"]."&borrow=true\">Borrow</a>";
        }
    
        $TPL["search_results"] .=  "<b>".page::htmlentities($TPL["item_itemName"])."</b> (".$TPL["loan_status"].")<br>".$TPL["item_searchDetails"]."<br>";
      }
    }
  }

} else if ($search && $needle && $category == "Time") {
  

  $db = new db_alloc;

  if (!$noRedirect && is_numeric($needle)) {
    $query = sprintf("SELECT timeSheetID FROM timeSheet WHERE timeSheetID = %d",$needle);
    $db->query($query);
    if ($db->next_record()) {
      alloc_redirect($TPL["url_alloc_timeSheet"]."timeSheetID=".$db->f("timeSheetID"));
    } 
    
  } else {
    $query = "SELECT timeSheet.*,timeSheetItem.*, project.* 
                FROM timeSheetItem
           LEFT JOIN timeSheet ON timeSheetItem.timeSheetID=timeSheet.timeSheetID
           LEFT JOIN project ON timeSheet.projectID=project.projectID
               WHERE (timeSheetItem.description LIKE '%".$needle_esc."%')
                  OR (timeSheetItem.comment LIKE '%".$needle_esc."%') 
                  OR (timeSheet.billingNote LIKE '%".$needle_esc."%') 
                  OR (project.projectName LIKE '%".$needle_esc."%') 
                  OR (project.projectShortName LIKE '%".$needle_esc."%') 
            GROUP BY timeSheet.timeSheetID";


    $db->query($query);

    while ($db->next_record()) {
      $details = array();
      $timeSheet = new timeSheet;
      $timeSheet->read_db_record($db,false);
      if ($timeSheet->have_perm(PERM_READ)) {
        $timeSheet->set_tpl_values(DST_HTML_ATTRIBUTE, "timeSheet_");

        $projectName = search::get_trimmed_description($db->f('projectName'), $needle, $category);
        $projectName and $details[] = "<b>Project Name: </b>".page::htmlentities($projectName);

        $projectShortName = search::get_trimmed_description($db->f('projectShortName'), $needle, $category);
        $projectShortName and $details[] = "<b>Project Short Name: </b>".page::htmlentities($projectShortName);

        $billingNote = search::get_trimmed_description($timeSheet->get_value('billingNote'), $needle, $category);
        $billingNote and $details[] = "<b>Billing Note: </b>".page::htmlentities($billingNote);

        $taskName = search::get_trimmed_description($db->f('description'), $needle, $category);
        $taskName and $details[] = "<b>Task Name: </b>".page::htmlentities($taskName);

        $taskComment = search::get_trimmed_description($db->f('comment'), $needle, $category);
        $taskComment and $details[] = "<b>Task Comment: </b>".page::htmlentities($taskComment);

        if (count($details)) {
          $TPL["search_results"] .= "<b><a href=\"".$TPL["url_alloc_timeSheet"]."timeSheetID=".$TPL["timeSheet_timeSheetID"]."\">Time Sheet: ".page::htmlentities($TPL["timeSheet_timeSheetID"]);
          $TPL["search_results"] .= "</a></b><br>".implode("<br/>",$details)."<br/>"."<br>";
        }
      }
    }
  }

} else if ($search && $needle && $category == "Wiki") {

  $allowed_suffixes = array("",".text",".txt",".html",".xml",".mdwn");

  $files = search::get_recursive_dir_list(wiki_module::get_wiki_path());
  foreach ($files as $file) {

    // check that the file is of an allowable type. This hopefully means we don't 
    // search through binaries etc. One day we'll have to implement this better...
    preg_match("/(\.\w{3,4}$)/",$file,$m);
    if (!in_array($m[1],$allowed_suffixes)) 
      continue;

    $matches = search::by_file($file,$needle);
    if ($matches) { 
      $f = str_replace(wiki_module::get_wiki_path(),"",$file);
      $TPL["search_results"].= "<br><br><a href='".$TPL["url_alloc_wiki"]."target=".urlencode($f)."'>".$f."</a><br>";
      foreach ($matches as $match) {
        $TPL["search_results"].= search::get_trimmed_description(page::htmlentities($match),$needle,$category);
      }
    }
    
  }
  

}


// setup generic values
$TPL["search_category_options"] = page::get_category_options($category);
$TPL["needle"] = $needle;
$TPL["needle2"] = $needle;
if (!$needle || $noRedirect) {
  $TPL["redir"] = "checked=\"1\"";
}

if ($TPL["search_results"]) {
  $TPL["search_results"] = str_replace("[[[","<em class=\"highlighted\">",$TPL["search_results"]);
  $TPL["search_results"] = str_replace("]]]","</em>",$TPL["search_results"]);
} else { 
  $TPL["search_results"] = "No records found.";
}

$TPL["main_alloc_title"] = "Search - ".APPLICATION_NAME;
include_template("templates/searchM.tpl");

?>
