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

define("NO_AUTH",1);
define("IS_GOD",true);
require_once("../alloc.php");

function apply_patch($f) {
  global $TPL;
  static $files;
  // Should never attempt to apply the same patch twice.. in case 
  // there are function declarations in the .php patches.
  if ($files[$f]) {
    return;
  }
  $files[$f] = true;
  $db = new db_alloc();
  $file = basename($f);
  $failed = false;
  $comments = array();


  // This is an important patch that converts money from 120.34 to 12034.
  // We MUST ensure that the user has a currency set before applying this patch.
  if ($file == "patch-00188-alla.sql") {
    if (!config::get_config_item('currency')) {
      alloc_error("No default currency is set! Login to alloc (ignore any errors, you may need to manually change the url to config/config.php after logging in) go to Setup -> Finance and select a Main Currency. And then click the 'Update Transactions That Have No Currency' button. Then return here and apply this patch (patch-188). IT IS REALLY IMPORTANT THAT YOU FOLLOW THESE INSTRUCTIONS as the storage format for monetary amounts has changed.",true);
    }
  }


  // Try for sql file
  if (strtolower(substr($file,-4)) == ".sql") {

    list($sql,$comments) = parse_sql_file($f);
    foreach ($sql as $query) {
      if (!$db->query($query)) {
        #$TPL["message"][] = "<b style=\"color:red\">Error:</b> ".$f."<br>".$db->get_error();
        $failed = true;
        alloc_error("<b style=\"color:red\">Error:</b> ".$f."<br>".$db->get_error());
      }
    }
    if (!$failed) {
      $TPL["message_good"][] = "Successfully Applied: ".$f;
    }

  // Try for php file
  } else if (strtolower(substr($file,-4)) == ".php") {
    $str = execute_php_file("../patches/".$file);
    if ($str && !defined("FORCE_PATCH_SUCCEED_".$file)) {
      #$TPL["message"][] = "<b style=\"color:red\">Error:</b> ".$f."<br>".$str;
      $failed = true;
      ob_end_clean();
      alloc_error("<b style=\"color:red\">Error:</b> ".$f."<br>".$str);
    } else {
      $TPL["message_good"][] = "Successfully Applied: ".$f;
    }
  }
  if (!$failed) {
    $q = prepare("INSERT INTO patchLog (patchName, patchDesc, patchDate) 
                  VALUES ('%s','%s','%s')",$file, implode(" ",$comments), date("Y-m-d H:i:s"));
    $db->query($q);
  }
}

// Get list of patch files in order
$abc123_files = get_patch_file_list();

// Get the most recently applied patch
$abc123_applied_patches = get_applied_patches();


// Hack to update everyones patch tree
if (!in_array("patch-00053-alla.php",$abc123_applied_patches)) {
  apply_patch(ALLOC_MOD_DIR."patches/patch-00053-alla.php");
}


// Apply all patches
if ($_REQUEST["apply_patches"]) {
  foreach ($abc123_files as $abc123_file) {
    $abc123_f = ALLOC_MOD_DIR."patches/".$abc123_file;
    if (!in_array($abc123_file,$abc123_applied_patches)) {
      apply_patch($abc123_f);
    }
  }

// Apply a single patch
} else if ($_REQUEST["apply_patch"] && $_REQUEST["patch_file"]) {
  $abc123_f = ALLOC_MOD_DIR."patches/".$_REQUEST["patch_file"];
  if (!in_array($abc123_file,$abc123_applied_patches)) {
    apply_patch($abc123_f);
  }
} else if ($_REQUEST["remove_patch"] && $_REQUEST["patch_file"]) {
  $abc123_f = ALLOC_MOD_DIR."patches/".$_REQUEST["patch_file"];
  $q = prepare("INSERT INTO patchLog (patchName, patchDesc, patchDate) 
                VALUES ('%s','%s','%s')",$_REQUEST["patch_file"], "Patch not applied.", date("Y-m-d H:i:s"));
  $db = new db_alloc();
  $db->query($q);
}



$abc123_applied_patches = get_applied_patches();
foreach ($abc123_files as $abc123_file) {
  if (!in_array($abc123_file,$abc123_applied_patches)) {
    $abc123_incomplete = true;
  }
}


if (!$abc123_incomplete) {
  alloc_redirect($TPL["url_alloc_login"]);
} 


include_template("templates/patch.tpl");

?>
