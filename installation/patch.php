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

define("NO_AUTH",1);
require_once("../alloc.php");

$db = new db_alloc;

// Get list of patch files in order
$files = get_patch_file_list();

// Get the most recently applied patch
$most_recent_patch = get_most_recent_patch();



if ($_POST["apply_patches"] && $most_recent_patch != end($files)) {


  foreach ($files as $file) {
    $f = ALLOC_MOD_DIR."/patches/".$file;

    if (!$most_recent_patch) {
      $go = true;
    }

    if ($go && is_readable($f) && substr($f,-3) == strtolower("sql")) {

      $msg[$f][] = "<b>Attempting:</b> ".$file."<br/>";
      unset($comments);
      unset($comments_html);
      $mqr = @get_magic_quotes_runtime();
      @set_magic_quotes_runtime(0);
      $query = fread(fopen($f, 'r'), filesize($f));
      @set_magic_quotes_runtime($mqr);
      preg_match_all("/[\n]?(--[^\n]*)\n/", $query, $m);
      if (is_array($m)) {
        $comments = implode(" ",$m[1]);
        $comments = str_replace("-- ","",$comments);
        $comments_html[$f] = implode("<br/>",$m[1]);
      }

      $query = ereg_replace("\n--[^\n]*\n", "\n", $query);
      $pieces = explode(";\n",$query.";\n");

      for ($i=0; $i<count($pieces); $i++) {
        $pieces[$i] = trim($pieces[$i]);
        if(!empty($pieces[$i]) && $pieces[$i] != "-" && $pieces[$i] != ";\n") {
          if (!$db->query($pieces[$i])) {
            $msg[$f][] = "<b style=\"color:red\">Error:</b> ".$f."<br/>".$db->get_error();
            $failed[$f] = true;
          } 
        }
      }
      if (!$failed[$f]) {
        $q = sprintf("INSERT INTO patchLog (patchName, patchDesc, patchDate) VALUES ('%s','%s','%s')",db_esc($file), db_esc($comments), date("Y-m-d H:i:s"));
        $db->query($q);
        $msg[$f][] = "<b style=\"color:green\">Success:</b> ".$f."<br/>".$comments_html[$f]."<br/>";
      } 

    }

    // If the last successfully applied patch is equal to the current file,
    // then we want to start applying patches from the next iteration.
    if ($most_recent_patch == $file) {
      $go = true;
    }
  }
}


if ($msg) {
  foreach ($files as $file) {
   $f = ALLOC_MOD_DIR."/patches/".$file;
   $msg[$f] and $TPL["msg"].= "\n\n<br/><br/>".implode("\n<br/>",$msg[$f]);
  }
} else if ($most_recent_patch == end($files)) {
  $TPL["msg"] = "All patches applied up to patch ".end($files);
} else {
  $TPL["msg"] = "<input type='submit' name='apply_patches' value='Apply Patches'>";
}


include_template("templates/patch.tpl");

?>
