<?php


class alloc_soap {

  function authenticate($username,$password) {
    $person = new person;
    $sess = new Session;
    $row = $person->get_valid_login_row($username,$password); 
    if ($row) {
      $sess->Start($row,false);
      $sess->UseGet();
      $sess->Save();
      return $sess->GetKey();
    } else {
      throw new SoapFault("Server","Authentication Failed(1)."); 
    }
  }  

  function get_current_user($key) {
    $sess = new Session($key);
    if (!$sess->Started()) {
      throw new SoapFault("Server","Authentication Failed(2).");
    } else {
      $person = new person;
      return $person->load_get_current_user($sess->Get("personID"));
    }
  }

  function get_task_comments($key,$taskID) {
    global $current_user; // Always need this :(
    $current_user = $this->get_current_user($key);
    if ($taskID) {
      $task = new task;
      $task->set_id($taskID);
      $task->select();
      return $task->get_task_comments_array();
    }
  }

  function add_timeSheetItem_by_task($key, $task, $duration, $comments) {
    global $current_user; // Always need this :(
    $current_user = $this->get_current_user($key);
    return timeSheet::add_timeSheetItem_by_task($task, $duration, $comments);
  }

  function get_tf_transactions($key,$tfName,$startDate=false,$endDate=false) {
    global $current_user; // Always need this :(
    $current_user = $this->get_current_user($key);
    if ($tfName) {
      $ops["return"] = "array";
      $ops["tfName"] = $tfName;
      #$ops["personID"] = $current_user->get_id();
      $ops["startDate"] = $startDate;
      $ops["endDate"] = $endDate;
      return transaction::get_transaction_list($ops);
    }
  }

  function get_table_rows($key,$tableName=false) {
    global $current_user; // Always need this :(
    $current_user = $this->get_current_user($key);
    if (class_exists($tableName)) {
      $t = new $tableName;
      $rows = $t->get_assoc_array();
      return $rows;
    } else {
      throw new SoapFault("Server","Table '".$tableName."' does not exist."); 
    }
  }






} 




?>
