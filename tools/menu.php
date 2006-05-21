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

require_once("alloc.inc");

$options = array(
  array("url"=>"announcementList"        ,"text"=>"Announcements"         ,"entity"=>"announcement"       ,"action"=>PERM_READ_WRITE),
  array("url"=>"permissionList"          ,"text"=>"allocPSA Security"     ,"entity"=>"permission"         ,"action"=>PERM_READ_WRITE),
  array("url"=>"stats"                   ,"text"=>"allocPSA Statistics"   ,"entity"=>"config"             ,"action"=>PERM_UPDATE),
  array("url"=>"costtime"                ,"text"=>"Cost & Time Estimater" ,"entity"=>"project"            ,"action"=>true),
  array("url"=>"personSkillMatrix"       ,"text"=>"Company Skill Matrix"  ,"entity"=>"person"             ,"action"=>true), 
  array("url"=>"config"                  ,"text"=>"allocPSA Configuration","entity"=>"config"             ,"action"=>PERM_UPDATE),
  array("url"=>"taskCommentTemplateList" ,"text"=>"Task Comment Templates","entity"=>"taskCommentTemplate","action"=>PERM_READ_WRITE)
);





function show_options($template) {
  global $options, $TPL;

  reset($options);
  while (list(, $option) = each($options)) {
    if ($option["entity"] != "") {
      if (have_entity_perm($option["entity"], $option["action"], $current_user, true)) {
        $TPL["url"] = $TPL["url_alloc_".$option["url"]];
        $TPL["params"] = $option["params"];
        $TPL["text"] = $option["text"];
        include_template($template);
      }
    } else {
      $TPL["url"] = $TPL["url_alloc_".$option["url"]];
      $TPL["params"] = $option["params"];
      $TPL["text"] = $option["text"];
      include_template($template);
    }
  }


}

include_template("templates/menuM.tpl");



?>
