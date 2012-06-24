<?php

class db_alloc extends db {
  function __construct() {
    parent::__construct(ALLOC_DB_USER,ALLOC_DB_PASS,ALLOC_DB_HOST,ALLOC_DB_NAME);
  }
}

?>
