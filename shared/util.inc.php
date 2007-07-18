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
function get_default_to_address() {
  $personID = config::get_config_item("timeSheetAdminEmail");
  $people = get_cached_table("person");
  $f = $people[$personID]["emailAddress"];
  $l = strpos($f, "<");
  $r = strpos($f, ">");
  $l === false and $f = "<".$f;
  $r === false and $f .= ">";
  return "allocPSA Administrator ".$f;
}
function get_alloc_version() {
  if (file_exists(ALLOC_MOD_DIR."util/alloc_version") && is_readable(ALLOC_MOD_DIR."util/alloc_version")) {
    $v = file(ALLOC_MOD_DIR."util/alloc_version");
    return $v[0];
  } else {
    die("No alloc_version file found.");
  }
}
function get_script_path($modules) {
  // Has to return something like
  // /alloc_dev/
  // /

  $path = dirname($_SERVER["SCRIPT_NAME"]);
  $bits = explode("/",$path);
  $last_bit = end($bits);

  if (is_array($modules) && in_array($last_bit,$modules)) {
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
  return sprintf("%0.2f hrs",$hours);
  
  if ($seconds < $day_in_seconds) {
    return sprintf("%0.2f hrs",$hours);
  } else {
    $days = $seconds / $day_in_seconds;
    #return sprintf("%0.1f days", $days);
    return sprintf("%0.2f hrs (%0.1f days)",$hours, $days);
     
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
function get_all_form_data($array=array(),$defaults=array()) {
  // Load up $_FORM with $_GET and $_POST
  $_FORM = array();
  foreach ($array as $name) {
    $_FORM[$name] = $defaults[$name] or $_FORM[$name] = $_POST[$name] or $_FORM[$name] = urldecode($_GET[$name]);
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
  $cache = alloc_cache::get_cache();
  $cache->load_cache($table);

  // Special processing for person table
  if ($table == "person") {
    $people = $cache->get_cached_table("person") or $people = array();
    foreach ($people as $id => $row) {
      if ($people[$id]["firstName"] && $people[$id]["surname"]) {
        $people[$id]["name"] = stripslashes($people[$id]["firstName"]." ".$people[$id]["surname"]);
      } else {
        $people[$id]["name"] = $people[$id]["username"];
      }
    }
    $cache->set_cached_table("person",$people);
  }

  if ($table == "htmlElement") {
    // Special processing for htmlElement table
    $htmlElement = $cache->get_cached_table("htmlElement") or $htmlElement = array();
    foreach ($htmlElement as $id => $row) {
      $rows_htmlElement[$row["handle"]] = $row;
    }
    $cache->set_cached_table("htmlElement",$rows_htmlElement);
  }

  if ($table == "config") {
    // Special processing for config table
    $config = $cache->get_cached_table("config") or $config = array();
    foreach ($config as $id => $row) {
      $rows_config[$row["name"]] = $row;
    }
    $cache->set_cached_table("config",$rows_config);
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
  include_template(ALLOC_MOD_DIR."shared/templates/headerS.tpl");
}
function get_stylesheet_name() {
  if ($_GET["media"] == "print") {
    echo "print.css";
  } else {
    global $current_user;

    $themes = get_customizedTheme_array();
    $fonts  = get_customizedFont_array();

    $style = strtolower($themes[sprintf("%d", $current_user->prefs["customizedTheme2"])]);
    $font = $fonts[sprintf("%d",$current_user->prefs["customizedFont"])];
    echo "style_".$style."_".$font.".css";
  }
}
function get_customizedFont_array() {
  return array("-3"=>1, "-2"=>2, "-1"=>3, "0"=>"4", "1"=>5, "2"=>6, "3"=>7, "4"=>8, "5"=>9, "6"=>10);
}
function get_customizedTheme_array() {
  return array("Default","Leaf");
}
function show_footer() {
  include_template(ALLOC_MOD_DIR."shared/templates/footerS.tpl");
}
function show_tabs() {
  global $TPL;

  $menu_links = array("Home"     =>array("url"=>$TPL["url_alloc_home"],"module"=>"home")
                     ,"Clients"  =>array("url"=>$TPL["url_alloc_clientList"],"module"=>"client")
                     ,"Projects" =>array("url"=>$TPL["url_alloc_projectList"],"module"=>"project")
                     ,"Tasks"    =>array("url"=>$TPL["url_alloc_taskList"],"module"=>"task")
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
    if (preg_match("/".str_replace("/", "\\/", $_SERVER["PHP_SELF"])."/", $url) || preg_match("/".$arr["module"]."/",$_SERVER["PHP_SELF"]) && !$done) {
      $TPL["active"] = " active";
      $done = true;
    }
    include_template(ALLOC_MOD_DIR."shared/templates/tabR.tpl");
  }
}
function show_toolbar() {
  global $TPL, $category;
  $TPL["category_options"] = get_category_options($_POST["category"]);
  $TPL["needle"] = $_POST["needle"] or $TPL["needle"] = "Enter Search...";
  include_template(ALLOC_MOD_DIR."shared/templates/toolbarS.tpl");
}
function move_attachment($entity, $id) {
  global $TPL;

  $id = sprintf("%d",$id);

  if ($_FILES["attachment"]) {
    is_uploaded_file($_FILES["attachment"]["tmp_name"]) || die("Uploaded document error.  Please try again.");

    $dir = $TPL["url_alloc_attachments_dir"].$entity.DIRECTORY_SEPARATOR.$id;
    if (!is_dir($dir)) {
      mkdir($dir, 0777);
    }

    $newname = $_FILES["attachment"]["name"];
    $newname = str_replace("/","",$newname);

    while (preg_match("/\.\./",$newname)) {
      $newname = str_replace("..",".",$newname);
    }

    if (!preg_match("/\.\./",$file) && !preg_match("/\//",$file)
    &&  !preg_match("/\.\./",$entity) && !preg_match("/\//",$entity)
    && strlen($newname) <= 40) {


      if (!move_uploaded_file($_FILES["attachment"]["tmp_name"], $dir.DIRECTORY_SEPARATOR.$newname)) {
        die("could not move attachment to: ".$dir.DIRECTORY_SEPARATOR.$newname);
      } else {
        chmod($dir.DIRECTORY_SEPARATOR.$newname, 0777);
      }
    } else {
      die("error uploading file. Please ensure that the filename only contains regular characters, 
           and that the length of the filename is shorter than 40 characters.");
    }
  }
}
function get_attachments($entity, $id) {
  
  global $TPL;
  $rows = array();
  $dir = $TPL["url_alloc_attachments_dir"].$entity.DIRECTORY_SEPARATOR.$id;

  if (isset($id)) {
    #if (!is_dir($dir)) {
      #mkdir($dir, 0777);
    #}

    $types["pdf"] = "pdf.gif";
    $types["xls"] = "xls.gif";
    $types["csv"] = "xls.gif";
    $types["zip"] = "zip.gif";
    $types[".gz"] = "zip.gif";
    $types["doc"] = "doc.gif";
    $types["sxw"] = "doc.gif";
    #$types["odf"] = "doc.gif";


    if (is_dir($dir)) {
      $handle = opendir($dir);

      // TODO add icons to files attachaments in general
      while (false !== ($file = readdir($handle))) {
        clearstatcache();

        if ($file != "." && $file != "..") {

          $type = substr($file,-3);
          $t = $types[$type] or $t = "unknown.gif";
          $image = "<img src=\"".$TPL["url_alloc_images"]."/fileicons/".$t."\">";

          $size = filesize($dir.DIRECTORY_SEPARATOR.$file);
          $row["file"] = "<a href=\"".$TPL["url_alloc_getDoc"]."id=".$id."&entity=".$entity."&file=".urlencode($file)."\">".$image.htmlentities($file)."</a>";
          $row["text"] = htmlentities($file);
          $size > 1023 and $row["size"] = sprintf("%dKb",$size/1024);
          $size < 1024 and $row["size"] = sprintf("%db",$size);
          $size > (1024 * 1024) and $row["size"] = sprintf("%0.1fMb",$size/(1024*1024));
          #$row["delete"] = "<a href=\"".$TPL["url_alloc_delDoc"]."id=".$id."&entity=".$entity."&file=".urlencode($file)."\">Delete</a>";
          $row["delete"] = "<form action=\"".$TPL["url_alloc_delDoc"]."\" method=\"post\">
                            <input type=\"hidden\" name=\"id\" value=\"".$id."\">
                            <input type=\"hidden\" name=\"file\" value=\"".$file."\">
                            <input type=\"hidden\" name=\"entity\" value=\"".$entity."\">
                            <input type=\"submit\" name=\"delete_file_attachment\" value=\"Delete\" onClick=\"return confirm('Delete File?')\">
                            </form>";


          $row["mtime"] = date("Y-m-d H:i:s",filemtime($dir.DIRECTORY_SEPARATOR.$file));
          $row["restore_name"] = $file;

          $rows[] = $row;    
        }
      }
    }
    is_array($rows) && usort($rows, "sort_by_mtime");
  }
  return $rows;
}
function sort_by_mtime($a, $b) {
  return $a["mtime"] >= $b["mtime"];
}
function util_show_attachments($entity, $id) {
  global $TPL;
  $TPL["entity_url"] = $TPL["url_alloc_".$entity];
  $TPL["entity_key_name"] = $entity."ID";
  $TPL["entity_key_value"] = $id;

  $rows = get_attachments($entity, $id);
  $rows or $rows = array();
  foreach ($rows as $row) {
    $TPL["attachments"].= "<tr><td>".$row["file"]."</td><td class=\"nobr\">".$row["mtime"]."</td><td>".$row["size"]."</td>";
    $TPL["attachments"].= "<td align=\"right\" width=\"1%\">".$row["delete"]."</td></tr>";
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

    if (!$v["comment"])
      continue ;

    unset($author,$emailed_text);
    $emailed_text = "Comment by ";
    if ($v["commentCreatedUserText"]) {
      $author = htmlentities($v["commentCreatedUserText"]);
      $emailed_text = "Comment emailed by ";

    } else if ($v["clientContactID"]) {
      $cc = new clientContact;
      $cc->set_id($v["clientContactID"]);
      $cc->select();
      #$author = " <a href=\"".$TPL["url_alloc_client"]."clientID=".$cc->get_value("clientID")."\">".$cc->get_value("clientContactName")."</a>";
      $author = $cc->get_value("clientContactName");
    } else {
      $person = new person;
      $person->set_id($v["personID"]);
      $person->select();
      $author = $person->get_username(1);
    }

    unset($modified_info);
    if ($v["commentModifiedTime"] || $v["commentModifiedUser"]) {
      $modified_info = ", last modified by ".person::get_fullname($v["commentModifiedUser"])." ".$v["commentModifiedTime"];
    }


    $comment_buttons = "";
    $ts_label = "";

    if ($v["timeSheetID"]) {
      $ts_label = " (Time Sheet Comment)";

    } else if (($v["personID"] == $current_user->get_id() || $current_user->have_role("admin")) && $options["showEditButtons"]) {
      $comment_buttons = "<nobr><input type=\"submit\" name=\"comment_edit\" value=\"Edit\">
                                <input type=\"submit\" name=\"comment_delete\" value=\"Delete\" onClick=\"return confirm('Are you sure you want to delete this comment?')\"></nobr>";
    }

    if (!$_GET["commentID"] || $_GET["commentID"] != $v["commentID"]) {

      $edit = false;
      if ($options["showEditButtons"]) {
        $edit = true;
      } 

      $files = get_attachments("comment",$v["commentID"]);
      #echo "<pre>".print_r($files,1)."</pre>";
      unset($f,$br);
      if (is_array($files)) {
        foreach($files as $key => $file) {
          $f.= $br.$file["file"];
          $br = "&nbsp;&nbsp;&nbsp;&nbsp;";
        }
      }

      unset($emailed);
      $v["commentEmailRecipients"] and $emailed = "<br>This comment has been emailed to ".$v["commentEmailRecipients"];


      $edit and $rtn[] =  '<form action="'.$TPL["url_alloc_comment"].'" method="post">';
      $edit and $rtn[] =  '<input type="hidden" name="entity" value="'.$entity.'">';
      $edit and $rtn[] =  '<input type="hidden" name="entityID" value="'.$v["commentLinkID"].'">';
      $edit and $rtn[] =  '<input type="hidden" name="commentID" value="'.$v["commentID"].'">';
      $edit and $rtn[] =  '<input type="hidden" name="comment_id" value="'.$v["commentID"].'">';
      $rtn[] =  '<table width="100%" cellspacing="0" border="0" class="comments">';
      $rtn[] =  '<tr>';
      $rtn[] =  '<th>'.$emailed_text.'<b>'.$author.'</b> '.$v["date"].$ts_label.$modified_info.$emailed."</th>";
      $edit and $rtn[] =  '<th align="right" width="2%">'.$comment_buttons.'</th>';
      $rtn[] =  '</tr>';
      $rtn[] =  '<tr>';
      $rtn[] =  '<td>'.nl2br(htmlentities($v["comment"])).'</td>';
      $edit and $rtn[] =  '<td>&nbsp;</td>';
      $rtn[] =  '</tr>';
      $files and $rtn[] =  '<tr>';
      $files and $rtn[] =  '<td colspan="2">'.$f.'</td>';
      $files and $rtn[] =  '</tr>';
      $rtn[] =  '</table>';
      $edit and $rtn[] =  '</form>';

    }
  }
  if (is_array($rtn)) {
    return implode("\n",$rtn);
  }
}
function get_display_date($db_date) {
  // Convert date from database format (yyyy-mm-dd) to display format (d/m/yyyy)
  if ($db_date == "0000-00-00 00:00:00") {
    return "";
  } else if (ereg("([0-9]{4})-?([0-9]{2})-?([0-9]{2})", $db_date, $matches)) {
    return sprintf("%d/%d/%d", $matches[3], $matches[2], $matches[1]);
  } else {
    return "";
  }
}
function get_date_stamp($db_date) {
  // Converts from DB date string of YYYY-MM-DD to a Unix time stamp
  ereg("^([0-9]{4})-([0-9]{2})-([0-9]{2})", $db_date, $matches);
  $date_stamp = mktime(0, 0, 0, $matches[2], $matches[3], $matches[1]);
  return $date_stamp;
}
function get_mysql_date_stamp($db_date) {
  // Converts mysql timestamp 20011024161045 to YYYY-MM-DD - AL
  if (ereg("^([0-9]{4})-?([0-9]{2})-?([0-9]{2})", $db_date, $matches)) {
    $date_stamp = mktime(0, 0, 0, $matches[2], $matches[3], $matches[1]);
    $date = date("Y", $date_stamp)."-".date("m", $date_stamp)."-".date("d", $date_stamp);
    return $date;
  } else {
    return $db_date;
  }
}
function get_select_options($options,$selected_value=NULL,$max_length=45) {
  /**
  * Builds up options for use in a html select widget (works with multiple selected too)
  *
  * @param   $options          mixed   An sql query or an array of options
  * @param   $selected_value   string  The current selected element
  * @param   $max_length       int     The maximum string length of the label
  * @return                    string  The string of options
  */

  // Build options from an SQL query: "SELECT col_a as name, col_b as value FROM"
  if (is_string($options)) {
    $db = new db_alloc;
    $db->query($options);
    while ($row = $db->row()) {
      $rows[$row["name"]] = $row["value"];
    }

  // Build options from an array: array(array("name1","value1"),array("name2","value2"))
  } else if (is_array($options)) {
    foreach ($options as $k => $v) {
      $rows[$k] = $v;
    }
  }

  if (is_array($rows)) {
    foreach ($rows as $value=>$label) {
      $sel = "";

      if (!$value && $value!==0 && !$value!=="0" && $label) {
        $value = $label; 
      }
      !$label && $value and $label = $value;

      // If an array of selected values!
      if (is_array($selected_value)) {
        foreach ($selected_value as $id) {
          $id == $value and $sel = " selected";
        }
      } else {
        $selected_value == $value and $sel = " selected";
      }

      $label = stripslashes($label);
      if (strlen($label) > $max_length) {
        $label = substr($label, 0, $max_length - 3)."...";
      } 

      $str.= "\n<option value=\"".$value."\"".$sel.">".$label."</option>";
    }
  }
  return $str;
}
function get_options_from_array($options, $selected_value, $use_values = true, $max_label_length = 40, $bitwise_values = false, $reverse_results = false) {
  // Get options for a <select> using an array of the form value=>label
  is_array($options) or $options = array();

  if ($reverse_results) {
    $options = array_reverse($options, TRUE);
  }
  foreach ($options as $value => $label) {
    $rtn.= "\n<option";
    if ($use_values) {
      $rtn.= " value=\"$value\"";

      if ($value == $selected_value || ($bitwise_values && (($selected_value & $value) == $value))) {
        $rtn.= " selected";
      }
    } else {
      $rtn.= " value=\"$label\"";
      if ($label == $selected_value) {
        $rtn.= " selected";
      }
    }
    $rtn.= ">";
    $label = stripslashes($label);
    if (strlen($label) > $max_label_length) {
      $rtn.= substr($label, 0, $max_label_length - 3)."...";
    } else {
      $rtn.= $label;
    }
    $rtn.= "</option>";
  }
  return $rtn;
}
function get_array_from_db($db, $key_field, $label_field) {
  // Constructs an array from a database containing 
  // $key_field=>$label_field entries
  // ALLA: Edited function so that an array of 
  // label_field could be passed $return is the 
  // _complete_ label string.
  // TODO: Make this function SORT
  $rtn = array();
  while ($db->next_record()) {
    if (is_array($label_field)) {
      $return = "";
      foreach($label_field as $key=>$label) {

        // Every second array element (starting with zero) will 
        // be the string separator. This really isn't quite as 
        // lame as it seems.  Although it's close.
        if (!is_int($key / 2)) {
          $return.= $db->f($label);
        } else {
          $return.= $label;
        }
      }
    } else {
      $return = $db->f($label_field);
    }
    if ($key_field) {
      $rtn[$db->f($key_field)] = stripslashes($return);
    } else {
      $rtn[] = stripslashes($return);
    }
  }
  return $rtn;
}
function get_options_from_db($db, $label_field, $value_field = "", $selected_value, $max_label_length = 40, $reverse_results = false) {
  // Get options for a <select> using a database object
  $options = get_array_from_db($db, $value_field, $label_field);
  return get_options_from_array($options, $selected_value, $value_field != "", $max_label_length, $bitwise_values = false, $reverse_results);
}
function get_tf_name($tfID) {
  if (!$tfID) {
    return false;
  } else {
    $db = new db_alloc;
    $db->query("select tfName from tf where tfID= ".$tfID);
    $db->next_record();
    return $db->f("tfName");
  }
}
function db_esc($str = "") {
  // If they're using magic_quotes_gpc then we gotta strip the 
  // automatically added backslashes otherwise they'll be added again..
  if (get_magic_quotes_gpc()) {
    $str = stripslashes($str);
  }
  $esc_function = "mysql_escape_string";
  if (version_compare(phpversion(), "4.3.0", ">")) {
    $esc_function = "mysql_real_escape_string";
  }
  
  if (is_numeric($str)) {
    return $str;
  }
  return $esc_function($str);
}
function db_get_where($where = array()) {
  // Okay so $value can be like eg: $where["status"] = array(" LIKE ","hey")
  // Or $where["status"] = "hey";
  foreach($where as $column_name=>$value) {
    $op = " = ";
    if (is_array($value)) {
      $op = $value[0];
      $value = $value[1];
    }
    $rtn.= " ".$and.$column_name.$op." '".db_esc($value)."'";
    $and = " AND ";
  }
  return $rtn;
}
function format_date($format="Y/m/d", $date="") {

  // If looks like this: 2003-07-07 21:37:01
  if (preg_match("/^[\d]{4}-[\d]{2}-[\d]{2} [\d]{2}:[\d]{2}:[\d]{2}$/",$date)) {
    list($d,$t) = explode(" ", $date);

  // If looks like this: 2003-07-07
  } else if (preg_match("/^[\d]{4}-[\d]{2}-[\d]{2}$/",$date)) {
    $d = $date;

  // If looks like this: 12:01:01
  } else if (preg_match("/^[\d]{2}:[\d]{2}:[\d]{2}$/",$date)) {
    $d = "2000-01-01";
    $t = $date;

  // Nasty hobbitses!
  } else if ($date) {
    return "Date unrecognized: ".$date;
  } else {
    return;
  }
  list($y,$m,$d) = explode("-", $d);
  list($h,$i,$s) = explode(":", $t);
  list($y,$m,$d,$h,$i,$s) = array(sprintf("%d",$y),sprintf("%d",$m),sprintf("%d",$d)
                                 ,sprintf("%d",$h),sprintf("%d",$i),sprintf("%d",$s)
                                 );
  return date($format, mktime(date($h),date($i),date($s),date($m),date($d),date($y)));
}
function get_config_link() {
  global $current_user, $TPL;
  if (have_entity_perm("config", PERM_UPDATE, $current_user, true)) {
    echo "<a href=\"".$TPL["url_alloc_config"]."\">Setup</a>";
  }
}
function get_print_link() {
  global $printable;
  if ($printable) {
    echo "<a href=\"" . $_SERVER["REQUEST_URI"] . "&media=print\">Print</a>";
  }
}
function parse_sql_file($file) {
  
  // Filename must be readable and end in .sql
  if (!is_readable($file) || substr($file,-4) != strtolower(".sql")) {
    return;
  }

  $sql = array();
  $comments = array();
  $mqr = @get_magic_quotes_runtime();
  @set_magic_quotes_runtime(0);
  $lines = file($file);
  @set_magic_quotes_runtime($mqr);

  foreach ($lines as $line) {
    if (preg_match("/^[\s]*(--[^\n]*)$/", $line, $m)) {
      $comments[] = str_replace("-- ","",trim($m[1]));
    } else if (!empty($line) && substr($line,0,2) != "--" && $line) {
      $queries[] = trim($line);
    }
  }

  $bits = array();
  foreach ($queries as $query) {
    if(!empty($query)) {
      $query = trim($query);
      $bits[] = $query;
      if (preg_match('/;\s*$/',$query)) {
        $sql[] = implode(" ",$bits);
        $bits = array();
      }
    }
  }
  return array($sql,$comments);
} 
function parse_php_file($file) {
  // Filename must be readable and end in .php
  if (!is_readable($file) || substr($file,-4) != strtolower(".php")) {
    return;
  }

  $php = array();
  $comments = array();
  $mqr = @get_magic_quotes_runtime();
  @set_magic_quotes_runtime(0);
  $lines = file($file);
  @set_magic_quotes_runtime($mqr);

  foreach ($lines as $line) {
    if (preg_match("/^[\s]*(\/\/[^\n]*)$/", $line, $m)) {
      $comments[] = str_replace("// ","",trim($m[1]));
    } else if (!empty($line) && substr($line,0,2) != "//" && $line) {
      $php[] = trim($line);
    }
  }
  return array($php,$comments);
}
function parse_patch_file($file) {
  if (!is_readable($file)) {
    return;
  }

  if (substr($file,-4) == strtolower(".php")) {
    return parse_php_file($file);

  } else if (substr($file,-4) == strtolower(".sql")) {
    return parse_sql_file($file);
  }
}
function execute_php_file($file_to_execute) {
   global $TPL;
   ob_start();
   include($file_to_execute);
   return ob_get_contents();
}
function apply_patch($f) {
  global $TPL;
  $db = new db_alloc();
  $file = basename($f);
  $failed = false;
  $comments = array();

  // Try for sql file
  if (strtolower(substr($file,-4)) == ".sql") {

    list($sql,$comments) = parse_sql_file($f);
    foreach ($sql as $query) {
      if (!$db->query($query)) {
        $TPL["message"][] = "<b style=\"color:red\">Error:</b> ".$f."<br/>".$db->get_error();
        $failed = true;
      }
    }
    if (!$failed) {
      $TPL["message_good"][] = "Successfully Applied: ".$f;
    }

  // Try for php file
  } else if (strtolower(substr($file,-4)) == ".php") {
    $str = execute_php_file("../patches/".$file);
    if ($str) {
      $TPL["message"][] = "<b style=\"color:red\">Error:</b> ".$f."<br/>".$str;
      $failed = true;
      ob_end_clean();
    } else {
      $TPL["message_good"][] = "Successfully Applied: ".$f;
    }
  }
  if (!$failed) {
    $q = sprintf("INSERT INTO patchLog (patchName, patchDesc, patchDate) 
                  VALUES ('%s','%s','%s')",db_esc($file), db_esc(implode(" ",$comments)), date("Y-m-d H:i:s"));
    $db->query($q);
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
    echo "<div style=\"text-align:center;\"><div class=\"message\">";

    foreach ($arr as $type => $str) {
      echo "<table cellspacing=\"0\"><tr><td width=\"1%\" style=\"vertical-align:top;\"><img src=\"".$TPL["url_alloc_images"]."icon_message_".$type.".gif\"/><td/>";
      echo "<td class=\"".$type."\" align=\"left\" width=\"99%\">".str_replace('\\','',$str)."</td></tr></table>";
    }
    echo "</div></div>";
  }

}
function show_history() {
  global $TPL, $current_user, $modules;
  $db = new db_alloc; 

  $str[] = "<option value=\"\">Quick List</option>";
  $str[] = "<option value=\"".$TPL["url_alloc_task"]."\">New Task</option>";

  if (isset($modules["time"]) && $modules["time"]) {
    $str[] = "<option value=\"".$TPL["url_alloc_timeSheet"]."\">New Time Sheet</option>";
  }

  $str[] = "<option value=\"".$TPL["url_alloc_task"]."tasktype=".TT_FAULT."\">New Fault</option>";
  $str[] = "<option value=\"".$TPL["url_alloc_task"]."tasktype=".TT_MESSAGE."\">New Message</option>";

  if (have_entity_perm("project", PERM_CREATE, $current_user)) {
    $str[] = "<option value=\"".$TPL["url_alloc_project"]."\">New Project</option>";
  } 

  if (isset($modules["client"]) && $modules["client"]) {
    $str[] = "<option value=\"".$TPL["url_alloc_client"]."\">New Client</option>";
  } 

  if (isset($modules["finance"]) && $modules["finance"]) {
    $str[] = "<option value=\"".$TPL["url_alloc_expOneOff"]."\">New Expense Form</option>";
  }

  $str[] = "<option value=\"".$TPL["url_alloc_reminderAdd"]."parentType=general&step=2\">New Reminder</option>";

  if (have_entity_perm("person", PERM_CREATE, $current_user)) {
    $str[] = "<option value=\"".$TPL["url_alloc_person"]."\">New Person</option>";
  }

  $str[] = "<option value=\"".$TPL["url_alloc_loanAndReturn"]."\">New Item Loan</option>";

  $history = new history;
  $str[] = get_options_from_db($history->get_history_db("DESC"), "the_label", "historyID", $_GET["historyID"], 35);
  echo implode("\n",$str);
}   
function get_category_options($category="") {
  $category_options = array("Tasks"=>"Tasks", "Projects"=>"Projects", "Time"=>"Time", "Items"=>"Items", "Clients"=>"Clients");
  return get_options_from_array($category_options, $category, true);
} 
function get_help_string($topic) {
  global $TPL;

  $file = $TPL["url_alloc_help"].$topic.".html";
  if (file_exists($file)) {
    $str = file_get_contents($file);

  } else {
    $rows = get_cached_table("htmlElement");
    $str = $rows[$topic]["helpText"];
  }

  $str = htmlentities(addslashes($str));
  $str = str_replace("\r"," ",$str);
  $str = str_replace("\n"," ",$str);

  return $str;
}
function get_help($topic) {
  global $TPL;
  $str = get_help_string($topic);
  if (strlen($str)) {
    $img = "<a href=\"".$TPL["url_alloc_help_relative"]."getHelp.php?topic=".$topic."\" target=\"_blank\">";
    $img.= "<img id=\"help_button_".$topic."\" border=\"0\" onmouseover=\"help_text_on(this,'".$str."');\" onmouseout=\"help_text_off(this);\" src=\"";
    $img.= $TPL["url_alloc_images"]."help.gif\" style=\"border:1px solid #999999;\"></a>";
  }
  echo $img;
}
function get_text($handle) {
  $rows = get_cached_table("htmlElement");
  echo $rows[$handle]["label"];
}
function get_html($handle,$value=false) {
  echo build_html_element($handle,$value);
}
function get_help_link() {
  global $TPL;
  $url = "../help/help.html#".$TPL["alloc_help_link_name"];
  echo "<a href=\"".$url."\">Help</a>";
}
function get_expand_link($id) {
  global $TPL;
  $display = "none";
  echo "<div id=\"button_".$id."\"><a class=\"nobr\" onClick=\"set_grow_shrink_box('".$id."','".$display."','".$TPL["url_alloc_images"]."');\">New ";
  echo "<img border=\"0\" src=\"".$TPL["url_alloc_images"]."small_grow.gif\"></a></div>";
}
function build_html_tag($htmlElementID,$value="") {
  $db = new db_alloc();

  $q = sprintf("SELECT * FROM htmlElement WHERE htmlElementID = %d",$htmlElementID);
  $db->query($q);
  $row = $db->next_record();

  $q = sprintf("SELECT * FROM htmlElementType WHERE htmlElementTypeID = %d",$row["htmlElementTypeID"]);
  $db->query($q);
  $row_type = $db->next_record();

  $str_nobr[] = "<".$row_type["name"];

  $q = sprintf("SELECT * FROM htmlAttribute WHERE htmlElementID = '%s'",db_esc($row["htmlElementID"]));
  $db->query($q);
  while ($row_attr = $db->next_record()) {
    if (!($row_type["hasValueAttribute"] && $row_type["valueAttributeName"] == $row_attr["name"])) {
      $str_nobr[] = $row_attr["name"]."=\"".$row_attr["value"]."\"";
      $attributes[$row_attr["name"]] = $row_attr["value"];
    }
  }

  if ($row_type["hasValueAttribute"] && $row_type["hasLabelValue"]) {
    $str_nobr[] = "value=\"".$row["label"]."\"";

  } else if ($row_type["hasValueAttribute"] && $row_type["valueAttributeName"] && ($attributes["value"] == $value || is_array($value) && in_array($attributes["value"],$value))) {
    $str_nobr[] = $row_type["valueAttributeName"]."=\"".$value."\"";

  } else if ($row_type["hasValueAttribute"] && !$row_type["valueAttributeName"]) {
    $str_nobr[] = "value=\"".$value."\"";
  } 

  if (!$row_type["hasEndTag"]) {
    $str_nobr[] = " />";
  } else {
    $str_nobr[] = ">";
  }

  $str[] = implode(" ",$str_nobr);

  if ($row_type["hasValueContent"]) {
    $str[] = $value;
  } else if ($row_type["hasContent"]) {
    $str[] = $row["label"];
  }

  if ($row_type["hasChildElement"]) { 
    $q = sprintf("SELECT * FROM htmlElement WHERE htmlElementParentID = %d AND enabled = 1 ORDER BY sequence",$row["htmlElementID"]);
    $db->query($q);
    while ($r = $db->next_record()) {
      $str[] = "\n".build_html_element($r["handle"],$value);
    }
  }

  if ($row_type["hasEndTag"]) {
    $str[] = "</".$row_type["name"].">";
  }
  
  return $str;
}
function build_html_element($handle,$value="") {
  $db = new db_alloc();
  $q = sprintf("SELECT * FROM htmlElement WHERE handle = '%s'",db_esc($handle));
  $db->query($q);
  $row = $db->next_record();

  $str = build_html_tag($row["htmlElementID"],$value);

  if (is_array($str))
  return implode("",$str);
}
function encrypt_password($password) {
  $t_hasher = new PasswordHash(8, FALSE);
  return $t_hasher->HashPassword($password);
}
function check_password($password, $hash) {
  $t_hasher = new PasswordHash(8, FALSE);
  return $t_hasher->CheckPassword($password, $hash);
}
function bad_filename($filename) {
  return preg_match("@[/\\\]@", $filename);
}

?>
