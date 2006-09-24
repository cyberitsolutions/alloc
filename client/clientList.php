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

require_once("../alloc.php");

function show_client($template_name) {
  global $TPL;

  $where = " where 1=1 ";

  if ($_POST["clientStatus"]) {
    $where.= sprintf(" AND clientStatus='%s' ",db_esc($_POST["clientStatus"]));
  }

  if ($_POST["clientN"]) {
    $where.= sprintf(" AND clientName like '%%%s%%'",db_esc($_POST["clientN"]));
  }

  if ($_GET["clientLetter"] && $_GET["clientLetter"] == "A") {
    $where.= " AND (clientName like 'A%' or clientName REGEXP '^[^[:alpha:]]')";
  } else if ($_GET["clientLetter"] && $_GET["clientLetter"] != "ALL") {
    $where.= sprintf(" AND clientName like '%s%%'",db_esc($_GET["clientLetter"]));
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
  global $TPL;
  $TPL["clientStatusOptions"] = get_select_options(array("Current", "Potential", "Archived"), $_POST["clientStatus"]);
  $TPL["clientN"] = $_POST["clientN"];
  $letters = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "ALL");
  foreach($letters as $letter) {
    if ($_GET["clientLetter"] == $letter) {
      $TPL["alphabet_filter"].= "&nbsp;&nbsp;".$letter;
    } else {
      $TPL["alphabet_filter"].= "&nbsp;&nbsp;<a href=\"".$TPL["url_alloc_clientList"]."clientLetter=".$letter."\">".$letter."</a>";
    }
  }

  include_template($template_name);
}

  // Set default filter values
if (!$_POST["clientStatus"] && !$_POST["clientN"] && !$_GET["clientLetter"]) {
  $_GET["clientLetter"] = "A";
  $_POST["clientStatus"] = "Current";
}

if (have_entity_perm("client", PERM_CREATE)) {
  $TPL["nav_links"] = "<a href=\"".$TPL["url_alloc_client"]."\">New Client</a>";
} else {
  $TPL["nav_links"] = "";
}

include_template("templates/clientListM.tpl");
page_close();



?>
