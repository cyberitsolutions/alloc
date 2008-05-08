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

define("PERM_PROJECT_PERSON_READ_DETAILS", 256);

class projectPerson extends db_entity
{
  var $data_table = "projectPerson";
  var $display_field_name = "projectID";

  function projectPerson() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_field("projectPersonID");
    $this->data_fields = array("personID"=>new db_field("personID")
                              ,"projectID"=>new db_field("projectID")
                              ,"emailType"=>new db_field("emailType")
                              ,"emailDateRegex"=>new db_field("emailDateRegex")
                              ,"rate"=>new db_field("rate")
                              ,"rateUnitID"=>new db_field("rateUnitID")
                              ,"projectPersonModifiedUser"=>new db_field("projectPersonModifiedUser")
                              ,"roleID"=>new db_field("roleID")
                              );
  }

  function date_regex_matches() {
    return eregi($this->get_value("emailDateRegex"), date("YmdD"));
  }


  function is_owner($person = "") {

    if (!$this->get_id()) {
      return true;
    } else {
      $project = new project;
      $project->set_id($this->get_value("projectID"));
      $project->select();
      return $project->is_owner($person);
    }
  }


  // This is a wrapper to simplify inserts into the projectPerson table using the new
  // Role methodology.. role handle is canEditTasks, or isManager atm
  function set_value_role($roleHandle) {
    $db = new db_alloc;
    $db->query(sprintf("SELECT * FROM role WHERE roleHandle = '%s' AND roleLevel = 'project'",$roleHandle));
    $db->next_record();
    $this->set_value("roleID",$db->f("roleID"));
  }



  function get_projectPerson_row($projectID, $personID) {
    $q = sprintf("SELECT * 
                    FROM projectPerson 
                   WHERE projectID = %d AND personID = %d"
                ,$projectID,$personID);
    $db = new db_alloc();
    $db->query($q);
    return $db->row();
  }


}



?>
