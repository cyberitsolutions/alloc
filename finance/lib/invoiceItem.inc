<?php
define("PERM_FINANCE_UPDATE_APPROVED", 256);

class invoiceItem extends db_entity
{
  var $data_table = "invoiceItem";
  var $display_field_name = "iiMemo";

  function invoiceItem() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_text_field("invoiceItemID");
    $this->data_fields = array("invoiceID"=>new db_text_field("invoiceID")
                               , "iiMemo"=>new db_text_field("iiMemo")
                               , "iiQuantity"=>new db_text_field("iiQuantity")
                               , "iiUnitPrice"=>new db_text_field("iiUnitPrice")
                               , "iiAmount"=>new db_text_field("iiAmount")
                               , "status"=>new db_text_field("status")
      );
    $this->permissions[PERM_FINANCE_UPDATE_APPROVED] = "Update approved transactions";
  }

  function is_owner($person = "") {
    global $current_user;

    if ($person == "") {
      $person = $current_user;
    }

    $db = new db_alloc();
    $db->query("SELECT * FROM transaction WHERE invoiceItemID=".$this->get_id());
    while ($db->next_record()) {
      $transaction = new transaction();
      $transaction->read_db_record($db, false);
      if ($transaction->is_owner($person)) {
        return true;
      }
    }
    return false;
  }
}

class invoiceItem_filter extends db_filter
{
  function invoiceItem_filter($filter_values = "") {
    $this->elements = array("status"=>new boolean_sql_filter_element("current", "Current", $this->get_current_element_sql())
                            , "phase"=>new boolean_sql_filter_element("top", "Top Level", "taskTypeID = 2")
                            , "top"=>new boolean_sql_filter_element("top", "Top Level", "parentTaskID = 0")
                            , "project"=>new foreign_key_filter_element("project", "Project", "projectID")
                            , "parent_task"=>new foreign_key_filter_element("parent_task", "Parent Task", "parentTaskID")
                            , "person"=>new foreign_key_filter_element("person", "Person Responsible", "personID")
      );
  }

  // Returns the SQL used to filter for current tasks
  function get_current_element_sql() {
    $today = date("Y-m-d");
    $one_week = mktime(0, 0, 0, 1, 8) - mktime(0, 0, 0, 1, 1);
    $start_before = date("Y-m-d", mktime() + $one_week);

    $criteria = "dateActualCompletion IS NULL AND 
                (dateActualStart IS NOT NULL OR dateTargetStart <= '$start_before' OR
                 dateTargetStart IS NULL)";
    return $criteria;
  }
}

class invoiceItem_list extends db_list
{
  var $table = "invoiceItem LEFT JOIN invoice ON invoiceItem.invoiceID = invoice.invoiceID";
  var $entity_class = "invoiceItem";
  var $fields = "invoiceItem.*, invoice.invoiceNum, invoice.invoiceDate, invoice.invoiceName";
  var $order_by = "invoiceItemID";
}


?>
