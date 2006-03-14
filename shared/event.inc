<?php
class event {
  var $object;
  var $name;
  var $extra;

  function event($object, $name, $extra = "") {
    $this->object = $object;
    $this->name = $name;
    $this->extra = $extra;
  }

  function get_object() {
    return $this->object;
  }

  function get_name() {
    return $this->name;
  }

  function get_extra() {
    return $this->extra;
  }
}

class eventFilter extends db_entity
{
  var $data_table = "eventFilter";

  function eventFilter() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_text_field("eventFilterID");
    $this->data_fields = array("className"=>new db_text_field("className")
                               , "eventName"=>new db_text_field("eventName")
                               , "action"=>new db_text_field("action")
                               , "personID"=>new db_text_field("personID")
                               , "objectFilter"=>new db_object_field("objectFilter")
      );
  }
}

function fire_event($event) {
  global $modules;
  reset($modules);
  while (list($module_name, $module) = each($modules)) {
    $module->handle_event($event);
  }
}

function get_event_classes() {
  global $modules;

  $event_classes = array();

  reset($modules);
  while (list($module_name, $module) = each($modules)) {
    $event_classes = array_merge($event_classes, $module->get_event_classes());
  }

  return $event_classes;
}



?>
