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


$dirName = str_replace("..","",$_POST["dirName"]);
#$dirName = preg_replace("/^[\/\\\]*/","",$dirName);

// Check if we're using a VCS
$vcs = vcs::get();

if ($_POST["save"]) {
  // path_under_path(wiki_module::get_wiki_path().$dirName, wiki_module::get_wiki_path(),$dont_check_filesystem=false) or $errors[] = "Bad directory name: ";
  //is_writeable(wiki_module::get_wiki_path().dirname($editName)) or $errors[] = "Path is not writeable.";
  strlen($dirName) or $errors[] = "Directory name empty.";
  $dirName and is_dir(wiki_module::get_wiki_path().$dirName) and $errors[] = "Directory already exists.";

  if ($errors) {
    $error = "<div class='message warn noprint' style='margin-top:0px; margin-bottom:10px; padding:10px;'>";
    $error.= implode("<br>",$errors);
    $error.= "</div>";
   
    $TPL["loadErrorPageDir"] = 1;
    $TPL["dirName"] = urlencode($dirName);
    $TPL["msg"] = urlencode($error);
    include_template("templates/wikiM.tpl");

  } else {

    // If we're using version control
    if (is_object($vcs)) {

      // Creating a new directory or directories
      if (!is_dir(wiki_module::get_wiki_path().$dirName)) {
        $bits = explode("/",$dirName);
        $str = wiki_module::get_wiki_path();
        foreach ((array)$bits as $bit) {
          $str.= $slash.$bit;
          mkdir($str);
          $vcs->add($str);
          $vcs->commit($str, "Added directory.");
          $slash = "/";
        }
        alloc_redirect($TPL["url_alloc_wiki"]."target=".urlencode($dirName));
      }

    // Else non-vcs save
    } else {
      // Creating a new directory or directories
      if (!is_dir(wiki_module::get_wiki_path().$dirName)) {
        $bits = explode("/",$dirName);
        $str = wiki_module::get_wiki_path();
        foreach ((array)$bits as $bit) {
          $str.= $slash.$bit;
          mkdir($str);
          $slash = "/";
        }
        $TPL["message_good"][] = "Directory created: ".$dirName;
        alloc_redirect($TPL["url_alloc_wiki"]."target=".urlencode($dirName));
      }
    }
  }


} else if ($_REQUEST["newDirectory"]) {

  if ($_REQUEST["p"]) {
    if (is_file(wiki_module::get_wiki_path().$_REQUEST["p"])) {
      $_REQUEST["p"] = dirname($_REQUEST["p"]);
      $_REQUEST["p"] && substr($_REQUEST["p"],-1,1) != DIRECTORY_SEPARATOR and $_REQUEST["p"].="/";
      $_REQUEST["p"] == ".".DIRECTORY_SEPARATOR and $_REQUEST["p"] = "";
    }
    $TPL["dirName"] = $_REQUEST["p"];
  }
  include_template("templates/newDirectoryM.tpl");

} else if ($_REQUEST["loadErrorPageDir"]) {
  $TPL["loadErrorPageDir"] = $_REQUEST["loadErrorPageDir"];
  $TPL["dirName"] = $_REQUEST["dirName"];
  $TPL["msg"] = $_REQUEST["msg"];
  include_template("templates/newDirectoryM.tpl");
}


$TPL["dirName"] = $dirName;




?>
