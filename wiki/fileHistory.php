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

$file = $_GET["file"];
$rev = $_GET["rev"];
$pathfile = realpath(wiki_module::get_wiki_path().$file);

if (path_under_path(dirname($pathfile), wiki_module::get_wiki_path())) {

  // Check if we're using a VCS
  $vcs = vcs::get();
  #$vcs->debug = true;
  if (is_object($vcs)) {
    $logs = $vcs->log($pathfile);
    $logs = $vcs->format_log($logs);
    foreach ($logs as $id => $bits) {
      unset($class);
      if (is_file($pathfile)) {
        $rev == $id and $class = "highlighted";
        !$rev && !$done and $done = $class = "highlighted";
      }
      echo "<div class=\"".$class."\" style=\"padding:3px; margin-bottom:10px;\">";
      is_file($pathfile) and print sprintf("<a href='%starget=%s&rev=%s'>",$TPL["url_alloc_wiki"],urlencode($file),urlencode($id));
      echo $bits["author"]." ".$bits["date"]."<br>".$bits["msg"];
      is_file($pathfile) and print "</a>";
      echo "</div>";
    }
  
  }

}

?>
