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

class page {

  // Initializer
  function __construct() {
  }
  function header() {
    global $TPL;
    $current_user = &singleton("current_user");

    if ($current_user->prefs["showFilters"]) {
      $TPL["onLoad"] []= "show_filter();";
    }

    $TPL["onLoad"] or $TPL["onLoad"] = array();

    include_template(ALLOC_MOD_DIR."shared/templates/headerS.tpl");
  }
  function footer() {
    $current_user = &singleton("current_user");

    include_template(ALLOC_MOD_DIR."shared/templates/footerS.tpl");
    // close page
    $sess = new session();
    $sess->Save();
    if (is_object($current_user) && method_exists($current_user,"get_id") && $current_user->get_id()) {
      $current_user->store_prefs();
    }
  }
  function tabs() {
    global $TPL;
    $current_user = &singleton("current_user");
    $c = new config();
    $tabs = $c->get_config_item("allocTabs");

    $menu_links["home"]    = array("name"=>"Home",    "url"=>$TPL["url_alloc_home"],           "module"=>"home");
    $menu_links["client"]  = array("name"=>"Clients", "url"=>$TPL["url_alloc_clientList"],     "module"=>"client");
    $menu_links["project"] = array("name"=>"Projects","url"=>$TPL["url_alloc_projectList"],    "module"=>"project");
    $menu_links["task"]    = array("name"=>"Tasks",   "url"=>$TPL["url_alloc_taskList"],       "module"=>"task");
    $menu_links["time"]    = array("name"=>"Time",    "url"=>$TPL["url_alloc_timeSheetList"],  "module"=>"time");
    $menu_links["invoice"] = array("name"=>"Invoices","url"=>$TPL["url_alloc_invoiceList"],    "module"=>"invoice");
    $menu_links["sale"]    = array("name"=>"Sales",   "url"=>$TPL["url_alloc_productSaleList"],"module"=>"sale");
    $menu_links["person"]  = array("name"=>"People",  "url"=>$TPL["url_alloc_personList"],     "module"=>"person");
    $menu_links["wiki"]    = array("name"=>"Wiki",    "url"=>$TPL["url_alloc_wiki"],           "module"=>"wiki");
    if (have_entity_perm("inbox",PERM_READ,$current_user) && config::get_config_item("allocEmailHost")) {
      $menu_links["inbox"] = array("name"=>"Inbox",   "url"=>$TPL["url_alloc_inbox"],          "module"=>"email");
    }
    $menu_links["tools"]   = array("name"=>"Tools",   "url"=>$TPL["url_alloc_tools"],          "module"=>"tools");

    $x = -1;
    foreach ($menu_links as $key => $arr) {
      if (in_array($key,$tabs) && has($key)) {
        $name = $arr["name"];
        $TPL["x"] = $x;
        $x+=70;
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
  }
  function toolbar() {
    global $TPL;
    $current_user = &singleton("current_user");
    $db = new db_alloc(); 
    has("task") and $str[] = "<option value=\"create_".$TPL["url_alloc_task"]."\">New Task</option>";
    has("time") and $str[] = "<option value=\"create_".$TPL["url_alloc_timeSheet"]."\">New Time Sheet</option>";
    has("task") and $str[] = "<option value=\"create_".$TPL["url_alloc_task"]."tasktype=Fault\">New Fault</option>";
    has("task") and $str[] = "<option value=\"create_".$TPL["url_alloc_task"]."tasktype=Message\">New Message</option>";
    if (has("project") && have_entity_perm("project", PERM_CREATE, $current_user)) {
      $str[] = "<option value=\"create_".$TPL["url_alloc_project"]."\">New Project</option>";
    } 
    has("client")   and $str[] = "<option value=\"create_".$TPL["url_alloc_client"]."\">New Client</option>";
    has("finance")  and $str[] = "<option value=\"create_".$TPL["url_alloc_expenseForm"]."\">New Expense Form</option>";
    has("reminder") and $str[] = "<option value=\"create_".$TPL["url_alloc_reminder"]."parentType=general&step=2\">New Reminder</option>";
    if (has("person") && have_entity_perm("person", PERM_CREATE, $current_user)) {
      $str[] = "<option value=\"create_".$TPL["url_alloc_person"]."\">New Person</option>";
    }
    has("item") and $str[] = "<option value=\"create_".$TPL["url_alloc_loanAndReturn"]."\">New Item Loan</option>";
    $str[] = "<option value=\"\" disabled=\"disabled\">--------------------";
    $history = new history();
    $q = $history->get_history_query("DESC");
    $db = new db_alloc();
    $db->query($q);
    while ($row = $db->row()) {
      $r["history_".$row["value"]] = $row["the_label"];
    }
    $str[] = page::select_options($r, $_POST["search_action"]);
    $TPL["history_options"] = implode("\n",$str);
    $TPL["category_options"] = page::get_category_options($_POST["search_action"]);
    $TPL["needle"] = $_POST["needle"];
    include_template(ALLOC_MOD_DIR."shared/templates/toolbarS.tpl");
  }
  function extra_links() {
    $current_user = &singleton("current_user");
    global $TPL;
    global $sess;
    $str = "<a href=\"".$TPL["url_alloc_starList"]."\" class=\"icon-star\"></a>&nbsp;&nbsp;&nbsp;";
    $str.= $current_user->get_link()."&nbsp;&nbsp;&nbsp;";
    if (defined("PAGE_IS_PRINTABLE") && PAGE_IS_PRINTABLE) {
      $sess or $sess = new session();
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
      $_GET[$type] && $type != "message_help_no_esc" and $TPL[$type][] = $_GET[$type];

      if (is_array($TPL[$type]) && count($TPL[$type])) {
        $arr[$label] = array("type"=>$type, "msg"=>implode("<br>",$TPL[$type]));
      }
    }

    $search  = array("&lt;br&gt;","&lt;br /&gt;","&lt;b&gt;","&lt;/b&gt;","&lt;u&gt;","&lt;/u&gt;",'\\');
    $replace = array("<br>"      ,"<br />"      ,"<b>"      ,"</b>"      ,"<u>"      ,"</u>"      ,'');

    $class_to_icon["good"] = "icon-ok-sign";
    $class_to_icon["bad"] = "icon-exclamation-sign";
    $class_to_icon["help"] = "icon-info-sign";

    if (is_array($arr) && count($arr)) {
      $str = "<div style=\"text-align:center;\"><div class=\"message corner\" style=\"width:60%;\">";
      $str.= "<table cellspacing=\"0\">";
      foreach ($arr as $class => $arr) {
        $info = $arr["msg"];
        $type = $arr["type"];

        $type != "message_help_no_esc" and $info = page::htmlentities($info);
        $type != "message_help_no_esc" and $info = str_replace($search,$replace,$info);

        $str.= "<tr>";
        $str.= "<td class='".$class."' width='1%' style='vertical-align:top;padding:6px;font-size:150%;'>";
        $str.= "<i class='".$class_to_icon[$class]."'></i><td/>";
        $str.= "<td class='".$class."' width='99%' style='vertical-align:top;padding-top:10px;text-align:left;font-weight:bold;'>".$info."</td></tr>";
      }
      $str.= "</table>";
      $str.= "</div></div>";
    }
    return $str;
  }
  function get_category_options($category="") {
    has("task")    and $category_options["search_tasks"] = "Search Tasks";
    has("project") and $category_options["search_projects"] = "Search Projects";
    has("time")    and $category_options["search_time"] = "Search Time Sheets";
    has("client")  and $category_options["search_clients"] = "Search Clients";
    has("comment") and $category_options["search_comment"] = "Search Comments";
    has("wiki")    and $category_options["search_wiki"] = "Search Wiki";
    has("item")    and $category_options["search_items"] = "Search Items";
    return page::select_options($category_options, $category);
  } 
  function help($topic, $hovertext=false) {
    global $TPL;
    $str = page::prepare_help_string(@file_get_contents($TPL["url_alloc_help"].$topic.".html"));
    if (strlen($str)) {
      $img = "<div id='help_button_".$topic."' style='display:inline;'><a href=\"".$TPL["url_alloc_getHelp"]."topic=".$topic."\" target=\"_blank\">";
      $img.= "<img border='0' class='help_button' onmouseover=\"help_text_on('help_button_".$topic."','".$str."');\" onmouseout=\"help_text_off('help_button_".$topic."');\" src=\"";
      $img.= $TPL["url_alloc_images"]."help.gif\" alt=\"Help\" /></a></div>";
    } else if ($topic) {
      $str = page::prepare_help_string($topic);
      $img = "<div id='help_button_".md5($topic)."' style='display:inline;'>";
      if ($hovertext) {
        $img.= "<span onmouseover=\"help_text_on('help_button_".md5($topic)."','".$str."');\" onmouseout=\"help_text_off('help_button_".md5($topic)."');\">";
        $img.= $hovertext."</span>";
      } else {
        $img.= "<img border='0' class='help_button' onmouseover=\"help_text_on('help_button_".md5($topic)."','".$str."');\" ";
        $img.= "onmouseout=\"help_text_off('help_button_".md5($topic)."');\" src=\"".$TPL["url_alloc_images"]."help.gif\" alt=\"Help\" />";
      }
      $img.= "</div>";
    }
    return $img;
  }
  function prepare_help_string($str) {
    $str = page::htmlentities(addslashes($str));
    $str = str_replace("\r"," ",$str);
    $str = str_replace("\n"," ",$str);
    return $str;
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
    $images = $TPL["url_alloc_images"];
    $year = date("Y");
    $str = <<<EOD
      <span class="calendar_container nobr">
      <input name="${name}" type="text" size="11" value="${default_value}" id="" class="datefield"><img src="${images}cal${year}.png" title="Date Selector" alt="Date Selector" id="">
      </span>
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
      $db = new db_alloc();
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
        if (strlen((string)$label) > $max_length) {
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
    $str = "<a class=\"growshrink nobr\" href=\"#x\" onClick=\"$('#".$id."').fadeToggle();".$extra."\">".$text."</a>";
    return $str;
  }
  function side_by_side_links($items=array(),$url,$redraw="") {
    $url = preg_replace("/[&?]+$/", "", $url);
    if (strpos($url, "?")) {
      $url.= "&";
    } else {
      $url.= "?";
    }
    foreach ($items as $id => $label) {
      $str.= $sp."<a id=\"sbs_link_".$id."\" data-sbs-redraw='".$redraw."' href=\"".$url."sbs_link=".$id."\" class=\"sidebyside\">".$label."</a>";
      $sp = "&nbsp;";
    }
    return "<div class=\"noprint\" style=\"margin:20px 0px 0px 0px; width:100%; text-align:center;\">".$str."</div>";
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
  function exclaim($field="") {
    $star = "&lowast;";
    if (stristr($_SERVER["HTTP_USER_AGENT"],"MSIE")) {
      $star = "*";
    }
    if ($field == "") {
      return "<b style=\"font-weight:bold;font-size:100%;color:green;display:inline;top:-5px !important;top:-3px;position:relative;\">".$star."</b>";
    }
  }
  function warn() {
    $star = "&lowast;";
    if (stristr($_SERVER["HTTP_USER_AGENT"],"MSIE")) {
      $star = "*";
    }
    return "<b style=\"font-weight:bold;font-size:100%;color:orange;display:inline;top:-5px !important;top:-3px;position:relative;\">".$star."</b>";
  }
  function stylesheet() {
    if ($_GET["media"] == "print") {
      return "print.css";
    } else {
      $current_user = &singleton("current_user");
      $themes = page::get_customizedTheme_array();
      if (!isset($current_user->prefs["customizedTheme2"])) {
        $current_user->prefs["customizedTheme2"] = 4;
      }
      $style = strtolower($themes[sprintf("%d", $current_user->prefs["customizedTheme2"])]);
      return "style_".$style.".css";
    }
  }
  function default_font_size() {
    $current_user = &singleton("current_user");
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
    return htmlentities($str,ENT_QUOTES | ENT_IGNORE,"UTF-8");
  }
  function money_fmt($c,$amount=null) {
    $currencies =& get_cached_table("currencyType");
    $n = $currencies[$c]["numberToBasic"];
    $num = sprintf("%0.".$n."f",$amount);
    $num == sprintf("%0.".$n."f",-0) and $num = sprintf("%0.".$n."f",0); // *sigh* to prevent -0.00
    return $num;
  }
  function money_out($c,$amount=null) {
    // AUD,100        -> 100.00
    // AUD,0|''|false -> 0.00
    if (imp($amount)) {
      $c or alloc_error("page::money(): no currency specified for amount $amount.");
      $currencies =& get_cached_table("currencyType");
      $n = $currencies[$c]["numberToBasic"];

      // We can use foo * 10^-n to move the decimal point left
      // Eg: sprintf(%0.2f, $amount * 10^-2) => 15000 becomes 150.00
      // We use the numberToBasic number (eg 2) to a) move the decimal point, and b) dictate the sprintf string
      return page::money_fmt($c, ($amount * pow(10,-$n)));
    }
  }
  function money_in($c, $amount=null) {
    // AUD,100.00 -> 100
    // AUD,0      -> 0
    // AUD        ->
    if (imp($amount)) {
      $c or alloc_error("page::money_in(): no currency specified for amount $amount.");
      $currencies =& get_cached_table("currencyType");
      $n = $currencies[$c]["numberToBasic"];

      // We can use foo * 10^n to move the decimal point right
      // Eg: $amount * 10^-2 => 150.00 becomes 15000
      // We use the numberToBasic number (eg 2) to move the decimal point
      return $amount * pow(10,$n);
    }
  }
  function money($c, $amount=null, $fmt="%s%mo") {
    // Money print
    $c or $c = config::get_config_item('currency');
    $currencies =& get_cached_table("currencyType");
    $fmt = str_replace("%mo",page::money_out($c,$amount),$fmt);                          //%mo = money_out        eg: 150.21
    $fmt = str_replace("%mi",page::money_in($c,$amount),$fmt);                           //%mi = money_in         eg: 15021
    $fmt = str_replace("%m", page::money_fmt($c,$amount),$fmt);                          // %m = format           eg: 150.2 => 150.20
                     $fmt = str_replace("%S",$currencies[$c]["currencyTypeLabel"],$fmt); // %S = mandatory symbol eg: $
    imp($amount) and $fmt = str_replace("%s",$currencies[$c]["currencyTypeLabel"],$fmt); // %s = optional symbol  eg: $
                     $fmt = str_replace("%C",$c,$fmt);                                   // %C = mandatory code   eg: AUD
    imp($amount) and $fmt = str_replace("%c",$c,$fmt);                                   // %c = optional code    eg: AUD
                     $fmt = str_replace("%N",$currencies[$c]["currencyTypeName"],$fmt);  // %N = mandatory name   eg: Australian dollars
    imp($amount) and $fmt = str_replace("%n",$currencies[$c]["currencyTypeName"],$fmt);  // %n = optional name    eg: Australian dollars
    $fmt = str_replace(array("%mo","%mi","%m","%S","%s","%C","%c","%N","%n"),"",$fmt); // strip leftovers away
    return $fmt;
  }
  function money_print($rows=array()) {
    $mainCurrency = config::get_config_item("currency");
    foreach ((array)$rows as $row) {
      $sums[$row["currency"]] += $row["amount"];
      $k = $row["currency"];
    }

    // If there's only one currency, then just return that figure.
    if (count($sums) == 1) {
      return page::money($k,$sums[$k],"%s%m %c");
    }

    // Else if there's more than one currency, we'll provide a tooltip of the aggregation.
    foreach ((array)$sums as $currency => $amount) {
      $str.= $sep.page::money($currency,$amount,"%s%m %c");
      $sep = " + ";
      if ($mainCurrency == $currency) {
        $total += $amount;
      } else {
        $total += exchangeRate::convert($currency,$amount);
      }
    }
    $total = page::money($mainCurrency,$total,"%s%m %c");
    if ($str && $str != $total) { 
      $rtn = page::help(page::exclaim()."<b>Approximate currency conversion</b><br>".$str." = ".$total,page::exclaim().$total);
    } else if ($str) {
      $rtn = $str;
    }
    return $rtn;
  }
  function star($entity,$entityID) {
    $current_user = &singleton("current_user");
    global $TPL;
    if ($current_user->prefs["stars"][$entity][$entityID]) {
      $star_sort = 1;
      $star_hot = " hot";
      $star_icon = "icon-star";
      $star_text = "<b style='display:none'>*</b>";
    } else {
      $star_sort = 2;
      $star_hot = "";
      $star_icon = "icon-star-empty";
      $star_text = "<b style='display:none'>.</b>";
    }
    return '<input type="hidden" value="'.$star_sort.'">'
          .'<a sorttable_customkey="'.$star_sort.'" class="star'.$star_hot.'" href="'.$TPL["url_alloc_star"]
          .'entity='.$entity.'&entityID='.$entityID.'"><b class="'.$star_icon.'">'.$star_text.'</b></a>';
  }
}

?>
