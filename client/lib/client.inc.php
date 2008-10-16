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

class client extends db_entity {
  public $classname = "client";
  public $data_table = "client";
  public $display_field_name = "clientName";
  public $key_field = "clientID";
  public $data_fields = array("clientName"
                             ,"clientPrimaryContactID"
                             ,"clientStreetAddressOne"
                             ,"clientStreetAddressTwo"
                             ,"clientSuburbOne"
                             ,"clientSuburbTwo"
                             ,"clientStateOne"
                             ,"clientStateTwo"
                             ,"clientPostcodeOne"
                             ,"clientPostcodeTwo"
                             ,"clientPhoneOne"
                             ,"clientFaxOne"
                             ,"clientCountryOne"
                             ,"clientCountryTwo"
                             ,"clientComment"
                             ,"clientCreatedTime"
                             ,"clientModifiedTime"
                             ,"clientModifiedUser"
                             ,"clientStatus"
                             );


  function has_attachment_permission($person) {
    // Placeholder for security check in shared/get_attchment.php
    return true;
  }

  function has_attachment_permission_delete($person) {
    // Placeholder for security check in shared/get_attchment.php
    return true;
  }

  function get_client_select($clientStatus="", $clientID="") {
    global $TPL;
    $db = new db_alloc;
    if ($clientStatus) {
      $q = sprintf("SELECT clientID as value, clientName as label 
                      FROM client 
                     WHERE clientStatus = '%s' 
                        OR clientID = %d 
                  ORDER BY clientName"
                    ,db_esc($clientStatus),$clientID);
    }
    $options.= page::select_options($q,$clientID,100);
    $str = "<select id=\"clientID\" name=\"clientID\" style=\"width:100%;\">";
    $str.= "<option value=\"\">";
    $str.= $options;
    $str.= "</select>";
    return $str;
  }
 
  function get_client_contact_select($clientID="",$clientContactID="") {
    $clientID or $clientID = $_GET["clientID"];
    $db = new db_alloc;
    $q = sprintf("SELECT clientContactName as label, clientContactID as value FROM clientContact WHERE clientID = %d",$clientID);
    $options = page::select_options($q,$clientContactID,100);
    return "<select id=\"clientContactID\" name=\"clientContactID\" style=\"width:100%\"><option value=\"\">".$options."</select>";
  }
 
  function get_client_name() {
    return $this->get_value("clientName");
  }

  function get_client_link() {
    global $TPL;
    return "<a href=\"".$TPL["url_alloc_client"]."clientID=".$this->get_id()."\">".$this->get_client_name()."</a>";
  }

  function get_list_filter($filter=array()) {
    
    if ($filter["clientStatus"]) {
      $sql[] = sprintf("(clientStatus = '%s')",db_esc($filter["clientStatus"]));
    } 

    if ($filter["clientName"]) {
      $sql[] = sprintf("(clientName LIKE '%%%s%%')",db_esc($filter["clientName"]));
    } 

    if ($filter["contactName"]) {
      $sql[] = sprintf("(clientContactName LIKE '%%%s%%')",db_esc($filter["contactName"]));
    } 

    if ($filter["clientLetter"] && $filter["clientLetter"] == "A") {
      $sql[] = "(clientName like 'A%' or clientName REGEXP '^[^[:alpha:]]')";
    } else if ($filter["clientLetter"] && $filter["clientLetter"] != "ALL") {
      $sql[] = sprintf("(clientName LIKE '%s%%')",db_esc($filter["clientLetter"]));
    }

    return $sql;
  }

