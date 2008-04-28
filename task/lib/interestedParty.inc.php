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
  var $data_table = "interestedParty";

  function interestedParty() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_field("interestedPartyID");
    $this->data_fields = array("entityID"=>new db_field("entityID")
                              ,"entity"=>new db_field("entity")
                              ,"fullName"=>new db_field("fullName")
                              ,"emailAddress"=>new db_field("emailAddress")
                              ,"personID"=>new db_field("personID")
                              ,"clientContactID"=>new db_field("clientContactID")
                              ,"external"=>new db_field("external")
                              );
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
        $info = task::get_decoded_interested_party_identifier($encoded);
        $interestedParty = new interestedParty;
        $interestedParty->set_value("entity",$entity);
        $interestedParty->set_value("entityID",$entityID);
        $interestedParty->set_value("fullName",$info["name"]);
        $interestedParty->set_value("emailAddress",$info["email"]);
        $interestedParty->set_value("personID",$info["personID"]);
        $interestedParty->set_value("clientContactID",$info["clientContactID"]);
        $info["external"] and $interestedParty->set_value("external",1);
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
        $info["email"] = $email;
        $info["identifier"] = task::get_encoded_interested_party_identifier($info);
        $rtn[$email] = $info;
      }

      uasort($rtn,array("interestedParty","sort_interested_parties"));
    }
    return $rtn;
  }

  function get_interested_parties_html($parties=array()) {
    foreach ($parties as $email => $info) {
      if ($info["name"]) {
        unset($sel,$c);
        $counter++;
        $info["selected"] and $sel = " checked";
        $info["external"] and $c.= " warn";
        $str.= "<div width=\"155px\" class=\"".$c."\" id=\"td_ect_".$counter."\" style=\"float:left; width:155px; margin-bottom:5px;\">";
        $str.= "<input id=\"ect_".$counter."\" type=\"checkbox\" name=\"commentEmailRecipients[]\" value=\"".$info["identifier"]."\"".$sel.">";
        $str.= "<label for=\"ect_".$counter."\">".$info["name"]."</label></div>";
      }
    }
    return $str;
  }


}



?>
