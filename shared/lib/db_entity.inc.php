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

define("PERM_READ", 1);
define("PERM_UPDATE", 2);
define("PERM_DELETE", 4);
define("PERM_CREATE", 8);
define("PERM_READ_WRITE", PERM_READ + PERM_UPDATE + PERM_DELETE + PERM_CREATE);

class db_entity {
  public $classname = "db_entity";   // Support phplib session variables
  public $data_table = "";           // Set this to the name of the data base table
  public $key_field = "";            // Set this to the table's primary key
  public $data_fields = array();     // Set this to the data fields using array("field_name1","field_name2");
  public $all_row_fields = array();  // This gets set to all fields from the row of the query result used to load this entity
  public $db_class = "db_alloc";
  public $db;
  public $debug = false;
  public $display_field_name;        // Set this to the field to be used by the get_display_value function
  public $cache;                     // Cache associative array stored by primary key index
  private $fields_loaded = false;    // This internal flag just specifies whether a row from the db was loaded


  function __construct($id = false) {

    $this->data_table or $this->data_table = get_class($this);
    $this->classname  or $this->classname  = get_class($this);
  
    $this->permissions[PERM_READ]   = "Read";
    $this->permissions[PERM_UPDATE] = "Update";
    $this->permissions[PERM_DELETE] = "Delete";
    $this->permissions[PERM_CREATE] = "Create";

    // convert key_field into a field object
    $this->key_field = new db_field($this->key_field); 

    // we're going to reload the data_fields as $name => $object
    $fields = $this->data_fields;
    unset($this->data_fields); 

    // convert data_fields into field objects
    foreach ($fields as $k=>$v) {

      // This caters for per-field options.
      if (is_array($v)) {
        $this->data_fields[$k] = new db_field($k, $v);
      } else {
        $this->data_fields[$v] = new db_field($v);
      }
    }

    // If we're passed an id load this object up
    if ($id) {
      $this->set_id($id);
      $this->select();
    }
  }

  function have_perm($action = 0, $person = "", $assume_owner = false) {
    $current_user = &singleton("current_user");
    global $permission_cache;
    if (defined("IS_GOD")) {
      return true;
    }

    if (!$person) {
      if ($current_user && is_object($current_user) && method_exists($current_user,"get_id") && $current_user->get_id()) {
        $person = $current_user;
      }
    }

    $entity_id = 0;
    
    if (is_object($person) && method_exists($person,"get_id") && $person->get_id()) {
      $person_id = $person->get_id();
      $person_type = $person->classname;
      $person_id and $person_flag = $person_type."_".$person_id;
    }

    $record_cache_key = $this->data_table.":".$entity_id.":".$action.":".$person_flag.":".$assume_owner;
    $table_cache_key = $this->data_table.":T:".$action.":".$person_flag.":".$assume_owner;

    if (isset($permission_cache[$table_cache_key])) {
      return $permission_cache[$table_cache_key];
    } else if (isset($permission_cache[$record_cache_key])) {
      return $permission_cache[$record_cache_key];
    }

    $db = new db_alloc();
    $query = prepare("SELECT * 
                        FROM permission 
                        WHERE (tableName = '%s')
                         AND (actions & %d = %d)
                    ORDER BY entityID DESC"
                    ,$this->data_table,$action,$action);
    $db->query($query);
    
    while ($db->next_record()) {

      // Ignore this record if it specifies a role the user doesn't have
      if ($db->f("roleName") && is_object($person) && !$person->have_role($db->f("roleName"))) {
        continue;
      }

      // Ignore this record if it specifies that the user must be the record's owner and they are not
      if ($db->f("entityID") == -1 && !$assume_owner && !$this->is_owner($person)) {
        continue;
      }

      // Cache the result in variables to prevent duplicate database lookups
      $permission_cache[$record_cache_key] = true;
      if ($db->f("entityID") == 0) {
        $permission_cache[$table_cache_key] = true;
      }

      return true;
    }

    // No matching records - return false
    $permission_cache[$record_cache_key] = false;
    return false;
  }

  function select() {
    if (!$this->has_key_values()) {
      return false;
    }
    if ($this->data_table && $this->key_field) {
      $query = "SELECT * FROM ".db_esc($this->data_table)." WHERE ".$this->get_name_equals_value(array($this->key_field), " AND ");
      if ($this->debug)
        echo "db_entity->select query: $query<br>\n";
      $db = $this->get_db();
      $db->query($query);
      if ($db->next_record()) {
        $this->read_db_record($db);
        return true;
      }
    }
  }
 
