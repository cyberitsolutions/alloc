<?php
class customize_alloc_home_item extends home_item {
  function customize_alloc_home_item() {
    global $TPL, $user, $current_user, $font, $theme, $customize_save, $customizedFont, $customizedTheme;
    home_item::home_item("", "Preferences", "home", "customizeH.tpl", "narrow");

    if (!is_object($current_user) || !is_object($user)) {
      return false;
    }

    if (isset($font)) {
      $customizedFont = $font;
    }
    if (isset($theme)) {
      $customizedTheme = $theme;
    }

    if ($customize_save) {
      $user->register ("customizedFont");
      $user->register ("customizedTheme");
    }


    $options = array("-3"=>1, "-2"=>2, "-1"=>3, "0"=>"Default", "1"=>5, "3"=>6, "4"=>7, "5"=>8, "6"=>9, "7"=>10);
    $TPL["customizeFontOptions"] = get_options_from_array($options, $customizedFont);

    $options = array("Default", "Darko", "Aneurism", "Clove", "None");
    $TPL["customizeThemeOptions"] = get_options_from_array($options, $customizedTheme);

  }


  function show_customization($template_name) {
    global $TPL, $current_user;
    include_template($template_name);
  }


}



?>
