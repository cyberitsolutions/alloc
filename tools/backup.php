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

$folders = array("client", "invoice", "project", "task"); # This list may need to be fixed as new modules are added

function add_to_archive($archive, $dir, $intpath) {
  
  if (is_dir($dir)) { 
    $handle = opendir($dir);

    while (false !== ($file = readdir($handle))) {
  
      if ($file != "." && $file != "..") {
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
          add_to_archive($archive, $path, $intpath . DIRECTORY_SEPARATOR . $file);
        } else {
          $archive->addFile($path, $intpath . DIRECTORY_SEPARATOR . $file);
        }
      }

    }
    closedir($handle);
  }
}

function empty_dir($dir) {
  if (is_dir($dir)) {
    $handle = opendir($dir);

    while (false !== ($file = readdir($handle))) {
      if ($file != "." && $file != "..") {
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
          empty_dir($path);
          rmdir($path);
        } else {
          unlink($path);
        }
      }
    }
  }
}
 
function backup($archivename) {
  global $db, $TPL, $folders; 

  $archive = new ZipArchive();
  $archive->open($TPL["url_alloc_attachments_dir"] . "backups" . DIRECTORY_SEPARATOR . "0" . DIRECTORY_SEPARATOR . $archivename,
      ZIPARCHIVE::CREATE); # This attribute may be wrong. 

  $dumpfile = $TPL["url_alloc_attachments_dir"] . "backups" . DIRECTORY_SEPARATOR . "database.sql";

  $db->dump_db($dumpfile);

  $archive->addFile($dumpfile, "database.sql");

  
  foreach ($folders as $folder) {
    add_to_archive($archive, $TPL["url_alloc_attachments_dir"] . $folder, $folder);
  }

  $archive->close();
}

function restore($archivename) {
  global $db, $TPL, $folders;
  $archive = new ZipArchive();
  if (!$archive->open($TPL["url_alloc_attachments_dir"] . "backups" . DIRECTORY_SEPARATOR . "0" . DIRECTORY_SEPARATOR. $archivename)) {
    return false;
  }

  if (FALSE === $archive->statName("database.sql")) {
    return false;
  }

  # Clear out the folder list
  foreach($folders as $folder) {
    empty_dir($TPL["url_alloc_attachments_dir"] . $folder);
  }

  # Extract attachments
  if (!$archive->extractTo($TPL["url_alloc_attachments_dir"])) {
    return false;
  }

  list($sql, $commends) = parse_sql_file($TPL["url_alloc_attachments_dir"] . "database.sql");

  foreach($sql as $q) {
    $db->qr($q);
  }
  unlink($TPL["url_alloc_attachments_dir"] . "database.sql");
  return true;
}

function show_backups() {
  global $TPL;
  $dir = $TPL["url_alloc_attachments_dir"] . "backups" . DIRECTORY_SEPARATOR . "0" . DIRECTORY_SEPARATOR;
  $handle = opendir($dir);

  while (false !== ($file = readdir($handle))) {
    if ($file != "." && $file != ".." && !is_dir($dir . $file)) {
      $path = $dir . $file;
      $TPL["filename"] = "<a target=\"_BLANK\" href=\"".$TPL["url_alloc_backup"]."&get_file=".urlencode($file)."\">".htmlentities($file)."</a>";
      $TPL["mtime"] = date("Y-m-d H:i:s",filemtime($path));

      $size = filesize($dir.DIRECTORY_SEPARATOR.$file);
      $size < 1024 and $TPL["size"] = sprintf("%db",$size);
      $size > 1023 and $TPL["size"] = sprintf("%dkb",$size/1024);
      $size > (1024 * 1024) and $TPL["size"] = sprintf("%dMb",$size/(1024*1024));

      $TPL["restore_name"] = $file;
      include_template("templates/backupFileM.tpl");
    }
  }
}

# End of functions

if (!person::is_god()) {
  die("Insufficient permissions. Backups may only be performed by super-users.");
}

if ($_POST["create_backup"]) {
  backup($_POST["backup_name"]);
}

if ($_POST["restore_backup"]) {
  if (!restore($_POST["file"])) {
    $TPL["message"][] = "Error restoring backup.";
  } else {
    $TPL["message_good"][] = "Backup restored successfully.";
  }
}

if ($_POST["delete_backup"]) {
  # Can't go through the normal del_attachments thing because this isn't a real entity

  $file = $_POST["file"];

  if (preg_match("/{\/}|{\\}/", $file)) {
    die("File delete error: Name contains slashes.");
  }
  $path = $TPL["url_alloc_attachments_dir"] . "backups" . DIRECTORY_SEPARATOR . "0" . DIRECTORY_SEPARATOR. $file;
  if (!is_file($path)) {
    die("File delete error: Not a file.");
  }
  unlink($path);
}

if ($_POST["save_attachment"]) {
  move_attachment("backups", 0);
}

if (($file = $_GET["get_file"])) { # = is intentional
  if (preg_match("/{\/}|{\\}/", $file)) {
    die("File retrieve error: Name contains slashes.");
  }
  $path = $TPL["url_alloc_attachments_dir"] . "backups" . DIRECTORY_SEPARATOR . "0" . DIRECTORY_SEPARATOR. $file;

  if (!is_file($path)) {
    die("Error: File does not exist.");
  }

  $fp = fopen($path, "rb");
  header('Content-Type: application/octet-stream');
  header("Content-Length: ".filesize($path));
  header('Content-Disposition: attachment; filename="'.$file.'"');
  fpassthru($fp);
  exit;
}


$TPL["default_filename"] = "backup-" . $TPL["today"];

include_template("templates/backupM.tpl");

?>
