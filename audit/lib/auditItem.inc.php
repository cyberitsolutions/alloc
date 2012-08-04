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

class auditItem extends db_entity {
  public $data_table = "auditItem";
  public $key_field = "auditItemID";
  public $data_fields = array("auditItemID"
                             ,"entityName"
                             ,"entityID"
                             ,"personID"
                             ,"dateChanged"
                             ,"changeType"
                             ,"fieldName"
                             ,"oldValue"
                             );
  var $newValue;

  function get_list($_FORM) {
    /*
     *
     * Get a list of task history items with sophisticated filtering and somewhat sophisticated output
     *
     * (n.b., the output from this generally needs to be post-processed to handle the semantic meaning of changes in various fields)
     *
     */

    $filter = auditItem::get_list_filter($_FORM);

    $_FORM["return"] or $_FORM["return"] = "array";

    if(is_array($filter) && count($filter)) {
      $where_clause = " WHERE " . implode(" AND ", $filter);
    }

    $entity = new $_FORM["entityType"];
    $entity->set_id($_FORM["entityID"]);
    $entity->select();

    $q = "SELECT *
            FROM auditItem
          $where_clause
        ORDER BY dateChanged";

    $db = new db_alloc();
    $db->query($q);

    $items = array();
    while($row = $db->next_record()) {
      $auditItem = new auditItem();
      $auditItem->read_db_record($db);
      $items[] = $auditItem;
    }

    for($i = 0; $i < count($items); ++$i) {
      $item = &$items[$i];
      if($item->get_value('changeType') == "FieldChange") {
        $item->set_new_value(auditItem::find_new_value_in_history($items, $item->get_value('fieldName'), $i + 1, $entity));
      }
    }

    if($_FORM["return"] == "array") {
      return $items;
    }
  }

  // helper function for get_changes_list, finds the new value at a given point in time
  function find_new_value_in_history(&$items, $fieldName, $start, $entity) {
    for($i = $start; $i < count($items); ++$i) {
      $auditItem = $items[$i];
      if($auditItem->get_value('changeType') == 'FieldChange' && $auditItem->get_value('fieldName') == $fieldName) {
        return $auditItem->get_value('oldValue');
      }
    }
    // there are no more changes to this field, so the new value is the current value
    return $entity->get_value($fieldName);
  }


  function get_list_filter($filter) {
    $sql[] = prepare("(entityName = '%s')", $filter["entityType"]);
    $sql[] = prepare("(entityID = %d)", $filter["entityID"]);
    return $sql;
  }

  function get_list_vars() {

    return array("return"           => "[MANDATORY] options: array"
                ,"entityType"       => "[MANDATORY] The entity type to find audit records for e.g. task"
                ,"entityID"         => "[MANDATORY] The entity id to find audit records for"
                );
  }

  function set_new_value($value) {
    $this->newValue = $value;
  }

  function get_new_value() {
    return $this->newValue;
  }

}



?>
