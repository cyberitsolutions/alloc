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


class backups {

  var $folders = array();

  function __construct() {
    global $external_storage_directories;

    ini_set('max_execution_time',900); // max time 15 minutes
    ini_set('memory_limit',"256M"); // max memory_limit

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

    if (!is_dir(ATTACHMENTS_DIR . "backups" . DIRECTORY_SEPARATOR . "0")) {
      mkdir(ATTACHMENTS_DIR . "backups" . DIRECTORY_SEPARATOR . "0", 0777);
    }

    $archivename = "backup_" . date("Ymd_His") . ".zip";
    $zipfile = ATTACHMENTS_DIR . "backups" . DIRECTORY_SEPARATOR . "0" . DIRECTORY_SEPARATOR . $archivename;
    $dumpfile = ATTACHMENTS_DIR . "database.sql";
    is_file($dumpfile) && unlink($dumpfile);

    $archive = new PclZip($zipfile);

    $db = new db_alloc();
    $db->dump_db($dumpfile);

    if (!file_exists($dumpfile)) { 
      alloc_error("Couldn't backup database to ".$dumpfile);
    } else {

      // database dump
      $files[] = $dumpfile;
    
      // load up all the attachment dirs
      foreach ($this->folders as $folder) {
        $files[] = ATTACHMENTS_DIR.$folder;
      }

      // add everything to the archive
      $archive->add($files,
                    PCLZIP_OPT_REMOVE_PATH, ATTACHMENTS_DIR); // again, nuke leading path

      is_file($dumpfile) && unlink($dumpfile);
      $TPL["message_good"][] = "Backup created: " . $archivename;
    }
  }

  function restore($archivename) {
    global $TPL;

    $file = ATTACHMENTS_DIR . "backups" . DIRECTORY_SEPARATOR . "0" . DIRECTORY_SEPARATOR. $archivename;

    $archive = new PclZip($file);

    # Clear out the folder list
    foreach($this->folders as $folder) {
      $this->empty_dir(ATTACHMENTS_DIR . $folder);
    }

    $archive->extract(ATTACHMENTS_DIR);

    list($sql, $commends) = parse_sql_file(ATTACHMENTS_DIR . "database.sql");

    $db = new db_alloc();
    foreach($sql as $q) {
      if (!$db->query($q)) {
        $errors[] = "Error! (".mysql_error().").";
      }
    }

    is_array($errors) and alloc_error(implode("<br>",$errors));
    unlink(ATTACHMENTS_DIR . "database.sql");
    if (!count($errors)) {
      return true;
    }
  }
}


?>
