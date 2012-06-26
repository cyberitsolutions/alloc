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

class wiki {

  function get_list($_FORM) {
    global $TPL;
    $current_user = &singleton("current_user");

    $wiki_path = wiki_module::get_wiki_path();
    $files = search::get_recursive_dir_list($wiki_path);

    foreach ($files as $row) {
      $file = str_replace($wiki_path,"",$row);
      if ($_FORM["starred"] && $current_user->prefs["stars"]["wiki"][base64_encode($file)]) {
        $rows[] = array("filename"=>$file);
      }
    }
    return (array)$rows;
  }

  function get_list_html($rows=array(),$ops=array()) {
    global $TPL;
    $TPL["wikiListRows"] = $rows;
    $TPL["_FORM"] = $ops;
    include_template(dirname(__FILE__)."/../templates/wikiListS.tpl");
  }
}
?>
