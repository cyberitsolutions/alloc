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

function get_default_from_address() {
  // Wrap angle brackets around the default From: email address 
  $f = config::get_config_item("AllocFromEmailAddress");
  $l = strpos($f, "<");
  $r = strpos($f, ">");
  $l === false and $f = "<".$f;
  $r === false and $f .= ">";
  return "allocPSA ".$f;
}

function get_alloc_version() {
  if (file_exists(ALLOC_MOD_DIR."/util/alloc_version") && is_readable(ALLOC_MOD_DIR."/util/alloc_version")) {
    $v = file(ALLOC_MOD_DIR."/util/alloc_version");
    return $v[0];
  } else {
    die("No alloc_version file found.");
  }
}

function get_script_path() {
  // Has to return something like
  // /alloc_dev/
  // /

  $modules = get_alloc_modules();
  $path = dirname($_SERVER["SCRIPT_NAME"]);
  $bits = explode("/",$path);
  $last_bit = end($bits);

  if ($modules[$last_bit]) {
    array_pop($bits);
  }
  is_array($bits) and $path = implode("/",$bits);

  $path[0] != "/" and $path = "/".$path;
  $path[strlen($path)-1] != "/" and $path.="/";
  return $path;

}

function seconds_to_display_format($seconds) {
  $day = config::get_config_item("hoursInDay");

  $day_in_seconds = $day * 60 * 60;
  $hours = $seconds / 60 / 60;
  
  if ($seconds < $day_in_seconds) {
    return sprintf("%0.2f hrs",$hours);
  } else {
    $days = $seconds / $day_in_seconds;
    #return sprintf("%0.1f days", $days);
    return sprintf("%0.2f hrs (%0.1f days)",$hours, $days);
     
  }
  
}

function get_alloc_modules() {
  if (defined("ALLOC_MODULES")) {
    return unserialize(ALLOC_MODULES);
  } else {
    echo "ALLOC_MODULES is not defined!";
  }
}

function page_close() {
  $sess = new Session;
  $sess->Save();

  global $current_user;
  if (is_object($current_user) && $current_user->get_id()) {
    $p = new person;
    $p->set_id($current_user->get_id());
    $p->select();

    if (is_array($current_user->prefs)) {
      $arr = serialize($current_user->prefs);
      $p->set_value("sessData",$arr);
    }
    $p->save();
  }
}

