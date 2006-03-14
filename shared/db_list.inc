<?php
class db_list {
  var $table;
  var $entity_class;
  var $fields = "*";
  var $order_by;
  var $entities;
  var $filter;
  var $debug = false;
  var $record_limit;

  function db_list($filter = "", $record_limit = "", $debug = false) {
    $this->debug = $debug;
    if ($filter == "") {
      $this->set_filter(new task_filter());
    } else {
      $this->set_filter($filter);
    }
    if ($record_limit) {
      $this->record_limit = $record_limit;
    }
    $this->load();
  }

  function load() {
    $query = $this->get_sql();
    if ($this->debug) {
      echo "db_list::load() \$query: $query<br>";
    }
    $db = new db_alloc;
    $db->query($query);
#echo "<br><br>Query: ".$query;
    $this->entities = array();
    while ($db->next_record()) {
      $entity_class = $this->entity_class;
      $entity = new $entity_class;
      $entity->read_db_record($db);
      $entity->recalculate();
      $this->entities[$entity->get_id()] = $entity;
    }
  }

  // Gets an array of the list's entities
  function get_entity_array() {
    return $this->entities;
  }

  function set_filter($filter) {
    $this->filter = $filter;
  }

  function get_sql() {
    $sql = "SELECT ".$this->fields;
    $sql.= " FROM ".$this->table;
    $joins = $this->filter->get_joins();
    if ($joins) {
      $sql.= " LEFT JOIN ".$joins;
    }
    $where = $this->filter->get_where();
    if ($where) {
      $sql.= " WHERE ".$where;
    }
    if ($this->order_by) {
      $sql.= " ORDER BY ".$this->order_by;
    }
    if ($this->record_limit) {
      $sql.= " LIMIT ".$this->record_limit;
    }
    return $sql;
  }

  function repeat_template($template) {
    $db = new db_alloc;
    $db->query($this->get_sql());
    while ($db->next_record()) {
      $entity_class = $this->entity_class;
      $entity = new $entity_class;
      $entity->read_db_record($db);
      $entity->recalculate();
      $entity->set_tpl_values();
      include_template($template);
    }
  }
}



?>
