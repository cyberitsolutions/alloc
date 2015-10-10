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

class audit extends db_entity {
  public $data_table = "audit";
  public $key_field = "auditID";
  public $data_fields = array("auditID"
                             ,"taskID"
                             ,"projectID"
                             ,"personID"
                             ,"dateChanged"
                             ,"field"
                             ,"value"
                             );

  public static function get_list($_FORM) {
    /*
     *
     * Get a list of task history items with sophisticated filtering and somewhat sophisticated output
     *
     * (n.b., the output from this generally needs to be post-processed to handle the semantic meaning of changes in various fields)
     *
     */

    $filter = audit::get_list_filter($_FORM);

    if(is_array($filter) && count($filter)) {
      $where_clause = " WHERE " . implode(" AND ", $filter);
    }

    if ($_FORM["projectID"]) {
      $entity = new project();
      $entity->set_id($_FORM["projectID"]);
      $entity->select();
    } else if ($_FORM["taskID"]) {
      $entity = new task();
      $entity->set_id($_FORM["taskID"]);
      $entity->select();
    }


    $q = "SELECT *
            FROM audit
          $where_clause
        ORDER BY dateChanged";

    $db = new db_alloc();
    $db->query($q);

    $items = array();
    while($row = $db->next_record()) {
      $audit = new audit();
      $audit->read_db_record($db);
      $rows[] = $row;
    }

    return $rows;
  }


  public static function get_list_filter($filter) {
    $filter["taskID"]    and $sql[] = prepare("(taskID = %d)", $filter["taskID"]);
    $filter["projectID"] and $sql[] = prepare("(projectID = %d)", $filter["projectID"]);
    return $sql;
  }

  function get_list_vars() {
    return array("taskID"           => "The task id to find audit records for"
                ,"projectID"        => "The project id to find audit records for"
                );
  }

}
?>
