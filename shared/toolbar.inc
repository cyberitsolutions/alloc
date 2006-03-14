<?php
class toolbar_item {
  var $name;
  var $label;
  var $url_parameters;

  function toolbar_item($name, $label, $url_parameters = "") {
    $this->name = $name;
    $this->label = $label;
    $this->url_parameters = $url_parameters;
  }

  function show() {
    $url = $this->get_url();
    if ($url_parameters) {
      $url.= "&$url_parameters";
    }

    global $loaded_home_items;

    if (preg_match("/".str_replace("/", "\\/", $_SERVER["PHP_SELF"])."/", $url)) {
      $class = " class=\"active\"";
    }


    $loaded_home_items[] = "<a".$class." href=\"$url\">".$this->get_label()."</a>";
  }

  function get_url() {
    global $TPL;
    $url = $TPL["url_alloc_".$this->name];
    if ($this->url_parameters) {
      $url.= "&".$this->url_parameters;
    }
    return $url;
  }



  function get_label() {
    return $this->label;
  }
}


function show_messages() {
  global $TPL;
  if ($TPL["message"] && is_string($TPL["message"])) {
    $str = $TPL["message"];
    $type = "bad";

  } else if (is_array($TPL["message"]) && count($TPL["message"])) {
    $str = implode("<br/>",$TPL["message"]);
    $type = "bad";

  } else  if ($TPL["message_good"] && is_string($TPL["message_good"])) {
    $str = $TPL["message_good"];
    $type = "good";

  } else if (is_array($TPL["message_good"]) && count($TPL["message_good"])) {
    $str = implode("<br/>",$TPL["message_good"]);
    $type = "good";

  } else  if ($TPL["message_help"] && is_string($TPL["message_help"])) {
    $str = $TPL["message_help"];
    $type = "help";

  } else if (is_array($TPL["message_help"]) && count($TPL["message_help"])) {
    $str = implode("<br/>",$TPL["message_help"]);
    $type = "help";
  } 

  if ($str) {
    echo "<div class=\"message ".$type."\"><table><tr><td width=\"1%\" valign=\"top\"><img src=\"".$TPL["url_alloc_images"]."icon_message_".$type.".gif\"/><td/>";
    echo "<td class=\"".$type."\">".$str."</td></tr></table></div>";
  }

}



function register_toolbar_item($tool_name, $label, $url_parameters = "") {
  global $toolbar_items;
  $toolbar_items[] = new toolbar_item($tool_name, $label, $url_parameters);
}

function register_toolbar_items() {
  global $modules, $toolbar_items;

  $toolbar_items = array();

  reset($modules);
  while (list($module_name, $module) = each($modules)) {
    $module->register_toolbar_items();
  }
  register_toolbar_item("logout", "Logout");
}



function show_toolbar_items() {
  global $toolbar_items, $loaded_home_items;

  reset($toolbar_items);
  while (list(, $toolbar_item) = each($toolbar_items)) {
    $toolbar_item->show();
  }

  return $loaded_home_items;
}


function show_history() {
  global $historyID;
  $history = new history;
  echo get_options_from_db($history->get_history_db(), "the_label", "historyID", $historyID, 30, $reverse_results = true);
}


function get_category_options() {
  global $category;
  $category_options = array("Tasks"=>"Tasks", "TaskID"=>"Task ID", "Announcements"=>"Announcements", "Clients"=>"Clients", "Items"=>"Items", "Projects"=>"Projects");
  echo get_options_from_array($category_options, $category, true, 10);
}







?>
