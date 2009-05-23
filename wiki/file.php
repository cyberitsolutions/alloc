<?php

/*
 * Copyright (C) 2006, 2007, 2008 Alex Lance, Clancy Malcolm, Cybersource
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

$file = $_POST["file"] or $file = urldecode($_GET["file"]);
$TPL["file"] = $file;
$editName = $_POST["editName"];

// Decode the wiki document ..
$text = html_entity_decode($_POST["wikitext"]);
$text = str_replace("\r\n","\n",$text);

// Check if we're using a VCS
$vcs = vcs::get();

if ($_POST["save"]) {

  path_under_path(wiki_module::get_wiki_path().dirname($editName), wiki_module::get_wiki_path()) or $errors[] = "Bad filename.".$editName;
  is_writeable(wiki_module::get_wiki_path().dirname($editName)) or $errors[] = "Path is not writeable.";
  strlen($_POST["wikitext"]) or $errors[] = "File empty.";
  strlen($_POST["commit_msg"]) or $errors[] = "No description of changes entered.";
  if ($errors) {
    $TPL["errors"] = "<div class='message warn' style='margin-top:0px; margin-bottom:10px; padding:10px;'>";
    $TPL["errors"].= implode("<br>",$errors);
    $TPL["errors"].= "</div>";
    include_template("templates/editFileS.tpl");

  } else {

    // If we're using version control
    if (is_object($vcs)) {

      // Creating a new file
      if (!$file) {
        wiki_module::file_save(wiki_module::get_wiki_path().$editName, $text);
        $vcs->add(wiki_module::get_wiki_path().$editName);
        $vcs->commit(wiki_module::get_wiki_path().$editName, $_POST["commit_msg"]);
        wiki_module::get_file($editName);

      // Moving or renaming the file
      } else if ($file && $editName && $editName != $file) {
        wiki_module::file_save(wiki_module::get_wiki_path().$file, $text);
        $msg = $_POST["commit_msg"]." (".$file. " -> ".$editName.")";
        $err = $vcs->mv(wiki_module::get_wiki_path().$file, wiki_module::get_wiki_path().$editName, $msg);
        $TPL["message_good"][] = "File saved: ".$file;
        $TPL["file"] = $editName;
        wiki_module::get_file($editName);

      // Else just regular save
      } else {
        wiki_module::file_save(wiki_module::get_wiki_path().$file, $text);
        $vcs->commit(wiki_module::get_wiki_path().$file, $_POST["commit_msg"]);
        $TPL["message_good"][] = "File saved: ".$file;
        $TPL["file"] = $file;
        $TPL["str"] = $text;
        $TPL["commit_msg"] = $_POST["commit_msg"];

        wiki_module::get_file($file);
      }

    // Else non-vcs save
    } else {
      wiki_module::file_save(wiki_module::get_wiki_path().$editName, $text);
      $TPL["message_good"][] = "File saved: ".$editName;
      //alloc_redirect($TPL["url_alloc_wiki"]."target=".urlencode($editName));
      wiki_module::get_file($editName);
    }
  }


} else if ($_REQUEST["newFile"]) {
  include_template("templates/newFileM.tpl");

} else if ($_REQUEST["newDirectory"]) {
  include_template("templates/newDirectoryM.tpl");

} else if (is_file(wiki_module::get_wiki_path().$file) && is_readable(wiki_module::get_wiki_path().$file)) {
  wiki_module::get_file($file, $_GET["rev"]);
}




$TPL["file"] = $file;




?>