  function get_list($_FORM) {
    /*
     * This is the definitive method of getting a list of clients that need a sophisticated level of filtering
     *
     */


    global $TPL;
    $filter = client::get_list_filter($_FORM);

    $debug = $_FORM["debug"];
    $debug and print "<pre>_FORM: ".print_r($_FORM,1)."</pre>";
    $debug and print "<pre>filter: ".print_r($filter,1)."</pre>";
  
    $_FORM["return"] or $_FORM["return"] = "html";

    // A header row
    $summary.= client::get_list_tr_header($_FORM);


    if (is_array($filter) && count($filter)) {
      $filter = " WHERE ".implode(" AND ",$filter);
    }

    if ($_FORM["contactName"]) {
      $join = sprintf("LEFT JOIN clientContact ON client.clientID = clientContact.clientID");
    } else {
      $join = sprintf("LEFT JOIN clientContact ON client.clientPrimaryContactID = clientContact.clientContactID");
    } 

    $q = "SELECT client.*,clientContactName, clientContactEmail, clientContactPhone, clientContactMobile
            FROM client 
                 ".$join." 
                 ".$filter." 
        GROUP BY client.clientID 
        ORDER BY clientName";
    $debug and print "Query: ".$q;
    $db = new db_alloc;
    $db2 = new db_alloc;
    $db->query($q);
    while ($row = $db->next_record()) {
      $print = true;
      $c = new client;
      $c->read_db_record($db);
      $row["clientLink"] = $c->get_client_link();

      if (!$row["clientContactName"]) {
        $q = sprintf("SELECT * FROM clientContact WHERE clientID = %d ORDER BY clientContactName LIMIT 1",$row["clientID"]);
        $db2->query($q);  
        $cc = $db2->row();
        $row["clientContactName"] = $cc["clientContactName"];
        $row["clientContactPhone"] = $cc["clientContactPhone"];
        $row["clientContactEmail"] = $cc["clientContactEmail"];
      }

      $summary.= client::get_list_tr($row,$_FORM);
      $summary_ops[$c->get_id()] = $c->get_value("clientName");
      $rows[$c->get_id()] = $row;
    }

    if ($print && $_FORM["return"] == "html") {
      return "<table class=\"list sortable\">".$summary."</table>";

    } else if ($print && $_FORM["return"] == "dropdown_options") {
      return $summary_ops;

    } else if ($print && $_FORM["return"] == "array") {
      return $rows;

    } else if (!$print && $_FORM["return"] == "html") {
      return "<table style=\"width:100%\"><tr><td colspan=\"10\" style=\"text-align:center\"><b>No Clients Found</b></td></tr></table>";
    }

  }

  function get_list_tr_header($_FORM) {
    if ($_FORM["showHeader"]) {
      $summary = "\n<tr>";
      $_FORM["showClientName"]          and $summary.= "\n<th>Client</th>";
      $_FORM["showClientLink"]          and $summary.= "\n<th>Client</th>";
      $_FORM["showPrimaryContactName"]  and $summary.= "\n<th>Contact Name</th>";
      $_FORM["showPrimaryContactPhone"] and $summary.= "\n<th>Contact Phone</th>";
      $_FORM["showPrimaryContactEmail"] and $summary.= "\n<th>Contact Email</th>";
      $_FORM["showClientStatus"]        and $summary.= "\n<th>Status</th>";
      $summary.="\n</tr>";
      return $summary;
    }
  }

  function get_list_tr($client,$_FORM) {

    $client["clientContactPhone"] or $client["clientContactPhone"] = $client["clientContactMobile"];
    $client["clientContactEmail"] and $client["clientContactEmail"] = "<a href=\"mailto:".$client["clientContactEmail"]."\">".$client["clientContactEmail"]."</a>";

    $summary[] = "<tr>";
    $_FORM["showClientName"]          and $summary[] = "  <td>".$client["clientName"]."&nbsp;</td>";
    $_FORM["showClientLink"]          and $summary[] = "  <td>".$client["clientLink"]."&nbsp;</td>";
    $_FORM["showPrimaryContactName"]  and $summary[] = "  <td>".$client["clientContactName"]."&nbsp;</td>";
    $_FORM["showPrimaryContactPhone"] and $summary[] = "  <td>".$client["clientContactPhone"]."&nbsp;</td>";
    $_FORM["showPrimaryContactEmail"] and $summary[] = "  <td>".$client["clientContactEmail"]."&nbsp;</td>";
    $_FORM["showClientStatus"]        and $summary[] = "  <td>".ucwords($client["clientStatus"])."&nbsp;</td>";
    $summary[] = "</tr>";

    $summary = "\n".implode("\n",$summary);
    return $summary;
  }

