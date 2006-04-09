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


// Load up $_FORM with $_GET and $_POST
function get_all_form_data($array=array()) {
  $_FORM = array();
  foreach ($array as $name) {
    $_FORM[$name] = $_POST[$name] or $_FORM[$name] = urldecode($_GET[$name]);
  } 
  return $_FORM;
} 



function timetook($start, $text="Duration: ") {
  $end = microtime();
  list($start_micro,$start_epoch,$end_micro,$end_epoch) = explode(" ",$start." ".$end);
  $started  = (substr($start_epoch,-4) + $start_micro);
  $finished = (substr($end_epoch  ,-4) + $end_micro);
  $dur = $finished - $started;
  $unit = " seconds.";
  $dur > 60 and $unit = " mins.";
  $dur > 60 and $dur = $dur / 60;
  echo "<br>".$text.sprintf("%0.5f", $dur) . $unit;
}

function get_cached_table($table) {
  static $cache;
  if (!$cache) {
    $cache = new alloc_cache(array("person","taskType","timeUnit"));
    $cache->load_cache();

    // Special processing for person table
    $people = $cache->get_cached_table("person");
    foreach ($people as $id => $row) {
      if ($people[$id]["firstName"] && $people[$id]["surname"]) {
        $people[$id]["name"] = $people[$id]["firstName"]." ".$people[$id]["surname"];
      } else {
        $people[$id]["name"] = $people[$id]["username"];
      }
    }
    $cache->set_cached_table("person",$people);
  

  }
  return $cache->get_cached_table($table);
}


function get_option($label, $value = "{label}", $selected = false) {
  $rtn = "<option";
  if ($value != "{label}") {
    $rtn.= " value=\"$value\"";
  }
  if ($selected) {
    $rtn.= " selected";
  }
  $rtn.= ">".$label."</option>";
  return $rtn;
}

function get_url_path() {
  global $SERVER_NAME, $SCRIPT_NAME;

  eregi("^(.*[/\\])[^/\\]*$", $SCRIPT_NAME, $matches);
  $script_dir = $matches[1];
  // $url_path = "http://$SERVER_NAME$script_dir";
  $url_path = $script_dir;

#$script_file = ereg_replace("^(.*)/([^/\\]*)$", "\\2", $SCRIPT_FILENAME);
#$script_file = $match[0];
#$url_path = eregi_replace($script_file, "", $SCRIPT_NAME);

  return $url_path;
}

function show_header() {
  include_template(ALLOC_MOD_DIR."/shared/templates/headerS.tpl");
}

function get_stylesheet_name() {
  global $customizedTheme;
  $themes = array("Classic", "Darko", "Aneurism", "Clove", "None");
  $stylesheet = "style_".strtolower($themes[sprintf("%d", $customizedTheme)]).".css";
  echo $stylesheet;
}


function show_style_sheet() {
  global $TPL, $customizedFont;

  $weight = $customizedFont;

  $font_size = array("H1_FONT_SIZE"               =>17 + $weight
                   , "H2_FONT_SIZE"               =>17 + $weight
                   , "H3_FONT_SIZE"               =>16 + $weight
                   , "TABLE_BOX_TH_FONT_SIZE"     =>14 + $weight
                   , "TABLE_BOX_TH_A_FONT_SIZE"   =>14 + $weight
                   , "TABLE_TOOLBAR_TH_FONT_SIZE" =>18 + $weight
                   , "TABLE_TOOLBAR_TD_FONT_SIZE" =>14 + $weight
                   , "TABLE_CALENDAR_TH_FONT_SIZE"=>13 + $weight
                   , "TD_FONT_SIZE"               =>13 + $weight
                   );


  $str = implode("", file(ALLOC_MOD_DIR."/stylesheets/font_sizes.css"));
  foreach($font_size as $search=>$replace) {
    $str = str_replace("(".$search.")", $replace, $str);
  }
  echo $str;
}

function show_footer() {
  include_template(ALLOC_MOD_DIR."/shared/templates/footerS.tpl");
}

function show_toolbar() {
  global $TPL, $modules;

  $toolbar_items = show_toolbar_items();

  foreach($toolbar_items as $number=>$item) {
    $TPL["toolbar_item".$number] = $item;
  }

  $TPL["default_history_item_1"] = "<option value=\"".$TPL["url_alloc_task"]."\">New Task</option>";

  $db = new db_alloc;
  $tasktype = new taskType;
  $query = "SELECT * FROM taskType WHERE taskTypeName=\"Fault\"";
  $db->query($query);
  $db->next_record();
  $tasktype->read_db_record($db);
  
  $TPL["default_history_item_10"] = "<option value=\"".$TPL["url_alloc_task"]."tasktype=".$tasktype->get_id()."\">New Fault</option>";

  $query = "SELECT * FROM taskType WHERE taskTypeName=\"Message\"";
  $db->query($query);
  $db->next_record();
  $tasktype->read_db_record($db);
  
  $TPL["default_history_item_11"] = "<option value=\"".$TPL["url_alloc_task"]."tasktype=".$tasktype->get_id()."\">New Message</option>";
  
  if (have_entity_perm("project", PERM_CREATE, $current_user)) {
    $TPL["default_history_item_2"] = "<option value=\"".$TPL["url_alloc_project"]."\">New Project</option>";
  }

  if (isset($modules["time"]) && $modules["time"]) {
    $TPL["default_history_item_3"] = "<option value=\"".$TPL["url_alloc_timeSheet"]."\">New Time Sheet</option>";
  }

  if (isset($modules["client"]) && $modules["client"]) {
    $TPL["default_history_item_4"] = "<option value=\"".$TPL["url_alloc_client"]."\">New Client</option>";
  }

  if (isset($modules["finance"]) && $modules["finance"]) {
    $TPL["default_history_item_5"] = "<option value=\"".$TPL["url_alloc_expOneOff"]."\">New Expense Form</option>";
  }

  $TPL["default_history_item_6"] = "<option value=\"".$TPL["url_alloc_reminderAdd"]."\">New Reminder</option>";

  if (have_entity_perm("person", PERM_CREATE, $current_user)) {
    $TPL["default_history_item_7"] = "<option value=\"".$TPL["url_alloc_person"]."\">New Person</option>";
  }

  $TPL["default_history_item_8"] = "<option value=\"".$TPL["url_alloc_loanAndReturn"]."\">New Item Loan</option>";



  include_template(ALLOC_MOD_DIR."/shared/templates/toolbarS.tpl");
}





?>
