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

class command {

  var $commands;
  var $email_receive;

  function get_help($type) {
    $message = ucwords($type)." fields:";
    $fields = command::get_fields($type);
    foreach ((array)$fields as $k => $arr) {
      $message.= "\n      ".$k.":\t".$arr[1];
    }
    return $message;
  }

  function get_fields($type="all") {

    $comment = array(
      "key"   => array("","The comment thread key.")
     ,"ip"    => array("","Add to a comment's interested parties. Eg: jon,jane@j.com,Hal Comp <hc@hc.com> ... can also remove eg: -jon")
     ,"quiet" => array("","Don't resend the email back out to anyone.")
    );

    $item = array(
      "tsid"      => array("timeSheetID",           "time sheet that this item belongs to")
     ,"date"      => array("dateTimeSheetItem",     "time sheet item's date")
     ,"duration"  => array("timeSheetItemDuration", "time sheet item's duration")
     ,"unit"      => array("timeSheetItemDurationUnitID", "time sheet item's unit of duration eg: 1=hours 2=days 3=week 4=month 5=fixed")
     ,"task"      => array("taskID",                "ID of the time sheet item's task")
     ,"rate"      => array("rate",                  "\$rate of the time sheet item's")
     ,"private"   => array("commentPrivate",        "privacy setting of the time sheet item's comment eg: 1=private 0=normal")
     ,"comment"   => array("comment",               "time sheet item comment")
     ,"multiplier"=> array("multiplier",            "time sheet item multiplier eg: 1=standard 1.5=time-and-a-half 2=double-time 3=triple-time 0=no-charge")
     ,"delete"    => array("",                      "set this to 1 to delete the time sheet item")
     ,"time"      => array("",                      "Add some time to a time sheet. Auto-creates time sheet if none exist.")
    );

    $task = array(
      "status"    => array("taskStatus",      "inprogress, notstarted, info, client, manager, invalid, duplicate, incomplete, complete; or: open, pending, closed")
     ,"name"      => array("taskName",        "task's title")
     ,"assign"    => array("personID",        "username of the person that the task is assigned to")
     ,"manage"    => array("managerID",       "username of the person that the task is managed by")
     ,"desc"      => array("taskDescription", "task's long description")
     ,"priority"  => array("priority",        "1, 2, 3, 4 or 5; or one of Wishlist, Minor, Normal, Important or Critical")
     ,"limit"     => array("timeLimit",       "limit in hours for effort spend on this task")
     ,"best"      => array("timeBest",        "shortest estimate of how many hours of effort this task will take")
     ,"likely"    => array("timeExpected",    "most likely amount of hours of effort this task will take")
     ,"worst"     => array("timeWorst",       "longest estimate of how many hours of effort this task will take")
     ,"project"   => array("projectID",       "task's project ID")
     ,"type"      => array("taskTypeID",      "Task, Fault, Message, Milestone or Parent")
     ,"dupe"      => array("duplicateTaskID", "If the task status is duplicate, then this should be set to the task ID of the related dupe")
     ,"task"      => array("",                "A task ID, or the word 'new' to create a new task.")
     ,"taskip"    => array("",                "Add some interested parties and send the desc to them.")
    );

    $types = array("all"=>array_merge($comment,$item,$task)
                  ,"comment"=>$comment
                  ,"item"=>$item
                  ,"task"=>$task);
    return $types[$type];
  }

