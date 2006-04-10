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

class customize_alloc_home_item extends home_item {
  function customize_alloc_home_item() {
    global $TPL, $current_user, $font, $theme, $customize_save, $customizedFont, $customizedTheme;
    home_item::home_item("", "Preferences", "home", "customizeH.tpl", "narrow");

    if (!is_object($current_user)) {
      return false;
    }

    if (isset($font)) {
      $customizedFont = $font;
    }
    if (isset($theme)) {
      $customizedTheme = $theme;
    }

    if ($customize_save) {
      $current_user->prefs["customizedFont"] = $customizedFont;
      $current_user->prefs["customizedTheme"] = $customizedTheme;
    }

    $TPL["customizeFontOptions"] = get_options_from_array(get_customizedFont_array(), $customizedFont);
    $TPL["customizeThemeOptions"] = get_options_from_array(get_customizedTheme_array(), $customizedTheme);
  }


  function show_customization($template_name) {
    global $TPL, $current_user;
    include_template($template_name);
  }


}



?>
