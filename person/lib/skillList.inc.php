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

class skillList extends db_entity {
  public $data_table = "skillList";
  public $display_field_name = "skillName";
  public $key_field = "skillID";
  public $data_fields = array("skillName"
                             ,"skillDescription"
                             ,"skillClass"
                             );

  // return true if a skill with same name and class already exists
  // and update fields of current if it does exist
  function skill_exists() {
    $query = "SELECT * FROM skillList";
    $query.= sprintf(" WHERE skillName='%s'", $this->get_value('skillName'));
    $query.= sprintf(" AND skillClass='%s'", $this->get_value('skillClass'));
    $db = new db_alloc;
    $db->query($query);
    if ($db->next_record()) {
      $skillList = new skillList;
      $skillList->read_db_record($db);
      $this->set_id($skillList->get_id());
      $this->set_value('skillDescription', $skillList->get_value('skillDescription'));
      return TRUE;
    }
    return FALSE;
  }

  function get_skill_classes() {
    $db = new db_alloc;
    $skill_classes = array(""=>"Any Class");
    $query = "SELECT skillClass FROM skillList ORDER BY skillClass";
    $db->query($query);
    while ($db->next_record()) {
      $skillList = new skillList;
      $skillList->read_db_record($db);
      if (!in_array($skillList->get_value('skillClass'), $skill_classes)) {
        $skill_classes[$skillList->get_value('skillClass')] = $skillList->get_value('skillClass');
      }
    }
    return $skill_classes;
  }

  function get_skills() {
    global $TPL, $skill_class;
    $skills = array(""=>"Any Skill");
    $query = "SELECT * FROM skillList";
    if ($skill_class != "") {
      $query.= sprintf(" WHERE skillClass='%s'", $skill_class);
    }
    $query.= " ORDER BY skillClass,skillName";
    $db = new db_alloc;
    $db->query($query);
    while ($db->next_record()) {
      $skillList = new skillList;
      $skillList->read_db_record($db);
      $skills[$skillList->get_id()] = sprintf("%s - %s", $skillList->get_value('skillClass'), $skillList->get_value('skillName'));
    }
    return $skills;
  }

  
}



?>
