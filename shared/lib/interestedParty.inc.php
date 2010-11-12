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

class interestedParty extends db_entity {
  public $data_table = "interestedParty";
  public $key_field = "interestedPartyID";
  public $data_fields = array("entityID"
                             ,"entity"
                             ,"fullName"
                             ,"emailAddress"
                             ,"personID"
                             ,"clientContactID"
                             ,"external"
                             ,"interestedPartyCreatedUser"
                             ,"interestedPartyCreatedTime"
                             );

  function is_owner() {
    global $current_user;
    return same_email_address($this->get_value("emailAddress"),$current_user->get_value("emailAddress"));
  }

  function exists($entity, $entityID, $email) {
    $db = new db_alloc();
    $db->query("SELECT *
                  FROM interestedParty
                 WHERE entityID = %d
                   AND entity = '%s'
                   AND emailAddress = '%s'
               ",$entityID,db_esc($entity),db_esc($email));
    return $db->row();
  }

  function make_interested_parties($entity,$entityID,$encoded_parties=array()) {
    // Nuke entries from interestedParty
    $q = sprintf("DELETE FROM interestedParty WHERE entity = '%s' AND entityID = %d",db_esc($entity),$entityID);
    $db = new db_alloc();
    $db->query($q);

    // Add entries to interestedParty
    if (is_array($encoded_parties)) {
      foreach ($encoded_parties as $encoded) {
        $info = interestedParty::get_decoded_interested_party_identifier($encoded);
        $interestedParty = new interestedParty;
        $interestedParty->set_value("entity",$entity);
        $interestedParty->set_value("entityID",$entityID);
        $interestedParty->set_value("fullName",$info["name"]);
        $interestedParty->set_value("emailAddress",$info["email"]);
        $interestedParty->set_value("personID",$info["personID"]);
        $interestedParty->set_value("clientContactID",$info["clientContactID"]);
        $info["external"] and $interestedParty->set_value("external","1");
        $interestedParty->save();
      }
    }
  }

  function sort_interested_parties($a, $b) {
    return strtolower($a["name"]) > strtolower($b["name"]);
  }

  function get_interested_parties($entity,$entityID=false,$ops=array()) {
    $rtn = array();

    if ($entityID) {
      $db = new db_alloc();
      $q = sprintf("SELECT *
                      FROM interestedParty
                     WHERE entity='%s'
                       AND entityID = %d
                  ",db_esc($entity),$entityID);
      $db->query($q);
      while ($db->row()) {
        $ops[$db->f("emailAddress")]["name"] = $db->f("fullName");
        $ops[$db->f("emailAddress")]["role"] = "interested";
        $ops[$db->f("emailAddress")]["selected"] = true;
        $ops[$db->f("emailAddress")]["personID"] = $db->f("personID");
        $ops[$db->f("emailAddress")]["clientContactID"] = $db->f("clientContactID");
        $ops[$db->f("emailAddress")]["external"] = $db->f("external");
      }
    }

    if (is_array($ops)) {
      foreach ($ops as $email => $info) {
        // if there is an @ symbol in email address
        if (stristr($email,"@")) { 
          $info["email"] = $email;
          $info["identifier"] = interestedParty::get_encoded_interested_party_identifier($info);
          $rtn[$email] = $info;
        }
      }

      uasort($rtn,array("interestedParty","sort_interested_parties"));
    }
    return $rtn;
  }

  function get_encoded_interested_party_identifier($info=array()) {
    return urlencode(base64_encode(serialize($info)));
  }

  function get_decoded_interested_party_identifier($blob) {
    return unserialize(base64_decode(urldecode($blob)));
  }

  function get_interested_parties_html($parties=array()) {
    foreach ($parties as $email => $info) {
      if ($info["name"]) {
        unset($sel,$c);
        $counter++;
        $info["selected"] and $sel = " checked";
        $info["external"] and $c.= " warn";
        $str.= "<div width=\"150px\" class=\"nobr ".$c."\" id=\"td_ect_".$counter."\" style=\"float:left; width:150px; margin-bottom:5px;\">";
        $str.= "<input id=\"ect_".$counter."\" type=\"checkbox\" name=\"commentEmailRecipients[]\" value=\"".$info["identifier"]."\"".$sel."> ";
        $str.= "<label for=\"ect_".$counter."\" title=\"" . $info["name"] . " &lt;" . $info["email"] . "&gt;\">".page::htmlentities($info["name"])."</label></div>";
      }
    }
    return $str;
  }

  function delete_interested_party($entity, $entityID, $emailAddress) {
    // Delete existing entries
    $row = interestedParty::exists($entity,$entityID,$emailAddress);
    if ($row) {
      $ip = new interestedParty();
      $ip->read_row_record($row);
      $ip->delete();
    }
  }
  
  function add_interested_party($data) {
    // Add new entry
    $ip = new interestedParty();
    $ip->set_value("entity",$data["entity"]);
    $ip->set_value("entityID",$data["entityID"]);
    $ip->set_value("fullName",$data["fullName"]);
    $ip->set_value("emailAddress",$data["emailAddress"]);
    if ($data["personID"]) {
      $ip->set_value("personID",$data["personID"]);
    } else {
      $q = sprintf("SELECT clientContactID FROM clientContact WHERE clientContactEmail = '%s'",$data["emailAddress"]);
      $db = new db_alloc();
      $db->query($q);
      $row = $db->row();
      $row and $ip->set_value("clientContactID",$row["clientContactID"]);
      $row and $ip->set_value("external",1);
    }
    $ip->save();
  }

  function adjust_by_email_subject($subject="",$entity,$entityID,$fullName="",$emailAddress="",$personID="",$clientContactID="",$body="",$msg_uid="") {
    global $current_user;

    // Load up the parent object that this comment refers to, be it task or timeSheet etc
    if ($entity == "comment" && $entityID) {
      $c = new comment();
      $c->set_id($entityID);
      $c->select();
      $object = $c->get_parent_object();
    } else if (class_exists($entity) && $entityID) {
      $object = new $entity;
      $object->set_id($entityID);
      $object->select();
    }

    // Build up an array of task sub-statuses for email subject line changing
    $m = new meta("taskStatus");
    $rows = $m->get_assoc_array();
    foreach ($rows as $taskStatusID => $arr) {
      list($s,$ss) = explode("_",$taskStatusID);
      $subStatuses[$ss] = $s;
    }
    $statuses["close"] = "close";
    $statuses["closed"] = "close";
    $statuses["open"] = "open";
    $statuses["pending"] = "pending";

    // If we're doing subject line magic, then we're only going to do it with
    // subject lines that have a {Key:fdsFFeSD} in them.
    preg_match("/\{Key:[A-Za-z0-9]{8}\}(.*)\s*$/i",$subject,$m);
    $commands = explode(" ",trim($m[1]));

    foreach((array)$commands as $command) {
      $command = strtolower($command);
      list($command,$command2) = explode(":",$command); // for eg: duplicate:1234

      // If "quiet" in the subject line, then the email/comment won't be re-emailed out again
      if ($command == "quiet") {
        $action["quiet"] = true;
      }

      // To unsubscribe from this conversation
      if ($command == "unsub" || $command == "unsubscribe") {
        if (interestedParty::exists($entity, $entityID, $emailAddress)) {
          interestedParty::delete_interested_party($entity, $entityID, $emailAddress);
          $action["interestedParty"] = $current_user->get_name()." is no longer a party to this conversation.";
        }

      // To subscribe to this conversation
      } else if ($command == "sub" || $command == "subscribe") {
        if (!interestedParty::exists($entity, $entityID, $emailAddress)) {
          $interestedParty = new interestedParty;
          $interestedParty->set_value("entity",$entity);
          $interestedParty->set_value("entityID",$entityID);
          $interestedParty->set_value("fullName",$fullName);
          $interestedParty->set_value("emailAddress",$emailAddress);
          $interestedParty->set_value("personID",$personID);
          $interestedParty->set_value("clientContactID",$clientContactID);
          $interestedParty->save();
          $action["interestedParty"] = $current_user->get_name()." is now a party to this conversation.";
        }
      }

      // Can only perform any of the other actions if they are being performed by a recognized user
      if (is_object($current_user) && $current_user->get_id()) {

        if (isset($statuses[$command])) {
          $method = $statuses[$command];
          if (is_object($object) && method_exists($object,$method) && $object->get_id()) {
            $object->$method();
            $object->save();
          }
        } else if (isset($subStatuses[$command])) {
          // method should be open(), close() or pending()
          $method = $subStatuses[$command];
          if (is_object($object) && method_exists($object,$method) && $object->get_id()) {
            $command2 and $object->set_value("duplicateTaskID",$command2);
            $object->$method($command);
            $object->save();
          }
        }

        // If there's a number/duration then add some time to a time sheet
        if (preg_match("/([\.\d]+)/i",$command,$m)) { 
          $duration = $m[1];
  
          if (is_numeric($duration)) {

            if (is_object($object) && $object->classname == "task" && $object->get_id() && $current_user->get_id()) {
              $timeSheet = new timeSheet();
              $tsi_row = $timeSheet->add_timeSheetItem_by_task($object->get_id(), $duration, $body, $msg_uid);

              $timeUnit = new timeUnit;
              $units = $timeUnit->get_assoc_array("timeUnitID","timeUnitLabelA");
              $unitLabel = $units[$tsi_row["timeSheetItemDurationUnitID"]];

              $action["timeSheet"] = $current_user->get_name()." added ".$tsi_row["timeSheetItemDuration"]." ".$unitLabel;
              $action["timeSheet"].= " to time sheet #".$tsi_row["timeSheetID"];
            }

            if (!$tsi_row || !$tsi_row["timeSheetID"] || !$tsi_row["timeSheetItemID"]) {
              $action["timeSheet"] = "Failed to add time via email to a time sheet for ".$current_user->get_name();
            }

            $tsi_row["error_no_projectPerson"] and $action["timeSheet"].= "\n".$current_user->get_name()." has not been added to project ".$object->get_value("projectID").".";
            $tsi_row["timeSheetItem_save_error"] and $action["timeSheet"].= "\nError saving time sheet item: ".$tsi_row["timeSheetItem_save_error"];
          }
        }
      }
    }
    return $action;
  }

  function get_list_filter($filter=array()) {
    $filter["emailAddress"]    and $sql[] = sprintf("(interestedParty.emailAddress LIKE '%%%s%%')",db_esc($filter["emailAddress"]));
    $filter["fullName"]        and $sql[] = sprintf("(interestedParty.fullName LIKE '%%%s%%')",db_esc($filter["fullName"]));
    $filter["personID"]        and $sql[] = sprintf("(interestedParty.personID = %d)",db_esc($filter["personID"]));
    $filter["clientContactID"] and $sql[] = sprintf("(interestedParty.clientContactID = %d)",db_esc($filter["clientContactID"]));
    $filter["entity"]          and $sql[] = sprintf("(interestedParty.entity = '%s')",db_esc($filter["entity"]));
    $filter["entityID"]        and $sql[] = sprintf("(interestedParty.entityID = %d)",db_esc($filter["entityID"]));
    return $sql;
  }

  function get_list($_FORM) {
    
    $filter = interestedParty::get_list_filter($_FORM);
    $_FORM["return"] or $_FORM["return"] = "html";
    
    if (is_array($filter) && count($filter)) {
      $f = " WHERE ".implode(" AND ",$filter);
    }
    
    $db = new db_alloc;
    $q = "SELECT * FROM interestedParty ".$f;
    
    $db->query($q);
    while ($row = $db->next_record()) {
      $interestedParty = new interestedParty();
      $interestedParty->read_db_record($db);
      $rows[$interestedParty->get_id()] = $row;
    }
    return (array)$rows;
  }

}



?>
