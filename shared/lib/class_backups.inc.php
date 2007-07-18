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


class backups {

  var $folders = array();

  function backups() {
    global $external_storage_directories;

    // externally_stored_directories is set in alloc.php
    foreach ($external_storage_directories as $folder) {
      $folder != "backups" and $folders[] = $folder;
    }
    $this->folders = $folders;
  }

  function set_id() { // dummy so can re-use the get_attachment.php script
    return true;
  }

  function select() { // dummy so can re-use the get_attachment.php script
    return true;
  }

  function has_attachment_permission($person) {
    return $person->have_role("god");
  }

  function add_to_archive($archive, $dir, $intpath) {
    
    if (is_dir($dir)) { 
      $handle = opendir($dir);

      while (false !== ($file = readdir($handle))) {
    
        if ($file != "." && $file != "..") {
          $path = $dir . DIRECTORY_SEPARATOR . $file;
          if (is_dir($path)) {
            $this->add_to_archive($archive, $path, $intpath . DIRECTORY_SEPARATOR . $file);
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
            $this->empty_dir($path);
            rmdir($path);
          } else {
            unlink($path);
          }
        }
      }
    }
  }

  function backup() {
    global $TPL; 

    $archivename = "backup_" . date("Ymd_His") . ".zip";

    $archive = new ZipArchive();
    $archive->open($TPL["url_alloc_attachments_dir"] . "backups" . DIRECTORY_SEPARATOR . "0" . DIRECTORY_SEPARATOR . $archivename,
        ZIPARCHIVE::CREATE); # This attribute may be wrong. 

    $dumpfile = $TPL["url_alloc_attachments_dir"] . "backups" . DIRECTORY_SEPARATOR . "database.sql";

    $db = new db_alloc();
    $db->dump_db($dumpfile);

    $archive->addFile($dumpfile, "database.sql");

    
    foreach ($this->folders as $folder) {
      $this->add_to_archive($archive, $TPL["url_alloc_attachments_dir"] . $folder, $folder);
    }

    $archive->close();
    $TPL["message_good"][] = "Backup created: " . $archivename;
  }

  function restore($archivename) {
    global $TPL;
    $archive = new ZipArchive();
    if (!$archive->open($TPL["url_alloc_attachments_dir"] . "backups" . DIRECTORY_SEPARATOR . "0" . DIRECTORY_SEPARATOR. $archivename)) {
      return false;
    }

    if (FALSE === $archive->statName("database.sql")) {
      return false;
    }

    # Clear out the folder list
    foreach($this->folders as $folder) {
      $this->empty_dir($TPL["url_alloc_attachments_dir"] . $folder);
    }

    # Extract attachments
    if (!$archive->extractTo($TPL["url_alloc_attachments_dir"])) {
      return false;
    }

    list($sql, $commends) = parse_sql_file($TPL["url_alloc_attachments_dir"] . "database.sql");

    $db = new db_alloc();
    foreach($sql as $q) {
      if (!$db->query($q)) {
        $errors[] = "Error! (".mysql_error().").";
      }
    }

    is_array($errors) and $TPL["message"][] = implode("<br>",$errors);
    unlink($TPL["url_alloc_attachments_dir"] . "database.sql");
    if (!count($errors)) {
      return true;
    }
  }
}


?>
