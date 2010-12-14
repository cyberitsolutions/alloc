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

class page {

  // Initializer
  function page() {
  }
  function header() {
    include_template(ALLOC_MOD_DIR."shared/templates/headerS.tpl");
  }
  function footer() {
    global $current_user;
    include_template(ALLOC_MOD_DIR."shared/templates/footerS.tpl");
    // close page
    $sess = new Session;
    $sess->Save();
    if (is_object($current_user) && $current_user->get_id()) {
      $current_user->store_prefs();
    }
  }
  function tabs() {
    global $TPL;

    $menu_links = array("Home"     =>array("url"=>$TPL["url_alloc_home"],"module"=>"home")
        ,"Clients"  =>array("url"=>$TPL["url_alloc_clientList"],"module"=>"client")
        ,"Projects" =>array("url"=>$TPL["url_alloc_projectList"],"module"=>"project")
        ,"Tasks"    =>array("url"=>$TPL["url_alloc_taskList"],"module"=>"task")
        ,"Time"     =>array("url"=>$TPL["url_alloc_timeSheetList"],"module"=>"time")
        ,"Invoices" =>array("url"=>$TPL["url_alloc_invoiceList"],"module"=>"invoice")
        ,"Sales"    =>array("url"=>$TPL["url_alloc_productSaleList"],"module"=>"sale")
        ,"People"   =>array("url"=>$TPL["url_alloc_personList"],"module"=>"person")
        ,"Wiki"     =>array("url"=>$TPL["url_alloc_wiki"],"module"=>"wiki")
        ,"Tools"    =>array("url"=>$TPL["url_alloc_tools"],"module"=>"tools")
        );

    $x = -1;
    foreach ($menu_links as $name => $arr) {
      $TPL["x"] = $x;
      $x+=80;
      $TPL["url"] = $arr["url"];
      $TPL["name"] = $name;
      unset($TPL["active"]);
      if (preg_match("/".str_replace("/", "\\/", $_SERVER["PHP_SELF"])."/", $url) || preg_match("/".$arr["module"]."/",$_SERVER["PHP_SELF"]) && !$done) {
        $TPL["active"] = " active";
        $done = true;
      }
      include_template(ALLOC_MOD_DIR."shared/templates/tabR.tpl");
    }
  }
  function toolbar() {
    global $TPL, $current_user, $modules;
    $db = new db_alloc; 
    $str[] = "<option value=\"\">Quick List</option>";
    $str[] = "<option value=\"".$TPL["url_alloc_task"]."\">New Task</option>";
    if (isset($modules["time"]) && $modules["time"]) {
      $str[] = "<option value=\"".$TPL["url_alloc_timeSheet"]."\">New Time Sheet</option>";
    }
    $str[] = "<option value=\"".$TPL["url_alloc_task"]."tasktype=Fault\">New Fault</option>";
    $str[] = "<option value=\"".$TPL["url_alloc_task"]."tasktype=Message\">New Message</option>";
    if (have_entity_perm("project", PERM_CREATE, $current_user)) {
      $str[] = "<option value=\"".$TPL["url_alloc_project"]."\">New Project</option>";
    } 
    if (isset($modules["client"]) && $modules["client"]) {
      $str[] = "<option value=\"".$TPL["url_alloc_client"]."\">New Client</option>";
    } 
    if (isset($modules["finance"]) && $modules["finance"]) {
      $str[] = "<option value=\"".$TPL["url_alloc_expenseForm"]."\">New Expense Form</option>";
    }
    $str[] = "<option value=\"".$TPL["url_alloc_reminderAdd"]."parentType=general&step=2\">New Reminder</option>";
    if (have_entity_perm("person", PERM_CREATE, $current_user)) {
      $str[] = "<option value=\"".$TPL["url_alloc_person"]."\">New Person</option>";
    }
    $str[] = "<option value=\"".$TPL["url_alloc_loanAndReturn"]."\">New Item Loan</option>";
    $history = new history;
    $str[] = page::select_options($history->get_history_query("DESC"), $_GET["historyID"]);
    $TPL["history_options"] = implode("\n",$str);

    $TPL["category_options"] = page::get_category_options($_GET["category"]);
    $TPL["needle"] = $_POST["needle"];
    include_template(ALLOC_MOD_DIR."shared/templates/toolbarS.tpl");
  }
  function extra_links() {
    global $current_user, $TPL, $sess;
    $str.= $current_user->get_link()."&nbsp;&nbsp;&nbsp;";
    if (defined("PAGE_IS_PRINTABLE") && PAGE_IS_PRINTABLE) {
      $sess or $sess = new Session;
      $str.= "<a href=\"".$sess->url($_SERVER["REQUEST_URI"])."media=print\">Print</a>&nbsp;&nbsp;&nbsp;";
    }
    if (have_entity_perm("config", PERM_UPDATE, $current_user, true)) {
      $str.= "<a href=\"".$TPL["url_alloc_config"]."\">Setup</a>&nbsp;&nbsp;&nbsp;";
    }
    $url = $sess->url("../help/help.php?topic=".$TPL["alloc_help_link_name"]);
    $str.= "<a href=\"".$url."\">Help</a>&nbsp;&nbsp;&nbsp;";
    $url = $TPL["url_alloc_logout"];
    $str.= "<a href=\"".$url."\">Logout</a>";
    return $str;
  }
  function messages() {
    global $TPL;

    $msgtypes["message"]      = "bad";
    $msgtypes["message_good"] = "good";
    $msgtypes["message_help"] = "help";
    $msgtypes["message_help_no_esc"] = "help";

    foreach ($msgtypes as $type => $label) {
      if ($TPL[$type] && is_string($TPL[$type])) {
        $t = $TPL[$type];
        unset($TPL[$type]);
        $TPL[$type][] = $t;
      }
      $_GET[$type] && $type != "message_help_no_esc" and $TPL[$type][] = urldecode($_GET[$type]);

      if (is_array($TPL[$type]) && count($TPL[$type])) {
        $arr[$label] = array("type"=>$type, "msg"=>implode("<br>",$TPL[$type]));
      }
    }

    $search  = array("&lt;br&gt;","&lt;br /&gt;","&lt;b&gt;","&lt;/b&gt;","&lt;u&gt;","&lt;/u&gt;",'\\');
    $replace = array("<br>"      ,"<br />"      ,"<b>"      ,"</b>"      ,"<u>"      ,"</u>"      ,'');


    if (is_array($arr) && count($arr)) {
      $str = "<div style=\"text-align:center;\"><div class=\"message corner\">";
      $str.= "<table cellspacing=\"0\">";
      foreach ($arr as $class => $arr) {
        $info = $arr["msg"];
        $type = $arr["type"];

        $type != "message_help_no_esc" and $info = page::htmlentities($info);
        $info = str_replace($search,$replace,$info);

        $str.= "<tr><td width=\"1%\" style=\"vertical-align:top;\"><img src=\"".$TPL["url_alloc_images"]."icon_message_".$class.".png\" alt=\"$class\" /><td/>";
        $str.= "<td class=\"".$class."\" align=\"left\" width=\"99%\">".$info."</td></tr>";
      }
      $str.= "</table>";
      $str.= "</div></div>";
    }
    return $str;
  }
  function get_category_options($category="") {
    $category_options = array("Tasks"=>"Tasks", "Projects"=>"Projects", "Time"=>"Time", "Items"=>"Items", "Clients"=>"Clients","Comment"=>"Comment","Wiki"=>"Wiki");
    return page::select_options($category_options, $category);
  } 
  function help($topic) {
    global $TPL;
    $str = page::get_help_string($topic);
    if (strlen($str)) {
      $img = "<div id='help_button_".$topic."' style='display:inline;'><a href=\"".$TPL["url_alloc_getHelp"]."topic=".$topic."\" target=\"_blank\">";
      $img.= "<img border='0' class='help_button' onmouseover=\"help_text_on('help_button_".$topic."','".$str."');\" onmouseout=\"help_text_off('help_button_".$topic."');\" src=\"";
      $img.= $TPL["url_alloc_images"]."help.gif\" alt=\"Help\" /></a></div>";
    }
    return $img;
  }
  function get_help_string($topic) {
    $str = page::htmlentities(addslashes(page::get_raw_help_string($topic)));
    $str = str_replace("\r"," ",$str);
    $str = str_replace("\n"," ",$str);
    return $str;
  }
  function get_raw_help_string($topic) {
    global $TPL;
    $file = $TPL["url_alloc_help"].$topic.".html";
    if (file_exists($file)) {
      return file_get_contents($file);
    } 
  }
  function textarea($name, $default_value="", $ops=array()) {
    $heights = array("small"=>40, "medium"=>100, "large"=>340, "jumbo"=>440);
    $height = $ops["height"] or $height = "small";

    $cols = $ops["cols"];
    !$ops["width"] && !$ops["cols"] and $cols = 85;

    $attrs["id"] = $name;
    $attrs["name"] = $name;
    $attrs["wrap"] = "virtual";
    $cols            and $attrs["cols"]     = $cols;
                         $attrs["style"]    = "height:".$heights[$height]."px";
    $ops["width"]    and $attrs["style"]   .= "; width:".$ops["width"];
    $ops["class"]    and $attrs["class"]    = $ops["class"];
    $ops["tabindex"] and $attrs["tabindex"] = $ops["tabindex"];

    foreach ($attrs as $k => $v) {
      $str.= sprintf(' %s="%s"',$k,$v);
    }
    return "<textarea".$str.">".page::htmlentities($default_value)."</textarea>\n";
  }
  function calendar($name, $default_value="") {
    global $TPL;
    // setup the first day of the week
    $days = array("Sun","Mon","Tue","Wed","Thu","Fri","Sat");
    $days = array_flip($days);
    $firstday = config::get_config_item("calendarFirstDay");
    $firstday = sprintf("%d",$days[$firstday]);
    $default_value and $default = ", date : ".$default_value;
    $images = $TPL["url_alloc_images"];
    $year = date("Y");
    $str = <<<EOD
      <div class="calendar_container enclose nobr">
      <input name="${name}" type="text" size="11" value="${default_value}" id="${name}" class="datefield"><img src="${images}cal${year}.png" id="button_${name}" title="Date Selector" alt="Date Selector">
      <script type="text/javascript">
      Calendar.setup( { inputField : "${name}", ifFormat : "%Y-%m-%d", button : "button_${name}", showOthers : 1, align : "Bl", firstDay : ${firstday}, step : 1, weekNumbers : 0 ${default} })
      </script>
      </div>

EOD;
    return $str;
  }
  function select_options($options,$selected_value=NULL,$max_length=45,$escape=true) {
    /**
     * Builds up options for use in a html select widget (works with multiple selected too)
     *
     * @param   $options          mixed   An sql query or an array of options
     * @param   $selected_value   string  The current selected element
     * @param   $max_length       int     The maximum string length of the label
     * @return                    string  The string of options
     */

    // Build options from an SQL query: "SELECT col_a as value, col_b as label FROM"
    if (is_string($options)) {
      $db = new db_alloc;
      $db->query($options);
      while ($row = $db->row()) {
        $rows[$row["value"]] = $row["label"];
      }

      // Build options from an array: array(value1=>label1, value2=>label2)
    } else if (is_array($options)) {
      foreach ($options as $k => $v) {
        $rows[$k] = $v;
      }
    }

    if (is_array($rows)) {

      // Coerce selected options into an array
      if (is_array($selected_value)) {
        $selected_values = $selected_value;
      } else if ($selected_value !== NULL) {
        $selected_values[] = $selected_value;
      }

      foreach ($rows as $value=>$label) {
        $sel = "";

        if ($value && !$label) { 
          $label = $value;
        }

        // If an array of selected values!
        if (is_array($selected_values)) {
          foreach ($selected_values as $selected_value) {
            if ($selected_value === "" && $value === 0) {
              // continue
            } else if ($selected_value == $value) {
              $sel = " selected";
            }
          }
        }

        $label = str_replace("&nbsp;"," ",$label);
        if (strlen($label) > $max_length) {
          $label = substr($label, 0, $max_length - 3)."...";
        } 
      
        $escape and $label = page::htmlentities($label);
        $label = str_replace(" ","&nbsp;",$label);

        $str.= "\n<option value=\"".$value."\"".$sel.">".$label."</option>";
      }
    }
    return $str;
  }
  function expand_link($id, $text="New ",$id_to_hide="") {
    global $TPL;
    $id_to_hide and $extra = "$('#".$id_to_hide."').slideToggle('fast');";
    $str = "<a class=\"growshrink nobr\" href=\"#x\" onClick=\"$('#".$id."').slideToggle('fast');".$extra."\">".$text."</a>";
    return $str;
  }
  function side_by_side_links($items=array(),$default=false, $url) {
    global $TPL;

    foreach ($items as $id => $label) {
      $default or $default = $id; // first option is default
      $ids[] = $id; 
    }

    $js_array = "['".implode("','",$ids)."']";

    $url = preg_replace("/[&?]+$/", "", $url);
    if (strpos($url, "?")) {
      $url.= "&";
    } else {
      $url.= "?";
    }

    foreach ($items as $id => $label) {
      $str.= $sp."<a id=\"sbs_link_".$id."\" href=\"".$url."sbs_link=".$id."\" class=\"sidebyside\" onClick=\"sidebyside_activate('".$id."',".$js_array."); return false;\">".$label."</a>";
      $sp = "&nbsp;";
    }

    // argh, I am bad man, this activates the default option, because it's minutely better than putting in a body onload
    $TPL["extra_footer_stuff"].= "<img alt=\"Dummy image\" src=\"".$TPL["url_alloc_images"]."pixel.gif\" onload=\"sidebyside_activate('".$default."',".$js_array.");\">";

    return "<div style=\"margin:20px 0px 0px 0px;\">".$str."</div>";
  }
  function mandatory($field="") {
    $star = "&lowast;";
    if (stristr($_SERVER["HTTP_USER_AGENT"],"MSIE")) {
      $star = "*";
    }
    if ($field == "") {
      return "<b style=\"font-weight:bold;font-size:100%;color:red;display:inline;top:-5px !important;top:-3px;position:relative;\">".$star."</b>";
    }
  }
  function stylesheet() {
    if ($_GET["media"] == "print") {
      return "print.css";
    } else {
      global $current_user;
      $themes = page::get_customizedTheme_array();
      $style = strtolower($themes[sprintf("%d", $current_user->prefs["customizedTheme2"])]);
      return "style_".$style.".css";
    }
  }
  function default_font_size() {
    global $current_user;
    $fonts  = page::get_customizedFont_array();
    $font = $fonts[sprintf("%d",$current_user->prefs["customizedFont"])];
    $font or $font = 4;
    $font+= 8;
    return $font;
  }
  function get_customizedFont_array() {
    return array("-3"=>1, "-2"=>2, "-1"=>3, "0"=>"4", "1"=>5, "2"=>6, "3"=>7, "4"=>8, "5"=>9, "6"=>10);
  }
  function get_customizedTheme_array() {
    global $TPL;
    $dir = $TPL["url_alloc_styles"];
    $rtn = array();
    if (is_dir($dir)) {
      $handle = opendir($dir);
      // TODO add icons to files attachaments in general
      while (false !== ($file = readdir($handle))) {
        if (preg_match("/style_(.*)\.ini$/",$file,$m)) {
          $rtn[] = ucwords($m[1]);
        }
      }
      sort($rtn);
    }
    return $rtn;
  }
  function to_html($str="",$maxlength=false) {
    $maxlength and $str = wordwrap($str,$maxlength,"\n");
    $str = page::htmlentities($str);
    $str = nl2br($str);
    return $str;
  }
  function htmlentities($str="") {
    return htmlentities($str,ENT_QUOTES,"UTF-8");
  }
  function money_out($c,$amount=null,$exchangeRate=null) {
    // AUD,100        -> 100.00
    // AUD,0|''|false -> 0.00
    if (imp($amount)) {
      $c or die("page::money(): no currency specified for amount $amount.");
      $currencies = get_cached_table("currencyType");
      $n = $currencies[$c]["numberToBasic"];

      // If there's an exchange rate, then MULTIPLY the amount by it.
      // Note that if the exchange rate is ===zero then it will
      // cause the amount to be zero. Which will help in tracking cases where
      // we're missing an exchange rate for a particular date.
      imp($exchangeRate) and $amount = $amount * $exchangeRate;

      // We can use foo * 10^-n to move the decimal point left
      // Eg: sprintf(%0.2f, $amount * 10^-2) => 15000 becomes 150.00
      // We use the numberToBasic number (eg 2) to a) move the decimal point, and b) dictate the sprintf string
      return sprintf("%0.".$n."f",($amount * pow(10,-$n)));
    }
  }
  function money_in($c, $amount=null,$exchangeRate=null) {
    // AUD,100.00 -> 100
    // AUD,0      -> 0
    // AUD        ->
    if (imp($amount)) {
      $c or die("page::money_in(): no currency specified for amount $amount.");
      $currencies = get_cached_table("currencyType");
      $n = $currencies[$c]["numberToBasic"];

      // If there's an exchange rate, then DIVIDE the amount by it. Note
      // that if the exchange rate is ===zero then it will BREAK. Which
      // will help in tracking cases where we're missing an exchange rate
      // for a particular date.
      imp($exchangeRate) and $amount = $amount / $exchangeRate;

      // We can use foo * 10^n to move the decimal point right
      // Eg: $amount * 10^-2 => 150.00 becomes 15000
      // We use the numberToBasic number (eg 2) to move the decimal point
      return $amount * pow(10,$n);
    }
  }
  function money($c, $amount=null, $fmt="%s%mo", $exchangeRate=null) {
    // Money print
    $currencies = get_cached_table("currencyType");
    $fmt = str_replace("%mo",page::money_out($c,$amount,$exchangeRate),$fmt);            //%mo = money_out        eg: 150.21
    $fmt = str_replace("%mi",page::money_in($c,$amount,$exchangeRate),$fmt);             //%mi = money_in         eg: 15021
    $fmt = str_replace("%m",sprintf("%0.".$currencies[$c]["numberToBasic"]."f",$amount),$fmt);// %m = format      eg: 150.2 => 150.20
                     $fmt = str_replace("%S",$currencies[$c]["currencyTypeLabel"],$fmt); // %S = mandatory symbol eg: $
    imp($amount) and $fmt = str_replace("%s",$currencies[$c]["currencyTypeLabel"],$fmt); // %s = optional symbol  eg: $
                     $fmt = str_replace("%C",$c,$fmt);                                   // %C = mandatory code   eg: AUD
    imp($amount) and $fmt = str_replace("%c",$c,$fmt);                                   // %c = optional code    eg: AUD
                     $fmt = str_replace("%N",$currencies[$c]["currencyTypeName"],$fmt);  // %N = mandatory name   eg: Australian dollars
    imp($amount) and $fmt = str_replace("%n",$currencies[$c]["currencyTypeName"],$fmt);  // %n = optional name    eg: Australian dollars
    $fmt = str_replace(array("%mo","%mi","%m","%S","%s","%C","%c","%N","%n"),"",$fmt); // strip leftovers away
    return $fmt;
  }
}

?>
