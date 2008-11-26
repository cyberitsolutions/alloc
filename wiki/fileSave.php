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


require_once("../alloc.php");

$file = $_POST["file"];
$filelabel = str_replace(get_wiki_path(),"",$file);
$target = str_replace(get_wiki_path(),"",$file);
$text = html_entity_decode($_POST["wikitext"]);
$text = str_replace("\r\n","\n",$text);


if (path_under_path(dirname($file), get_wiki_path()) && is_file($file) && is_writable($file)) {

  // Check if we're using a VCS
  $vcs = vcs::get();
  //$vcs->debug = true;

  // Save the file ...
  $handle = fopen($file,"w+b");
  fputs($handle,$text);
  fclose($handle);

  // VCS commit the file
  if (is_object($vcs)) {
    $vcs->commit($file,$_POST["commit_msg"]);
  }

  $TPL["message_good"][] = "File saved: ".$filelabel;
} else {
  $TPL["message"][] = "Problem saving file: ".$filelabel;
}

alloc_redirect($TPL["url_alloc_wiki"]."target=".urlencode($target));

?>
