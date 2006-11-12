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

class installation_module extends module {
}

function get_patch_file_list() {
  $dir = ALLOC_MOD_DIR."/patches/";
  $files = array();
  if (is_dir($dir)) {
    $dh = opendir($dir);
    if ($dh) {
      while (($file = readdir($dh)) !== false) {
        if (filetype($dir.$file) == "file") {
          $files[] = $file;
        }
      }
      closedir($dh);
    }

    // Sort files in natural counting order file8 fil9 fil10
    natsort($files);
    // Order the indexes too
    $files = array_values($files);
  }
  return $files;
}


function get_most_recent_patch() {
  $db = new db_alloc;
  $db->query("SELECT patchName FROM patchLog ORDER BY patchDate DESC,patchName DESC LIMIT 1;");
  $row = $db->row();
  return $row["patchName"];
}

?>