  function perm_cleanup(&$row=array()) {
    foreach ($row as $field_name => $object) {
      if (!$this->can_read_field($field_name)) {
        $str = "Permission denied to ".$this->permissions[$this->data_fields[$field_name]->read_perm_name]." of ".$this->data_table.".".$field_name;
        $row[$field_name] = $str;
      }
    }
    return $row;
  }

  function delete() {
    if (!$this->have_perm(PERM_DELETE)) {
      $current_user = &singleton("current_user");
      alloc_error(sprintf("Person %d does not have permission %s for %s #%d"
                         ,$current_user->get_id(), $this->permissions[PERM_DELETE], $this->data_table, $this->get_id()));
      return false;
    }
    if (!$this->has_key_values()) {
      return false;
    }
    $query = "DELETE FROM ".db_esc($this->data_table)." WHERE ".$this->get_name_equals_value(array($this->key_field), " AND ");
    if ($this->debug)
      echo "db_entity->delete query: $query<br>\n";
    $db = $this->get_db();
    $db->query($query);

    return true;
  }

  function insert() {
    $current_user = &singleton("current_user");
    if (is_object($current_user) && method_exists($current_user,"get_id") && $current_user->get_id()) {
      $current_user_id = $current_user->get_id();
    } else {
      $current_user_id = "0";
    }
    if (!$this->have_perm(PERM_CREATE)) {
      $current_user = &singleton("current_user");
      if (is_object($this) && method_exists($this,"get_id")) {
        $this_id = $this->get_id();
      }
      alloc_error(sprintf("Person %d does not have permission %s for %s #%d"
                         ,$current_user_id, $this->permissions[PERM_CREATE], $this->data_table, $this_id));
      return false;
    }

    if (isset($this->data_fields[$this->data_table."CreatedUser"]) && $current_user_id) {
      $this->set_value($this->data_table."CreatedUser", $current_user_id);
    }
    if (isset($this->data_fields[$this->data_table."CreatedTime"])) {
      $this->set_value($this->data_table."CreatedTime", date("Y-m-d H:i:s"));
    }
    if (isset($this->data_fields[$this->data_table."ModifiedUser"])) {
      #$this->set_value($this->data_table."ModifiedUser", $current_user_id);
    }
    if (isset($this->data_fields[$this->data_table."ModifiedTime"])) {
      #$this->set_value($this->data_table."ModifiedTime", date("Y-m-d H:i:s"));
    }

    // Even if we're doing an insert, if a primary key is set, then insert the row with that PK.
    if ($this->get_id()) {
      $this->data_fields[] = $this->key_field;
    }

    $query = "INSERT INTO ".db_esc($this->data_table)." (";
    $query.= $this->get_insert_fields($this->data_fields);
    $query.= ") VALUES (";
    $query.= $this->get_insert_values($this->data_fields);
    $query.= ")";
    $this->debug and print "<br>db_entity->insert() query: ".$query;
    $db = $this->get_db();
    $db->query($query);

    $id = $db->get_insert_id();
    if ($id === 0) {
      $this->debug and print "<br>db_entity->insert(): The previous query does not generate an AUTO_INCREMENT value`";
    } else if ($id === FALSE) {
      $this->debug and print "<br>db_entity->insert(): No MySQL connection was established";
    } else {
      $this->debug and print "<br>db_entity->insert(): New ID: ".$id;
    }
    $this->key_field->set_value($id);

    return true;
  }

  function update() {

    if (!$this->have_perm(PERM_UPDATE)) {
      $current_user = &singleton("current_user");
      alloc_error(sprintf("Person %d does not have permission %s for %s #%d"
                         ,$current_user->get_id(), $this->permissions[PERM_UPDATE], $this->data_table, $this->get_id()));
      return false;
    }

    // retrieve a copy of this object with the old values from the database
    if (class_exists($this->data_table)) {
      $old_this = new $this->data_table;
      $old_this->set_id($this->get_id());
      $old_this->select();
    }

    $current_user = &singleton("current_user");
    if (is_object($current_user) && $current_user->get_id()) {
      $current_user_id = $current_user->get_id();
    } else {
      $current_user_id = "0";
    }

    if (!$this->skip_modified_fields) {
      if (isset($this->data_fields[$this->data_table."ModifiedUser"]) && $current_user_id) {
        $this->set_value($this->data_table."ModifiedUser", $current_user_id);
      }
      if (isset($this->data_fields[$this->data_table."ModifiedTime"])) {
        $this->set_value($this->data_table."ModifiedTime", date("Y-m-d H:i:s"));
      }
    }

    $write_fields = array();

    reset($this->data_fields);
    while (list(, $field) = each($this->data_fields)) {
      if ($this->can_write_field($field)) {
        $write_fields[] = $field;
      }
    }

    $query = "UPDATE ".db_esc($this->data_table)." SET ".$this->get_name_equals_value($write_fields)." WHERE ";
    $query.= $this->get_name_equals_value(array($this->key_field));
    $db = $this->get_db();
    $this->debug and print "<br>db_entity->update() query: ".$query;
    $db->query($query);
    return true;
  }

