<?php

/*
 *
 * Copyright 2006, Alex Lance, Clancy Malcolm, Cybersource Pty. Ltd.
 * 
 * This file is part of AllocPSA <info@cyber.com.au>.
 * 
 * AllocPSA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * AllocPSA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * AllocPSA; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */

class quick_links_home_item extends home_item {
  function quick_links_home_item() {
    home_item::home_item("quick_links", "Quick Links", "home", "quickLinksH.tpl", "narrow");
  }

  function show_links($template_name) {
    global $toolbar_items, $TPL;
    reset($toolbar_items);
    while (list(, $item) = each($toolbar_items)) {
      $TPL["link_url"] = $item->get_url();
      $TPL["link_label"] = $item->get_label();
      include_template($this->get_template_dir().$template_name, $this);
    }
  }
}



?>
