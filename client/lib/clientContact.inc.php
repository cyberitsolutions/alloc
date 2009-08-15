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

class clientContact extends db_entity {
  public $classname = "clientContact";
  public $data_table = "clientContact";
  public $display_field_name = "clientContactName";
  public $key_field = "clientContactID";
  public $data_fields = array("clientID"
                             ,"clientContactName"
                             ,"clientContactStreetAddress"
                             ,"clientContactSuburb"
                             ,"clientContactState"
                             ,"clientContactPostcode"
                             ,"clientContactCountry"
                             ,"clientContactPhone"
                             ,"clientContactMobile"
                             ,"clientContactFax"
                             ,"clientContactEmail"
                             ,"clientContactOther"
                             ,"primaryContact"
                             );

  function find_by_name($name=false,$projectID=false) {

    $stack1 = array();

    static $people;
    if (!$people) {
      $people = array();
      $q = sprintf("SELECT clientContact.clientContactID, clientContact.clientContactName
                      FROM client
                 LEFT JOIN clientContact ON client.clientID = clientContact.clientID
                 LEFT JOIN project ON project.clientID = client.clientID 
                     WHERE project.projectID = %d
                   ",$projectID);
      $db = new db_alloc();
      $db->query($q);
      while ($row = $db->row()) {
        $people[$db->f("clientContactID")] = $row;
      }
    }
  
    foreach ($people as $personID => $row) {
      similar_text($row["clientContactName"],$name,$percent1);
      $stack1[$personID] = $percent1;
    }

    asort($stack1);
    end($stack1);
    $probable1_clientContactID = key($stack1);
    $person_percent1 = current($stack1);

    if ($probable1_clientContactID && $person_percent1 > 70) {
      return $probable1_clientContactID;
    }
  }

  function find_by_email($email=false,$projectID=false) {
    static $people;
    if (!$people) {
      $people = array();
      $q = sprintf("SELECT clientContact.clientContactID, clientContact.clientContactEmail
                      FROM client
                 LEFT JOIN clientContact ON client.clientID = clientContact.clientID
                 LEFT JOIN project ON project.clientID = client.clientID 
                     WHERE project.projectID = %d
                   ",$projectID);
      $db = new db_alloc();
      $db->query($q);
      while ($row = $db->row()) {
        $people[$db->f("clientContactID")] = $row;
      }
    }

    $email = str_replace(array("<",">"),"",$email);
    foreach($people as $clientContactID => $row) {
      if ($email == $row["clientContactEmail"]) {
        return $clientContactID;
      }
    }
  }

  function delete() {
    // have to null out any records that point to this clientContact first to satisfy the referential integrity constraints
    if ($this->get_id()) {
      $db = new db_alloc();
      $q = sprintf("UPDATE interestedParty SET clientContactID = NULL where clientContactID = %d",$this->get_id());
      $db->query($q);
      $q = sprintf("UPDATE comment SET commentCreatedUserClientContactID = NULL where commentCreatedUserClientContactID = %d",$this->get_id());
      $db->query($q);
      $q = sprintf("UPDATE project SET clientContactID = NULL where clientContactID = %d",$this->get_id());
      $db->query($q);
    }
    return parent::delete();
  }

}



?>
