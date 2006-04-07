<?php
require_once("alloc.inc");

function show_client($template_name) {
  global $TPL, $clientStatus, $clientN, $clientLetter;

  $where = " where 1=1 ";

  if ($clientStatus) {
    $where.= sprintf(" AND clientStatus='%s' ", $clientStatus);
  }

  if ($clientN) {
    $where.= " AND clientName like '%$clientN%'";
  }

  if ($clientLetter && $clientLetter == "A") {
    $where.= " AND (clientName like 'A%' or clientName REGEXP '^[^[:alpha:]]')";
  } else if ($clientLetter && $clientLetter != "ALL") {
    $where.= " AND clientName like '".$clientLetter."%'";
  }

  $db = new db_alloc;
  $query = "SELECT * FROM client $where ORDER BY clientName";
  $db->query($query);
  while ($db->next_record()) {
    $client = new client;
    $client->read_db_record($db);
    $client->set_tpl_values(DST_HTML_ATTRIBUTE, "client_");
    if ($client->get_value('clientPrimaryContactID') != "NULL") {
      $clientContact = new clientContact;
      $clientContact->set_id($client->get_value('clientPrimaryContactID'));
      $clientContact->select();
      $clientContact->set_tpl_values(DST_HTML_ATTRIBUTE, "contact_");
      if ($TPL["contact_clientContactEmail"]) {
        $TPL["contact_clientContactEmail"] = "<a href=\"mailto:".$TPL["contact_clientContactEmail"]."\">".$TPL["contact_clientContactEmail"]."</a>";
      }
    }
    $TPL["odd_even"] = $TPL["odd_even"] == "odd" ? "even" : "odd";
    include_template($template_name);
  }
}

function show_filter($template_name) {
  global $clientStatus, $TPL, $clientN, $clientLetter;
  $TPL["clientStatusOptions"] = get_options_from_array(array("Current", "Potential", "Archived"), $clientStatus, false);
  $TPL["clientN"] = $clientN;
  $letters = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "ALL");
  foreach($letters as $letter) {
    if ($clientLetter == $letter) {
      $TPL["alphabet_filter"].= "&nbsp;&nbsp;".$letter;
    } else {
      $TPL["alphabet_filter"].= "&nbsp;&nbsp;<a href=\"".$TPL["url_alloc_clientList"]."clientLetter=".$letter."\">".$letter."</a>";
    }
  }

  include_template($template_name);
}

  // Set default filter values
if (!isset($clientStatus) && !isset($clientN) && !isset($clientLetter)) {
  $clientLetter = "A";
  $clientStatus = "Current";
}

if (have_entity_perm("client", PERM_CREATE)) {
  $TPL["nav_links"] = "<a href=\"".$TPL["url_alloc_client"]."\">New Client</a>";
} else {
  $TPL["nav_links"] = "";
}

include_template("templates/clientListM.tpl");
page_close();



?>
