<?php
// Class to handle caching
// 
class alloc_cache {


  var $db = "db_alloc";
  var $tables_to_cache = array();
  var $cache; 

  
  // Initializer
  function alloc_cache($arr=array()) {
    $this->tables_to_cache = $arr;
  }

  // Loads up assoc array[$table][primarykey] = row;
  function load_cache() {
    $db = new $this->db;

    foreach ($this->tables_to_cache as $table) {
      $db->query("SELECT * FROM ".$table);
      while ($db->next_record()) {
        $this->cache[$table][$db->f($table."ID")] = $db->Record;
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
