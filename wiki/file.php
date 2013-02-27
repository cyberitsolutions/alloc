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

$file = $_POST["file"] or $file = $_GET["file"];
$TPL["file"] = $file;
$editName = $_POST["editName"];
$TPL["editName"] = $_POST["editName"];

// Decode the wiki document ..
$text = html_entity_decode($_POST["wikitext"]);
$text = str_replace("\r\n","\n",$text);

// Check if we're using a VCS
$vcs = vcs::get();

if ($_POST["save"]) {

  path_under_path(wiki_module::get_wiki_path().dirname($editName), wiki_module::get_wiki_path()) or $errors[] = "Bad filename: ".$editName;
  is_writeable(wiki_module::get_wiki_path().dirname($editName)) or $errors[] = "Path is not writeable.";
  strlen($_POST["wikitext"]) or $errors[] = "File contents empty.";
  strlen($editName) or $errors[] = "Filename empty.";
  strlen($_POST["commit_msg"]) or $errors[] = "No description of changes entered.";

  if ($errors) {
    $error = "<div class='message warn noprint' style='margin-top:0px; margin-bottom:10px; padding:10px;'>";
    $error.= implode("<br>",$errors);
    $error.= "</div>";
   
    $TPL["loadErrorPage"] = 1;
    $TPL["str"] = urlencode($_POST["wikitext"]);
    $TPL["commit_msg"] = urlencode($_POST["commit_msg"]);
    $TPL["file"] = urlencode($editName);
    $TPL["msg"] = $error;
    include_template("templates/wikiM.tpl");

  } else {

    // If we're using version control
    if (is_object($vcs)) {

      // Creating a new file
      if (!$file) {
        wiki_module::file_save(wiki_module::get_wiki_path().$editName, $text);
        $vcs->add(wiki_module::get_wiki_path().$editName);
        $vcs->commit(wiki_module::get_wiki_path().$editName, $_POST["commit_msg"]);
        alloc_redirect($TPL["url_alloc_wiki"]."target=".urlencode($editName));

      // Moving or renaming the file
      } else if ($file && $editName && $editName != $file) {
        wiki_module::file_save(wiki_module::get_wiki_path().$file, $text);
        $msg = $_POST["commit_msg"]." (".$file. " -> ".$editName.")";
        $err = $vcs->mv(wiki_module::get_wiki_path().$file, wiki_module::get_wiki_path().$editName, $msg);
        $TPL["message_good"][] = "File saved: ".$file;
        $TPL["file"] = $editName;
        alloc_redirect($TPL["url_alloc_wiki"]."target=".urlencode($editName));

      // Else just regular save
      } else if ($editName == $file) {
        wiki_module::file_save(wiki_module::get_wiki_path().$file, $text);
        $vcs->commit(wiki_module::get_wiki_path().$file, $_POST["commit_msg"]);
        $TPL["message_good"][] = "File saved: ".$file;
        $TPL["file"] = $file;
        $TPL["str"] = $text;
        $TPL["commit_msg"] = $_POST["commit_msg"];
        alloc_redirect($TPL["url_alloc_wiki"]."target=".urlencode($file));
      }

    // Else non-vcs save
    } else {
      wiki_module::file_save(wiki_module::get_wiki_path().$editName, $text);
      $TPL["message_good"][] = "File saved: ".$editName;
      alloc_redirect($TPL["url_alloc_wiki"]."target=".urlencode($editName));
    }
  }

} else if ($_REQUEST["delete"]) {

  path_under_path(wiki_module::get_wiki_path().dirname($editName), wiki_module::get_wiki_path()) or $errors[] = "Bad filename: ".$editName;
  is_writeable(wiki_module::get_wiki_path().dirname($file)) or $errors[] = "Path is not writeable.";
  strlen($file) or $errors[] = "Filename empty.";
  $_POST["commit_msg"] and $_POST["commit_msg"].= " ";
  $_POST["commit_msg"].= "File deleted: ".$file;

  if (!$errors && !is_dir(wiki_module::get_wiki_path().$file)) {
    // If we're using version control
    if (is_object($vcs)) {
      wiki_module::file_delete(wiki_module::get_wiki_path().$file);
      $vcs->rm(wiki_module::get_wiki_path().$file, $_POST["commit_msg"]);
      $TPL["message_good"][] = "File deleted: ".$file;
      $TPL["file"] = $file;
      $TPL["str"] = $text;
      $TPL["commit_msg"] = $_POST["commit_msg"];
      alloc_redirect($TPL["url_alloc_wiki"]."target=".urlencode(dirname($file)));

    // Else non-vcs save
    } else {
      wiki_module::file_delete(wiki_module::get_wiki_path().$file);
      $TPL["message_good"][] = "File deleted: ".$file;
      alloc_redirect($TPL["url_alloc_wiki"]."target=".urlencode(dirname($file)));
    }
  }

} else if ($_REQUEST["newFile"]) {

  if ($_REQUEST["p"]) {
    if (is_file(wiki_module::get_wiki_path().$_REQUEST["p"])) {
      $_REQUEST["p"] = dirname($_REQUEST["p"]);
      $_REQUEST["p"] && substr($_REQUEST["p"],-1,1) != DIRECTORY_SEPARATOR and $_REQUEST["p"].="/";
      $_REQUEST["p"] == ".".DIRECTORY_SEPARATOR and $_REQUEST["p"] = "";
    }
    $TPL["editName"] = $_REQUEST["p"];
  }
  if ($_REQUEST["file"]) {
    $TPL["editName"] = $_REQUEST["file"];
  }
  include_template("templates/fileM.tpl");

} else if ($file && is_file(wiki_module::get_wiki_path().$file) && is_readable(wiki_module::get_wiki_path().$file)) {

  $TPL['current_path'] = dirname($file);
  //dirname may return '.' if there's no dirname, need to get rid of it
  if ($TPL['current_path'] == '.') {
    $TPL['current_path'] = '';
  } else {
    $TPL['current_path'] .= DIRECTORY_SEPARATOR;
  }
  $TPL["editName"] = $file;
  wiki_module::get_file($file, $_GET["rev"]);

} else if ($_REQUEST["loadErrorPage"]) {
  $TPL["loadErrorPage"] = $_REQUEST["loadErrorPage"];
  $TPL["str"] = $_REQUEST["str"];
  $TPL["commit_msg"] = $_REQUEST["commit_msg"];
  $TPL["file"] = $_REQUEST["file"];
  $TPL["editName"] = $_REQUEST["file"];
  $TPL["msg"] = $_REQUEST["msg"];
  include_template("templates/fileGetM.tpl");
}


$TPL["file"] = $file;


?>
