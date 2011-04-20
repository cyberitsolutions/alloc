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


define("NO_REDIRECT",1);
require_once("../alloc.php");

$dont_print_these_dirs = array(".","..","CVS",".hg",".bzr","_darcs",".git");


// relative path
$DIR = urldecode($_POST['dir']);                                                       

// full path
$PATH = realpath(wiki_module::get_wiki_path().$DIR).DIRECTORY_SEPARATOR; 

if (path_under_path($PATH, wiki_module::get_wiki_path()) && is_dir($PATH)) {
  $files = scandir($PATH);
  natcasesort($files);
  $str.= "\n<ul class=\"jqueryFileTree\" style=\"display: none;\">";
  // All dirs
  foreach ($files as $file) {
    if(!in_array($file, $dont_print_these_dirs) && is_dir($PATH.$file) ) {
      $str.= "\n  <li class=\"directory collapsed\"><a class=\"file\" href=\"#\" rel=\"".page::htmlentities($DIR.$file.DIRECTORY_SEPARATOR)."\">".page::htmlentities($file)."</a></li>";
    }
  }

  // All files
  foreach($files as $file) {
    if(file_exists($PATH.$file) && $file != '.' && $file != '..' && !is_dir($PATH.$file) && is_readable($PATH.$file)) {
      unset($extra);
      !is_writable($PATH.$file) and $extra = "(ro) ";
      $ext = strtolower(preg_replace('/^.*\./', '', $file));
      $str.= "\n  <li class=\"file ext_$ext nobr\">";
      $str.= "\n    <a style=\"position:relative;\" class=\"file nobr\" href=\"#x\" rel=\"".page::htmlentities($DIR.$file)."\">".page::htmlentities($file);
      $str.= "<div class='faint nobr' style='top:0px; position:absolute;'>".$extra.get_filesize_label($PATH.$file)."</div></a>";
      $str.= "\n  </li>";
    }
  }
  $str.= "\n</ul>";	

  #echo "<pre>".page::htmlentities($str)."</pre>";
  echo $str;
}

?>
