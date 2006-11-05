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

function get_help_button($id,$str) {
  global $TPL;
  $str = htmlentities(addslashes($str));
  $str = str_replace("\n"," ",$str);
  $img = "<img id=\"".$id."\" onmouseover=\"help_text_on(this,'".$str."');\" onmouseout=\"help_text_off();\" src=\"";
  $img.= $TPL["url_alloc_images"]."help.gif\" style=\"position:relative; top:4px\">";
  return $img;
}

function help_button($topic = "") {
  global $TPL;
  $file = $TPL["url_alloc_help"].$topic.".html";
  if (file_exists($file)) {
    $str = file_get_contents($file);
    echo get_help_button($topic, $str);
  }
}

function get_help_link() {
  global $TPL;
  $url = "../help/help.html#".$TPL["alloc_help_link_name"];
  echo "<a href=\"".$url."\">Help</a>";
}




?>
