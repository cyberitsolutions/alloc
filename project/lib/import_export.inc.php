<?php

/*
 * Copyright (C) 2006-2011 Alex Lance, Clancy Malcolm, Cyber IT Solutions
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

////IMPORT FUNCTIONS

define('CSV_EXPIRY', 60*30); //30 mins

function store_csv($file) {

  // Before storing it, go through the tmp directory and clean up any old files.
  // This is the only point where the number of files should increase, so this
  // is a good time to limit the expansion.
  $dir = ATTACHMENTS_DIR . 'tmp' . DIRECTORY_SEPARATOR;
  $files = glob($dir . "csv_*");

  $expiry = time() - CSV_EXPIRY;
  foreach ($files as $fn) {
    // read the timestamp out of the filename... or maybe stat it?
    $props = stat($fn);
    if ($props['mtime'] < $expiry) {
      unlink($fn);
    }
  }

  if (!is_uploaded_file($file)) {
    //client messing around
    error_log("CSV Upload: '$file' was not an uploaded file.");
    return false;
  }

  $filename = "csv_" . time();
  if (!move_uploaded_file($file, $dir . $filename)) {
    error_log("CSV Upload: Couldn't move file $file to " . $dir . $filename);
    return false;
  }

  return $filename;
}

// Sort out the list of interested parties and add them to the task.
function add_ips($parties, $taskID, $projectID) {
  // each ip can be either a username or an email address. If it's a username, 
  // look them up. If it's an email address, see if it belongs to anybody. If 
  // the email doesn't match a client contact, drop it - if it's added as an IP 
  // their name can't be changed later.
  
  foreach ($parties as $party) {
    $ipdata = array('entity' => 'task',
      'entityID' => $taskID);
    // same logic as the real interested parties code - if the name contains an 
    // '@', it's an email address. Otherwise, it's a login.
    if (strpos($party, '@') === false) {
      $person = import_find_username(array($party));
      if (!$person) {
        continue;
      }
      $ipdata['personID'] = $person->get_id();
      $ipdata['name'] = person::get_fullname($person->get_id());
      $ipdata['emailAddress'] = $person->get_value("emailAddress");
    } else {
      $ipdata['emailAddress'] = $party;
      // add_client_contact will figure out the rest
    }
    interestedparty::add_interested_party($ipdata);
  }
}

function import_csv($infile, $mapping, $header = true) {
  global $TPL;
  global $projectID;
  $current_user = &singleton("current_user");

  /* Make sure the user isn't messing around and inserting ../../ into the 
   * filename.
   */

  $rp = realpath(ATTACHMENTS_DIR.'tmp'.DIRECTORY_SEPARATOR.$infile);
  if ($rp === FALSE || strpos($rp, ATTACHMENTS_DIR.'tmp'.DIRECTORY_SEPARATOR) !== 0)
    alloc_error("Illegal file path.",true); //should occur through user dodginess

  $db = new db_alloc(); //import_find_username needs it
  //Import a CSV file
  $project = new project();
  $project->set_id($projectID);
  $project->select();

  $filename = $rp;
  $result = array();
  $result[0] = array();
  $fh = @fopen($filename, 'rb');
  if ($fh === FALSE) {
    $result[0] = "There was a problem reading the uploaded file.";
  } else {

    $line = 1;
    // Find the project manager
    $projectManager = $project->get_project_manager();
    if ($header) {
      fgetcsv($fh, 8192);               // Skip the header line
      $line++;
    }
    
    while($row = fgetcsv($fh, 8192)) {
      $warning = false;

      $task_result = array();

      $task = new task();
      $ips = array();
      for ($i = 0;$i < count($row);$i++) {
        switch($mapping[$i]) {
        case 'ignore':
          break;
        case 'name':
          $task->set_value('taskName', $row[$i]);
          break;
        case 'description':
          $task->set_value('taskDescription', $row[$i]);
          break;
        case 'assignee':
          $assignee = import_find_username(array($row[$i]));
          if($assignee) {
            $task->set_value('personID', $assignee->get_id());
          } else {
            //We don't know who the manager is, so assign it to the project manager
            $task->set_value('personID', $projectManager);
            $task_result []= sprintf('Warning: Unable to find a username corresponding to "%s", assigning task to project manager.', $row[$i]);
          }
          break;
        case 'manager':
          $manager = import_find_username(array($row[$i]));
          if($manager) {
            $task->set_value('managerID', $manager->get_id());
          } else {
            //We don't know who the manager is, so assign it to the project manager
            $task->set_value('managerID', $projectManager);
            $task_result []= sprintf('Warning: Unable to find a username corresponding to "%s", setting task manager to project manager.', $row[$i]);
          }
          break;
        case 'limit':
          $task->set_value('timeLimit', $row[$i]);
          break;
        case 'timeBest':
          $task->set_value('timeBest', $row[$i]);
          break;
        case 'timeWorst':
          $task->set_value('timeWorst', $row[$i]);
          break;
        case 'timeExpected':
          $task->set_value('timeExpected', $row[$i]);
          break;
        case 'startDate':
          $task->set_value('dateTargetStart', date("Y-m-d", strtotime($row[$i])));
          break;
        case 'completionDate':
          $task->set_value('dateTargetCompletion', date("Y-m-d", strtotime($row[$i])));
          break;
        case 'interestedParties':
          // Field is a grouped list of names
          $ips = explode(" ", $row[$i]);
          break;
	}
      }
      $task->set_value('projectID', $projectID);
      // if no manager set, use the uploader

      // Having a blank assignee actually works, but set it back to the 
      // project manager
      if (!$task->get_value('personID')) {
        $task->set_value('personID', $projectManager);
      }
      if (!$task->get_value('taskName')) {
        $result[0] []= "Line " . $line . ": Task has no name, creation failed.";
        $line++;
        continue;
      }
      // Hardcoded defaults
      $task->set_value('taskTypeID', 'Task');

      $task->set_value('priority', '3');
      $task->set_value('dateCreated', date('Y-m-d H:i:s'));
      $task->set_value('dateAssigned', date('Y-m-d H:i:s'));
      $task->set_value('taskStatus', 'open_notstarted');
      $task->save();

      $task_result []= "Task ".$task->get_value('taskName') . " created";

      // can only add interested parties after the task has an ID
      if ($ips) {
        add_ips($ips, $task->get_id(), $projectID);
      }

      $result[$task->get_id()] = $task_result;
      $line++;
    }

    fclose($fh);
    unlink($filename);
  }
  return $result;
}

