<?php

/*
 *
 * Copyright 2006, Alex Lance, Clancy Malcolm, Cybersource Pty. Ltd.
 * 
 * This file is part of allocPSA <info@cyber.com.au>.
 * 
 * allocPSA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * allocPSA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * allocPSA; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */

class toolbar_item {
  var $name;
  var $label;
  var $url_parameters;

  function toolbar_item($name, $label, $url_parameters = "") {
    $this->name = $name;
    $this->label = $label;
    $this->url_parameters = $url_parameters;
  }

  function show() {
    $url = $this->get_url();
    if ($url_parameters) {
      $url.= "&$url_parameters";
    }

    global $loaded_home_items;

    if (preg_match("/".str_replace("/", "\\/", $_SERVER["PHP_SELF"])."/", $url)) {
      $class = " class=\"active\"";
    }


    $loaded_home_items[] = "<a".$class." href=\"$url\">".$this->get_label()."</a>";
  }

  function get_url() {
    global $TPL, $sess;
    $url = $TPL["url_alloc_".$this->name];
    if ($this->url_parameters) {
      $url.= "&".$this->url_parameters;
    }
    return $url;
  }

  function get_label() {
    return $this->label;
  }
} 


function show_messages() {
  global $TPL;


  if ($TPL["message"] && is_string($TPL["message"])) {
    $t = $TPL["message"];
    unset($TPL["message"]);
    $TPL["message"][] = $t;
  } 
  $_GET["message"] and $TPL["message"][] = urldecode($_GET["message"]);
  
  if ($TPL["message_good"] && is_string($TPL["message_good"])) {
    $t = $TPL["message_good"];
    unset($TPL["message_good"]);
    $TPL["message_good"][] = $t;
  }
  $_GET["message_good"] and $TPL["message_good"][] = urldecode($_GET["message_good"]);

  if ($TPL["message_help"] && is_string($TPL["message_help"])) {
    $t = $TPL["message_help"];
    unset($TPL["message_help"]);
    $TPL["message_help"][] = $t;
  }
  $_GET["message_help"] and $TPL["message_help"][] = urldecode($_GET["message_help"]);

  
  if (is_array($TPL["message"]) && count($TPL["message"])) {
    $arr["bad"] = implode("<br/>",$TPL["message"]);
  }
  if (is_array($TPL["message_good"]) && count($TPL["message_good"])) {
    $arr["good"] = implode("<br/>",$TPL["message_good"]);
  }
  if (is_array($TPL["message_help"]) && count($TPL["message_help"])) {
    $arr["help"] = implode("<br/>",$TPL["message_help"]);
  } 

  if (is_array($arr) && count($arr)) {
    echo "<div class=\"message\">";

    foreach ($arr as $type => $str) {
      echo "<table cellspacing=\"0\" cellpadding=\"3\"><tr><td width=\"1%\" style=\"vertical-align:top;\"><img src=\"".$TPL["url_alloc_images"]."icon_message_".$type.".gif\"/><td/>";
      echo "<td class=\"".$type."\" align=\"left\" width=\"99%\">".$str."</td></tr></table>";
    }
    echo "</div>";
  }

}



function register_toolbar_item($tool_name, $label, $url_parameters = "") {
  global $toolbar_items;
  $toolbar_items[] = new toolbar_item($tool_name, $label, $url_parameters);
}

function register_toolbar_items($modules) {
  global $toolbar_items;

  $toolbar_items = array();

  reset($modules);
  while (list($module_name, $module) = each($modules)) {
    $module->register_toolbar_items();
  }
  register_toolbar_item("logout", "Logout");
}



function show_toolbar_items() {
  global $toolbar_items, $loaded_home_items;

  reset($toolbar_items);
  while (list(, $toolbar_item) = each($toolbar_items)) {
    $toolbar_item->show();
  }

  return $loaded_home_items;
}


function show_history() {
  global $TPL, $current_user, $modules;
  $db = new db_alloc;

  $str[] = "<option value=\"\">Quick List</option>";
  $str[] = "<option value=\"".$TPL["url_alloc_task"]."\">New Task</option>";
  $str[] = "<option value=\"".$TPL["url_alloc_task"]."tasktype=".TT_FAULT."\">New Fault</option>";
  $str[] = "<option value=\"".$TPL["url_alloc_task"]."tasktype=".TT_MESSAGE."\">New Message</option>";

  if (have_entity_perm("project", PERM_CREATE, $current_user)) {
    $str[] = "<option value=\"".$TPL["url_alloc_project"]."\">New Project</option>";
  }

  if (isset($modules["time"]) && $modules["time"]) {
    $str[] = "<option value=\"".$TPL["url_alloc_timeSheet"]."\">New Time Sheet</option>";
  }

  if (isset($modules["client"]) && $modules["client"]) {
    $str[] = "<option value=\"".$TPL["url_alloc_client"]."\">New Client</option>";
  }

  if (isset($modules["finance"]) && $modules["finance"]) {
    $str[] = "<option value=\"".$TPL["url_alloc_expOneOff"]."\">New Expense Form</option>";
  }

  $str[] = "<option value=\"".$TPL["url_alloc_reminderAdd"]."\">New Reminder</option>";

  if (have_entity_perm("person", PERM_CREATE, $current_user)) {
    $str[] = "<option value=\"".$TPL["url_alloc_person"]."\">New Person</option>";
  }

  $str[] = "<option value=\"".$TPL["url_alloc_loanAndReturn"]."\">New Item Loan</option>";

  $history = new history;
  $str[] = get_options_from_db($history->get_history_db("DESC"), "the_label", "historyID", $_GET["historyID"], 43);
  echo implode("\n",$str);
}


function get_category_options($category="") {
  $category_options = array("Tasks"=>"Tasks", "Projects"=>"Projects", "Items"=>"Items", "Clients"=>"Clients");
  return get_options_from_array($category_options, $category, true);
}







?>