function get_all_form_data($array=array()) {
// Load up $_FORM with $_GET and $_POST
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

function get_option($label, $value = "", $selected = false) {
  $rtn = "<option";
  $rtn.= " value=\"$value\"";
  if ($selected) {
    $rtn.= " selected";
  }
  $rtn.= ">".$label."</option>";
  return $rtn;
}

function show_header() {
  include_template(ALLOC_MOD_DIR."/shared/templates/headerS.tpl");
}

function get_stylesheet_name() {
  global $current_user;

  $themes = get_customizedTheme_array();
  $fonts  = get_customizedFont_array();

  $style = strtolower($themes[sprintf("%d", $current_user->prefs["customizedTheme2"])]);
  $font = $fonts[sprintf("%d",$current_user->prefs["customizedFont"])];
  echo "style_".$style."_".$font.".css";
}

function get_customizedFont_array() {
  return array("-3"=>1, "-2"=>2, "-1"=>3, "0"=>"4", "1"=>5, "2"=>6, "3"=>7, "4"=>8, "5"=>9, "6"=>10);
}

function get_customizedTheme_array() {
  return array("Default","Leaf", "Icy", "Clove", "Puddle", "None");
}

function show_footer() {
  include_template(ALLOC_MOD_DIR."/shared/templates/footerS.tpl");
}

function show_tabs() {
  global $TPL;

  $menu_links = array("Home"     =>array("url"=>$TPL["url_alloc_home"],"module"=>"home")
                     ,"Clients"  =>array("url"=>$TPL["url_alloc_clientList"],"module"=>"client")
                     ,"Projects" =>array("url"=>$TPL["url_alloc_projectList"],"module"=>"project")
                     ,"Tasks"    =>array("url"=>$TPL["url_alloc_taskSummary"],"module"=>"task")
                     ,"Time"     =>array("url"=>$TPL["url_alloc_timeSheetList"],"module"=>"time")
                     ,"Finance"  =>array("url"=>$TPL["url_alloc_financeMenu"],"module"=>"finance")
                     ,"People"   =>array("url"=>$TPL["url_alloc_personList"],"module"=>"person")
                     ,"Tools"    =>array("url"=>$TPL["url_alloc_tools"],"module"=>"tools")
                     );

  $x = -1;
  foreach ($menu_links as $name => $arr) {
    $TPL["x"] = $x;
    $x+=81;
    $TPL["url"] = $arr["url"];
    $TPL["name"] = $name;
    unset($TPL["active"]);
    if (preg_match("/".str_replace("/", "\\/", $_SERVER["PHP_SELF"])."/", $url) || preg_match("/".$arr["module"]."/",$_SERVER["PHP_SELF"])) {
       $TPL["active"] = " active";
    }
    include_template(ALLOC_MOD_DIR."/shared/templates/tabR.tpl");
  }
}

function show_toolbar() {
  global $TPL, $modules, $category;

  $TPL["category_options"] = get_category_options($_POST["category"]);
  $TPL["needle"] = $_POST["needle"] or $TPL["needle"] = "Search...";

  include_template(ALLOC_MOD_DIR."/shared/templates/toolbarS.tpl");
}

function move_attachment($entity, $id) {
  global $TPL;

  if ($_FILES["attachment"]) {
    is_uploaded_file($_FILES["attachment"]["tmp_name"]) || die("Uploaded document error.  Please try again.");

    $dir = $TPL["url_alloc_attachments_dir"].$entity."/".$id;
    if (!is_dir($dir)) {
      mkdir($dir, 0777);
    }

    if (!move_uploaded_file($_FILES["attachment"]["tmp_name"], $dir."/".$_FILES["attachment"]["name"])) {
      die("could not move attachment to: ".$dir."/".$_FILES["attachment"]["name"]);
    } else {
      chmod($dir."/".$_FILES["attachment"]["name"], 0777);
    }
  }
}

function get_attachments($entity, $id) {
  
  global $TPL;
  $rows = array();
  $dir = $TPL["url_alloc_attachments_dir"].$entity."/".$id;

  if ($id) {
    if (!is_dir($dir)) {
      mkdir($dir, 0777);
    }

    if (is_dir($dir)) {
      $handle = opendir($dir);

      while (false !== ($file = readdir($handle))) {

        if ($file != "." && $file != "..") {
          $size = filesize($dir."/".$file);
          $row["file"] = "<a href=\"".$TPL["url_alloc_getDoc"]."id=".$id."&entity=".$entity."&file=".urlencode($file)."\">".htmlentities($file)."</a>";
          $row["size"] = sprintf("%dkb",$size/1024);
          $rows[] = $row;    
        }
      }
    }
    return $rows;
  }
}

function util_show_attachments($entity, $id) {
  global $TPL;
  $TPL["entity_url"] = $TPL["url_alloc_".$entity];
  $TPL["entity_key_name"] = $entity."ID";
  $TPL["entity_key_value"] = $id;

  $rows = get_attachments($entity, $id);
  $rows or $rows = array();
  foreach ($rows as $row) {
    $TPL["attachments"].= "<tr><td>".$row["size"]."</td><td>".$row["file"]."</td></tr>";
  }

  include_template("../shared/templates/attachmentM.tpl");
}

function sort_task_comments_callback_func($a, $b) {
  return $a["date"] > $b["date"];
}

function util_get_comments($entity, $id, $options=array()) {
  global $TPL, $current_user;

  // Need to get timeSheet comments too for task comments
  if ($entity == "task") {
    $rows = comment::get_comments($entity,$id);
    $rows2 = timeSheetItem::get_timeSheetItemComments($id);

    if (is_array($rows2) && is_array($rows)) {
      $rows = array_merge($rows,$rows2);
    }
    if (is_array($rows)) {
      usort($rows, "sort_task_comments_callback_func");
    }

  } else {
    $rows = comment::get_comments($entity,$id);
  }
  $rows or $rows = array();

  foreach ($rows as $v) {

    if (!$v["comment"]) continue ;
      $person = new person;
      $person->set_id($v["personID"]);
      $person->select();

      $comment_buttons = "";
      $ts_label = "";
      if ($v["timeSheetID"]) {
        $ts_label = "(Time Sheet Comment)";

      } else if ($v["personID"] == $current_user->get_id() && $options["showEditButtons"]) {
        $comment_buttons = "<nobr><input type=\"submit\" name=\"taskComment_edit\" value=\"Edit\">
                                         <input type=\"submit\" name=\"taskComment_delete\" value=\"Delete\"></nobr>";
      }

      if (!$_GET["commentID"] || $_GET["commentID"] != $v["commentID"]) {

        $edit = false;
        if ($options["showEditButtons"]) {
          $edit = true;
        } 

        $edit and $rtn[] =  '<form action="'.$TPL["url_alloc_taskComment"].'" method="post">';
        $edit and $rtn[] =  '<input type="hidden" name="'.$entity.'ID" value="'.$v["commentLinkID"].'">';
        $edit and $rtn[] =  '<input type="hidden" name="commentID" value="'.$v["commentID"].'">';
        $edit and $rtn[] =  '<input type="hidden" name="taskComment_id" value="'.$v["commentID"].'">';
        $rtn[] =  '<table width="100%" cellspacing="0" border="0" class="comments">';
        $rtn[] =  '<tr>';
        $rtn[] =  '<th>Comment by <b>'.$person->get_username(1).'</b> '.$v["date"].' '.$ts_label."</th>";
        $edit and $rtn[] =  '<th align="right" width="2%">'.$comment_buttons.'</th>';
        $rtn[] =  '</tr>';
        $rtn[] =  '<tr>';
        $rtn[] =  '<td>'.nl2br(htmlentities($v["comment"])).'</td>';
        $edit and $rtn[] =  '<td>&nbsp;</td>';
        $rtn[] =  '</tr>';
        $rtn[] =  '</table>';
        $edit and $rtn[] =  '</form>';

      }
    }
    if (is_array($rtn))
    return implode("\n",$rtn);
  }


?>
