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
  public static $errors_fatal = true;
  
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

  function skip_errors() {
    self::$errors_fatal = false;
  }

  function err($str) {
    if (self::$errors_fatal) {
      die($str);
    } else {
      echo $str;
    }
  }

  function get($id=array()) {
    if ($id && is_numeric($id)) {
      $db = $this->get_db();
      $q = sprintf("SELECT * FROM ".$this->data_table." WHERE id = %d",$id);
      $db->query($q);
      return $db->row();

    } else {
      $db = $this->get_db();
      foreach ((array)$id as $field=>$value) {
        unset($op,$quotes);
        // look for "field !=" etc
        preg_match("/(>|<|=|!=|>=|<=|<>)\s*$/",$field,$m);
        isset($m[1]) or $op = "=";
      
        // all strings (except null) should be surrounded in quotes
        !is_numeric($value) && $value !== null and $quotes = '"';

        $str .= $and." ".$field." ".$op." ".$quotes.$db->esc($value).$quotes;
        $and = " AND ";
      }
      $str and $str = " WHERE ".$str;
      $q = sprintf("SELECT * FROM ".$this->data_table.$str);
      $db->query($q);
      while ($row = $db->row()) {
        $rows[] = $row;
      }
      return (array)$rows;
    }
  }

  function is_god() {
    global $current_user;
    if (is_object($current_user)) {
      $perms = explode(",", $current_user->get_value("perms"));
      if (in_array("god", $perms)) {
        return true;
      } else {
        return false;
      }
    }
  }

  function have_perm($action = 0, $person = "", $assume_owner = false) {
    global $current_user, $permission_cache, $guest_permission_cache;
    if ($this->is_god() || defined("IS_GOD")) {
      return true;
    }

    if ($person == "") {
      $current_user and $person = $current_user;
    }

    $entity_id = $this->get_id();
    if (!$entity_id) {
      $entity_id = 0;
    }
    
    if (is_object($person)) {
      $person_id = $person->get_id();
      $person_type = $person->classname;
      $person_id and $person_flag = $person_type."_".$person_id;
    }

    $record_cache_key = $this->data_table.":".$entity_id.":".$action.":".$person_flag.":".$assume_owner;
    $table_cache_key = $this->data_table.":T:".$action.":".$person_flag.":".$assume_owner;

    // This allows us to hardcode a (guest) user's perms, see receiveEmail.php
    foreach ((array)$guest_permission_cache as $r) {
      if ($this->data_table == $r["entity"] && $entity_id == $r["entityID"] && ($r["perms"] & $action == $action)) {
        $permission_cache[$record_cache_key] = true;
        $permission_cache[$table_cache_key] = true;
      }
    }

    if (isset($permission_cache[$table_cache_key])) {
      return $permission_cache[$table_cache_key];
    } else if (isset($permission_cache[$record_cache_key])) {
      return $permission_cache[$record_cache_key];
    }

    $db = new db_alloc;
    $query = sprintf("SELECT * 
                        FROM permission 
                        WHERE (tableName = '".$this->data_table."' OR tableName='')
                         AND (entityID = %d OR entityID = 0 OR entityID = -1)
                         AND (personID = %d OR personID IS NULL)
                         AND (actions & %d = %d OR actions = 0)
                    ORDER BY sortKey"
                    ,$entity_id,$person_id,$action,$action);
    $db->query($query);
    
    while ($db->next_record()) {

      // If the permission specifies a role, not having a $person is the same as having no roles
      if ($db->f("roleName") && !is_object($person)) {
        continue;
      }

      // Ignore this record if it specifies a role the user doesn't have
      if ($db->f("roleName") && is_object($person) && !$person->have_role($db->f("roleName"))) {
        continue;
      }

      // Ignore this record if it specifies that the user must be the record's owner and they are not
      if ($db->f("entityID") == -1 && !$assume_owner && !$this->is_owner($person)) {
        continue;
      }

      // Cache the result in variables to prevent duplicate database lookups
      $permission_cache[$record_cache_key] = $db->f("allow");
      if ($db->f("entityID") == 0) {
        $permission_cache[$table_cache_key] = $db->f("allow");
      }

      return $db->f("allow");
    }

    // No matching records - return false
    $permission_cache[$record_cache_key] = false;
    return false;
  }

  function check_perm($action = 0, $person = "", $assume_owner = false) {
    if ($this->have_perm($action, $person, $assume_owner)) {
      return true;

    } else {
      $description = $this->permissions[$action];
      if (!$description) {
        $description = $action;
      }

      $this->err("You do not have permission '$description' for ".$this->data_table." #".$this->get_id());
      return false;
    }
  }

  function select($errors_fatal=true) {
    if (!$this->has_key_values()) {
      return false;
    }
    $query = "SELECT * FROM $this->data_table WHERE ".$this->get_name_equals_value(array($this->key_field), " AND ");
    if ($this->debug)
      echo "db_entity->select query: $query<br>\n";
    $db = $this->get_db();
    $db->query($query);
    if ($db->next_record()) {
      $this->read_db_record($db,$errors_fatal);
      return true;
    } else {
      return false;
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
    if (!$this->check_perm(PERM_DELETE)) {
      return false;
    }
    if (!$this->has_key_values()) {
      return false;
    }
    $query = "DELETE FROM $this->data_table WHERE ".$this->get_name_equals_value(array($this->key_field), " AND ");
    if ($this->debug)
      echo "db_entity->delete query: $query<br>\n";
    $db = $this->get_db();
    $db->query($query);

    // and audit this
    $this->audit_delete();

    return true;
  }

  // subclasses with special needs for auditing deletion should override this function
  function audit_delete() {
  }
 
  function insert() {
    global $current_user;
    if (is_object($current_user) && $current_user->get_id()) {
      $current_user_id = $current_user->get_id();
    } else {
      $current_user_id = "0";
    }
    if (!$this->check_perm(PERM_CREATE)) {
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

    $query = "INSERT INTO $this->data_table (";
    $query.= $this->get_insert_fields($this->data_fields);
    $query.= ") VALUES (";
    $query.= $this->get_insert_values($this->data_fields);
    $query.= ")";
    $this->debug and print "<br>db_entity->insert() query: ".$query;
    $db = $this->get_db();
    $db->query($query);

    $id = mysql_insert_id($db->link_id);
    if ($id === 0) {
      $this->debug and print "<br>db_entity->insert(): The previous query does not generate an AUTO_INCREMENT value`";
    } else if ($id === FALSE) {
      $this->debug and print "<br>db_entity->insert(): No MySQL connection was established";
    } else {
      $this->debug and print "<br>db_entity->insert(): New ID: ".$id;
    }
    $this->key_field->set_value($id);

    // and since we're successful, we can audit the insertion
    $this->audit_insert();

    return true;
  }

  // subclasses with special needs for auditing insertion should override this function
  function audit_insert() {
  }

  function update() {

    if (!$this->check_perm(PERM_UPDATE)) {
      return false;
    }

    // retrieve a copy of this object with the old values from the database
    if (class_exists($this->data_table)) {
      $old_this = new $this->data_table;
      $old_this->set_id($this->get_id());
      $old_this->select();
    }

    global $current_user;
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

        // auditing -- check if we audit this field, and if it's changed
        if($field->is_audited() && is_object($old_this) && $field->get_value() != $old_this->get_value($field->get_name())) {
          $this->audit_updated_field($field, $old_this->get_value($field->get_name()));
        }
      }
    }

    $query = "UPDATE $this->data_table SET ".$this->get_name_equals_value($write_fields)." WHERE ";
    $query.= $this->get_name_equals_value(array($this->key_field));
    $db = $this->get_db();
    $this->debug and print "<br>db_entity->update() query: ".$query;
    $db->query($query);
    return true;
  }

  // subclasses with special needs for auditing updated field should override this function
  function audit_updated_field($field, $old_value) {
    $auditItem = new auditItem;
    $auditItem->audit_field_change($field, $this, $old_value);
    $auditItem->insert();
  }

  function is_new() {
    if (!$this->has_key_values()) {
      return true;
    } else if ($this->key_field->has_value() && $this->key_field->get_name() && $this->key_field->get_value()) {
      $db = $this->get_db();
      $row = $db->qr("SELECT ".$this->key_field->get_name()."
                        FROM ".$this->data_table."
                       WHERE ".$this->key_field->get_name()." = '".db_esc($this->key_field->get_value())."'");
      return !$row;
    }
  }

  function save() {
    global $TPL;
    $this->doMoney = true;
    $error = $this->validate();
    if (is_array($error) && count($error)) {
      $TPL["message"] = $error;
      return false;
    } else if (strlen($error) && $error) {
      $TPL["message"][] = $error;
      return false;
    }

    if ($this->is_new()) {
      $rtn = $this->insert();
    } else {
      $rtn = $this->update();
    }

    // Update the search index for this entity, if any
    if ($this->classname && is_dir(ATTACHMENTS_DIR.'search/'.$this->classname)) {

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

  function read_db_record($db, $errors_fatal = true) {
    $this->set_id($db->f($this->key_field->get_name()));
    $this->read_array($db->row, "", SRC_DATABASE);
    $this->all_row_fields = $db->row;
    if ($errors_fatal) {
      if (!$this->check_perm(PERM_READ)) {
        $this->clear();
        return false;
      }
      return true;
    } else {
      $have_perm = $this->have_perm(PERM_READ);
      if (!$have_perm) {
        // we don't bother doing read permission checking for all the meta tables
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
  } 

  function read_row_record($row, $errors_fatal = true) {
    $this->set_id($row[$this->key_field->get_name()]);
    $this->read_array($row, "", SRC_DATABASE);
    $this->all_row_fields = $row;
    if ($errors_fatal) {
      if (!$this->check_perm(PERM_READ)) {
        $this->clear();
        return false;
      }
      return true;
    } else {
      $have_perm = $this->have_perm(PERM_READ);
      if (!$have_perm) {
        $this->clear();
      }
      return $have_perm;
    }
  } 

  function row() {
    return $this->all_row_fields;
  }

  function set_value($field_name, $value, $source = SRC_VARIABLE) {
    if (is_object($this->data_fields[$field_name])) {
      $this->set_field_value($this->data_fields[$field_name], $value, $source);
    } else {
      $this->err("Cannot set field value - field not found: ".$field_name);
    }
  }

  function get_value($field_name, $dest = DST_VARIABLE) {
    $field = $this->data_fields[$field_name];
    if (!is_object($field)) {
      $msg = "Field $field_name does not exist in ".$this->data_table;
      $this->err($msg);
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
    $this->key_field->set_value($id);
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
    $query = "SELECT * FROM $class_name WHERE $key_name=".$this->get_id();
    $db = new db_alloc;
    $db->query($query);
    while ($db->next_record()) {
      $o = new $class_name;
      $o->read_db_record($db);
      $foreign_objects[$o->get_id()] = $o;
    }
    return $foreign_objects;
  }

  function get_calculated_value($name) {
    $code = "return \$this->calculate_$name();";
    #$code = "echo '<br>'.\$this->classname;";
    eval($code);

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
    global $current_user;
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

  // Called when this object should generate calculated values
  // This function should be overridden if calculated values apply to this entity
  function recalculate() {
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
    $rtn = "";
    reset($fields);
    while (list(, $field) = each($fields)) {
      if ($rtn) {
        $rtn.= ",";
      }
      $rtn.= $field->get_name();
    }
    return $rtn;
  }
  function get_insert_values($fields) {
    $rtn = "";
    reset($fields);
    while (list(, $field) = each($fields)) {
      if ($rtn) {
        $rtn.= ",";
      }
      $rtn.= $field->get_value(DST_DATABASE);
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


  /***************************************************************************
  Array utilitity functions I have included these inside the class to avoid
  potential name conflicts with functions defined in individual applications
  ****************************************************************************/
  function copy_array(&$source, &$dest_array, $source_prefix = "", $clear_dest = false) {
    reset($dest_array);
    while (list($field_name, $field_value) = each($dest_array)) {
      if (isset($source[$source_prefix.$field_name])) {
        $dest_array[$field_name] = $source[$source_prefix.$field_name];
      } else if ($clear_dest) {
        $dest_array[$field_name] = "";
      }
      if ($this->debug)
        echo "db_entity->copy_array dest_array[$field_name]: ".$dest_array[$field_name]."<br>\n";
    }
  }
  function append_array(&$source, &$dest_array, $dest_key_prefix = "") {
    reset($source);
    while (list($field_name, $field_value) = each($source)) {
      if ($this->debug)
        echo "append_array: $dest_key_prefix$field_name=> ".$source[$field_name]."<br>\n";
      $dest_array[$dest_key_prefix.$field_name] = $source[$field_name];
    }
  }
  function array_has_values($array) {
    reset($array);
    while (list($field_name, $field_value) = each($array)) {
      if (isset($field_value) && $field_value != "") {
        return true;
      }
    }
    return false;
  }

  function get_assoc_array($key=false,$value=false,$sel=false, $where=array()){
    $key or $key = $this->key_field->name;
    $value or $value = "*";
    $value != "*" and $key_sql = $key.",";

    $q = sprintf('SELECT %s %s FROM %s WHERE 1=1 '
                ,$key_sql,$value,$this->data_table);

    $pkey_sql = " OR ".$this->key_field->name." = ";
    if (is_array($sel) && count($sel)) {
      foreach ($sel as $s) {
        $extra.= $pkey_sql.sprintf("%d",$s);
      }
    } else if ($sel) {
      $extra = $pkey_sql.$sel;
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
      $q.= " ORDER BY ".$this->data_table."Sequence";
    } else if (is_object($this->data_fields[$this->data_table."Seq"])) {
      $q.= " ORDER BY ".$this->data_table."Seq";
    } else if ($value != "*") {
      $q.= " ORDER BY ".$value;
    }

    $db = new db_alloc;
    $db->query($q);
    $rows = array();
    while($row = $db->row()) {
      if ($this->read_db_record($db,false)) {
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


// Determine if the person speicified by $person (default current user) has
// the permission specified by $perm_name for the data entity class specified 
// by $class_name.  Returns true if the user has the person has the 
// permission.
function have_entity_perm($class_name, $perm_name = "", $person = "", $assume_owner = false) {
  $entity = new $class_name;
  $entity->set_id(0);
  return $entity->have_perm($perm_name, $person, $assume_owner);
}


// Returns an array of all the entities
function get_entities() {
  global $modules;
  $entities = array();
  reset($modules);
  while (list($module_name, $module) = each($modules)) {
    $mod_entities = $module->db_entities;
    $entities = array_merge($entities, $mod_entities);
  }
  return $entities;
}


// Returns an array of all table names used by entities
function get_entity_table_names() {
  $entities = get_entities();
  $table_names = array();
  reset($entities);
  while (list(, $entity_name) = each($entities)) {
    $entity = new $entity_name;
    $table_names[] = $entity->data_table;
  }
  return $table_names;
}



?>
