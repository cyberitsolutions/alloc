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

require_once("../alloc.php");

$db = new db_alloc;

# End of functions

if (!$current_user->have_role("god")) {
  die("Insufficient permissions. Backups may only be performed by super-users.");
}

$backup = new backups();



if ($_POST["create_backup"]) {
  $backup->backup();
}

if ($_POST["restore_backup"]) {
  $backup->backup();
  if ($backup->restore($_POST["file"])) {
    $TPL["message_good"][] = "Backup restored successfully: " . $_POST["file"];
  } else {
    $TPL["message"][] = "Error restoring backup: " . $_POST["file"];
  }
}

if ($_POST["delete_backup"]) {
  # Can't go through the normal del_attachments thing because this isn't a real entity

  $file = $_POST["file"];

  if (bad_filename($file)) {
    die("File delete error: Name contains slashes.");
  }
  $path = $TPL["url_alloc_attachments_dir"] . "backups" . DIRECTORY_SEPARATOR . "0" . DIRECTORY_SEPARATOR. $file;
  if (!is_file($path)) {
    die("File delete error: Not a file.");
  }
  if (dirname($TPL["url_alloc_attachments_dir"] . "backups" . DIRECTORY_SEPARATOR . "0" . DIRECTORY_SEPARATOR.".") != dirname($path)) {
    die("File delete error: Bad path.");
  }

  unlink($path);
}

if ($_POST["save_attachment"]) {
  move_attachment("backups", 0);
}


$TPL["main_alloc_title"] = "Database Backups - ".APPLICATION_NAME;
include_template("templates/backupM.tpl");

?>
