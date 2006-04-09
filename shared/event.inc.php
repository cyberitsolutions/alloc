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