function import_gnome_planner($infile) {
  global $TPL;
  global $projectID;
  $current_user = &singleton("current_user");
  //Import a GNOME Planner XML file
  $filename = $_FILES[$infile]['tmp_name'];
  $result = array();
  $fileIsValid = true;
  if(is_uploaded_file($filename)) {
    $doc = get_xml_document();
    if(@$doc->load($filename) === FALSE) {
      // Something's wrong with the file, bail out
      $result[] = "The file does not seem to be a valid GNOME Planner XML file.";
      $TPL['import_result'] = implode("<br>", $result);
      return;
    }
    // This function does two passes of each file. First, it does a set of basic tests to check the file is valid.
    // If it is, the function then actually imports the data.
    $result[] = "<li>Checking the file for validity...</li>";
    // Check that every <resource /> element has a short-name that corresponds to the user id of a user we know about
    $resource_people = array();
    $resources = $doc->getElementsByTagName("resource");
    for($i = 0; $i < $resources->length; $i++) {
      $resource = $resources->item($i);
      $user = import_find_username(array($resource->getAttribute("name"), $resource->getAttribute("short-name")));
      if(!$user) {
        $result[] = sprintf("Couldn't find the person who corresponds to %s (%s).", $resource->getAttribute("short-name"), $resource->getAttribute("name"));
        $fileIsValid = false;
      } else {
        // Remember this person for when we do the actual import
        $resource_people[$resource->getAttribute("id")] = $user;
      }
    }
    $result[] = "<li>Done checking resource names.</li>";
    // Check that at most one person is assigned to each task
    $task_allocation = array();
    $allocations = $doc->getElementsByTagName("allocation");
    for($i = 0; $i < $allocations->length; $i++) {
      $allocation = $allocations->item($i);
      $taskid = $allocation->getAttribute("task-id");
      if(isset($task_allocation[$taskid])) {
        // multiple people assigned to this task
        if(is_array($task_allocation[$taskid])) {
          // > 2 people assigned to the task, add to the array
          $task_allocation[$taskid][] = $allocation->getAttribute("resource-id");
        } else {
          // 2 people assigned to the task (so far), convert to an array
          $task_allocation[$taskid] = array($task_allocation[$taskid], $allocation->getAttribute("resource-id"));
        }
      } else {
        $task_allocation[$allocation->getAttribute("task-id")] = $allocation->getAttribute("resource-id");
      }
    }
    $result[] = "<li>Done checking task allocations.</li>";

    // OK, checks are done, now we attempt to actually do the import
    if($fileIsValid) {
      $project = new project();
      $project->set_id($projectID);
      $project->select();
      $projectManager = $project->get_project_manager();

      $result[] = "<li>File looks valid, running import.</li>";
      // First import tasks
      $taskNode = $doc->getElementsByTagName("tasks");
      $result = array_merge($result, import_planner_tasks($taskNode->item(0), '0', 0, $task_allocation, $resource_people, $projectManager));
      $result[] = "<li>Import completed.</li>";
    } else {
      $result[] = "<li>Please fix the above problems and then attempt to reimport the file. No changes have been made to alloc's database.</li>";
    }
  } else {
    $result[] = "<li>There was a problem with the upload.</li>";
  }
  $TPL['import_result'] = "<ul>" . implode("", $result) . "</ul>";
}

