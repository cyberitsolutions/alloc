<?php

/*
 * Copyright (C) 2006, 2007, 2008 Alex Lance, Clancy Malcolm, Cybersource
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

class home_item {
  var $name;
  var $label;
  var $module;
  var $template;
  var $library;
  var $width = "standard";
  var $help_topic;
  var $seq;
  var $print;

  function home_item($name, $label, $module, $template, $width="standard",$seq=0, $print=true) {
    $this->name = $name;
    $this->label = $label;
    $this->module = $module;
    $this->template = $template;
    $this->width = $width;
    $this->seq = $seq;
    $this->print = $print;
  }

  function get_template_dir() {
    return ALLOC_MOD_DIR.$this->module."/templates/";
  }

  function get_seq() {
    return $this->seq;
  }

  function show() {
    global $TPL;
    if ($this->template) {
      $TPL["this"] = $this;
      include_template($this->get_template_dir().$this->template);
    }  
  }

  function get_label() {
    return $this->label;
  }


  function get_title() {
    return $this->get_label();
  }

  function get_width() {
    return $this->width;
  }

  function get_help() {
    if ($this->help_topic) {
      get_help($this->help_topic);
    }
  }
}

function register_home_item($home_item) {
  global $home_items;
  $home_items[$home_item->get_width()][$home_item->get_seq()] = $home_item;
}

function register_home_items() {
  global $modules, $home_items;

  $home_items = array();

  reset($modules);
  while (list($module_name, $module) = each($modules)) {
    $module->register_home_items();
  }
}




?>
