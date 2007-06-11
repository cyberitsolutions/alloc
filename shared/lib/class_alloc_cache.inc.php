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

// Class to handle caching
// 
class alloc_cache {

  var $db = "db_alloc";
  var $tables_to_cache = array();
  var $cache; 

  
  // Initializer
  function alloc_cache() {
  }

  // Singleton
  function get_cache() {
    static $instance;
    if (!$instance) {
      $instance = new alloc_cache();
    }
    return $instance;
  }

  // Loads up assoc array[$table][primarykey] = row;
  function load_cache($table) {
    $db = new $this->db;

    if (!$this->cache[$table]) {
      $db->query("SELECT * FROM ".$table);
      while ($row = $db->next_record()) {
        $this->cache[$table][$db->f($table."ID")] = $row;
      }
    }

  }

  function get_cached_table($table) {
    return $this->cache[$table];
  }

  function set_cached_table($name, $table) {
    $this->cache[$name] = $table;
  }


}


?>