function import_planner_tasks($parentNode, $parentTaskId, $depth, $task_allocation, $resource_people, $project_manager_ID) {
  //Recursively imports tasks from GNOME Planner, given the parentNode.
  global $projectID;
  $current_user = &singleton("current_user");
  $result = array();
  // our dodgy DOM_NodeList doesn't support foreach....
  for($i = 0; $i < $parentNode->childNodes->length; $i++) {
    $taskXML = $parentNode->childNodes->item($i);
    if($taskXML->nodeType == XML_ELEMENT_NODE && $taskXML->tagName == "task") {
      $task = new task();
      $task->set_value('taskName', trim($taskXML->getAttribute("name")));
      $task->set_value('projectID', $projectID);
      // We can find the task assignee's id in the $task_allocation array, and that person's Person record in the $resource_people array
      $planner_taskid = $taskXML->getAttribute("id");
      // Dates we guess at (i.e., set to now)
      $task->set_value('dateCreated', date("Y-m-d H:i:s"));
      $task->set_value('dateAssigned', date("Y-m-d H:i:s"));
      if($taskXML->hasAttribute("work-start")) {
        $task->set_value('dateTargetStart', import_planner_date($taskXML->getAttribute("work-start")));
      } else {
        $task->set_value('dateTargetStart', import_planner_date($taskXML->getAttribute("start")));
        $result[] = "Resorting to work value for " . $task->get_value('taskName');
      }
      $task->set_value('dateTargetCompletion', import_planner_date($taskXML->getAttribute("end")));
      if($taskXML->hasAttribute("note")) {
        $task->set_value('taskDescription', $taskXML->getAttribute("note"));
      }
      $task->set_value('creatorID', $current_user->get_id());
      $task->set_value('managerID', $project_manager_ID);
      if($taskXML->hasAttribute("type") and $taskXML->getAttribute("type") == "milestone") {
        $task->set_value('taskTypeID', 'Milestone');
      } else {
        $task->set_value('taskTypeID', 'Task');
      }
      $task->set_value('taskStatus', 'open_notstarted');
      $task->set_value('priority', '3');
      $task->set_value('parentTaskID', ($parentTaskId == 0 ? "" : $parentTaskId));
      // The following fields we leave at their default values: duplicateTaskID, dateActualCompletion, dateActualStart, closerID, timeExpected, dateClosed, parentTaskID, taskModifiedUser

      // Handle task assignment
      if(isset($task_allocation[$planner_taskid])) {
        if(is_array($task_allocation[$planner_taskid])) {
          // This task was assigned to more than one person. Assign it to the project manager and make a comment about it.
          $task->set_value('personID', $project_manager_ID);
          // Save the task so we have a task ID
          $task->save();
          // Make a comment about this task
          $comment = new comment();
          $comment->set_value("commentType","task");
          $comment->set_value("commentLinkID", $task->get_id());
          $comment->set_value("commentCreatedTime", date("Y-m-d H:i:s"));
          // The user doing the import is (implicitly) the user creating the comment
          $comment->set_value("commentCreatedUser", $current_user->get_id());
          // Get the relevant usernames
          $names = array();
          foreach($task_allocation[$planner_taskid] as $assignee) {
            $names[] = person::get_fullname($assignee);
          }

          $comment->set_value("comment", "Import notice: This task was originally assigned to " . implode($names, ', ') . ".");
          $comment->save();
          $result[] = sprintf("<li>Note: multiple people were assigned to the task %d %s</li>", $task->get_id(), $task->get_value("taskName"));
        } else {
          $task->set_value('personID', $resource_people[$task_allocation[$taskXML->getAttribute("id")]]->get_id());
        }
      } else {
        // Task not assigned to anyone, assign the task to the nominated manager
        $task->set_value('personID', $project_manager_ID);
      }

      $task->save();

      $result[] = sprintf('<li>%sCreated task <a href="%s">%d %s</a>.</li>', str_repeat("&gt;", $depth), $task->get_url(), $task->get_id(), $task->get_value('taskName'));

      // Do child nodes
      if($taskXML->hasChildNodes()) {
        $result = array_merge($result, import_planner_tasks($taskXML, $task->get_id(), $depth + 1, $task_allocation, $resource_people, $project_manager_ID));
      }
    }
  }
  return $result;
}

