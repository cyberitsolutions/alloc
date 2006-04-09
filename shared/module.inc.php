<?php

/*
 *
 * Copyright 2006, Alex Lance, Clancy Malcolm, Cybersource Pty. Ltd.
 * 
 * This file is part of AllocPSA <info@cyber.com.au>.
 * 
 * AllocPSA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * AllocPSA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * AllocPSA; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */

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
