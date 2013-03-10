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

define("PERM_PROJECT_PERSON_READ_DETAILS", 256);

class projectPerson extends db_entity {
  public $data_table = "projectPerson";
  public $display_field_name = "projectID";
  public $key_field = "projectPersonID";
  public $data_fields = array("personID"
                             ,"projectID"
                             ,"emailType"
                             ,"emailDateRegex"
                             ,"rate" => array("type"=>"money")
                             ,"rateUnitID"
                             ,"projectPersonModifiedUser"
                             ,"roleID"
                             );

  function date_regex_matches() {
    return eregi($this->get_value("emailDateRegex"), date("YmdD"));
  }


  function is_owner($person = "") {

    if (!$this->get_id()) {
      return true;
    } else {
      $project = new project();
      $project->set_id($this->get_value("projectID"));
      $project->select();
      return $project->is_owner($person);
    }
  }


  // This is a wrapper to simplify inserts into the projectPerson table using the new
  // Role methodology.. role handle is canEditTasks, or isManager atm
  function set_value_role($roleHandle) {
    $db = new db_alloc();
    $db->query(prepare("SELECT * FROM role WHERE roleHandle = '%s' AND roleLevel = 'project'",$roleHandle));
    $db->next_record();
    $this->set_value("roleID",$db->f("roleID"));
  }


  //deprecated in favour of get_rate
  function get_projectPerson_row($projectID, $personID) {
    $q = prepare("SELECT * 
                    FROM projectPerson 
                   WHERE projectID = %d AND personID = %d"
                ,$projectID,$personID);
    $db = new db_alloc();
    $db->query($q);
    return $db->row();
  }

  function get_rate($projectID, $personID) {
    // Try to get the person's rate from the following sources:
    // project.defaultTimeSheetRate
    // person.defaultTimeSheetRate
    // config.name == defaultTimeSheetRate

    // First check the project for a rate
    $project = new project($projectID);
    $row = array('rate' => $project->get_value("defaultTimeSheetRate"),
                 'unit' => $project->get_value("defaultTimeSheetRateUnitID"));
    if (imp($row['rate']) && $row['unit']) {
      return $row;
    }

    // Next check person, which is in global currency rather than project currency - conversion required
    $db = new db_alloc();
    $q = prepare("SELECT defaultTimeSheetRate as rate, defaultTimeSheetRateUnitID as unit FROM person WHERE personID = %d", $personID);
    $db->query($q);
    $row = $db->row();
    if (imp($row['rate']) && $row['unit']) {
      if ($project->get_value("currencyTypeID") != config::get_config_item("currency")) {
        $row['rate'] = exchangeRate::convert(config::get_config_item("currency"), $row["rate"], $project->get_value("currencyTypeID"));
      }
      return $row;
    }

    // Lowest priority: global
    $rate = config::get_config_item("defaultTimeSheetRate");
    $unit = config::get_config_item("defaultTimeSheetUnit");
    if (imp($rate) && $unit) {
      if (config::get_config_item("currency") && $project->get_value("currencyTypeID")) {
        $rate = exchangeRate::convert(config::get_config_item("currency"), $rate, $project->get_value("currencyTypeID"));
      }
      return array('rate'=>$rate, 'unit'=>$unit);
    }
  }
}



?>
