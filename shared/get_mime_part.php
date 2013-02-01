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

if (isset($_GET["id"]) && $_GET["part"]) {
  $comment = new comment();
  $comment->set_id($_GET["id"]);
  $comment->select() or die("Bad _GET[id]");
  list($mail,$text,$mimebits) = $comment->find_email(false,true);
  if ($comment->has_attachment_permission($current_user)) {
    foreach((array)$mimebits as $bit) {
      if ($bit["part"] == $_GET["part"]) {
        $thing = $bit["blob"];
        $filename = $bit["name"];
        break;
      }   
    }
    header('Content-Type: '.$mimetype);
    header("Content-Length: ".strlen($thing));
    header('Content-Disposition: inline; filename="'.basename($filename).'"');
    echo $thing;
    exit;
  } else {
    echo "Permission denied.";
    exit;
  }
}



?>
