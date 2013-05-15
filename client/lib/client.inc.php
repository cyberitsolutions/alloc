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

class client extends db_entity {
  public $classname = "client";
  public $data_table = "client";
  public $display_field_name = "clientName";
  public $key_field = "clientID";
  public $data_fields = array("clientName"
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
                             ,"clientCategory"
                             );


  function delete() {
    // delete all contacts and comments linked with this client as well
    $db = new db_alloc();
    $query = prepare("SELECT * FROM clientContact WHERE clientID=%d", $this->get_id());
    $db->query($query);
    while ($db->next_record()) {
      $clientContact = new clientContact();
      $clientContact->read_db_record($db);
      $clientContact->delete();
    }
    $query = prepare("SELECT * FROM comment WHERE commentType = 'client' and commentLinkID=%d", $this->get_id());
    $db->query($query);
    while ($db->next_record()) {
      $comment = new comment();
      $comment->read_db_record($db);
      $comment->delete();
    }
    return parent::delete();
  }

  function is_owner() {
    $current_user = &singleton("current_user");
    return $current_user->is_employee();
  }

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
    $db = new db_alloc();
    if ($clientStatus) {
      $q = prepare("SELECT clientID as value, clientName as label 
                      FROM client 
                     WHERE clientStatus = '%s' 
                        OR clientID = %d 
                  ORDER BY clientName"
                    ,$clientStatus,$clientID);
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
    $db = new db_alloc();
    $q = prepare("SELECT clientContactName as label, clientContactID as value FROM clientContact WHERE clientID = %d",$clientID);
    $options = page::select_options($q,$clientContactID,100);
    return "<select id=\"clientContactID\" name=\"clientContactID\" style=\"width:100%\"><option value=\"\">".$options."</select>";
  }
 
  function get_name($_FORM=array()) {
    if ($_FORM["return"] == "html") { 
      return $this->get_value("clientName",DST_HTML_DISPLAY);
    } else {
      return $this->get_value("clientName");
    }
  }

  function get_client_link($_FORM=array()) {
    global $TPL;
    return "<a href=\"".$TPL["url_alloc_client"]."clientID=".$this->get_id()."\">".$this->get_name($_FORM)."</a>";
  }

