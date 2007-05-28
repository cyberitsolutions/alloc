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

class client extends db_entity {
  var $data_table = "client";
  var $display_field_name = "clientName";


  function client() {
    $this->db_entity();
    $this->display_field_name = "clientName";
    $this->key_field = new db_field("clientID");
    $this->data_fields = array("clientName"=>new db_field("clientName")
                               , "clientPrimaryContactID"=>new db_field("clientPrimaryContactID")
                               , "clientStreetAddressOne"=>new db_field("clientStreetAddressOne")
                               , "clientStreetAddressTwo"=>new db_field("clientStreetAddressTwo")
                               // , "clientContactNameOne"=> new db_field("clientContactNameOne")
                               // , "clientContactNameTwo"=> new db_field("clientContactNameTwo")
                               , "clientSuburbOne"=>new db_field("clientSuburbOne")
                               , "clientSuburbTwo"=>new db_field("clientSuburbTwo")
                               , "clientStateOne"=>new db_field("clientStateOne")
                               , "clientStateTwo"=>new db_field("clientStateTwo")
                               , "clientPostcodeOne"=>new db_field("clientPostcodeOne")
                               , "clientPostcodeTwo"=>new db_field("clientPostcodeTwo")
                               , "clientPhoneOne"=>new db_field("clientPhoneOne")
                               // , "clientPhoneTwo"=> new db_field("clientPhoneTwo")
                               , "clientFaxOne"=>new db_field("clientFaxOne")
                               // , "clientFaxTwo"=> new db_field("clientFaxTwo")
                               // , "clientEmailOne"=> new db_field("clientEmailOne")
                               // , "clientEmailTwo"=> new db_field("clientEmailTwo")
                               , "clientCountryOne"=>new db_field("clientCountryOne")
                               , "clientCountryTwo"=>new db_field("clientCountryTwo")
                               , "clientComment"=>new db_field("clientComment")
                               , "clientCreatedTime"=>new db_field("clientCreatedTime")
                               , "clientModifiedTime"=>new db_field("clientModifiedTime")
                               , "clientModifiedUser"=>new db_field("clientModifiedUser")
                               , "clientStatus"=>new db_field("clientStatus"));

  }

  function has_attachment_permission($person) {
    // Placeholder for security check in shared/get_attchment.php
    return true;
  }

  function get_client_contact_select($clientID="",$clientContactID="") {
    $db = new db_alloc;
    $q = sprintf("SELECT clientContactName as value, clientContactID as name FROM clientContact WHERE clientID = %d",$clientID);
    $options = get_option("None", "")."\n";
    $options.= get_select_options($q,$clientContactID);
    return "<select name=\"clientContactID\" style=\"width:300px\">".$options."</select>";
  }
 
  function get_client_name() {
    return stripslashes($this->get_value("clientName"));
  }

  function get_client_link() {
    global $TPL;
    return "<a href=\"".$TPL["url_alloc_client"]."clientID=".$this->get_id()."\">".$this->get_client_name()."</a>";
  }

  function get_client_list_filter($filter=array()) {
    
    if ($filter["clientStatus"]) {
      $sql[] = sprintf("(clientStatus = '%s')",db_esc($filter["clientStatus"]));
    } 

    if ($filter["clientName"]) {
      $sql[] = sprintf("(clientName LIKE '%%%s%%')",db_esc($filter["clientName"]));
    } 

    if ($filter["clientLetter"] && $filter["clientLetter"] == "A") {
      $sql[] = "(clientName like 'A%' or clientName REGEXP '^[^[:alpha:]]')";
    } else if ($filter["clientLetter"] && $filter["clientLetter"] != "ALL") {
      $sql[] = sprintf("(clientName LIKE '%s%%')",db_esc($filter["clientLetter"]));
    }

    return $sql;
  }

  function get_client_list($_FORM) {
    /*
     * This is the definitive method of getting a list of clients that need a sophisticated level of filtering
     * 
     * Display Options:
     *  showHeader
     *  showClientName
     *  showClientLink
     *  showPrimaryContactName
     *  showPrimaryContactPhone
     *  showPrimaryContactEmail
     *  
     * Filter Options:
     *   clientStatus
     *   clientName
     *   clientLetter
     *   return = html | dropdown_options
     *
     */

    $filter = client::get_client_list_filter($_FORM);

    $debug = $_FORM["debug"];
    $debug and print "<pre>_FORM: ".print_r($_FORM,1)."</pre>";
    $debug and print "<pre>filter: ".print_r($filter,1)."</pre>";
  
    $_FORM["return"] or $_FORM["return"] = "html";

    // A header row
    $summary.= client::get_client_list_tr_header($_FORM);


    if (is_array($filter) && count($filter)) {
      $filter = " WHERE ".implode(" AND ",$filter);
    }

    $q = "SELECT * FROM client ".$filter." ORDER BY clientName";
    $debug and print "Query: ".$q;
    $db = new db_alloc;
    $db->query($q);
    while ($row = $db->next_record()) {
      $print = true;
      $c = new client;
      $c->read_db_record($db);
      $row["clientLink"] = $c->get_client_link();
      $summary.= client::get_client_list_tr($row,$_FORM);
      $summary_ops[$c->get_id()] = stripslashes($c->get_value("clientName"));

      #$TPL["odd_even"] = $TPL["odd_even"] == "odd" ? "even" : "odd";
    }

    if ($print && $_FORM["return"] == "html") {
      return "<table class=\"tasks\" border=\"0\" cellspacing=\"0\">".$summary."</table>";

    } else if ($print && $_FORM["return"] == "dropdown_options") {
      return $summary_ops;

    } else if (!$print && $_FORM["return"] == "html") {
      return "<table style=\"width:100%\"><tr><td colspan=\"10\" style=\"text-align:center\"><b>No Clients Found</b></td></tr></table>";
    }

  }

