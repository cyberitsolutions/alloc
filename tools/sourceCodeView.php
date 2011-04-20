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

$prohibited[] = "alloc_config.php";

if ($_GET["dir"] && $_GET["file"]) {
  $path = realpath($_GET["dir"].DIRECTORY_SEPARATOR.$_GET["file"]);
  $TPL["path"] = $path;
  if (path_under_path($path,ALLOC_MOD_DIR) && is_file($path) && !in_array(basename($path),$prohibited)) {
    $TPL["results"] = page::htmlentities(file_get_contents($path));
  }
}


include_template("templates/sourceCodeView.tpl");

?>
