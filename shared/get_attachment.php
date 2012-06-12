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

// For use like get_attachment.php?entity=project&id=5&file=foo.bar

require_once("../alloc.php");

$file = $_GET["file"];

if (isset($_GET["id"]) && $file && !bad_filename($file)) {

  $entity = new $_GET["entity"];
  $entity->set_id(sprintf("%d",$_GET["id"]));
  $entity->select();

  $file = ATTACHMENTS_DIR.$_GET["entity"]."/".$_GET["id"]."/".$file;

  if ($entity->has_attachment_permission($current_user)) {
    if (file_exists($file)) {
      $fp = fopen($file, "rb");
      $mimetype = get_mimetype($file);

      // Forge html for the whatsnew files
      if (basename(dirname(dirname($file))) == "whatsnew") {
        $forged_suffix = ".html";
        $mimetype="text/html";
      }

      header('Content-Type: '.$mimetype);
      header("Content-Length: ".filesize($file));
      header('Content-Disposition: inline; filename="'.basename($file).$forged_suffix.'"');
      fpassthru($fp);
      exit;
    } else {
      echo "File not found.";
      exit;
    }
  } else {
    echo "Permission denied.";
    exit;
  }
}



?>
