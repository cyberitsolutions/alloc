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


function path_under_path($unsafe,$safe,$use_realpath=true) {
  // Checks that the potentially unsafe path is under the safe path
  if ($use_realpath) {
    $unsafe = realpath($unsafe);
    $safe = realpath($safe);
  }

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

// Format a time offset in seconds to (+|-)HH:MM
function format_offset($secs) {
  // sign will be included in the hours
  $sign = $secs < 0 ? '' : '+';
  $h = $secs / 3600;
  $m = $secs % 3600 / 60;
  
  return sprintf('%s%2d:%02d', $sign,$h,$m);
}

// List of Timezone => Offset Timezone
// i.e. Australia/Melbourne => +11:00 Australia/Melbourne
// Ordered by GMT offset
function get_timezone_array() {
  $zones = timezone_identifiers_list();
  $zonelist = array();

  // List format suitable for sorting
  $now = new DateTime();

  $idx = 0; //to distinguish timezones on the same offset
  foreach ($zones as $zone) {
    $tz = new DateTimeZone($zone);
    $offset = $tz->getOffset($now);
    // Index is [actual offset]+[arbitrary index]{3}
    $zonelist[$offset * 10000 + $idx++] = array($zone, format_offset($offset) . " " . $zone);
  }

  // Sort and unpack
  $list = array();
  ksort($zonelist);
  foreach ($zonelist as $zone) {
    $list[$zone[0]] = $zone[1];
  }

  return $list;
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
function add_brackets($email="") {
  if ($email) {
    $l = strpos($email, "<");
    $r = strpos($email, ">");
    $l === false and $email = "<".$email;
    $r === false and $email .= ">";
    return $email;
  }
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
function seconds_to_display_format($seconds) {
  $day = config::get_config_item("hoursInDay");

  $day_in_seconds = $day * 60 * 60;
  $hours = $seconds / 60 / 60;
  if ($seconds != "") {
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
function get_all_form_data($array=array(),$defaults=array()) {
  // Load up $_FORM with $_GET and $_POST
  $_FORM = array();
  foreach ($array as $name) {
    $_FORM[$name] = $defaults[$name] or $_FORM[$name] = $_POST[$name] or 
      $_FORM[$name] = (is_array($_GET[$name]) ? $_GET[$name] : urldecode($_GET[$name]));
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
function get_mimetype($filename="") {
  // We define our own mime_content_type() function (if the inbuilt one is
  // not available) at the end of this file.
  return mime_content_type($filename); 
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
  $TPL["show_buttons"] = !$options["hide_buttons"];

  $rows = get_attachments($entity, $id);
  if (!$rows && $options["hide_buttons"]) {
    return; // no rows, and no buttons, leave the whole thing out.
  }
  $rows or $rows = array();
  foreach ($rows as $row) {
    $TPL["attachments"].= "<tr><td>".$row["file"]."</td><td class=\"nobr\">".$row["mtime"]."</td><td>".$row["size"]."</td>";
    $TPL["attachments"].= "<td align=\"right\" width=\"1%\" style=\"padding:5px;\">".$row["delete"]."</td></tr>";
  }
  include_template("../shared/templates/attachmentM.tpl");
}
function get_filesize_label($file) {
  $size = filesize($file);
  $size > 1023 and $rtn = sprintf("%dK",$size/1024);
  $size < 1024 and $rtn = sprintf("%d",$size);
  $size > (1024 * 1024) and $rtn = sprintf("%0.1fM",$size/(1024*1024));
  return $rtn;
}
function get_file_type_image($file) {
  global $TPL;
  // hardcoded types ...
  $types["pdf"] = "pdf.gif";
  $types["xls"] = "xls.gif";
  $types["csv"] = "xls.gif";
  $types["zip"] = "zip.gif";
  $types[".gz"] = "zip.gif";
  $types["doc"] = "doc.gif";
  $types["sxw"] = "doc.gif";
  #$types["odf"] = "doc.gif";

  $type = strtolower(substr($file,-3));
  $icon_dir = ALLOC_MOD_DIR."images".DIRECTORY_SEPARATOR."fileicons".DIRECTORY_SEPARATOR;
  if ($types[$type]) {
    $t = $types[$type];
  } else if (file_exists($icon_dir.$type.".gif")) {
    $t = $type.".gif";
  } else if (file_exists($icon_dir.$type.".png")) {
    $t = $type.".png";
  } else {  
    $t = "unknown.gif";
  }
  return "<img border=\"0\" alt=\"icon\" src=\"".$TPL["url_alloc_images"]."/fileicons/".$t."\">";
}
function get_attachments($entity, $id, $ops=array()) {
  
  global $TPL;
  $rows = array();
  $dir = $TPL["url_alloc_attachments_dir"].$entity.DIRECTORY_SEPARATOR.$id;

  if (isset($id)) {
    #if (!is_dir($dir)) {
      #mkdir($dir, 0777);
    #}


    if (is_dir($dir)) {
      $handle = opendir($dir);

      // TODO add icons to files attachaments in general
      while (false !== ($file = readdir($handle))) {
        clearstatcache();

        if ($file != "." && $file != "..") {

          $image = get_file_type_image($dir.DIRECTORY_SEPARATOR.$file);

          $row["size"] = get_filesize_label($dir.DIRECTORY_SEPARATOR.$file);
          $row["path"] = $dir.DIRECTORY_SEPARATOR.$file;
          $row["file"] = "<a href=\"".$TPL["url_alloc_getDoc"]."id=".$id."&entity=".$entity."&file=".urlencode($file)."\">".$image.$ops["sep"].page::htmlentities($file)."</a>";
          $row["text"] = page::htmlentities($file);
          #$row["delete"] = "<a href=\"".$TPL["url_alloc_delDoc"]."id=".$id."&entity=".$entity."&file=".urlencode($file)."\">Delete</a>";
          $row["delete"] = "<form action=\"".$TPL["url_alloc_delDoc"]."\" method=\"post\">
                            <input type=\"hidden\" name=\"id\" value=\"".$id."\">
                            <input type=\"hidden\" name=\"file\" value=\"".$file."\">
                            <input type=\"hidden\" name=\"entity\" value=\"".$entity."\">
                            <input type=\"submit\" name=\"delete_file_attachment\" value=\"Delete\" class=\"delete_button\">
                            <input type=\"hidden\" name=\"sbs_link\" value=\"attachments\">
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
function db_esc($str = "") {
  return db::esc($str);
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
function same_email_address($addy1, $addy2) {
  list($from_address1,$from_name1) = parse_email_address($addy1);
  list($from_address2,$from_name2) = parse_email_address($addy2);
  if ($from_address1 == $from_address2) {
    return true;
  }
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
function obj2array($obj) {
  $out = array();
  foreach ($obj as $key => $val) {
    switch(true) {
        case is_object($val):
         $out[$key] = obj2array($val);
         break;
      case is_array($val):
         $out[$key] = obj2array($val);
         break;
      default:
        $out[$key] = $val;
    }
  }
  return $out;
}
function query_string_to_array($str="") {
  $pairs = explode("&", $str);
  foreach ($pairs as $pair) {
      $nv = explode("=", $pair);
      $name = urldecode($nv[0]);
      $value = urldecode($nv[1]);
      $vars[$name] = $value;
  }
  return (array)$vars;
}
if (!function_exists('mime_content_type')) {
  function mime_content_type($filename="") {
    $mime_types = array('txt'   => 'text/plain'
                       ,'mdwn'  => 'text/plain' // markdown text files
                       ,'htm'   => 'text/html'
                       ,'html'  => 'text/html'
                       ,'php'   => 'text/html'
                       ,'css'   => 'text/css'
                       ,'js'    => 'application/javascript'
                       ,'json'  => 'application/json'
                       ,'xml'   => 'application/xml'
                       ,'swf'   => 'application/x-shockwave-flash'
                       ,'flv'   => 'video/x-flv'
                       ,'png'   => 'image/png'
                       ,'jpe'   => 'image/jpeg'
                       ,'jpeg'  => 'image/jpeg'
                       ,'jpg'   => 'image/jpeg'
                       ,'gif'   => 'image/gif'
                       ,'bmp'   => 'image/bmp'
                       ,'ico'   => 'image/vnd.microsoft.icon'
                       ,'tiff'  => 'image/tiff'
                       ,'tif'   => 'image/tiff'
                       ,'svg'   => 'image/svg+xml'
                       ,'svgz'  => 'image/svg+xml'
                       ,'zip'   => 'application/zip'
                       ,'rar'   => 'application/x-rar-compressed'
                       ,'exe'   => 'application/x-msdownload'
                       ,'msi'   => 'application/x-msdownload'
                       ,'cab'   => 'application/vnd.ms-cab-compressed'
                       ,'mp3'   => 'audio/mpeg'
                       ,'qt'    => 'video/quicktime'
                       ,'mov'   => 'video/quicktime'
                       ,'pdf'   => 'application/pdf'
                       ,'psd'   => 'image/vnd.adobe.photoshop'
                       ,'ai'    => 'application/postscript'
                       ,'eps'   => 'application/postscript'
                       ,'ps'    => 'application/postscript'
                       ,'doc'   => 'application/msword'
                       ,'rtf'   => 'application/rtf'
                       ,'xls'   => 'application/vnd.ms-excel'
                       ,'ppt'   => 'application/vnd.ms-powerpoint'
                       ,'odt'   => 'application/vnd.oasis.opendocument.text'
                       ,'ods'   => 'application/vnd.oasis.opendocument.spreadsheet'
    );

    $bits = explode('.',$filename);
    count($bits) > 1 and $ext = strtolower(end($bits));

    // Or look for the suffix in our array
    if (array_key_exists($ext, $mime_types)) {
      $mt = $mime_types[$ext];

    // Or if we have the PECL FileInfo stuff available, use that to determine mimetype
    } else if (function_exists('finfo_open')) {
      $finfo = finfo_open(FILEINFO_MIME);
      $mimetype = finfo_file($finfo, $filename);
      finfo_close($finfo);
      $mt = $mimetype;
      $mt = current(explode(" ",$mimetype));

    // Or if the file is an image, get mime type the old-fashioned way
    } else if ($size = @getimagesize($filename)) {
      $mt = $size['mime'];

    // Or if no suffix at all, return text/plain
    } else if (!$ext) {
      $mt = 'text/plain'; 

    // Else unrecognised suffix, force browser to offer download dialog
    } else {
      $mt = 'application/octet-stream';
    }
    return $mt;
  }
}
function alloc_json_encode($arr=array()) {
  $sj = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
  return $sj->encode($arr);
}
function alloc_json_decode($str="") {
  $sj = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
  return $sj->decode($str);
}
function image_create_from_file($path) {
  $info = getimagesize($path);
  if(!$info) {
    echo "unable to determine getimagesize($path)";
    return false;
  }
  $functions = array(
    IMAGETYPE_GIF => 'imagecreatefromgif',
    IMAGETYPE_JPEG => 'imagecreatefromjpeg',
    IMAGETYPE_PNG => 'imagecreatefrompng',
    IMAGETYPE_WBMP => 'imagecreatefromwbmp',
    IMAGETYPE_XBM => 'imagecreatefromwxbm',
  );
  
  if(!$functions[$info[2]]) {
    echo "no function to handle $info[2]";
    return false;
  }
  if(!function_exists($functions[$info[2]])) {
    echo "no function exists to handle ".$functions[$info[2]];
    return false;
  }
  $f = $functions[$info[2]];
  return $f($path);
}
function operator_comparison($operator,$figure,$subject) {
  if ($operator == '=')  { return $figure == $subject; }
  if ($operator == '>')  { return $figure >  $subject; }
  if ($operator == '>=') { return $figure >= $subject; }
  if ($operator == '<')  { return $figure <  $subject; }
  if ($operator == '<=') { return $figure <= $subject; }
  if ($operator == '!=') { return $figure != $subject; }
}
function parse_operator_comparison($str,$figure) {
  $operator_regex = "/\s*([><=!]*)\s*([\d\.]+)\s*/";

  // 5
  if (is_numeric($str)) {
    $operator = '=';
    $number = $str;
    return operator_comparison($operator,$figure,$number);

  // <5 OR =10
  } else if (stristr($str,"OR")) {
    $criterias = explode("OR",$str);
    foreach ($criterias as $criteria) {
      if (parse_operator_comparison($criteria,$figure)) {
        return true;
      }
    }

  // >5 AND <10
  } else if (stristr($str,"AND")) {
    $criterias = explode("AND",$str);
    foreach ($criterias as $criteria) {
      preg_match($operator_regex,$criteria,$matches);
      $operator = $matches[1];
      $number = $matches[2];
      if (operator_comparison($operator,$figure,$number)) {
        $alive = true;
      } else {
        $dead = true;
      }
    }
    return $alive && !$dead;

  // >5
  } else if (preg_match($operator_regex,$str,$matches)) {
    $operator = $matches[1];
    $number = $matches[2];
    return operator_comparison($operator,$figure,$number);
  }
}
function imp($var) {
  // This function exists because php equates zeroes to false values.
  // imp == important == is this variable important == if imp($var)
  return $var !== array() && trim($var) !== '' && $var !== null && $var !== false;
}
function get_exchange_rate($from, $to) {
  $debug = $_REQUEST["debug"];

  usleep(500000); // So we don't hit their servers too hard
  $debug and print "<br>";

  $url = 'http://finance.yahoo.com/d/quotes.csv?f=l1d1t1&s='.$from.$to.'=X';
  $data = file_get_contents($url);
  $debug and print "<br>Y: ".htmlentities($data);
  $results = explode(",",$data);
  $rate = $results[0];
  $debug and print "<br>Yahoo says 5 ".$from." is worth ".($rate*5)." ".$to." at this exchange rate: ".$rate;

  if (!$rate) {
    $url = 'http://www.google.com/ig/calculator?hl=en&q='.urlencode('1'.$from.'=?'.$to);
    $data = file_get_contents($url);
    $debug and print "<br>G: ".htmlentities($data);
    $arr = alloc_json_decode($data);
    $rate = current(explode(" ",$arr["rhs"]));
    $debug and print "<br>Google says 5 ".$from." is worth ".($rate*5)." ".$to." at this exchange rate: ".$rate;
  }

  return trim($rate);
}
function array_kv($arr,$k,$v) {
  foreach ((array)$arr as $key => $value) {
    $rtn[$value[$k]] = $value[$v];
  }
  return $rtn;
}
?>
