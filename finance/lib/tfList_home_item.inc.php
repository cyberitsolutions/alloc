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

class tfList_home_item extends home_item {
  function tfList_home_item() {
    home_item::home_item("", "Tagged Funds", "finance", "tfListH.tpl", "narrow",20);
  }


  function show_tfList($template_name) {
    global $TPL, $current_user;

    $db = new db_alloc;
    $q = sprintf("SELECT * FROM tfPerson WHERE personID = %d",$current_user->get_id());
    $db->query($q);

    while ($db->next_record()) {
      $tf = new tf;
      $tf->set_id($db->f("tfID"));
      $tf->select();

      if ($tf->get_value("status") != 'active') {
        continue;
      }

      $tf->set_tpl_values();

      if (have_entity_perm("transaction", PERM_READ, $current_user, $tf->is_owner())) {
        $TPL["tfBalance"] = sprintf("%0.2f",$tf->get_balance());
        $TPL["pending_amount"] = sprintf("%0.2f",$tf->get_balance(array("status"=>"pending")));
        $grand_total += $tf->get_balance();
      } else {
        $TPL["tfBalance"] = "not available";
      }
      $TPL["odd_even"] = $TPL["odd_even"] == "even" ? "odd" : "even";

      $nav_links = $tf->get_nav_links();
      $TPL["data"] = implode(" | ", $nav_links);
      include_template($template_name);
    }

    $TPL["grand_total"] = number_format($grand_total, 2);

  }




}



?>
