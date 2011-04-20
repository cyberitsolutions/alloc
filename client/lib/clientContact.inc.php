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

  function save() {
    $c = new client();
    $c->set_id($this->get_value("clientID"));
    $c->select();
    $c->save();
    return parent::save();
  }

  function find_by_name($name=false,$projectID=false) {

    $stack1 = array();

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
  
    foreach ($people as $personID => $row) {
      similar_text($row["clientContactName"],$name,$percent1);
      $stack1[$personID] = $percent1;
    }

    asort($stack1);
    end($stack1);
    $probable1_clientContactID = key($stack1);
    $person_percent1 = current($stack1);

    if ($probable1_clientContactID && $person_percent1 > 90) {
      return $probable1_clientContactID;
    }
  }

  function find_by_email($email=false,$projectID=false) {
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

    $email = str_replace(array("<",">"),"",$email);
    foreach($people as $clientContactID => $row) {
      if (strtolower($email) == strtolower($row["clientContactEmail"])) {
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

  function format_contact() {
    $this->get_value("clientContactName")          and $str.= $this->get_value("clientContactName",DST_HTML_DISPLAY)."<br>";
    $this->get_value("clientContactStreetAddress") and $str.= $this->get_value("clientContactStreetAddress",DST_HTML_DISPLAY)."<br>";  
    $this->get_value("clientContactSuburb")        and $str.= $this->get_value("clientContactSuburb",DST_HTML_DISPLAY)."<br>";  
    $this->get_value("clientContactPostcode")      and $str.= $this->get_value("clientContactPostcode",DST_HTML_DISPLAY)."<br>";  
    $this->get_value("clientContactPhone")         and $str.= $this->get_value("clientContactPhone",DST_HTML_DISPLAY)."<br>";
    $this->get_value("clientContactMobile")        and $str.= $this->get_value("clientContactMobile",DST_HTML_DISPLAY)."<br>";
    $this->get_value("clientContactFax")           and $str.= $this->get_value("clientContactFax",DST_HTML_DISPLAY)."<br>";
    $this->get_value("clientContactEmail")         and $str.= $this->get_value("clientContactEmail",DST_HTML_DISPLAY)."<br>";  
    return $str;
  }

  function output_vcard() {

    //array of mappings from DB field to vcard field
    $fields = array( //clientContactName is special
        "clientContactPhone" => "TEL;WORK;VOICE",
        "clientContactMobile" => "TEL;CELL",
        "clientContactFax" => "TEL;TYPE=WORK;FAX",
        "clientContactEmail" => "EMAIL;WORK"
    ); //address fields are handled specially because they're a composite of DB fields

    $vcard = array();

    // This could be templated, but there's not much point
    // Based off the vcard output by Android 2.1
    header("Content-type: text/x-vcard");
    $filename = strtr($this->get_value("clientContactName"), " ", "_") . ".vcf";
    header('Content-Disposition: attachment; filename="'.$filename.'"');

    print("BEGIN:VCARD\nVERSION:2.1\n");

    if ($this->get_value("clientContactName")) {
      //vcard stuff requires N to be last name; first name
      // Assume whatever comes after the last space is the last name
      // cut the string up to get <last name>;<everything else>
      $name = explode(" ",$this->get_value("clientContactName"));
      $lastname = array_slice($name, -1);
      $lastname = $lastname[0];

      $rest = implode(array_slice($name, 0, -1));
      print "N:" . $lastname . ";" . $rest . "\n";
      print "FN:" . $this->get_value("clientContactName")."\n";

    }

    foreach ($fields as $db => $label) {
      if ($this->get_value($db))
        print $label . ":" . $this->get_value($db) . "\n";
    }
    if ($this->get_value("clientContactStreetAddress")) {
        print "ADR;HOME:;;" . $this->get_value("clientContactStreetAddress") . ";" .
            $this->get_value("clientContactSuburb") . ";;" . //county or something
	    $this->get_value("clientContactPostcode") . ";" . 
	    $this->get_value("clientContactCountry") . "\n";
    }
    print("END:VCARD\n");
  }
}



?>
