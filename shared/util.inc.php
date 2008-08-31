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



function path_under_path($path,$path2) {
  // Checks that path is under the directory path2
  $safe = realpath($path2);
  $unsafe = realpath($path);

  // strip trailing slash
  substr($safe,-1,1) == DIRECTORY_SEPARATOR and $safe = substr($safe,0,-1);
  substr($unsafe,-1,1) == DIRECTORY_SEPARATOR and $unsafe = substr($unsafe,0,-1);

  if ($safe && $unsafe) {
    // Make sure the unsafe dir is under the safe dir
    if (substr($unsafe,0,strlen($safe)) == $safe) {
      return true;
    }
  }
}
function get_textarea($name, $default_value="", $ops=array()) {
  $height = $ops["height"] or $height = "small";
  $heights["small"] = array(40, 120);
  $heights["medium"] = array(100, 300);
  $heights["large"] = array(340, 1020);
  $heights["jumbo"] = array(440, 1320);
  list($default_height, $max_height) = $heights[$height];

  $cols = $ops["cols"];
  !$ops["width"] && !$cols and $cols = 85;
  $cols and $cols = " cols=\"".$cols."\"";

  $ops["width"] and $width = "; width:".$ops["width"];
  $div_value = text_to_html($default_value);
  $str=<<<EOD
    <div id="shadow_${name}" style="position:absolute; left:-8000px; top:-8000px;">${div_value}</div>
    <textarea id="${name}" name="${name}" ${cols} wrap="virtual" style="height:${default_height}px${width}"
              onFocus="adjust_textarea(this,${default_height},${max_height})" 
              onBlur="stop_textarea_timer()""
    >${default_value}</textarea>
EOD;
  echo $str;
}
function get_calendar($name, $default_value="") {
  echo get_calendar_string($name, $default_value);
}
function get_calendar_string($name, $default_value="") {
  global $TPL;
  // setup the first day of the week
  $days = array("Sun","Mon","Tue","Wed","Thu","Fri","Sat");
  $days = array_flip($days);
  $firstday = config::get_config_item("calendarFirstDay");
  $firstday = sprintf("%d",$days[$firstday]);
  $default_value and $default = ", date : ".$default_value;
  $images = $TPL["url_alloc_images"];
  $year = date("Y");
  $str = <<<EOD
  <div class="calendar_container enclose nobr">
    <input name="${name}" type="text" size="11" value="${default_value}" id="${name}" class="datefield"><img src="${images}cal${year}.png" id="button_${name}" title="Date Selector">
  </div>
  <script type="text/javascript">
  Calendar.setup( { inputField : "${name}", ifFormat : "%Y-%m-%d", button : "button_${name}", showOthers : 1, align : "Bl", firstDay : ${firstday}, step : 1, weekNumbers : 0 ${default} })
  </script>

EOD;
  return $str;
}
function get_timezone_array() {
  return array("-12"  => "-12"
              ,"-11"  => "-11"
              ,"-10"  => "-10"
              ,"-9.5" => "-09.5"
              ,"-9"   => "-09"
              ,"-8.5" => "-08.5"
              ,"-8"   => "-08 PST"
              ,"-7"   => "-07 MST"
              ,"-6"   => "-06 CST"
              ,"-5"   => "-05 EST"
              ,"-4"   => "-04 AST"
              ,"-3.5" => "-03.5"
              ,"-3"   => "-03 ADT"
              ,"-2"   => "-02"
              ,"-1"   => "-01"
              ,"0"    => "00 GMT"
              ,"1"    => "+01 CET"
              ,"2"    => "+02"
              ,"3"    => "+03"
              ,"3.5"  => "+03.5"
              ,"4"    => "+04"
              ,"4.5"  => "+04.5"
              ,"5"    => "+05"
              ,"5.5"  => "+05.5"
              ,"6"    => "+06"
              ,"6.5"  => "+06.5"
              ,"7"    => "+07"
              ,"8"    => "+08"
              ,"9"    => "+09"
              ,"9.5"  => "+09.5"
              ,"10"   => "+10"
              ,"10.5" => "+10.5"
              ,"11"   => "+11"
              ,"11.5" => "+11.5"
              ,"12"   => "+12"
              ,"13"   => "+13"
              ,"14"   => "+14"
              );
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
function get_default_from_address() {
  // Wrap angle brackets around the default From: email address 
  $f = config::get_config_item("AllocFromEmailAddress");
  if ($f) {
    $l = strpos($f, "<");
    $r = strpos($f, ">");
    $l === false and $f = "<".$f;
    $r === false and $f .= ">";
    return "allocPSA ".$f;
  }
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
  static $version;
  if ($version) {
    return $version;
  }
  if (file_exists(ALLOC_MOD_DIR."util/alloc_version") && is_readable(ALLOC_MOD_DIR."util/alloc_version")) {
    $v = file(ALLOC_MOD_DIR."util/alloc_version");
    $version = trim($v[0]);
  } 
  return $version;
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
  if ($seconds > 0 || $seconds === 0.00) {
    return sprintf("%0.2f hrs",$hours);
  }
  return;
  
  if ($seconds < $day_in_seconds) {
    return sprintf("%0.2f hrs",$hours);
  } else {
    $days = $seconds / $day_in_seconds;
    #return sprintf("%0.1f days", $days);
    return sprintf("%0.2f hrs (%0.1f days)",$hours, $days);
     
  }
  
}
function page_close() {
  global $current_user;
  $sess = new Session;
  $sess->Save();
  if (is_object($current_user) && $current_user->get_id()) {
    $current_user->store_prefs();
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
function timetook($start, $friendly_output=true) {
  $end = microtime();
  list($start_micro,$start_epoch,$end_micro,$end_epoch) = explode(" ",$start." ".$end);
  $started  = (substr($start_epoch,-4) + $start_micro);
  $finished = (substr($end_epoch  ,-4) + $end_micro);
  $dur = $finished - $started;
  if ($friendly_output) {
    $unit = " seconds.";
    if ($dur > 60) {
      $unit = " mins.";
      $dur = $dur / 60;
    }
    return sprintf("%0.5f", $dur). $unit;
  }
  return sprintf("%0.5f", $dur);
}
function sort_by_name($a, $b) {
  return strtolower($a["name"]) >= strtolower($b["name"]);
}
function get_cached_table($table,$anew=false) {
  $cache = alloc_cache::get_cache();
  $cache->load_cache($table,$anew);

  // Special processing for person table
  if ($table == "person") {
    $people = $cache->get_cached_table("person") or $people = array();
    foreach ($people as $id => $row) {
      if ($people[$id]["firstName"] && $people[$id]["surname"]) {
        $people[$id]["name"] = $people[$id]["firstName"]." ".$people[$id]["surname"];
      } else {
        $people[$id]["name"] = $people[$id]["username"];
      }
    }
    uasort($people,"sort_by_name");
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
function show_header() {
  include_template(ALLOC_MOD_DIR."shared/templates/headerS.tpl");
}
function get_stylesheet_name() {
  if ($_GET["media"] == "print") {
    echo "print.css";
  } else {
    global $current_user;
    $themes = get_customizedTheme_array();
    $style = strtolower($themes[sprintf("%d", $current_user->prefs["customizedTheme2"])]);
    echo "style_".$style.".css";
  }
}
function get_default_font_size() {
  global $current_user;
  $fonts  = get_customizedFont_array();
  $font = $fonts[sprintf("%d",$current_user->prefs["customizedFont"])];
  $font or $font = 4;
  $font+= 8;
  echo $font;
}
function get_customizedFont_array() {
  return array("-3"=>1, "-2"=>2, "-1"=>3, "0"=>"4", "1"=>5, "2"=>6, "3"=>7, "4"=>8, "5"=>9, "6"=>10);
}
function get_customizedTheme_array() {
  global $TPL;
  $dir = $TPL["url_alloc_styles"];
  $rtn = array();
  if (is_dir($dir)) {
    $handle = opendir($dir);
    // TODO add icons to files attachaments in general
    while (false !== ($file = readdir($handle))) {
      if (preg_match("/style_(.*)\.ini$/",$file,$m)) {
        $rtn[] = ucwords($m[1]);
      }
    }
    sort($rtn);
  }
  return $rtn;
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
                     ,"Invoices" =>array("url"=>$TPL["url_alloc_invoiceList"],"module"=>"invoice")
                     ,"People"   =>array("url"=>$TPL["url_alloc_personList"],"module"=>"person")
                     ,"Tools"    =>array("url"=>$TPL["url_alloc_tools"],"module"=>"tools")
                     );

  $x = -1;
  foreach ($menu_links as $name => $arr) {
    $TPL["x"] = $x;
    $x+=80;
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
  global $TPL;
  $TPL["category_options"] = get_category_options($_GET["category"]);
  $TPL["needle"] = $_POST["needle"] or $TPL["needle"] = "Enter Search...";
  include_template(ALLOC_MOD_DIR."shared/templates/toolbarS.tpl");
}
function move_attachment($entity, $id=false) {
  global $TPL;

  $id = sprintf("%d",$id);

  // Re-jig the $_FILES array so that it can handle <input type="file" name="many_files[]">
  if ($_FILES) {
    foreach ($_FILES as $key => $f) {
      if (is_array($_FILES[$key]["tmp_name"])) {
        foreach ($_FILES[$key]["tmp_name"] as $k=>$v) {
          if ($_FILES[$key]["tmp_name"][$k]) {
            $files[] = array("name"     =>$_FILES[$key]["name"][$k]
                            ,"tmp_name" =>$_FILES[$key]["tmp_name"][$k]
                            ,"type"     =>$_FILES[$key]["type"][$k]
                            ,"error"    =>$_FILES[$key]["error"][$k]
                            ,"size"     =>$_FILES[$key]["size"][$k]
                            );
          }
        }
      } else if ($_FILES[$key]["tmp_name"]) {
          $files[] = array("name"     =>$_FILES[$key]["name"]
                          ,"tmp_name" =>$_FILES[$key]["tmp_name"]
                          ,"type"     =>$_FILES[$key]["type"]
                          ,"error"    =>$_FILES[$key]["error"]
                          ,"size"     =>$_FILES[$key]["size"]
                          );
      }
    }
  } 

  if (is_array($files) && count($files)) {

    foreach ($files as $file) {

  #    print_r($file);
  
      if (is_uploaded_file($file["tmp_name"])) {

        $dir = $TPL["url_alloc_attachments_dir"].$entity.DIRECTORY_SEPARATOR.$id;
        if (!is_dir($dir)) {
          mkdir($dir, 0777);
        }
        $newname = $file["name"];
        $newname = str_replace("/","",$newname);

        while (preg_match("/\.\./",$newname)) {
          $newname = str_replace("..",".",$newname);
        }

        if (!preg_match("/\.\./",$entity) && !preg_match("/\//",$entity)) {
          if (!move_uploaded_file($file["tmp_name"], $dir.DIRECTORY_SEPARATOR.$newname)) {
            die("Could not move attachment to: ".$dir.DIRECTORY_SEPARATOR.$newname);
          } else {
            chmod($dir.DIRECTORY_SEPARATOR.$newname, 0777);
          }
        } else {
          die("Error uploading file. Bad filename.");
        }
  
      } else {
        switch($file['error']){
          case 0: 
            die("There was a problem with your upload.");
            break;
          case 1: // upload_max_filesize in php.ini
            die("The file you are trying to upload is too big(1).");
            break;
          case 2: // MAX_FILE_SIZE
            die("The file you are trying to upload is too big(2).");
            break;
          case 3: 
            echo "The file you are trying upload was only partially uploaded.";
            break;
          case 4: 
            echo "You must select a file for upload.";
            break;
          default: 
            echo "There was a problem with your upload.";
            break;
        } 
      }
    }
  }
}
function get_mimetype($file) {
  $mimetype="application/octet-stream";
  if (function_exists("mime_content_type")) {
    $mimetype = mime_content_type($file);

  } else if ($size = getimagesize($file)) {
    $mimetype = $size['mime'];
  }
  return $mimetype;
}
function get_attachments($entity, $id, $ops=array()) {
  
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
          $image = "<img border=\"0\" alt=\"icon\" src=\"".$TPL["url_alloc_images"]."/fileicons/".$t."\">";

          $size = filesize($dir.DIRECTORY_SEPARATOR.$file);
          $row["path"] = $dir.DIRECTORY_SEPARATOR.$file;
          $row["file"] = "<a href=\"".$TPL["url_alloc_getDoc"]."id=".$id."&entity=".$entity."&file=".urlencode($file)."\">".$image.$ops["sep"].htmlentities($file)."</a>";
          $row["text"] = htmlentities($file);
          $size > 1023 and $row["size"] = sprintf("%dKb",$size/1024);
          $size < 1024 and $row["size"] = sprintf("%db",$size);
          $size > (1024 * 1024) and $row["size"] = sprintf("%0.1fMb",$size/(1024*1024));
          #$row["delete"] = "<a href=\"".$TPL["url_alloc_delDoc"]."id=".$id."&entity=".$entity."&file=".urlencode($file)."\">Delete</a>";
          $row["delete"] = "<form action=\"".$TPL["url_alloc_delDoc"]."\" method=\"post\">
                            <input type=\"hidden\" name=\"id\" value=\"".$id."\">
                            <input type=\"hidden\" name=\"file\" value=\"".$file."\">
                            <input type=\"hidden\" name=\"entity\" value=\"".$entity."\">
                            <input type=\"submit\" name=\"delete_file_attachment\" value=\"Delete\" class=\"delete_button\">
                            </form>";


          $row["mtime"] = date("Y-m-d H:i:s",filemtime($dir.DIRECTORY_SEPARATOR.$file));
          $row["restore_name"] = $file;

          $rows[] = $row;    
        }
      }
      closedir($handle);
    }
    is_array($rows) && usort($rows, "sort_by_mtime");
  }
  return $rows;
}
function sort_by_mtime($a, $b) {
  return $a["mtime"] >= $b["mtime"];
}
function util_show_attachments($entity, $id, $options=array()) {
  global $TPL;
  $TPL["entity_url"] = $TPL["url_alloc_".$entity];
  $TPL["entity_key_name"] = $entity."ID";
  $TPL["entity_key_value"] = $id;
  $TPL["bottom_button"] = $options["bottom_button"];

  $rows = get_attachments($entity, $id);
  $rows or $rows = array();
  foreach ($rows as $row) {
    $TPL["attachments"].= "<tr><td>".$row["file"]."</td><td class=\"nobr\">".$row["mtime"]."</td><td>".$row["size"]."</td>";
    $TPL["attachments"].= "<td align=\"right\" width=\"1%\" style=\"padding:5px;\">".$row["delete"]."</td></tr>";
  }
  include_template("../shared/templates/attachmentM.tpl");
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

  // Build options from an SQL query: "SELECT col_a as value, col_b as label FROM"
  if (is_string($options)) {
    $db = new db_alloc;
    $db->query($options);
    while ($row = $db->row()) {
      $rows[$row["value"]] = $row["label"];
    }

  // Build options from an array: array(value1=>label1, value2=>label2)
  } else if (is_array($options)) {
    foreach ($options as $k => $v) {
      $rows[$k] = $v;
    }
  }

  if (is_array($rows)) {
  
    // Coerce selected options into an array
    if (is_array($selected_value)) {
      $selected_values = $selected_value;
    } else if ($selected_value !== NULL) {
      $selected_values[] = $selected_value;
    }

    foreach ($rows as $value=>$label) {
      $sel = "";

      if ($value && !$label) { 
        $label = $value;
      }

      // If an array of selected values!
      if (is_array($selected_values)) {
        foreach ($selected_values as $selected_value) {
          if ($selected_value === "" && $value === 0) {
            // continue
          } else if ($selected_value == $value) {
            $sel = " selected";
          }
        }
      }

      $label = str_replace("&nbsp;"," ",$label);
      if (strlen($label) > $max_length) {
        $label = substr($label, 0, $max_length - 3)."...";
      } 
      #$label = htmlentities($label); nope!
      $label = str_replace(" ","&nbsp;",$label);

      $str.= "\n<option value=\"".$value."\"".$sel.">".$label."</option>";
    }
  }
  return $str;
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
  return db::esc($str);
}
function get_config_link() {
  global $current_user, $TPL;
  if (have_entity_perm("config", PERM_UPDATE, $current_user, true)) {
    echo "<a href=\"".$TPL["url_alloc_config"]."\">Setup</a>&nbsp;&nbsp;&nbsp;";
  }
}
function get_print_link() {
  if (defined("PAGE_IS_PRINTABLE") && PAGE_IS_PRINTABLE) {
    global $sess;
    $sess or $sess = new Session;
    echo "<a href=\"" . $sess->url($_SERVER["REQUEST_URI"]) . "media=print\">Print</a>&nbsp;&nbsp;&nbsp;";
  }
}
function get_help_link() {
  global $TPL;
  $url = "../help/help.php?topic=".$TPL["alloc_help_link_name"];
  echo "<a href=\"".$url."\">Help</a>&nbsp;&nbsp;&nbsp;";
}
function get_logout_link() {
  global $TPL;
  $url = $TPL["url_alloc_logout"];
  echo "<a href=\"".$url."\">Logout</a>";
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
  static $files;
  // Should never attempt to apply the same patch twice.. in case 
  // there are function declarations in the .php patches.
  if ($files[$f]) {
    return;
  }
  $files[$f] = true;
  $db = new db_alloc();
  $file = basename($f);
  $failed = false;
  $comments = array();

  // Try for sql file
  if (strtolower(substr($file,-4)) == ".sql") {

    list($sql,$comments) = parse_sql_file($f);
    foreach ($sql as $query) {
      if (!$db->query($query)) {
        #$TPL["message"][] = "<b style=\"color:red\">Error:</b> ".$f."<br/>".$db->get_error();
        $failed = true;
        die("<b style=\"color:red\">Error:</b> ".$f."<br/>".$db->get_error());
      }
    }
    if (!$failed) {
      $TPL["message_good"][] = "Successfully Applied: ".$f;
    }

  // Try for php file
  } else if (strtolower(substr($file,-4)) == ".php") {
    $str = execute_php_file("../patches/".$file);
    if ($str) {
      #$TPL["message"][] = "<b style=\"color:red\">Error:</b> ".$f."<br/>".$str;
      $failed = true;
      ob_end_clean();
      die("<b style=\"color:red\">Error:</b> ".$f."<br/>".$str);
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

  $msgtypes["message"]      = "bad";
  $msgtypes["message_good"] = "good";
  $msgtypes["message_help"] = "help";

  foreach ($msgtypes as $type => $label) {
    if ($TPL[$type] && is_string($TPL[$type])) {
      $t = $TPL[$type];
      unset($TPL[$type]);
      $TPL[$type][] = $t;
    }
    $_GET[$type] and $TPL[$type][] = urldecode($_GET[$type]);

    if (is_array($TPL[$type]) && count($TPL[$type])) {
      $arr[$label] = implode("<br/>",$TPL[$type]);
    }
  }

  if (is_array($arr) && count($arr)) {
    echo "<div style=\"text-align:center;\"><div class=\"message corner\">";
    echo "<table cellspacing=\"0\">";
    foreach ($arr as $type => $str) {
      echo "<tr><td width=\"1%\" style=\"vertical-align:top;\"><img src=\"".$TPL["url_alloc_images"]."icon_message_".$type.".png\"/><td/>";
      echo "<td class=\"".$type."\" align=\"left\" width=\"99%\">".str_replace('\\','',$str)."</td></tr>";
    }
    echo "</table>";
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
    $str[] = "<option value=\"".$TPL["url_alloc_expenseForm"]."\">New Expense Form</option>";
  }

  $str[] = "<option value=\"".$TPL["url_alloc_reminderAdd"]."parentType=general&step=2\">New Reminder</option>";

  if (have_entity_perm("person", PERM_CREATE, $current_user)) {
    $str[] = "<option value=\"".$TPL["url_alloc_person"]."\">New Person</option>";
  }

  $str[] = "<option value=\"".$TPL["url_alloc_loanAndReturn"]."\">New Item Loan</option>";

  $history = new history;
  $str[] = get_select_options($history->get_history_query("DESC"), $_GET["historyID"]);
  echo implode("\n",$str);
}   
function get_category_options($category="") {
  $category_options = array("Tasks"=>"Tasks", "Projects"=>"Projects", "Time"=>"Time", "Items"=>"Items", "Clients"=>"Clients");
  return get_select_options($category_options, $category);
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
    $img = "<div id='help_button_".$topic."' style='display:inline;'><a href=\"".$TPL["url_alloc_getHelp"]."topic=".$topic."\" target=\"_blank\">";
    $img.= "<img border='0' class='help_button' onmouseover=\"help_text_on('help_button_".$topic."','".$str."');\" onmouseout=\"help_text_off('help_button_".$topic."');\" src=\"";
    $img.= $TPL["url_alloc_images"]."help.gif\"></a></div>";
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
function get_expand_link($id, $text="New",$id_to_hide=false) {
  global $TPL;
  $id_to_hide and $extra = "$('#".$id_to_hide."').slideToggle('fast');";
  $str = "<a class=\"growshrink nobr\" href=\"#x\" onClick=\"$('#".$id."').slideToggle('fast');".$extra."\">".$text."</a>";
  return $str;
}
function print_expand_link($id, $text="New ",$id_to_hide="") {
  echo get_expand_link($id, $text, $id_to_hide);
}
function get_side_by_side_links($items=array(),$default=false) {
  global $TPL;

  foreach ($items as $id => $label) {
    $default or $default = $id; // first option is default
    $ids[] = $id; 
  }

  $js_array = "['".implode("','",$ids)."']";

  foreach ($items as $id => $label) {
    $str.= $sp."<a id=\"sbs_link_".$id."\" href=\"#x\" class=\"sidebyside\" onClick=\"sidebyside_activate('".$id."',".$js_array.");\">".$label."</a>";
    $sp = "&nbsp;";
  }

  // argh, I am bad man, this activates the default option, because it's minutely better than putting in a body onload
  $TPL["extra_footer_stuff"].= "<img src=\"".$TPL["url_alloc_images"]."pixel.gif\" onload=\"sidebyside_activate('".$default."',".$js_array.");\">";

  echo "<div style=\"margin:15px 0px 0px 0px;\">".$str."</div>";
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
function get_clientID_from_name($name) {                                                                                                                                 
  static $clients;                                                                                                                                                       
  if (!$clients) {                                                                                                                                                       
    $db = new db_alloc();                                                                                                                                                
    $q = sprintf("SELECT * FROM client");                                                                                                                                
    $db->query($q);                                                                                                                                                      
    while ($db->next_record()) {                                                                                                                                         
      $clients[$db->f("clientID")] = $db->f("clientName");                                                                                                               
    }                                                                                                                                                                    
  }                                                                                                                                                                      
                                                                                                                                                                         
  $stack = array();                                                                                                                                                      
  foreach ($clients as $clientID => $clientName) {
    similar_text($name,$clientName,$percent);
    $stack[$clientID] = $percent;
  }
                                                                                                                                                                         
  asort($stack);                                                                                                                                                         
  end($stack);
  $probable_clientID = key($stack);
  $client_percent = current($stack);
  
  return array($probable_clientID,$client_percent);
}  
function bad_filename($filename) {
  return preg_match("@[/\\\]@", $filename);
}
function has_backup_perm() {
  global $current_user;
  if (is_object($current_user)) {
    return $current_user->have_role("god");
  }
  return false;
}
function parse_email_address($email="") {
  // Takes Alex Lance <alla@cyber.com.au> and returns array("alla@cyber.com.au", "Alex Lance");
  if ($email) {
    $bits = explode(" ",$email);
    $last_bit = array_pop($bits);
    $address = str_replace(array("<",">"),"",$last_bit);
    is_array($bits) && count($bits) and $name = implode(" ",$bits);
    return array($address, $name);
  }
  return array();
}
function text_to_html($str="") {
  $str = htmlentities($str);
  $str = nl2br($str);
  return $str;
}
  function get_max_alloc_users() {
    if (function_exists("ace_get_max_alloc_users")) {
      return ace_get_max_alloc_users();
    }
    return 0;
  }
  function get_max_alloc_users_message() {
    if (function_exists("ace_get_max_alloc_users_message")) {
      return ace_get_max_alloc_users_message();
    }
    return "The number of active allocPSA user accounts has exceeded the maximum allowed.";
  }
  function get_num_alloc_users() {
    $db = new db_alloc();
    $db->query("SELECT COUNT(*) AS total FROM person WHERE personActive = 1");
    $row = $db->row();
    return $row["total"];
  }
function alloc_redirect($url) {
  global $TPL;

  $sep = "&";
  strpos($url,"?") === false and $sep = "?";

  foreach (array("message","message_good","message_help") as $type) {
    if ($TPL[$type]) {
      is_array($TPL[$type]) and $TPL[$type] = implode("<br>",$TPL[$type]);
      is_string($TPL[$type]) && strlen($TPL[$type]) and $str[] = $type."=".urlencode($TPL[$type]);
    }
  }

  $str and $str = $sep.implode("&",$str);
  header("Location: ".$url.$str);
  exit();
}
function mandatory($field="") {
  $star = "&lowast;";
  if (stristr($_SERVER["HTTP_USER_AGENT"],"MSIE")) {
    $star = "*";
  }
  if ($field == "") {
    echo "<b style=\"font-weight:bold;font-size:100%;color:red;display:inline;top:-5px !important;top:-3px;position:relative;\">".$star."</b>";
  }
}
  
?>