////EXPORT FUNCTIONS
function export_gnome_planner($projectID) {
  $project = new project();
  $project->set_id($projectID);
  $project->select();

  // Note: DOM_Document is a wrapper that wraps DOMDocument for PHP5 and DomDocument for PHP4
  $doc = get_xml_document();
  $doc->load(ALLOC_MOD_DIR."shared".DIRECTORY_SEPARATOR."export_templates".DIRECTORY_SEPARATOR."template.planner");
  // General metadata
  $rootNode = $doc->getElementsByTagName("project"); $rootNode = $rootNode->item(0);
  $rootNode->setAttribute("company", config::get_config_item("companyName"));
  // Get the project manager
  $projectManager = $project->get_project_manager();
  $rootNode->setAttribute("manager", person::get_fullname($projectManager[0]));
  $rootNode->setAttribute("name", $project->get_value("projectName"));
  if($project->get_value("dateActualStart")) {
    $projectStartDate = export_planner_date(planner_date_timestamp($project->get_value("dateActualStart")));
  } else {
    $projectStartDate = export_planner_date(planner_date_timestamp($project->get_value("dateTargetStart")));
  }
  $rootNode->setAttribute("project-start", $projectStartDate);

  $resourcesUsed = array();
  // Export all tasks in the project
  $taskOptions["projectIDs"] = array($project->get_id());
  $taskOptions["return"] = "array";
  $taskOptions["taskView"] = "byProject";

  $tasks = task::get_list($taskOptions);
  // We need to sort by taskID (we assume taskIDs were assigned linearly on import) otherwise Planner will get very confused with ordering
  foreach($tasks as $task) {
    $taskIDs[] = $task['taskID'];
  }
  array_multisort($taskIDs, $tasks);
  $taskRootNode = $doc->getElementsByTagName("tasks"); $taskRootNode = $taskRootNode->item(0);
  foreach($tasks as $task) {
    $taskNode = $doc->createElement("task");
    // Use the alloc internal ID rather than pointlessly renumbering things
    $taskNode->setAttribute("id", $task["taskID"]);
    $taskNode->setAttribute("name", $task["taskName"]);
    $taskNode->setAttribute("note", $task["taskDescription"]);

    // Ugly date handling
    if(!$task["dateActualStart"]) {
      if(!$task["dateTargetStart"]) {
        // This is a reasonably bad situation
        $taskStartDate = time();
      } else {
        $taskStartDate = planner_date_timestamp($task["dateTargetStart"]);
      }
    } else {
      $taskStartDate = planner_date_timestamp($task["dateActualStart"]);
    }
    if(!$task["dateActualCompletion"]) {
      if(!$task["dateTargetCompletion"]) {
        //The task has to last for some amount of time, so end = start (otherwise we get end = 1970)
        $taskEndDate = $taskStartDate;
      } else {
        $taskEndDate = planner_date_timestamp($task["dateTargetCompletion"]);
      }
    } else {
      $taskEndDate = planner_date_timestamp($task["dateActualCompletion"]);
    }

    // Take a stab at the duration we need to give this task
    $taskDuration = $taskEndDate - $taskStartDate;
    // That's the total number of seconds, Planner expects the number of 8-hour days worth of seconds
    $taskDuration = ($taskDuration / 86400) * 28800;
    // note: the above doesn't account for weekends so there is a discrepancy between task durations in alloc and those in Planner, the solution is to make people work on the weekends

    $taskNode->setAttribute("work", $taskDuration);
    $taskNode->setAttribute("start", export_planner_date($taskStartDate));
    $taskNode->setAttribute("work-start", export_planner_date($taskStartDate));
    $taskNode->setAttribute("end", export_planner_date($taskEndDate));
    $taskNode->setAttribute("scheduling", "fixed-work");

    $constraintNode = $doc->createElement("constraint");
    $constraintNode->setAttribute("type", "start-no-earlier-than");
    $constraintNode->setAttribute("time", export_planner_date($taskStartDate));
    $taskNode->appendChild($constraintNode);

    if($task["taskTypeID"] == "Milestone") { 
      $taskNode->setAttribute("type", "milestone");
    }

    $resourcesUsed[$task["taskID"]] = $task['personID'];

    $taskRootNode->appendChild($taskNode);
  }

  // Now do the resources and their linkage to tasks
  $resourcesRootNode = $doc->getElementsByTagName("resources"); $resourcesRootNode = $resourcesRootNode->item(0);
  $allocationsRootNode = $doc->getElementsByTagName("allocations"); $allocationsRootNode = $allocationsRootNode->item(0);

  $resources = array();       //Store the users that need to be added to <resources>

  foreach($resourcesUsed as $taskID => $resourceID ) {
    if(isset($resources[$resourceID])) {
      $person = $resources[$resourceID];
    } else {
      $person = new person();
      $person->set_id($resourceID);
      $person->select();

      $resources[$resourceID] = $person;

      // Add this person to <resources>
      $resourceNode = $doc->createElement("resource");
      $resourceNode->setAttribute("id", $person->get_id());
      $resourceNode->setAttribute("name", $person->get_value("firstName") . " " . $person->get_value("surname"));
      $resourceNode->setAttribute("short-name", $person->get_value("username"));
      $resourceNode->setAttribute("email", $person->get_value("emailAddress"));
      $resourceNode->setAttribute("units", "0");
      $resourceNode->setAttribute("type", "1");       //1 means "Work" (the other option being Materials)
      $resourcesRootNode->appendChild($resourceNode);
    }

    //Now the actual allocation
    $allocationNode = $doc->createElement("allocation");
    //Units means "percentage working on this" for which alloc has no analgoue
    $allocationNode->setAttribute("units", "100");
    $allocationNode->setAttribute("task-id", $taskID);
    $allocationNode->setAttribute("resource-id", $resourceID);
    $allocationsRootNode->appendChild($allocationNode);
  }

  return $doc->saveXML();
}

