<?php

/*
 *
 * Copyright 2006, Alex Lance, Clancy Malcolm, Cybersource Pty. Ltd.
 * 
 * This file is part of AllocPSA <info@cyber.com.au>.
 * 
 * AllocPSA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * AllocPSA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * AllocPSA; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */

require_once("alloc.inc");

$options = array(array("url"=>"announcementList",
                       "params"=>"",
                       "text"=>"Announcements",
                       "entity"=>"announcement",
                       "action"=>PERM_READ_WRITE),
                 array("url"=>"permissionList",
                       "params"=>"",
                       "text"=>"Security",
                       "entity"=>"permission",
                       "action"=>PERM_READ_WRITE),
                 array("url"=>"stats",
                       "params"=>"&web=true",
                       "text"=>"Alloc Statistics",
                       "entity"=>"project",
                       "action"=>true),
                 array("url"=>"costtime",
                       "params"=>"",
                       "text"=>"Simple Cost & Time Estimater",
                       "entity"=>"project",
                       "action"=>true),
                 array("url"=>"search",
                       "params"=>"",
                       "text"=>"Alloc Search",
                       "entity"=>"",
                       "action"=>true), 
                 array("url"=>"personSkillMatrix", 
                       "params"=>"", 
                       "text"=>"Company Wide Skill Matrix", 
                       "entity"=>"person", 
                       "action"=>true), 
                 array("url"=>"config", 
                       "params"=>"", 
                       "text"=>"Alloc Configuration", 
                       "entity"=>"config", 
                       "action"=>PERM_UPDATE),
                 array("url"=>"taskCommentTemplateList",
                       "params"=>"",
                       "text"=>"Task Comment Templates",
                       "entity"=>"taskCommentTemplate",
                       "action"=>PERM_READ_WRITE)


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