  function get_client_list_tr_header($_FORM) {
    if ($_FORM["showHeader"]) {
      $summary = "\n<tr>";
      $_FORM["showClientName"]          and $summary.= "\n<th class=\"col\">Client</th>";
      $_FORM["showClientLink"]          and $summary.= "\n<th class=\"col\">Client</th>";
      $_FORM["showPrimaryContactName"]  and $summary.= "\n<th class=\"col\">Contact Name</th>";
      $_FORM["showPrimaryContactPhone"] and $summary.= "\n<th class=\"col\">Contact Phone</th>";
      $_FORM["showPrimaryContactEmail"] and $summary.= "\n<th class=\"col\">Contact Email</th>";
      $_FORM["showClientStatus"]        and $summary.= "\n<th class=\"col\">Status</th>";
      $summary.="\n</tr>";
      return $summary;
    }
  }

  function get_client_list_tr($client,$_FORM) {

    static $odd_even;
    $odd_even = $odd_even == "even" ? "odd" : "even";

    if ($_FORM["showPrimaryContactName"] || $_FORM["showPrimaryContactPhone"] || $_FORM["showPrimaryContactEmail"]) {
      $clientContact = new clientContact;
      $clientContact->set_id($client['clientPrimaryContactID']);
      $clientContact->select();
      $primaryContactName = $clientContact->get_value("clientContactName");
      $primaryContactPhone = $clientContact->get_value("clientContactPhone");
      $primaryContactEmail = $clientContact->get_value("clientContactEmail");
      $primaryContactEmail and $primaryContactEmail = "<a href=\"mailto:".$primaryContactEmail."\">".$primaryContactEmail."</a>";
    }

    $summary[] = "<tr class=\"".$odd_even."\">";
    $_FORM["showClientName"]          and $summary[] = "  <td class=\"col\">".$client["clientName"]."&nbsp;</td>";
    $_FORM["showClientLink"]          and $summary[] = "  <td class=\"col\">".$client["clientLink"]."&nbsp;</td>";
    $_FORM["showPrimaryContactName"]  and $summary[] = "  <td class=\"col\">".$primaryContactName."&nbsp;</td>";
    $_FORM["showPrimaryContactPhone"] and $summary[] = "  <td class=\"col\">".$primaryContactPhone."&nbsp;</td>";
    $_FORM["showPrimaryContactEmail"] and $summary[] = "  <td class=\"col\">".$primaryContactEmail."&nbsp;</td>";
    $_FORM["showClientStatus"]        and $summary[] = "  <td class=\"col\">".ucwords($client["clientStatus"])."&nbsp;</td>";
    $summary[] = "</tr>";

    $summary = "\n".implode("\n",$summary);
    return $summary;
  }

  function load_form_data($defaults=array()) {
    global $current_user;

    $page_vars = array("clientStatus"
                      ,"clientName"
                      ,"clientLetter"
                      ,"url_form_action"
                      ,"form_name"
                      ,"dontSave"
                      ,"applyFilter"
                      ,"showHeader"
                      ,"showClientName"
                      ,"showClientLink"
                      ,"showClientStatus"
                      ,"showPrimaryContactName"
                      ,"showPrimaryContactPhone"
                      ,"showPrimaryContactEmail"
                      );

    $_FORM = get_all_form_data($page_vars,$defaults);

    if (!$_FORM["applyFilter"]) {
      $_FORM = $current_user->prefs[$_FORM["form_name"]];
      if (!isset($current_user->prefs[$_FORM["form_name"]])) {
        $_FORM["clientLetter"] = "A";
        $_FORM["clientStatus"] = "current";
      }

    } else if ($_FORM["applyFilter"] && is_object($current_user) && !$_FORM["dontSave"]) {
      $url = $_FORM["url_form_action"];
      unset($_FORM["url_form_action"]);
      $current_user->prefs[$_FORM["form_name"]] = $_FORM;
      $_FORM["url_form_action"] = $url;
    }

    return $_FORM;
  }

  function load_client_filter($_FORM) {
    global $TPL;

    $db = new db_alloc;

    // Load up the forms action url
    $rtn["url_form_action"] = $_FORM["url_form_action"];

    $rtn["clientStatusOptions"] = get_select_options(array("current"=>"Current", "potential"=>"Potential", "archived"=>"Archived"), $_FORM["clientStatus"]);
    $rtn["clientName"] = $_FORM["clientName"];
    $letters = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "ALL");
    foreach($letters as $letter) {
      if ($_FORM["clientLetter"] == $letter) {
        $rtn["alphabet_filter"].= "&nbsp;&nbsp;".$letter;
      } else {
        $rtn["alphabet_filter"].= "&nbsp;&nbsp;<a href=\"".$TPL["url_alloc_clientList"]."clientLetter=".$letter."&clientStatus=".$_FORM["clientStatus"]."&applyFilter=1\">".$letter."</a>";
      }
    }
   
    // Get
    $rtn["FORM"] = "FORM=".urlencode(serialize($_FORM));

    return $rtn;
  }



}



?>