function export_csv($projectID) {
  $project = new project();
  $project->set_id($projectID);
  $project->select();

  $retstr = '"Task Name","Estimated Time","Assignee"';
  // Export all tasks in the project
  $taskOptions["projectIDs"] = array($project->get_id());
  $taskOptions["return"] = "array";
  $taskOptions["taskView"] = "byProject";
  $tasks = task::get_list($taskOptions);

  // Sort by taskID--we assume taskIDs were assigned linearly on import/creation--so as to produce an identical file
  foreach($tasks as $task) {
    $taskIDs[] = $task['taskID'];
  }
  array_multisort($taskIDs, $tasks);
  foreach($tasks as $task) {
    $assignee = new person();
    $assignee->set_id($task['personID']);
    $assignee->select();

    $estimatedHours = $task['timeExpected'];
    is_numeric($estimatedHours) or $estimatedHours = 0;

    $retstr .= "\n" . export_escape_csv($task['taskName']) . ',' . export_escape_csv($estimatedHours) . ',' . export_escape_csv($assignee->get_name(array("format"=>"nick")));
  }

  return $retstr;
}

////GENERAL HELPER FUNCTIONS
function import_planner_date($indate) {
  //Takes a GNOME Planner formatted date (YYYYMMDDTHHMMSSZ, where "T" and "Z" are literal) and converts it into a MySQL formatted date (Y-m-d H:i:s)
  $year = substr($indate, 0, 4);
  $month = substr($indate, 4, 2);
  $day = substr($indate, 6, 2);
  // skip the literal "T"
  $hour = substr($indate, 9, 2);
  $minute = substr($indate, 11, 2);
  $second = substr($indate, 13, 2);
  return $year . "-" . $month . "-" . $day . " " . $hour . ":" . $minute . ":" . $second;
}

