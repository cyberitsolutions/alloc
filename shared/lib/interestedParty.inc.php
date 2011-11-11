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
                             ,"interestedPartyActive"
                             );

  function delete() {
    $this->set_value("interestedPartyActive",0);
    $this->save();
  }

  function is_owner() {
    global $current_user;
    return same_email_address($this->get_value("emailAddress"),$current_user->get_value("emailAddress"));
  }

  function save() {
    $this->set_value("emailAddress", str_replace(array("<",">"),"",$this->get_value("emailAddress")));
    return parent::save();
  }

  function exists($entity, $entityID, $email) {
    $email = str_replace(array("<",">"),"",$email);
    $db = new db_alloc();
    $db->query("SELECT *
                  FROM interestedParty
                 WHERE entityID = %d
                   AND entity = '%s'
                   AND emailAddress = '%s'
               ",$entityID,db_esc($entity),db_esc($email));
    return $db->row();
  }

  function active($entity, $entityID, $email) {
    $email = str_replace(array("<",">"),"",$email);
    $db = new db_alloc();
    $db->query("SELECT *
                  FROM interestedParty
                 WHERE entityID = %d
                   AND entity = '%s'
                   AND emailAddress = '%s'
                   AND interestedPartyActive = 1
               ",$entityID,db_esc($entity),db_esc($email));
    return $db->row();
  }

  function make_interested_parties($entity,$entityID,$encoded_parties=array()) {
    // Nuke entries from interestedParty
    $db = new db_alloc();
    $db->start_transaction();
    $q = sprintf("UPDATE interestedParty
                     SET interestedPartyActive = 0
                   WHERE entity = '%s'
                     AND entityID = %d",db_esc($entity),$entityID);
    $db->query($q);

    // Add entries to interestedParty
    if (is_array($encoded_parties)) {
      foreach ($encoded_parties as $encoded) {
        $info = interestedParty::get_decoded_interested_party_identifier($encoded);
        $info["entity"] = $entity;
        $info["entityID"] = $entityID;
        $info["emailAddress"] or $info["emailAddress"] = $info["email"];
        interestedParty::add_interested_party($info);
      }
    }
    $db->commit();
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
        $ops[$db->f("emailAddress")]["selected"] = $db->f("interestedPartyActive") ? true : false;
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
    global $current_user;
    if (is_object($current_user) && $current_user->get_id()) {
      $current_user_email = $current_user->get_value("emailAddress");
    }
    foreach ($parties as $email => $info) {
      if ($info["name"]) {
        unset($sel,$c);
        $counter++;

        if ($current_user_email && same_email_address($current_user_email,$email)) {
          $sel = " checked";
        }

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
    $emailAddress = str_replace(array("<",">"),"",$emailAddress);
    $row = interestedParty::active($entity,$entityID,$emailAddress);
    if ($row) {
      $ip = new interestedParty();
      $ip->read_row_record($row);
      $ip->delete();
    }
  }
  
  function add_interested_party($data) {
    static $people;
    $data["emailAddress"] = str_replace(array("<",">"),"",$data["emailAddress"]);
    // Add new entry

    $ip = new interestedParty;
    $existing = interestedParty::exists($data["entity"], $data["entityID"], $data["emailAddress"]);
    if ($existing) {
      $ip->set_id($existing["interestedPartyID"]);
      $ip->select();
    }
    $ip->set_value("entity",$data["entity"]);
    $ip->set_value("entityID",$data["entityID"]);
    $ip->set_value("fullName",$data["fullName"]);
    $ip->set_value("emailAddress",$data["emailAddress"]);
    $ip->set_value("interestedPartyActive",1);
    if ($data["personID"]) {
      $ip->set_value("personID",$data["personID"]);
      $ip->set_value("fullName",person::get_fullname($data["personID"]));

    } else {
      $people or $people = get_cached_table("person");
      foreach ($people as $personID => $p) {
        if ($data["emailAddress"] && same_email_address($p["emailAddress"], $data["emailAddress"])) {
          $ip->set_value("personID",$personID);
          $ip->set_value("fullName",$p["name"]);
        }
      }
    }
    if (!$ip->get_value("personID")) {
      $q = sprintf("SELECT * FROM clientContact WHERE clientContactEmail = '%s'",$data["emailAddress"]);
      $db = new db_alloc();
      $db->query($q);
      if ($row = $db->row()) {
        $ip->set_value("clientContactID",$row["clientContactID"]);
        $ip->set_value("fullName",$row["clientContactName"]);
        $ip->set_value("external",1);
      }
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
        if (interestedParty::active($entity, $entityID, $emailAddress)) {
          interestedParty::delete_interested_party($entity, $entityID, $emailAddress);
        }

      // To subscribe to this conversation
      } else if ($command == "sub" || $command == "subscribe") {
        $ip = interestedParty::exists($entity, $entityID, $emailAddress);

        if (!$ip) {
          $data = array("entity"         => $entity
                       ,"entityID"       => $entityID
                       ,"fullName"       => $fullName
                       ,"emailAddress"   => $emailAddress
                       ,"personID"       => $personID
                       ,"clientContactID"=> $clientContactID);
          interestedParty::add_interested_party($data);

        // Else reactivate existing IP
        } else if (!interestedParty::active($entity, $entityID, $emailAddress)) {
          $interestedParty = new interestedParty;
          $interestedParty->set_id($ip["interestedPartyID"]);
          $interestedParty->select();
          $interestedParty->set_value("interestedPartyActive",1);
          $interestedParty->save();
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
              $tsi_row = $timeSheet->add_timeSheetItem(array("taskID"=>$object->get_id(), "duration"=>$duration, "comment"=>$body, "msg_uid"=>$msg_uid, "multiplier"=>1));
              $timeUnit = new timeUnit;
              $units = $timeUnit->get_assoc_array("timeUnitID","timeUnitLabelA");
              $unitLabel = $units[$tsi_row["timeSheetItemDurationUnitID"]];
            }
          }
        }
      }
    }
    return $action;
  }

  function get_list_filter($filter=array()) {
    $filter["emailAddress"] = str_replace(array("<",">"),"",$filter["emailAddress"]);
    $filter["emailAddress"]    and $sql[] = sprintf("(interestedParty.emailAddress LIKE '%%%s%%')",db_esc($filter["emailAddress"]));
    $filter["fullName"]        and $sql[] = sprintf("(interestedParty.fullName LIKE '%%%s%%')",db_esc($filter["fullName"]));
    $filter["personID"]        and $sql[] = sprintf("(interestedParty.personID = %d)",db_esc($filter["personID"]));
    $filter["clientContactID"] and $sql[] = sprintf("(interestedParty.clientContactID = %d)",db_esc($filter["clientContactID"]));
    $filter["entity"]          and $sql[] = sprintf("(interestedParty.entity = '%s')",db_esc($filter["entity"]));
    $filter["entityID"]        and $sql[] = sprintf("(interestedParty.entityID = %d)",db_esc($filter["entityID"]));
    $filter["active"]          and $sql[] = sprintf("(interestedParty.interestedPartyActive = %d)",db_esc($filter["active"]));
    $filter["taskID"]          and $sql[] = sprintf("(comment.commentMaster='task' AND comment.commentMasterID=%d)",$filter["taskID"]);
    return $sql;
  }

  function get_list($_FORM) {

    if ($_FORM["taskID"]) {
      $join = " LEFT JOIN comment ON ((interestedParty.entity = comment.commentType AND interestedParty.entityID = comment.commentLinkID) OR (interestedParty.entity = 'comment' and interestedParty.entityID = comment.commentID))";
      $groupby = ' GROUP BY interestedPartyID';
    }
    
    $filter = interestedParty::get_list_filter($_FORM);
    $_FORM["return"] or $_FORM["return"] = "html";
    
    if (is_array($filter) && count($filter)) {
      $f = " WHERE ".implode(" AND ",$filter);
    }
    
    $db = new db_alloc;
    $q = "SELECT * FROM interestedParty ".$join.$f.$groupby;

    $db->query($q);
    while ($row = $db->next_record()) {
      $interestedParty = new interestedParty();
      $interestedParty->read_db_record($db);
      $rows[$interestedParty->get_id()] = $row;
    }
    return (array)$rows;
  }

  function is_external($entity, $entityID) {
    $ips = interestedParty::get_interested_parties($entity,$entityID);
    foreach ($ips as $email => $info) {
      if ($info["external"] && $info["selected"]) {
        return true;
      }
    }
  }


}



?>
