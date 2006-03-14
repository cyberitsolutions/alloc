<?php
class db_filter {
  var $elements;
  var $debug = false;
  var $persistent_slots = array("elements");

  function set_element_value(&$element, $value) {
    $element->set_value($value);
  }

  function set_element($element_name, $value) {
    $this->set_element_value($this->elements[$element_name], $value);
  }

  function get_element($element_name) {
    return $this->elements[$element_name]->get_value();
  }

  function get_where() {
    $sql = "";
    reset($this->elements);
    while (list(, $element) = each($this->elements)) {
      $element_sql = $element->get_where();
      if ($element_sql) {
        if ($sql) {
          $sql.= " AND ";
        }
        $sql.= "($element_sql)";
      }
    }
    if ($this->debug) {
      echo "db_filter::get_where returning $sql<br>";
    }
    return $sql;
  }

  function get_joins() {
    $joins = array();
    reset($this->elements);
    while (list(, $element) = each($this->elements)) {
      $element_joins = $element->get_joins();
      $joins = array_merge($joins, $element_joins);
    }
    $joins = array_unique($joins);
    return implode($joins, " LEFT JOIN ");
  }

  // Check if a db_entity object matches the filter or not
  // Returns true if the object does match, otherwise false
  function check_object($o) {
    reset($this->elements);
    while (list(, $element) = each($this->elements)) {
      if (!$element->check_object($o)) {
        return false;
      }
    }
    return true;
  }

  function get_display_value() {
    return $this->get_description();
  }

  function get_description() {
    $description = "";
    reset($this->elements);
    while (list(, $element) = each($this->elements)) {
      $element_description = $element->get_description();
      if ($element_description) {
        if ($description) {
          $description.= " and ";
        }
        $description.= $element_description;
      }
    }
    return $description;
  }

  // Override this function to return a string containing HTML form elements that allows the user to enter an action
  // The form shown will not include the <form> and </form> tags
  function get_form() {
    die("call to abstract function db_filter::get_form");
  }

  // Override this function to set the filter's element values according to values entered
  // by the user in a form displayed using the show_form function
  function read_form() {
    die("call to abstract function db_filter::read_form");
  }
}

class db_filter_element
{
  var $name;
  var $label;
  var $value;
  var $joins = array();
  var $classname = "db_filter_element";
  var $persistent_slots = array("name", "label", "value", "isset");
  var $isset = false;           // Workaround for when using with PHPLib sessions which sets originally unset variables

  function db_filter_element($name, $label, $joins = "") {
    if ($joins == "") {
      $joins = array();
    }

    $this->name = $name;
    $this->label = $label;
    $this->joins = $joins;
  }

  function set_value($value) {
    $this->value = $value;
    $this->isset = true;
  }

  function get_value() {
    return $this->value;
  }

  function get_name() {
    return $this->name;
  }

  function get_joins() {
    if ($this->get_where()) {
      return $this->joins;
    } else {
      return array();
    }
  }

  // This function should be overridden to return the SQL (if any) to include the where clause of a query
  function get_where() {
  }

  function get_description() {
    if ($this->get_where() == "") {
      return "";
    } else {
      return $this->label." = ".$this->value;
    }
  }

  // This function should be overridden to return true if the object matches the filter
  function check_object($o) {
    return true;
  }
}

class boolean_sql_filter_element extends db_filter_element
{
  var $sql;
  var $classname = "boolean_sql_filter_element";
  var $persistent_slots = array("name", "label", "value", "isset", "sql");

  function boolean_sql_filter_element($name = "", $label = "", $sql = "", $joins = "") {
    $this->db_filter_element($name, $label, $joins);    // Call parent's constructor

    $this->sql = $sql;
  }

  function get_description() {
    if ($this->isset) {
      $description = "";
      if (!$this->value) {
        $description = "not ";
      }
      $description.= $this->label;
      return $description;
    } else {
      return "";
    }
  }

  function get_where() {
    if ($this->isset) {
      if ($this->value) {
        return $this->sql;
      } else {
        return "NOT (".$this->sql.")";
      }
    } else {
      return "";
    }
  }

  function check_object($o) {
    if ($this->isset) {
      return $o->get_calculated_value($this->get_name()) == $this->get_value();
    } else {
      return true;
    }
  }
}

        // A filter to select based on the value of a foreign key
        // The value for this filter should be the db_entity object that you want to find relating records for
class foreign_key_filter_element extends db_filter_element
{
  var $field_name;
  var $classname = "foreign_key_filter_element";
  var $persistent_slots = array("name", "label", "value", "isset", "field_name");

  function foreign_key_filter_element($name = "", $label = "", $field_name = "", $joins = "") {
    $this->db_filter_element($name, $label, $joins);    // Parent's constructor
    $this->field_name = $field_name;
  }

  function get_description() {
    if ($this->get_where() == "") {
      return "";
    } else {
      return $this->label." = ".$this->value->get_display_value();
    }
  }

  function get_where() {
    if (is_object($this->value)) {
      return sprintf("%s = '%s'", $this->field_name, addslashes($this->value->get_id()));
    } else {
      return "";
    }
  }

  function get_bare_field_name() {
    if (ereg("^.*\\.(.*)$", $this->field_name, $matches)) {
      // Field name is qualified using table_name.bare_field_name
      return $matches[1];
    } else {
      // Field name is not qualified with the table name.
      return $this->field_name;
    }
  }

  function check_object($o) {
    if (is_object($this->value)) {
      $field_name = $this->get_bare_field_name();
      return $o->get_value($field_name) == $this->value->get_id();
    } else {
      return true;
    }
  }
}

class foreign_key_filter_array extends foreign_key_filter_element {
  var $field_name;
  var $classname = "foreign_key_filter_array";
  var $persistent_slots = array("name", "label", "value", "isset", "field_name");

  function get_where() {
    if (is_array($this->value)) {
      return sprintf("%s in ('%s')", $this->field_name, implode("','",$this->value));
    } else {
      return "";
    }
  }
  function check_object($o) {
    if (is_array($this->value)) {
      $field_name = $this->get_bare_field_name();
      return in_array($o->get_value($field_name), $this->value);
    } else {
      return true;
    }
  }



}

        // A filter to select based on the value of a field
class field_filter_element extends db_filter_element
{
  var $field_name;
  var $classname = "field_filter_element";
  var $persistent_slots = array("name", "label", "value", "isset", "field_name");

  function field_filter_element($name = "", $label = "", $field_name = "", $joins = "") {
    $this->db_filter_element($name, $label, $joins);    // Parent's constructor
    $this->field_name = $field_name;
  }

  function get_where() {
    if (is_object($this->value)) {
      return sprintf("%s = '%s'", $this->field_name, addslashes($this->value->get_id()));
    } else if ($this->value) {
      return sprintf("%s = '%s'", $this->field_name, addslashes($this->value));
    } else {
      return "";
    }
  }

  function get_bare_field_name() {
    if (ereg("^.*\\.(.*)$", $this->field_name, $matches)) {
      // Field name is qualified using table_name.bare_field_name
      return $matches[1];
    } else {
      // Field name is not qualified with the table name.
      return $this->field_name;
    }
  }

  function check_object($o) {
    if ($this->value) {
      $field_name = $this->get_bare_field_name();
      return $o->get_value($field_name) == $this->value;
    } else {
      return true;
    }
  }
}



?>
