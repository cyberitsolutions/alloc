<?php
class module {
  var $db_entities = array();   // A list of db_entity class names implemented by this module

  // Allow a module to load its libraries at the start of a request
  function load_libararies() {
  }

  // Called to instruct the module to register any toolbar items using the register_toolbar_item function
  function register_toolbar_items() {
  }

  // Called to instruct the module to register any home page items using the register_home_item function
  function register_home_items() {
  }

  // Called to allow the module to respond to an event
  function handle_event($event) {
  }

  // Called to get the list of class names that generate events in this module
  function get_event_classes() {
    $event_classes = array();

    reset($this->db_entities);
    while (list(, $class_name) = each($this->db_entities)) {
      $entity = new $class_name;
      if ($entity->fire_events) {
        $event_classes[] = $class_name;
      }
    }

    return $event_classes;
  }
}



?>
