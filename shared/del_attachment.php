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

$id = $_GET["id"] or $id = $_POST["id"];
$file = $_GET["file"] or $file = $_POST["file"];
$entity = $_GET["entity"] or $entity = $_POST["entity"];

$id = sprintf("%d",$id);



if ($id && $file 
&& !preg_match("/\.\./",$file) && !preg_match("/\//",$file)
&& !preg_match("/\.\./",$entity) && !preg_match("/\//",$entity)) {

  $e = new $entity;
  $e->set_id($id);
  $e->select();

  $dir = ATTACHMENTS_DIR.$entity.DIRECTORY_SEPARATOR.$id.DIRECTORY_SEPARATOR;
  $file = $dir.$file;

  if ($e->has_attachment_permission_delete($current_user) && file_exists($file)) {
    if (dirname($file) == dirname($dir.".")) { // last check
      unlink($file);
      alloc_redirect($TPL["url_alloc_".$entity].$entity."ID=".$id."&sbs_link=attachments");
      exit();
    }
  }
}

// return by default
alloc_redirect($TPL["url_alloc_".$entity].$entity."ID=".$id."&sbs_link=attachments");

?>
