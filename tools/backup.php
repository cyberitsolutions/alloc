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

$db = new db_alloc();

# End of functions

if (!$current_user->have_role("god")) {
  alloc_error("Insufficient permissions. Backups may only be performed by super-users.",true);
}

$backup = new backups();



if ($_POST["create_backup"]) {
  $backup->backup();
}

if ($_POST["restore_backup"]) {
  $backup->backup();
  if ($backup->restore($_POST["file"])) {
    $TPL["message_good"][] = "Backup restored successfully: " . $_POST["file"];
    $TPL["message_good"][] = "You will now need to manually import the installation/db_triggers.sql file into your database. THIS IS VERY IMPORTANT.";
  } else {
    alloc_error("Error restoring backup: " . $_POST["file"]);
  }
}

if ($_POST["delete_backup"]) {
  # Can't go through the normal del_attachments thing because this isn't a real entity

  $file = $_POST["file"];

  if (bad_filename($file)) {
    alloc_error("File delete error: Name contains slashes.");
  }
  $path = ATTACHMENTS_DIR . "backups" . DIRECTORY_SEPARATOR . "0" . DIRECTORY_SEPARATOR. $file;
  if (!is_file($path)) {
    alloc_error("File delete error: Not a file.");
  }
  if (dirname(ATTACHMENTS_DIR . "backups" . DIRECTORY_SEPARATOR . "0" . DIRECTORY_SEPARATOR.".") != dirname($path)) {
    alloc_error("File delete error: Bad path.");
  }

  unlink($path);
}

if ($_POST["save_attachment"]) {
  move_attachment("backups", 0);
}


$TPL["main_alloc_title"] = "Database Backups - ".APPLICATION_NAME;
include_template("templates/backupM.tpl");

?>
