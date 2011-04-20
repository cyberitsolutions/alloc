<?php

/*
 * Copyright (C) 2006-2011 Alex Lance, Clancy Malcolm, Cyber IT Solutions
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

require_once("../alloc.php");


function get_all_source_files($dir="") {
  global $TPL;
  $dir or $dir = ALLOC_MOD_DIR;

  if (path_under_path($dir,ALLOC_MOD_DIR) && is_dir($dir)) {

    $dir = realpath($dir);
    $handle = opendir($dir);
    while (false !== ($file = readdir($handle))) {
      clearstatcache();

      if ($file == ".") continue;
      if ($file == ".." && realpath($dir) == realpath(ALLOC_MOD_DIR)) continue;

      if (is_file($dir.DIRECTORY_SEPARATOR.$file)) {
        $image = "<img border=\"0\" alt=\"icon\" src=\"".$TPL["url_alloc_images"]."/fileicons/unknown.gif\">";
        $files[$file] = "<a href=\"".$TPL["url_alloc_sourceCodeView"]."dir=".urlencode($dir)."&file=".urlencode($file)."\">".$image.$file."</a>";
      } else if (is_dir($dir.DIRECTORY_SEPARATOR.$file)) {
        $image = "<img border=\"0\" alt=\"icon\" src=\"".$TPL["url_alloc_images"]."/fileicons/directory.gif\">";
        $dirs[$file] = "<a href=\"".$TPL["url_alloc_sourceCodeList"]."dir=".urlencode($dir.DIRECTORY_SEPARATOR.$file)."\">".$image.$file."</a>";
      } else { 
        #echo "<br>wtf: ".$dir.DIRECTORY_SEPARATOR.$file;
      }

    }
  }
  

  $files or $files = array();
  $dirs or $dirs = array();
  asort($files);
  asort($dirs);
  $rtn = array_merge($dirs, $files);
  return $rtn;
}


$files = get_all_source_files($_GET["dir"]);
if (is_array($files)) {
  foreach ($files as $file => $link) {
    $TPL["results"].= "<p style=\"padding:0px; margin:4px\">".$link."</p>";
  }
}



include_template("templates/sourceCodeListM.tpl");

?>
