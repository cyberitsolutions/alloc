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

class skill extends db_entity {
  public $data_table = "skill";
  public $display_field_name = "skillName";
  public $key_field = "skillID";
  public $data_fields = array("skillName"
                             ,"skillDescription"
                             ,"skillClass"
                             );

  // return true if a skill with same name and class already exists
  // and update fields of current if it does exist
  function skill_exists() {
    $query = "SELECT * FROM skill";
    $query.= prepare(" WHERE skillName='%s'", $this->get_value('skillName'));
    $query.= prepare(" AND skillClass='%s'", $this->get_value('skillClass'));
    $db = new db_alloc();
    $db->query($query);
    if ($db->next_record()) {
      $skill = new skill();
      $skill->read_db_record($db);
      $this->set_id($skill->get_id());
      $this->set_value('skillDescription', $skill->get_value('skillDescription'));
      return TRUE;
    }
    return FALSE;
  }

  function get_skill_classes() {
    $db = new db_alloc();
    $skill_classes = array(""=>"Any Class");
    $query = "SELECT skillClass FROM skill ORDER BY skillClass";
    $db->query($query);
    while ($db->next_record()) {
      $skill = new skill();
      $skill->read_db_record($db);
      if (!in_array($skill->get_value('skillClass'), $skill_classes)) {
        $skill_classes[$skill->get_value('skillClass')] = $skill->get_value('skillClass');
      }
    }
    return $skill_classes;
  }

  function get_skills() {
    global $TPL;
    global $skill_class;
    $skills = array(""=>"Any Skill");
    $query = "SELECT * FROM skill";
    if ($skill_class != "") {
      $query.= prepare(" WHERE skillClass='%s'", $skill_class);
    }
    $query.= " ORDER BY skillClass,skillName";
    $db = new db_alloc();
    $db->query($query);
    while ($db->next_record()) {
      $skill = new skill();
      $skill->read_db_record($db);
      $skills[$skill->get_id()] = sprintf("%s - %s", $skill->get_value('skillClass'), $skill->get_value('skillName'));
    }
    return $skills;
  }

  
}



?>