  function get_list_vars() {

    return array("return"                   => "[MANDATORY] eg: array | html | dropdown_options"
                ,"clientStatus"             => "Client status eg: current | potential | archived"
                ,"clientName"               => "Client name like *something*"
                ,"contactName"              => "Client Contact name like *something*"
                ,"clientLetter"             => "Client name starts with this letter"
                ,"url_form_action"          => "The submit action for the filter form"
                ,"form_name"                => "The name of this form, i.e. a handle for referring to this saved form"
                ,"dontSave"                 => "Specify that the filter preferences should not be saved this time"
                ,"applyFilter"              => "Saves this filter as the persons preference"
                ,"showHeader"               => "A descriptive html header row"
                ,"showClientName"           => "Shows the clients name"
                ,"showClientLink"           => "Shows a client link"
                ,"showClientStatus"         => "Shows the clients status"
                ,"showPrimaryContactName"   => "Shows the primary contacts name"
                ,"showPrimaryContactPhone"  => "Shows the primary contacts phone"
                ,"showPrimaryContactEmail"  => "Shows the primary contacts email"
                );
  }

  function load_form_data($defaults=array()) {
    global $current_user;

    $page_vars = array_keys(client::get_list_vars());

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

    $rtn["clientStatusOptions"] = page::select_options(array("current"=>"Current", "potential"=>"Potential", "archived"=>"Archived"), $_FORM["clientStatus"]);
    $rtn["clientName"] = $_FORM["clientName"];
    $rtn["contactName"] = $_FORM["contactName"];
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

  function get_url() {
    global $TPL;
    global $sess;
    $url = "client/client.php?&clientID=".$this->get_id();
    return $TPL["url_alloc_client"].$url;
  }

  function get_clientID_from_name($name) {
    static $clients;
    if (!$clients) {
      $db = new db_alloc();
      $q = sprintf("SELECT * FROM client");
      $db->query($q);
      while ($db->next_record()) {
        $clients[$db->f("clientID")] = $db->f("clientName");
      }
    }

    $stack = array();
    foreach ($clients as $clientID => $clientName) {
      similar_text($name,$clientName,$percent);
      $stack[$clientID] = $percent;
    }
    asort($stack);
    end($stack);
    $probable_clientID = key($stack);
    $client_percent = current($stack);
    return array($probable_clientID,$client_percent);
  }

  function get_client_and_project_dropdowns_and_links($clientID=false, $projectID=false) {
    // This function returns dropdown lists and links for both client and
    // project. The two dropdown lists are linked, in that if you change the
    // client, then the project dropdown dynamically updates 
    global $TPL;

    $client = new client;
    $client->set_id($clientID);
    $client->select();

    $options["clientStatus"] = "current";
    $options["return"] = "dropdown_options";
    $ops = client::get_list($options);

    $client_select = "<select size=\"1\" id=\"clientID\" name=\"clientID\" onChange=\"makeAjaxRequest('".$TPL["url_alloc_updateProjectListByClient"]."clientID='+$('#clientID').attr('value'),'projectDropdown')\"><option></option>";
    $client_select.= page::select_options($ops,$clientID,100)."</select>";

    $client_link = $client->get_link();

    $project = new project;
    $project->set_id($projectID);
    $project->select();

    $project_select = '<div id="projectDropdown" style="display:inline">'.$project->get_dropdown_by_client($clientID).'</div>';
    $project_link = $project->get_link();
  
    return array($client_select, $client_link, $project_select, $project_link);
  }

}



?>
