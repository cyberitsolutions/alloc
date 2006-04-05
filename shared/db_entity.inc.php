<?php
define("PERM_READ", 1);
define("PERM_UPDATE", 2);
define("PERM_DELETE", 4);
define("PERM_CREATE", 8);
define("PERM_MONITOR_EVENTS", 16);
define("PERM_READ_WRITE", PERM_READ + PERM_UPDATE + PERM_DELETE + PERM_CREATE);
class db_entity {
  var $classname = "db_entity"; // Support phplib session variables
  var $persistent_slots = array("key_field", "data_fields");    // Support phplib session variables - only save key fields by default
  var $data_table = "";         // Set this to the name of the data base table
  var $key_field;               // Set this to a db_field object that corresponds to the table's primary key
  var $data_fields = array();   // Set this to the data fields using array("field_name"=> new db_field, ...)
  var $all_row_fields = array();        // This gets set to all fields from the row of the query result used to load this entity
  var $db_class = "db_alloc";
  var $db;
  var $debug = false;
  var $fire_events = false;
  var $event_names = array("created", "updated", "deleted");
  var $filter_class;            // Set this to the name of the db_filter class corresponding to this entity if one exists
  var $fields_loaded = false;
  var $display_field_name;      // Set this to the field to be used by the get_display_value function
  var $permissions;
  var $cache;                   // Cache associative array stored by primary key index



 /**********************************************************************************************
  * Public functions                                                                         *
  **********************************************************************************************/


  // Constructor
  function db_entity() {
    $this->permissions = array(PERM_READ=>"Read", PERM_UPDATE=>"Update", PERM_DELETE=>"Delete", PERM_CREATE=>"Create",);
    if ($this->fire_events) {
      $this->permissions[PERM_MONITOR_EVENTS] = "Monitor events";
    }
  }

  // Quick and dirty - use with caution.
  function is_god() {
    global $auth;
    $perms = explode(",", $auth->auth["perm"]);
    if (in_array("god", $perms)) {
      return true;
    } else {
      return false;
    }
  }

  // Check if user has permission to access this entity
  function have_perm($action = 0, $person = "", $assume_owner = false) {
    if ((defined("NO_AUTH") && NO_AUTH) || $this->is_god()) {
      return true;
    }
    #echo $this->data_table . "->have_perm($action, " . (is_object($person) ? $person->get_id() : $person) . ", $assume_owner)<br>";
    global $current_user, $permission_cache;
    if ($person == "") {
      $person = $current_user;
    }
    $entity_id = $this->get_id();
    if (!$entity_id) {
      $entity_id = 0;
    }
    $person_id = $person->get_id();
    $record_cache_key = $this->data_table.":".$entity_id.":".$action.":".$person_id.":".$assume_owner;
    $table_cache_key = $this->data_table.":T:".$action.":".$person_id.":".$assume_owner;

    if (isset($permission_cache[$table_cache_key])) {
      #echo "cache[$table_cache_key] = " . $cache[$table_cache_key] . "<br>";
      return $permission_cache[$table_cache_key];

    } else if (isset($permission_cache[$record_cache_key])) {
      #echo "cache[$record_cache_key] = " . $cache[$record_cache_key] . "<br>";
      return $permission_cache[$record_cache_key];
    }

    $db = new db_alloc;
    $query = "SELECT * 
                FROM permission 
                WHERE (tableName = '".$this->data_table."' OR tableName='')
                     AND (entityID = $entity_id OR entityID = 0 OR entityID = -1)
                     AND (personID = $person_id OR personID = 0)
                     AND (actions & $action = $action OR actions = 0)
                ORDER BY sortKey";
    $db->query($query);
    #$action == 4 and print $query;

    while ($db->next_record()) {

      // Ignore this record if it specifies a role the user doesn't have
      $required_role = $db->f("roleName");
      if ($required_role != "" && !$person->have_role($required_role)) {
        continue;
      }
      // Ignore this record if it specifies that the user must be the record's owner and they are not
      if ($db->f("entityID") == -1 && !$assume_owner && !$this->is_owner($person)) {
        continue;
      }
      // Read the value of the allow field to determine whether to grant the permission
      $have_perm = $db->f("allow") == "Y";

      // Cache the result in variables to prevent duplicate database lookups
      $permission_cache[$record_cache_key] = $have_perm;
      if ($db->f("entityID") == 0) {
        $permission_cache[$table_cache_key] = $have_perm;
      }
      // Return the result
      return $have_perm;
    }

    // No matching records - return false
    $permission_cache[$record_cache_key] = false;
    return false;
  }