  function get_list_filter($filter=array()) {
    $current_user = &singleton("current_user");

    // If they want starred, load up the clientID filter element
    if ($filter["starred"]) {
      foreach ((array)$current_user->prefs["stars"]["client"] as $k=>$v) {
        $filter["clientID"][] = $k;
      }
      is_array($filter["clientID"]) or $filter["clientID"][] = -1;
    }

    // Filter on clientID
    $filter["clientID"] and $sql[] = sprintf_implode("client.clientID = %d", $filter["clientID"]);

    // No point continuing if primary key specified, so return
    if ($filter["clientID"] || $filter["starred"]) {
      return $sql;
    }

    $filter["clientStatus"]   and $sql[] = sprintf_implode("client.clientStatus = '%s'", $filter["clientStatus"]);
    $filter["clientCategory"] and $sql[] = sprintf_implode("IFNULL(client.clientCategory,'') = '%s'", $filter["clientCategory"]);
    $filter["clientName"]     and $sql[] = sprintf_implode("IFNULL(clientName,'') LIKE '%%%s%%'",$filter["clientName"]);
    $filter["contactName"]    and $sql[] = sprintf_implode("IFNULL(clientContactName,'') LIKE '%%%s%%'",$filter["contactName"]);

    if ($filter["clientLetter"] && $filter["clientLetter"] == "A") {
      $sql[] = "(clientName like 'A%' or clientName REGEXP '^[^[:alpha:]]')";
    } else if ($filter["clientLetter"] && $filter["clientLetter"] != "ALL") {
      $sql[] = sprintf_implode("clientName LIKE '%s%%'",$filter["clientLetter"]);
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

    if (is_array($filter) && count($filter)) {
      $filter = " WHERE ".implode(" AND ",$filter);
    }

    $cc = config::get_config_item("clientCategories");
    foreach ($cc as $k => $v) {
      $clientCategories[$v["value"]] = $v["label"];
    }

    $q = "SELECT client.*,clientContactName, clientContactEmail, clientContactPhone, clientContactMobile
            FROM client 
       LEFT JOIN clientContact ON client.clientID = clientContact.clientID AND clientContact.clientContactActive = 1
                 ".$filter." 
        GROUP BY client.clientID 
        ORDER BY clientName,clientContact.primaryContact asc";
    $debug and print "Query: ".$q;
    $db = new db_alloc();
    $db2 = new db_alloc();
    $db->query($q);
    while ($row = $db->next_record()) {
      $print = true;
      $c = new client();
      $c->read_db_record($db);
      $row["clientCategoryLabel"] = $clientCategories[$c->get_value("clientCategory")];
      $row["clientLink"] = $c->get_client_link($_FORM);
      $row["clientContactEmail"] and $row["clientContactEmail"] = "<a href=\"mailto:".page::htmlentities($row["clientContactName"]." <".$row["clientContactEmail"].">")."\">".page::htmlentities($row["clientContactEmail"])."</a>";

      $rows[$c->get_id()] = $row;
    }
    return (array)$rows;
  }

  function get_list_vars() {

    return array("clientStatus"             => "Client status eg: Current | Potential | Archived"
                ,"clientCategory"           => "Client category eg: 1-7"
                ,"clientName"               => "Client name like *something*"
                ,"contactName"              => "Client Contact name like *something*"
                ,"clientLetter"             => "Client name starts with this letter"
                ,"url_form_action"          => "The submit action for the filter form"
                ,"form_name"                => "The name of this form, i.e. a handle for referring to this saved form"
                ,"dontSave"                 => "Specify that the filter preferences should not be saved this time"
                ,"applyFilter"              => "Saves this filter as the persons preference"
                );
  }

  function load_form_data($defaults=array()) {
    $current_user = &singleton("current_user");

    $page_vars = array_keys(client::get_list_vars());

    $_FORM = get_all_form_data($page_vars,$defaults);

    if (!$_FORM["applyFilter"]) {
      $_FORM = $current_user->prefs[$_FORM["form_name"]];
      if (!isset($current_user->prefs[$_FORM["form_name"]])) {
        $_FORM["clientLetter"] = "A";
        $_FORM["clientStatus"] = "Current";
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

    $db = new db_alloc();

    // Load up the forms action url
    $rtn["url_form_action"] = $_FORM["url_form_action"];

    $m = new meta("clientStatus");
    $clientStatus_array = $m->get_assoc_array("clientStatusID","clientStatusID");
    $rtn["clientStatusOptions"] = page::select_options($clientStatus_array, $_FORM["clientStatus"]);
    $rtn["clientName"] = $_FORM["clientName"];
    $rtn["contactName"] = $_FORM["contactName"];
    $letters = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "ALL");
    foreach($letters as $letter) {
      if ($_FORM["clientLetter"] == $letter) {
        $rtn["alphabet_filter"].= "&nbsp;&nbsp;".$letter;
      } else {
        $rtn["alphabet_filter"].= "&nbsp;&nbsp;<a href=\"".$TPL["url_alloc_clientList"]."clientLetter=".$letter."&clientStatus=Current&applyFilter=1\">".$letter."</a>";
      }
    }
    
    $clientCategory = $_FORM["clientCategory"];
    $clientCategories = config::get_config_item("clientCategories") or $clientCategories = array();
    foreach ($clientCategories as $k => $v) {
      $cc[$v["value"]] = $v["label"];
    }
    $rtn["clientCategoryOptions"] = page::select_options($cc,$clientCategory);

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
      $q = prepare("SELECT * FROM client");
      $db->query($q);
      while ($db->next_record()) {
        $clients[$db->f("clientID")] = $db->f("clientName");
      }
    }

    $stack = array();
    foreach ($clients as $clientID => $clientName) {
      similar_text(strtolower($name),strtolower($clientName),$percent);
      $stack[$clientID] = $percent;
    }
    asort($stack);
    end($stack);
    $probable_clientID = key($stack);
    $client_percent = current($stack);
    return array($probable_clientID,$client_percent);
  }

  function get_client_and_project_dropdowns_and_links($clientID=false, $projectID=false, $onlymine=false) {
    // This function returns dropdown lists and links for both client and
    // project. The two dropdown lists are linked, in that if you change the
    // client, then the project dropdown dynamically updates 
    global $TPL;

    $project = new project();
    $project->set_id($projectID);
    $project->select();
    if (!$clientID) {
      $clientID = $project->get_value("clientID");
    }

    $client = new client();
    $client->set_id($clientID);
    $client->select();

    $options["clientStatus"] = "Current";
    $ops = client::get_list($options);
    $ops = array_kv($ops,"clientID","clientName");
    $client->get_id() and $ops[$client->get_id()] = $client->get_value("clientName");
    $client_select = "<select id=\"clientID\" name=\"clientID\" onChange=\"makeAjaxRequest('".$TPL["url_alloc_updateProjectListByClient"]."clientID='+$('#clientID').attr('value')+'&onlymine=".sprintf("%d",$onlymine)."','projectDropdown')\"><option></option>";
    $client_select.= page::select_options($ops,$clientID,100)."</select>";

    $client_link = $client->get_link();


    $project_select = '<div id="projectDropdown" style="display:inline">'.$project->get_dropdown_by_client($clientID,$onlymine).'</div>';
    $project_link = $project->get_link();
  
    return array($client_select, $client_link, $project_select, $project_link);
  }

  function update_search_index_doc(&$index) {
    $p =& get_cached_table("person");
    $clientModifiedUser = $this->get_value("clientModifiedUser");
    $clientModifiedUser_field = $clientModifiedUser." ".$p[$clientModifiedUser]["username"]." ".$p[$clientModifiedUser]["name"];

    $this->get_value("clientStreetAddressOne") and $postal[] = $this->get_value("clientStreetAddressOne");
    $this->get_value("clientSuburbOne")        and $postal[] = $this->get_value("clientSuburbOne");
    $this->get_value("clientStateOne")         and $postal[] = $this->get_value("clientStateOne");
    $this->get_value("clientPostcodeOne")      and $postal[] = $this->get_value("clientPostcodeOne");
    $this->get_value("clientCountryOne")       and $postal[] = $this->get_value("clientCountryOne");
    $p = implode("\n",(array)$postal);
    $p and $p = "Postal Address:\n".$p;

    $this->get_value("clientStreetAddressTwo") and $street[] = $this->get_value("clientStreetAddressTwo");
    $this->get_value("clientSuburbTwo")        and $street[] = $this->get_value("clientSuburbTwo");
    $this->get_value("clientStateTwo")         and $street[] = $this->get_value("clientStateTwo");
    $this->get_value("clientPostcodeTwo")      and $street[] = $this->get_value("clientPostcodeTwo");
    $this->get_value("clientCountryTwo")       and $street[] = $this->get_value("clientCountryTwo");
    $s = implode("\n",(array)$street);
    $s and $s = "Street Address:\n".$s;

    $p && $s and $p.= "\n\n";
    $addresses = $p.$s;

    $this->get_value("clientPhoneOne") and $ph = "Ph: ".$this->get_value("clientPhoneOne");
    $this->get_value("clientFaxOne")   and $fx = "Fax: ".$this->get_value("clientFaxOne");

    $ph and $ph = " ".$ph;
    $fx and $fx = " ".$fx;
    $name = $this->get_name().$ph.$fx;

    $q = prepare("SELECT * FROM clientContact WHERE clientID = %d",$this->get_id());
    $db = new db_alloc();
    $db->query($q);
    while ($row = $db->row()) {
      $c.= $nl.$row["clientContactName"];
      $row["clientContactEmail"]         and $c.= " <".$row["clientContactEmail"].">";
      $c.= " | ";
      $row["clientContactStreetAddress"] and $c.= " ".$row["clientContactStreetAddress"];
      $row["clientContactSuburb"]        and $c.= " ".$row["clientContactSuburb"];
      $row["clientContactState"]         and $c.= " ".$row["clientContactState"];
      $row["clientContactPostcode"]      and $c.= " ".$row["clientContactPostcode"];
      $row["clientContactCountry"]       and $c.= " ".$row["clientContactCountry"];
      $c.= " | ";
      $row["clientContactPhone"]         and $c.= " Ph: ".$row["clientContactPhone"];
      $row["clientContactMobile"]        and $c.= " Mob: ".$row["clientContactMobile"];
      $row["clientContactFax"]           and $c.= " Fax: ".$row["clientContactFax"];
      $row["primaryContact"]             and $c.= " Primary contact";
      $c.= " | ";
      $row["clientContactOther"]         and $c.= " ".$row["clientContactOther"];
      $nl = "|+|=|";
    }
    $c and $contacts = $c;

    $doc = new Zend_Search_Lucene_Document();
    $doc->addField(Zend_Search_Lucene_Field::Keyword('id'   ,$this->get_id()));
    $doc->addField(Zend_Search_Lucene_Field::Text('name'    ,$name,"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('desc'    ,$addresses,"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('contact' ,$contacts,"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('status'  ,$this->get_value("clientStatus"),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('modifier',$clientModifiedUser_field,"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('dateModified',str_replace("-","",$this->get_value("clientModifiedTime")),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('category',$this->get_value("clientCategory"),"utf-8"));
    $doc->addField(Zend_Search_Lucene_Field::Text('dateCreated',str_replace("-","",$this->get_value("clientCreatedTime")),"utf-8"));
    $index->addDocument($doc);
  }

  function format_address($type="street", $map_link=true) {

    if ($type == "postal") {
      $f1 = $this->get_value("clientStreetAddressOne",DST_HTML_DISPLAY);
      $f2 = $this->get_value("clientSuburbOne",DST_HTML_DISPLAY);
      $f3 = $this->get_value("clientStateOne",DST_HTML_DISPLAY);
      $f4 = $this->get_value("clientPostcodeOne",DST_HTML_DISPLAY);
      $f5 = $this->get_value("clientCountryOne",DST_HTML_DISPLAY);

    } else if ($type == "street") {
      $f1 = $this->get_value("clientStreetAddressTwo",DST_HTML_DISPLAY);
      $f2 = $this->get_value("clientSuburbTwo",DST_HTML_DISPLAY);
      $f3 = $this->get_value("clientStateTwo",DST_HTML_DISPLAY);
      $f4 = $this->get_value("clientPostcodeTwo",DST_HTML_DISPLAY);
      $f5 = $this->get_value("clientCountryTwo",DST_HTML_DISPLAY);
    }

    if ($f1 != "") {
      $str = $f1;
      $f2 and $str.= "<br>".$f2;
      $f3 and $str.= " ".$f3;
      $f4 and $str.= " ".$f4;
      $f5 and $str.= "<br>".$f5;

      // Create a map link, probably for google maps
      // a street address and postcode works reasonably well
      if ($map_link && !empty($f1) && !empty($f4)) {
        $cfg = new config();
        $map_base = $cfg->get_config_item('mapURL');
        $address = str_replace("%ad", urlencode(implode(", ",array($f1,$f2,$f3,$f4,$f5))), $map_base);
        $str .= '<br><br><a href="' . $address . '">View map</a>';
      }
    }

    return $str;
  }

  function get_all_parties($clientID=false) {
    if (!$clientID && is_object($this)) {
      $clientID = $this->get_id();
    }
    if ($clientID) {
      // Get all client contacts 
      $db = new db_alloc();
      $q = prepare("SELECT clientContactName, clientContactEmail, clientContactID 
                      FROM clientContact 
                     WHERE clientID = %d
                       AND clientContactActive = 1
                     ",$clientID);
      $db->query($q);
      while ($db->next_record()) {
        $interestedPartyOptions[$db->f("clientContactEmail")] = array("name"=>$db->f("clientContactName"),"external"=>"1","clientContactID"=>$db->f("clientContactID"));
      }
    }
    // return an aggregation of the current task/proj/client parties + the existing interested parties
    $interestedPartyOptions = interestedParty::get_interested_parties("client",$clientID,$interestedPartyOptions);
    return (array)$interestedPartyOptions;
  }

  function get_list_html($rows=array(),$ops=array()) {
    global $TPL;
    $TPL["clientListRows"] = $rows;
    $TPL["_FORM"] = $ops;
    include_template(dirname(__FILE__)."/../templates/clientListS.tpl");
  }
}



?>
