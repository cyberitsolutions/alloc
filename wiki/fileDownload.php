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

$file = realpath(wiki_module::get_wiki_path().$_GET["file"]);

if (path_under_path(dirname($file), wiki_module::get_wiki_path())) {
  $fp = fopen($file, "rb");
  $mimetype = get_mimetype($file);
  $disposition = "attachment";
  preg_match("/jpe?g|gif|png/i",basename($file)) and $disposition = "inline";
  header('Content-Type: '.$mimetype);
  header("Content-Length: ".filesize($file));
  header('Content-Disposition: '.$disposition.'; filename="'.basename($file).'"');
  fpassthru($fp);
  exit;
}


?>
