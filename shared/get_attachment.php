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

// For use like get_attachment.php?entity=project&id=5&file=foo.bar

require_once("../alloc.php");

$file = urldecode($_GET["file"]);

if (isset($_GET["id"]) && $file && !bad_filename($file)) {

  $entity = new $_GET["entity"];
  $entity->set_id(sprintf("%d",$_GET["id"]));
  $entity->select();

  $file = $TPL["url_alloc_attachments_dir"].$_GET["entity"]."/".$_GET["id"]."/".$file;

  if ($entity->has_attachment_permission($current_user) && file_exists($file)) {
    $fp = fopen($file, "rb");
    $mimetype="application/octet-stream";
    if (function_exists("mime_content_type")) {
      $mimetype = mime_content_type($file);
    }
    elseif ($size = getimagesize($file)) {
      $mimetype = $size['mime'];
    }
    header('Content-Type: '.$mimetype);
    header("Content-Length: ".filesize($file));
    header('Content-Disposition: attachment; filename="'.basename($file).'"');
    fpassthru($fp);
    exit;
  }
}



?>
