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
                             );

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
    $q = sprintf("DELETE 
                    FROM interestedParty 
                   WHERE entity='%s' 
                     AND entityID='%d' 
                     AND emailAddress='%s'",db_esc($entity),$entityID,db_esc($emailAddress));
    $db = new db_alloc();
    $db->query($q);
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

    // If we're doing subject line magic, then we're only going to do it with
    // subject lines that have a {Key:fdsFFeSD} in them.
    preg_match("/\{Key:[A-Za-z0-9]{8}\}(.*)\s*$/i",$subject,$m);
    $commands = explode(" ",trim($m[1]));

    foreach((array)$commands as $command) {
      $command = strtolower($command);

      // If "quiet" in the subject line, then the email/comment won't be re-emailed out again
      if ($command == "quiet") {
        $action["quiet"] = true;
      }

      // To unsubscribe from this conversation
      if ($command == "unsub" || $command == "unsubscribe") {
        if (interestedParty::exists($entity, $entityID, $emailAddress)) {
          interestedParty::delete_interested_party($entity, $entityID, $emailAddress);
          $action["interestedParty"] = $current_user->get_username(1)." is no longer a party to this conversation.";
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
          $action["interestedParty"] = $current_user->get_username(1)." is now a party to this conversation.";
        }
      }

      // Can only perform any of the other actions if they are being performed by a recognized user
      if (is_object($current_user) && $current_user->get_id()) {

        // To close the entity (i.e. the task)
        if ($command == "close" || $command == "closed") {
          if (is_object($object) && method_exists($object,"close") && $object->get_id()) {
            $object->close();
            $object->save();
          }

        // To open the entity (i.e. the task)
        } else if ($command == "open") {
          if (is_object($object) && method_exists($object,"open") && $object->get_id()) {
            $object->open();
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

              $action["timeSheet"] = $current_user->get_username(1)." added ".$tsi_row["timeSheetItemDuration"]." ".$unitLabel;
              $action["timeSheet"].= " to time sheet #".$tsi_row["timeSheetID"];
            }

            if (!$tsi_row || !$tsi_row["timeSheetID"] || !$tsi_row["timeSheetItemID"]) {
              $action["timeSheet"] = "Failed to add time via email to a time sheet for ".$current_user->get_username(1);
            }

            $tsi_row["error_no_projectPerson"] and $action["timeSheet"].= "\n".$current_user->get_username(1)." has not been added to project ".$object->get_value("projectID").".";
            $tsi_row["timeSheetItem_save_error"] and $action["timeSheet"].= "\nError saving time sheet item: ".$tsi_row["timeSheetItem_save_error"];
          }
        }
      }
    }
    return $action;
  }


}



?>
