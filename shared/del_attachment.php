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

$id = $_GET["id"] or $id = $_POST["id"];
$file = $_GET["file"] or $file = $_POST["file"];
$entity = $_GET["entity"] or $entity = $_POST["entity"];

$id = sprintf("%d",$id);



if ($id && $file 
&& !preg_match("/\.\./",$file) && !preg_match("/\//",$file)
&& !preg_match("/\.\./",$entity) && !preg_match("/\//",$entity)
&& strlen($file) <= 40) {

  $e = new $entity;
  $e->set_id($id);
  $e->select();

  $dir = $TPL["url_alloc_attachments_dir"].$entity."/".$id."/";
  $file = $dir.$file;

  if ($e->has_attachment_permission_delete($current_user) && file_exists($file)) {
    if (dirname($file) == dirname($dir.".")) { // last check
      unlink($file);
      header("Location: ".$TPL["url_alloc_".$entity].$entity."ID=".$id);
      exit();
    }
  }
}

// return by default
header("Location: ".$TPL["url_alloc_".$entity].$entity."ID=".$id);

?>