  function is_new() {
    if (!$this->has_key_values()) {
      return true;
    } else if ($this->key_field->has_value() && $this->key_field->get_name() && $this->key_field->get_value()) {
      $db = $this->get_db();
      $row = $db->qr("SELECT ".db_esc($this->key_field->get_name())."
                        FROM ".db_esc($this->data_table)."
                       WHERE ".db_esc($this->key_field->get_name())." = '".db_esc($this->key_field->get_value())."'");
      return !$row;
    }
  }

  function save() {
    global $TPL;
    $this->doMoney = true;
    $error = $this->validate();
    if (is_array($error) && count($error)) {
      alloc_error(implode(" ",$error));
      return false;
    } else if (strlen($error) && $error) {
      alloc_error($error);
      return false;
    }

    if ($this->is_new()) {
      $rtn = $this->insert();
    } else {
      $rtn = $this->update();
    }

    // Update the search index for this entity, if any
    if ($rtn && $this->get_id() && $this->classname && is_dir(ATTACHMENTS_DIR.'search/'.$this->classname)) {

      // Update the index asynchronously (later from a job running search/updateIndex.php)
      if ($this->updateSearchIndexLater) {
        $i = new indexQueue();
        $i->set_value("entity",$this->classname);
        $i->set_value("entityID",$this->get_id());
        $i->save();

      // Update the index right now
      } else {
        $index = Zend_Search_Lucene::open(ATTACHMENTS_DIR.'search/'.$this->classname);
        $this->delete_search_index_doc($index);
        $this->update_search_index_doc($index);
        $index->commit();
      }
    }

    return $rtn;
  }

  function validate($message=array()) {
    $c = $this->currency;
    if (isset($this->data_fields["currencyTypeID"]) && imp($this->data_fields["currencyTypeID"]->get_value())) {
      $c = $this->data_fields["currencyTypeID"]->get_value();
    }
    $c and $this->currency = $c;
    reset($this->data_fields);
    while (list($field_index, $field) = each($this->data_fields)) {
      $message[] = $field->validate($this);
    }

    $message = implode(" ",$message);
    if ($message && preg_match("/[a-zA-Z0-9]+/",$message)) 
    return $message;
  }

  function set_field_value(&$field, $value, $source = SRC_VARIABLE) {
    $field->set_value($value, $source);
  }

  function clear_field_value(&$field) {
    $field->clear_value();
  }

  function read_array(&$array, $source_prefix = "", $source = SRC_VARIABLE) {

    // Data fields
    foreach ($this->data_fields as $field_index=>$field) {
      $source_index = $source_prefix.$field->get_name();
      $this->set_field_value($this->data_fields[$field_index], $array[$source_index], $source);
    }

    // Key field
    $source_index = $source_prefix.$this->key_field->get_name();
    $this->key_field->set_value($array[$source_index], $source);
    $this->debug and print "db_entity->read_array key_field->set_value(".$array[$source_index].", $source)<br>\n";
    $this->fields_loaded = true;
  }

  function write_array(&$array, $dest = DST_VARIABLE, $array_index_prefix = "") {

    // Data fields
    reset($this->data_fields);
    while (list($field_name) = each($this->data_fields)) {
      $array_index = $array_index_prefix.$field_name;
      $array[$array_index] = $this->get_value($field_name, $dest);
    }

    // Key field
    $array_index = $array_index_prefix.$this->key_field->get_name();
    $array[$array_index] = $this->key_field->get_value($dest);
  }

  function set_values($tpl_key_prefix="") {
    $this->write_array($GLOBALS["TPL"], DST_VARIABLE, $tpl_key_prefix);
  }

  function set_tpl_values($tpl_key_prefix = "") {
    $this->write_array($GLOBALS["TPL"], DST_HTML_DISPLAY, $tpl_key_prefix);
  }

  function read_globals($prefix = "", $source = SRC_VARIABLE) {
    $this->read_array($_POST, $prefix, $source);
  }

  function set_global_variables($variable_name_prefix = "") {
    $this->write_array($GLOBALS, DST_VARIABLE, $variable_name_prefix);
  }

  function has_key_values() {
    return $this->key_field->has_value();
  }

  function read_db_record($db) {
    $this->set_id($db->f($this->key_field->get_name()));
    $this->read_array($db->row, "", SRC_DATABASE);
    $this->all_row_fields = $db->row;
    $have_perm = $this->have_perm(PERM_READ);
    if (!$have_perm) {
      $m = new meta();
      $meta_tables = (array)$m->get_tables();
      $meta_tables = array_keys($meta_tables);
      if (in_array($this->data_table,(array)$meta_tables)) {
        return true;
      }
      $this->clear();
    }
    return $have_perm;
  } 

  function read_row_record($row) {
    $this->set_id($row[$this->key_field->get_name()]);
    $this->read_array($row, "", SRC_DATABASE);
    $this->all_row_fields = $row;
    $have_perm = $this->have_perm(PERM_READ);
    if (!$have_perm) {
      $this->clear();
    }
    return $have_perm;
  } 

  function row() {
    return $this->all_row_fields;
  }

  function set_value($field_name, $value, $source = SRC_VARIABLE) {
    if (is_object($this->data_fields[$field_name])) {
      $this->set_field_value($this->data_fields[$field_name], $value, $source);
    } else {
      alloc_error("Cannot set field value - field not found: ".$field_name);
    }
  }

  function get_value($field_name, $dest = DST_VARIABLE) {
    $field = $this->data_fields[$field_name];
    if (!is_object($field)) {
      $msg = "Field $field_name does not exist in ".$this->data_table;
      alloc_error($msg);
      return $msg;
    }
    if (!$this->can_read_field($field_name)) {
      return "Permission denied to ".$this->permissions[$this->data_fields[$field_name]->read_perm_name]." of ".$this->data_table.".".$field_name;
    }

    $c = $this->currency;
    if (isset($this->data_fields["currencyTypeID"]) && imp($this->data_fields["currencyTypeID"]->get_value())) {
      $c = $this->data_fields["currencyTypeID"]->get_value();
    }
    $c and $this->currency = $c;
    return $field->get_value($dest,$this);
  }

  function get_row_value($field_name) {
    return $this->all_row_fields[$field_name];
  }

  function get_id($dest = DST_VARIABLE) {
    return $this->key_field->get_value($dest);
  }

  function set_id($id) {
    $this->key_field->set_value(db_esc($id));
  }

  function get_foreign_object($class_name, $key_name = "") {
    if ($key_name == "") {
      $key_name = $class_name."ID";
    }
    $object = new $class_name;
    $object->set_id($this->get_value($key_name, DST_VARIABLE));
    $object->select();
    return $object;
  }

  function get_foreign_objects($class_name, $key_name = "") {
    if ($key_name == "") {
      $key_name = $this->key_field->get_name();
    }
    $foreign_objects = array();
    $query = prepare("SELECT * FROM %s WHERE %s = %d",$class_name,$key_name,$this->get_id());
    $db = new db_alloc();
    $db->query($query);
    while ($db->next_record()) {
      $o = new $class_name;
      $o->read_db_record($db);
      $foreign_objects[$o->get_id()] = $o;
    }
    return $foreign_objects;
  }

  function get_filter_class() {
    return $this->filter_class;
  }

  function get_display_value($dst=DST_HTML_DISPLAY) {
    if ($this->display_field_name) {
      if (!$this->fields_loaded) {
        $found = $this->select();
        if (!$found) {
          return "";
        }
      }
      return $this->get_value($this->display_field_name, $dst);
    } else {
      return "#".$this->get_id();
    }
  }

  function can_read_field($field_name) {
    $field = $this->data_fields[$field_name];
    if (is_object($field) && $field->read_perm_name) {
      return $this->have_perm($field->read_perm_name);
    } else {
      return true;
    }
  }

  function can_write_field($field) {
    if (is_string($field)) {
      $field = $this->data_fields[$field];
    }
    return $field->write_perm_name == 0 || $this->have_perm($field->write_perm_name);
  }

  function is_owner($person = "") {
    $current_user = &singleton("current_user");
    $person or $person = $current_user;
    if (is_object($person) && $person->classname == "person") {
      if (isset($this->data_fields["personID"])) {
        return $this->get_value("personID") == $person->get_id();
      } else if ($this->key_field->get_name() == "personID") {
        return $this->get_id() == $person->get_id();
      } else {
        echo "Warning: could not determine owner for ".$this->data_table."<br>";
      }
    }
  }

  function clear() {

    // Data fields
    reset($this->data_fields);
    while (list($field_index,) = each($this->data_fields)) {
      $this->clear_field_value($this->data_fields[$field_index]);
    }

    // Key field
    $this->key_field->clear_value();
    if ($this->debug)
      echo "db_entity->read_array key_field->set_value(".$array[$source_index].", $source)<br>\n";
    $this->fields_loaded = false;
  }

  /**************************************************************************
  'Private' utilitity functions These functions probably won't be useful to
  users of this class but are used by the other functions in the class
  ***************************************************************************/
  function get_db() {
    if (!is_object($this->db)) {
      $db_class = $this->db_class;
      $this->db = new $db_class;
    }
    return $this->db;
  }
  function get_insert_fields($fields) {
    foreach((array)$fields as $k=>$field) {
      if (strtolower($field->get_value(DST_DATABASE)) != "null") {
        $rtn.= $comma.$field->get_name();
        $comma = ",";
      }
    }
    return $rtn;
  }
  function get_insert_values($fields) {
    foreach((array)$fields as $k=>$field) {
      if (strtolower($field->get_value(DST_DATABASE)) != "null") {
        $rtn.= $comma.$field->get_value(DST_DATABASE);
        $comma = ",";
      }
    }
    return $rtn;
  }
  function get_name_equals_value($fields, $glue = ",") {
    $query = "";
    reset($fields);
    while (list(, $field) = each($fields)) {
      if ($query) {
        $query.= $glue;
      }
      $query.= $field->get_name()." = ".$field->get_value(DST_DATABASE);
    }
    return $query;
  }

  function get_assoc_array($key=false,$value=false,$sel=false, $where=array()){
    $key or $key = $this->key_field->get_name();
    $value or $value = "*";
    $value != "*" and $key_sql = $key.",";

    $q = sprintf('SELECT %s %s FROM %s WHERE 1=1 '
                ,db_esc($key_sql),db_esc($value),db_esc($this->data_table));

    $pkey_sql = " OR ".$this->key_field->get_name()." = ";
    if (is_array($sel) && count($sel)) {
      foreach ($sel as $s) {
        $extra.= $pkey_sql.sprintf("%d",$s);
      }
    } else if ($sel) {
      $extra = $pkey_sql.db_esc($sel);
    }

    // If they haven't specifically asked for inactive or all
    // records, we default to giving them only active records.
    if (is_object($this->data_fields[$this->data_table."Active"]) && !isset($where[$this->data_table."Active"])) {
      $where[$this->data_table."Active"] = 1;

    // Else get all records
    } else if ($where[$this->data_table."Active"] == "all") {
      unset($where[$this->data_table."Active"]);
    }

    if (is_array($where) && count($where)) {
      foreach ($where as $colname => $colvalue) {
        $q.= " AND ".$colname." = '".db_esc($colvalue)."'";
      }
    }

    $q.= $extra;

    if (is_object($this->data_fields[$this->data_table."Sequence"])) {
      $q.= " ORDER BY ".db_esc($this->data_table)."Sequence";
    } else if (is_object($this->data_fields[$this->data_table."Seq"])) {
      $q.= " ORDER BY ".db_esc($this->data_table)."Seq";
    } else if ($value != "*") {
      $q.= " ORDER BY ".db_esc($value);
    }

    $db = new db_alloc();
    $db->query($q);
    $rows = array();
    while($row = $db->row()) {
      if ($this->read_db_record($db)) {
        if ($value && $value != "*") {
          $v = $row[$value];
        } else {
          $v = $row;
        }
        $rows[$row[$key]] = $v;
      }
    }
    return $rows;
  }

  function get_link($field=false) {
    global $TPL;
    if ($this->get_id()) {
      $label = $this->get_display_value();

      if ($field && $this->key_field->get_name() == $field) {
        $label = $this->get_id();
      } else if ($field) {
        $label = $this->get_value($field,DST_HTML_DISPLAY);
      }
      return "<a href=\"".$TPL["url_alloc_".$this->classname].$this->key_field->get_name()."=".$this->get_id()."\">".$label."</a>";
    }
  }

  function get_name($dst=null) {
    return $this->get_display_value($dst);
  }

  function delete_search_index_doc(&$index) {
    if ($this->get_id()) {
      $hits = $index->find('id:' . $this->get_id());
      foreach ($hits as $hit) {
        $index->delete($hit->id);
      }
    }
    return $index;
  }

}


// Statically callable version of $entity->have_perm();
function have_entity_perm($class_name, $perm_name = "", $person = "", $assume_owner = false) {
  $entity = new $class_name;
  $entity->set_id(0);
  return $entity->have_perm($perm_name, $person, $assume_owner);
}


?>