function planner_date_timestamp($indate) {
  //Takes a MySQL formatted date (YYYY-MM-DD HH:MM:SS) and converts it into a Unix timestamp
  $parts = explode(" ", $indate);
  list($year, $month, $day) = explode("-", $parts[0]);
  if(count($parts) > 0) {
    list($hour, $minute, $second) = explode(":", $parts[1]);
  } else {
    $hour = 0;
    $minute = 0;
    $second = 0;
  }
  return mktime(intval($hour), intval($minute), intval($second), intval($month), intval($day), intval($year));
}

function export_planner_date($timestamp) {
  //Takes unix timestamp and returns a GNOME Planner formatted date (YYYYMMDDTHHMMSSZ)
  return date("Ymd\\THis\\Z", $timestamp);
}

function import_find_username($candidates) {
  //Attempts to find a Person record that corresponds to one of the names specified in the $candidates array. Returns false on failure.
  global $db;
  //Our aim is just to find one record that matches the username
  foreach($candidates as $candidate) {
    $query = prepare("SELECT * FROM person WHERE username = '%s'", $candidate);
    $db->query($query);
    if($db->next_record()) {
      $person = new person();
      $person->read_db_record($db);
      $person->select();
      return $person;
    }
  }

  //No such user
  return false;
}

function export_escape_csv($str) {
  // Escapes the string suitably for CSV
  $str = str_replace('"', '""', $str);
  return '"' . $str . '"';
}

//// XML WRAPPER FUNCTIONS and CLASSES
function get_xml_document() {
  //construct an appropriate XMLDocument class
  if(version_compare(PHP_VERSION, '5.0.0', '<')) {
    // DOM_XML_Wrapper_Document is a wrapper that wraps PHP 4's DomDocument
    echo "using php 4 compat";
    return new DOM_XML_Wrapper_Document();
  } else {
    return new DOMDocument();
  }
}

class DOM_XML_Wrapper_Document {
  function load($filename) {
    $this->dom_xml_doc = domxml_open_file($filename);
    return !($this->dom_xml_doc === FALSE);
  }

  function getElementsByTagName($tagname) {
    return new DOM_XML_Wrapper_NodeList($this->dom_xml_doc->get_elements_by_tagname($tagname));
  }

  function createElement($element_name) {
    return $this->dom_xml_doc->create_element($element_name);
  }

  function saveXML() {
    return $this->dom_xml_doc->dump_mem();
  }
}

class DOM_XML_Wrapper_Node {
  // Technically this class implements the functionality of both DOMNode and DOMElement
  // For simplicity of the wrapper we have the function as part of one object
  function DOM_XML_Wrapper_Node($original_node) {
    $this->node = $original_node;

    $this->childNodes = new DOM_XML_Wrapper_NodeList($this->node->child_nodes());
    $this->nodeType = $this->node->node_type();
    if($this->nodeType == XML_ELEMENT_NODE) {
      $this->tagName = $this->node->tagname();
    }
  }

  function setAttribute($attr_name, $attr_value) {
    $this->node->set_attribute($attr_name, $attr_value);
  }

  function getAttribute($attr_name) {
    return $this->node->get_attribute($attr_name);
  }

  function hasAttribute($attr_name) {
    return $this->node->has_attribute($attr_name);
  }

  function hasChildNodes() {
    return $this->node->has_child_nodes();
  }

  function appendChild($newNode) {
    return $this->node->append_child($newNode);
  }

}

function DOMNodeWrapper($node) {
  return new DOM_XML_Wrapper_Node($node);
}

class DOM_XML_Wrapper_NodeList {
  function DOM_XML_Wrapper_NodeList($in_array) {
    $this->items = array_map("DOMNodeWrapper", $in_array);
    $this->length = count($in_array);
  }

  function item($index) {
    return $this->items[$index];
  }
}


?>
