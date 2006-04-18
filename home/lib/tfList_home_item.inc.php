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

class tfList_home_item extends home_item {
  function tfList_home_item() {
    home_item::home_item("", "TF", "home", "tfListH.tpl", "narrow");
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
      $tf->set_tpl_values();

      if (have_entity_perm("transaction", PERM_READ, $current_user, $tf->is_owner())) {
        $TPL["tfBalance"] = number_format($tf->get_balance(), 2);
        $grand_total += $tf->get_balance();
      } else {
        $TPL["tfBalance"] = "not available";
      }
      $TPL["odd_even"] = $TPL["odd_even"] == "even" ? "odd" : "even";

      $nav_links = $tf->get_nav_links();
      $TPL["data"] = format_nav_links($nav_links);
      include_template($template_name);
    }

    $TPL["grand_total"] = number_format($grand_total, 2);

  }




}



?>
