<?php
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
