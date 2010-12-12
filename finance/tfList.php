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

require_once("../alloc.php");

$current_user->check_employee();

if ($_REQUEST["owner"]) {
  $TPL["owner_checked"] = " checked";
  $filter[] = sprintf("tfPerson.personID = %d",$current_user->get_id());
} else {
  $TPL["owner_checked"] = "";
}

if ($_REQUEST["showall"]) {
  $TPL["showall_checked"] = " checked";
} else {
  $filter[] = "tfActive = 1";
  $filter2[] = "tfActive = 1";
}

$TPL["main_alloc_title"] = "TF List - ".APPLICATION_NAME;

include_template("templates/tfListM.tpl");











function show_tf_list($template_name) {
  global $TPL, $filter, $filter2;

  if (is_array($filter) && count($filter)) {
    $f = " AND ".implode(" AND ",$filter);
  }
  if (is_array($filter) && count($filter)) {
    $f2 = " AND ".implode(" AND ",$filter);
  }

  
  $db = new db_alloc;
  $q = sprintf("SELECT transaction.tfID as id, tf.tfName, transactionID,
                       sum(amount * pow(10,-currencyType.numberToBasic) * exchangeRate) AS balance
                  FROM transaction
             LEFT JOIN currencyType ON currencyType.currencyTypeID = transaction.currencyTypeID
             LEFT JOIN tf on transaction.tfID = tf.tfID
                 WHERE 1 AND transaction.status = 'approved' %s
              GROUP BY transaction.tfID"
              ,$f2);
  $db->query($q);
  while ($row = $db->row()) {
    $adds[$row["id"]] = $row["balance"];
  }

      
  $q = sprintf("SELECT transaction.fromTfID as id, tf.tfName, transactionID,
                       sum(amount * pow(10,-currencyType.numberToBasic) * exchangeRate) AS balance
                  FROM transaction
             LEFT JOIN currencyType ON currencyType.currencyTypeID = transaction.currencyTypeID
             LEFT JOIN tf on transaction.fromTfID = tf.tfID
                 WHERE 1 AND transaction.status = 'approved' %s
              GROUP BY transaction.fromTfID"
              ,$f2);
  $db->query($q);
  while ($row = $db->row()) {
    $subs[$row["id"]] = $row["balance"];
  }

  $q = sprintf("SELECT tf.* 
                  FROM tf 
             LEFT JOIN tfPerson ON tf.tfID = tfPerson.tfID 
                 WHERE 1 %s 
              GROUP BY tf.tfID 
              ORDER BY tf.tfName",$f);  

  $db->query($q);
  while ($db->next_record()) {
    $tf = new tf;
    $tf->read_db_record($db);
    $tf->set_values();

    $total = $adds[$db->f("tfID")] - $subs[$db->f("tfID")];

    if (have_entity_perm("transaction", PERM_READ, $current_user, $tf->is_owner())) {
      $TPL["tfBalance"] = page::money(config::get_config_item("currency"),$total,"%s%m %c");
      $TPL["grand_total"] += $total;
    } else {
      $TPL["tfBalance"] = "not available";
    }

    $TPL["odd_even"] = $TPL["odd_even"] == "odd" ? "even" : "odd";
    $nav_links = $tf->get_nav_links();
    $TPL["nav_links"] = implode(" ", $nav_links);
    $TPL["tfActive_label"] = "";
    $tf->get_value("tfActive") and $TPL["tfActive_label"] = "Y";
    include_template($template_name);
  }
}


?>