  // Ensure that the user has permission to access this entity
  function check_perm($action = 0, $person = "", $assume_owner = false) {
    if (!$this->have_perm($action, $person, $assume_owner)) {
      $description = $this->permissions[$action];
      if (!$description) {
        $description = $action;
      }
      die("You do not have permission '$description' for ".$this->data_table." #".$this->get_id());
    }
  }

  // Query the database to retrieve a record according to the value supplied in key_field
  // The field values in the database record are copied in to the data_fields array
  // Returns true if a record was found, otherwise false
  function select() {
    if (!$this->has_key_values()) {
      return false;
    }
    $query = "SELECT * FROM $this->data_table WHERE ".$this->get_name_equals_value(array($this->key_field), " AND ");
    if ($this->debug)
      echo "db_entity->select query: $query<br>\n";
    $db = $this->get_db();
    $db->query($query);
    if ($db->next_record()) {
      $this->read_db_record($db);
      return true;
    } else {
      return false;
    }
  }

  // Delete records according to the value supplied in key_field
  // Returns true if a record was found, otherwise false
  function delete() {
    $this->check_perm(PERM_DELETE);
    if (!$this->has_key_values()) {
      return false;
    }
    $query = "DELETE FROM $this->data_table WHERE ".$this->get_name_equals_value(array($this->key_field), " AND ");
    if ($this->debug)
      echo "db_entity->delete query: $query<br>\n";
    $db = $this->get_db();
    $db->query($query);
    if ($this->fire_events) {
      fire_event(new event($this, "deleted"));
    }
    return true;
  }

  // Insert a record in to the database
  function insert() {
    global $auth;
    $this->check_perm(PERM_CREATE);
    if (isset($this->data_fields[$this->data_table."ModifiedUser"])) {
      $this->set_value($this->data_table."ModifiedUser", $auth->auth["uid"]);
    }
    $query = "INSERT INTO $this->data_table (";
    $query.= $this->get_insert_fields($this->data_fields);
    #$query.= ",".$this->get_insert_fields(array($this->key_field));
    $query.= ") VALUES (";
    $query.= $this->get_insert_values($this->data_fields);
    #$query.= ",".$this->get_insert_values(array($this->key_field));
    $query.= ")";
    $db = $this->get_db();
    $db->query($query);
    $this->key_field->set_value(mysql_insert_id());

    if ($this->fire_events) {
      fire_event(new event($this, "created"));
    }
    return true;
  }

  // Update the database with values from the data_fields data members
  // The key_field value is used to create the where_clause portion of the SQL update statement
  function update() {
    $this->check_perm(PERM_UPDATE);
    $write_fields = array();
    reset($this->data_fields);
    while (list(, $field) = each($this->data_fields)) {
      if ($this->can_write_field($field)) {
        $write_fields[] = $field;
      }
    }
    $query = "UPDATE $this->data_table SET ".$this->get_name_equals_value($write_fields)." WHERE ";
    $query.= $this->get_name_equals_value(array($this->key_field));
    $db = $this->get_db();
    if ($this->debug)
      echo "update() { query=$query<br>\n";
    $db->query($query);
    if ($this->fire_events) {
      fire_event(new event($this, "updated"));
    }
    return true;
  }

  // Determine if this object is an new entity that doesn't exist in the database yet.
  // Returns true if this object is a new entity or false if it is an existing entity
  function is_new() {
    return !$this->has_key_values();
  }

  // Validates the entity using validate() then if this is a new record, calls insert() otherwise calls update()
  function save() {
    global $TPL;
    if ($this->debug)
      echo "db_entity->save<br>\n";
    $error = $this->validate();
    if (strlen($error) && $error) {
      $TPL["message"][] = $error;
      return false;
    }
    if ($this->is_new()) {
      return $this->insert();
    } else {
      return $this->update();
    }
  }