  function run_commands($commands=array(), $email_receive=false) {
    global $current_user;
    $task_fields = $this->get_fields("task");
    $item_fields = $this->get_fields("item");

    // If there's Key in the email, then add a comment with the contents of the email.
    if ($commands["key"]) {
      $token = new token;
      if ($token->set_hash($commands["key"])) {

        global $guest_permission_cache, $current_user;
        // If sent from a client or someone we don't recognize, we need to imbue this process with guest perms
        $comment = $token->get_value("tokenEntity");
        $commentID = $token->get_value("tokenEntityID");
        $q = sprintf("SELECT commentMaster,commentMasterID FROM comment WHERE commentID = %d",$commentID);
        $db = new db_alloc();
        $r = $db->qr($q);
        $master = $r["commentMaster"];
        $masterID = $r["commentMasterID"];

        if (!$current_user) {
          // Used in db_entity::have_perm();
          global $guest_permission_cache;
          // Hard code some additional permissions for this guest user (just enough to create a comment)
          $guest_permission_cache[] = array("entity"=>$comment,"entityID"=>$commentID,"perms"=>15);
          $guest_permission_cache[] = array("entity"=>$comment,"entityID"=>0,"perms"=>15);
          $guest_permission_cache[] = array("entity"=>$master ,"entityID"=>$masterID,"perms"=>15);
          $guest_permission_cache[] = array("entity"=>"indexQueue","entityID"=>0,"perms"=>15);
        }

        list($entity,$method) = $token->execute();
        if (is_object($entity) && $method == "add_comment_from_email") {

          $c = comment::add_comment_from_email($email_receive,$entity);
          $quiet = interestedParty::adjust_by_email_subject($email_receive,$entity);

          if ($commands["ip"]) {
            $rtn = interestedParty::add_remove_ips($commands["ip"],$entity->classname,$entity->get_id(),$entity->get_project_id());
          }

          if (!$quiet) {
            comment::send_comment($c->get_id(),array("interested"),$email_receive);
          }
        }
      }
    }


    // If there's a number/duration then add some time to a time sheet
    if ($commands["time"]) {

      // CLI passes time along as a string, email passes time along as an array
      if (!is_array($commands["time"])) {
        $t = $commands["time"];
        unset($commands["time"]);
        $commands["time"][] = $t;
      }

      foreach ((array)$commands["time"] as $time) {
        $t = timeSheetItem::parse_time_string($time);

        if (is_numeric($t["duration"]) && $current_user->get_id()) {
          $timeSheet = new timeSheet();
          is_object($email_receive) and $t["msg_uid"] = $email_receive->msg_uid;
          $tsi_row = $timeSheet->add_timeSheetItem($t);
          $status[] = $tsi_row["status"];
          $message[] = $tsi_row["message"];
        }
      }
    }

    // Time Sheet Item commands
    if ($commands["item"]) {

      $timeSheetItem = new timeSheetItem;
      if ($commands["item"] && strtolower($commands["item"] != "new")) {
        $timeSheetItem->set_id($commands["item"]);
        $timeSheetItem->select();
      }
      $timeSheet = $timeSheetItem->get_foreign_object("timeSheet");
      $timeSheetItem->currency = $timeSheet->get_value("currencyTypeID");
      $timeSheetItem->set_value("rate",$timeSheetItem->get_value("rate",DST_HTML_DISPLAY));

      // This has to be here for CLI, as multiplier === 0 is valid.
      if (!isset($commands["multiplier"])) {
        $commands["multiplier"] = 1;
      }

      foreach ($commands as $k => $v) {

        // Validate/coerce the fields
        if ($k == "unit") {
          in_array($v,array(1,2,3,4,5)) or $err[] = "Invalid unit. Try a number from 1-5.";
        } else if ($k == "task") {
          $t = new task;
          $t->set_id($v);
          $t->select();
          is_object($timeSheet) && $timeSheet->get_id() && $t->get_value("projectID") != $timeSheet->get_value("projectID") and $err[] = "Invalid task. Task belongs to different project.";
        }

        // Plug the value in
        if ($item_fields[$k][0]) {
          $timeSheetItem->set_value($item_fields[$k][0],sprintf("%s",$v));
        }
      }

      if ($commands["delete"]) {
        $id = $timeSheetItem->get_id();
        $timeSheetItem->delete();
        $status[] = "yay";
        $message[] = "Time sheet item ".$id." deleted.";

      // Save timeSheetItem
      } else if (!$err && $commands["item"] && $timeSheetItem->save()) {
        $status[] = "yay";
        if (strtolower($commands["item"]) == "new") {
          $message[] = "Time sheet item ".$timeSheetItem->get_id()." created.";
        } else {
          $message[] = "Time sheet item updated.";
        }

      // Problems
      } else if ($err && $commands["item"]) {
        $status[] = "err";
        $message[] = "Problem updating time sheet item: ".implode("\n",(array)$err);
      }


    // Task commands
    } else if ($commands["task"]) {

      $taskPriorities = config::get_config_item("taskPriorities") or $taskPriorities = array();
      foreach ($taskPriorities as $k => $v) {
        $priorities[strtolower($v["label"])] = $k;
      }

      $people_by_username = person::get_people_by_username();

      // Else edit/create the task ...
      $task = new task;
      if ($commands["task"] && strtolower($commands["task"]) != "new") {
        $task->set_id($commands["task"]);
        $task->select();
      }

      foreach ($commands as $k => $v) {
        // transform from username to personID
        if ($k == "assign" || $k == "manage") {
          $v = $people_by_username[$v]["personID"];
        }

        // transform from priority label to priority ID
        if ($k == "priority" && !is_numeric($v)) {
          $v = $priorities[strtolower($v)];
        }

        // Plug the value in
        if ($task_fields[$k][0]) {
          $task->set_value($task_fields[$k][0],sprintf("%s",$v));
        }
      }

      if (strtolower($commands["task"]) == "new") {
        if (!$commands["desc"] && is_object($email_receive)) {
          $task->set_value("taskDescription",$email_receive->get_converted_encoding());
        }
      }

      // Save task
      $err = $task->validate();
      if (!$err && $task->save()) {

        if ($commands["taskip"]) {
          $rtn = interestedParty::add_remove_ips($commands["taskip"],"task",$task->get_id(),$task->get_value("projectID"));
          if ($rtn["status"] == "err") {
            return $rtn;
          }
        }

        $status[] = "yay";
        $message[] = "Task updated.";
        strtolower($commands["task"]) == "new" and $message[] = "Task ".$task->get_id()." created.";

      // Problems
      } else {
        $status[] = "err";
        $message[] = "Problem updating task: ".implode("\n",(array)$err);
      }


    // Adding a comment from CLI
    } else if ($commands["comment"] == "new") {
      $commentID = comment::add_comment($commands["entity"], $commands["entityID"], $commands["comment_text"]);

      // add interested parties
      foreach((array)$commands["ip"] as $k => $info) {
        $info["entity"] = "comment";
        $info["entityID"] = $commentID;
        $info["fullName"] = $info["name"];
        interestedParty::add_interested_party($info);
      }

      // Re-email the comment out
      comment::send_comment($commentID,array("interested"));
    }


    // Figure out if success or failure
    foreach ((array)$status as $k => $v) {
      if ($v == "err") {
        $status = "err";
      }
    }
    $status == "err" or $status = "yay";

    // Status will be yay, msg, err or die, i.e. mirrored with the alloc-cli messaging system
    return array("status"=>$status,"message"=>implode(" ",(array)$message));
  }


}

?>
