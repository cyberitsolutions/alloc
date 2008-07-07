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
  var $classname = "clientContact";
  var $data_table = "clientContact";
  var $display_field_name = "clientContactName";

  function clientContact() {
    $this->db_entity();
    $this->key_field = new db_field("clientContactID");
    $this->data_fields = array("clientID"=>new db_field("clientID")
                              ,"clientContactName"=>new db_field("clientContactName")
                              ,"clientContactStreetAddress"=>new db_field("clientContactStreetAddress")
                              ,"clientContactSuburb"=>new db_field("clientContactSuburb")
                              ,"clientContactState"=>new db_field("clientContactState")
                              ,"clientContactPostcode"=>new db_field("clientContactPostcode")
                              ,"clientContactCountry"=>new db_field("clientContactCountry")
                              ,"clientContactPhone"=>new db_field("clientContactPhone")
                              ,"clientContactMobile"=>new db_field("clientContactMobile")
                              ,"clientContactFax"=>new db_field("clientContactFax")
                              ,"clientContactEmail"=>new db_field("clientContactEmail")
                              ,"clientContactOther"=>new db_field("clientContactOther")
                              );
  }

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


}



?>