  // Validate the entity
  // Returns an error message if data is invalid, or an empty string if it is valid
  function validate() {
    $message = array();
    reset($this->data_fields);
    while (list($field_index, $field) = each($this->data_fields)) {
      $message[] = $field->validate();
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

  // Read field values from an array
  function read_array(&$array, $source_prefix = "", $source = SRC_VARIABLE) {

    // Data fields
    reset($this->data_fields);
    while (list($field_index,) = each($this->data_fields)) {
      $field = $this->data_fields[$field_index];
      $source_index = $source_prefix.$field->get_name();
      $this->set_field_value($this->data_fields[$field_index], $array[$source_index], $source);
      if ($this->debug)
        echo "db_entity->read_array data_fields[$field_index]->set_value(".$array[$source_index].", $source)<br>\n";
    }

    // Key field
    $source_index = $source_prefix.$this->key_field->get_name();
    $this->key_field->set_value($array[$source_index], $source);
    if ($this->debug)
      echo "db_entity->read_array key_field->set_value(".$array[$source_index].", $source)<br>\n";
    $this->fields_loaded = true;
  }

  // Write field values to an array
  function write_array(&$array, $dest = DST_VARIABLE, $array_index_prefix = "") {

    // Data fields
    reset($this->data_fields);
    while (list($field_name) = each($this->data_fields)) {
      $array_index = $array_index_prefix.$field_name;
      $array[$array_index] = stripslashes($this->get_value($field_name, $dest));
    }

    // Key field
    $array_index = $array_index_prefix.$this->key_field->get_name();
    $array[$array_index] = $this->key_field->get_value($dest);
  }

  // Set template variables with field values.  Variable names are field names prefixed with the tpl_key_prefix param
  function set_tpl_values($dest = DST_HTML_ATTRIBUTE, $tpl_key_prefix = "") {
    $this->write_array($GLOBALS["TPL"], $dest, $tpl_key_prefix);
  }

  // Copies values from global variables in to any items in the key_field or data_fields data members that have the same
  // name
  // If prefix is set, then $GLOBALS[$prefix . $var] gets copied to field[$var]
  function read_globals($prefix = "", $source = SRC_VARIABLE) {
    $this->read_array($GLOBALS, $prefix, $source);
  }

  // Set global variables with field values.  Variable names are field names prefixed with the variable_name_prefix param
  function set_global_variables($variale_name_prefix = "") {
    $this->write_array($GLOBALS, DST_VARIABLE, $variable_name_prefix);
  }

  // Returns true if values have been supplied for all key fields
  function has_key_values() {
    return $this->key_field->has_value();
  }

  // Read data in to fields array out of a database record
  // $db is a phplib database object
  function read_db_record($db, $errors_fatal = true) {
    $this->set_id($db->f($this->key_field->get_name()));
    $this->read_array($db->Record, "", SRC_DATABASE);
    $this->all_row_fields = $db->Record;
    if ($errors_fatal) {
      $this->check_perm(PERM_READ);
      return true;
    } else {
      $have_perm = $this->have_perm(PERM_READ);
      if (!$have_perm) {
        $this->clear();
      }
      return $have_perm;
    }
  }

  // Set the value of a field
  function set_value($field_name, $value, $source = SRC_VARIABLE) {
    is_object($this->data_fields[$field_name]) || die("Cannot set field value - field not found: ".$field_name);
    $this->set_field_value($this->data_fields[$field_name], $value, $source);
  }

  // Get the value of a field
  function get_value($field_name, $dest = DST_VARIABLE) {
    $field = $this->data_fields[$field_name];
    if (!is_object($field)) {
      die("Field $field_name does not exist in ".$this->data_table);
    }
    if (!$this->can_read_field($field_name)) {
      return "Permission denied (".$this->data_table.":".$this->data_fields[$field_name]->read_perm_name.")";
    }
    return $field->get_value($dest);
  }

  // Get the value of a field in the row used to load this entity that isn't one of the standard fields
  function get_row_value($field_name) {
    return $this->all_row_fields[$field_name];
  }

  // Get the value of the record's identifying field
  // Assumes the entity only has one key field
  function get_id($dest = DST_VARIABLE) {
    return $this->key_field->get_value($dest);
  }

  // Set the value of the record's identifying field
  function set_id($id) {
    $this->key_field->set_value($id);
  }

  // Get an object related to this record through a foreign key (i.e. a many to one relationship)
  function get_foreign_object($class_name, $key_name = "") {
    if ($key_name == "") {
      $key_name = $class_name."ID";
    }
    $object = new $class_name;
    $object->set_id($this->get_value($key_name, DST_VARIABLE));
    $object->select();
    return $object;
  }

  // Get objects related to this record through a foreign key in another table (i.e. a one to many relationship)
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
  function get_event_names() {
    return $this->event_names;
  }

  // Return the value of a 'calculated' field
  function get_calculated_value($name) {
    $code = "return \$this->calculate_$name();";
    #$code = "echo '<br>'.\$this->classname;";
    eval($code);

  }
  function get_filter_class() {
    return $this->filter_class;
  }

  // This function returns a string to display to the user that represents this object
  function get_display_value() {
    if ($this->display_field_name) {
      if (!$this->fields_loaded) {
        $found = $this->select();
        if (!$found) {
          return "not found";
        }
      }
      return $this->get_value($this->display_field_name, DST_HTML_DISPLAY);
    } else {
      return "#".$this->get_id();
    }
  }

  // Determines if the current user can read a field by checking that the 
  // user has the permission specified by the field's read_perm_name (if any)
  function can_read_field($field_name) {
    $field = $this->data_fields[$field_name];
    if ($field->read_perm_name) {
      return $this->have_perm($field->read_perm_name);
    } else {
      return true;
    }
  }

  // Determines if the current user can write a field by checking that the 
  // user has the permission specified by the field's write_perm_name (if any)
  // $field can be either a field object or a data field name
  function can_write_field($field) {
    if (is_string($field)) {
      $field = $this->data_fields[$field];
    }
    return $field->write_perm_name == 0 || $this->have_perm($field->write_perm_name);
  }
  function is_owner($person = "") {
    global $current_user;
    if ($person == "") {
      $person = $current_user;
    }
    if (isset($this->data_fields["personID"])) {

#echo "data_table: " .$this->data_table. ",  data field: " .$this->get_value("personID") . " == " . $person->get_id() . "<br>";
      return $this->get_value("personID") == $person->get_id();
    } else if ($this->key_field->get_name() == "personID") {

#echo "key field " . $this->get_id() . " == " . $person->get_id() . "<br>";
      return $this->get_id() == $person->get_id();
    } else {
      echo "Warning: could not determine owner for ".$this->data_table."<br>";
    }
  }

  // Clear the value of all fields
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

    /**********************************************************************************************
    'Private' utilitity functions
    These functions probably won't be useful to users of this class but are used by the other
    functions in the class
    **********************************************************************************************/
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


    /**********************************************************************************************
    Array utilitity functions
    I have included these inside the class to avoid potential name conflicts with
    functions defined in individual applications
    **********************************************************************************************/
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


  function get_assoc_array($key,$value=false,$sel=false){
    $value or $value = $key;
    $q = sprintf('SELECT %s, %s FROM %s'
                ,$key,$value,$this->data_table);


    $pkey_sql = " OR ".$this->key_field->name." = ";
    if (is_array($sel) && count($sel)) {
      foreach ($sel as $s) {
        $extra.= $pkey_sql.sprintf("%d",$s);
      }
    } else if ($sel) {
      $extra = $pkey_sql.$sel;
    }

    is_object($this->data_fields[$this->data_table."Active"])   and $q.= " WHERE (".$this->data_table."Active = 1 ".$extra.")";
    is_object($this->data_fields[$this->data_table."Sequence"]) and $q.= " ORDER BY ".$this->data_table."Sequence";

    $db = new db_alloc;
    $db->query($q);
    while($db->next_record()) {
      $rtn[$db->f($key)] = $db->f($value);
    }
    return $rtn;
  }


  // This could be called like: 
  // $timeUnit->get_dropdown_options("timeUnitID","timeUnitLabelA",$task->get_value("timeEstimateUnitID"));
  function get_dropdown_options($key,$label,$sel=false,$blank=false) {
    $arr = $this->get_assoc_array($key,$label,$sel);

    $blank and $options = get_option("", "0", !$sel)."\n";
    $options.= get_select_options($arr,$sel);
    return $options;
  }

}


  // Check that the person speicified by $person (default current user) has the
  // permission specified by $perm_name for the data entity class specified by
  // $class_name.  If they do not have permission then an error is displayed.
function check_entity_perm($class_name, $perm_name = 0, $person = "", $assume_owner = false) {
  $entity = new $class_name;
  $entity->set_id(0);
  $entity->check_perm($perm_name, $person, $assume_owner);
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
