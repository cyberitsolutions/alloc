<?php
class home_item {
  var $name;
  var $label;
  var $module;
  var $template;
  var $library;
  var $width = "standard";
  var $help_topic;

  function home_item($name, $label, $module, $template, $width = "standard") {
    $this->name = $name;
    $this->label = $label;
    $this->module = $module;
    $this->template = $template;
    $this->width = $width;
  }

  function get_template_dir() {
    return ALLOC_MOD_DIR."/".$this->module."/templates/";
  }

  function show() {
    include_template($this->get_template_dir().$this->template, $this);
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

  function help_button() {
    if ($this->help_topic) {
      help_button($this->help_topic, $this->module);
    }
  }
}

function register_home_item($home_item) {
  global $home_items;
  $home_items[] = $home_item;
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
