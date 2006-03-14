<?php
define("PERM_FINANCE_WRITE_INVOICE_TRANSACTION", 256);
define("PERM_FINANCE_WRITE_FREE_FORM_TRANSACTION", 512);
define("PERM_FINANCE_WRITE_WAGE_TRANSACTION", 1024);
define("PERM_FINANCE_CREATE_TRANSACTION_FROM_REPEAT", 2048);
define("PERM_FINANCE_WRITE_APPROVED_TRANSACTION", 4096);
define("PERM_FINANCE_UPLOAD_EXPENSES_FILE", 16384);
define("PERM_FINANCE_RECONCILIATION_REPORT", 32768);

class transaction extends db_entity
{
  var $data_table = "transaction";
  var $display_field_name = "product";

  function transaction() {
    $this->db_entity();
    $this->key_field = new db_text_field("transactionID");
    $this->data_fields = array("companyDetails"=>new db_text_field("companyDetails", "Company Details", "", array("empty_to_null"=>false))
                               , "product"=>new db_text_field("product")
                               , "amount"=>new db_text_field("amount")
                               , "status"=>new db_text_field("status")
                               , "expenseFormID"=>new db_text_field("expenseFormID", "Expense Form ID", "", array("empty_to_null"=>false))
                               , "invoiceItemID"=>new db_text_field("invoiceItemID")
                               , "tfID"=>new db_text_field("tfID")
                               , "projectID"=>new db_text_field("projectID")
                               , "transactionModifiedUser"=>new db_text_field("transactionModifiedUser")
                               , "lastModified"=>new db_text_field("lastModified")
                               , "dateEntered"=>new db_text_field("dateEntered")
                               , "quantity"=>new db_text_field("quantity")
                               , "transactionDate"=>new db_text_field("transactionDate")
                               , "transactionType"=>new db_text_field("transactionType")
                               , "timeSheetID"=>new db_text_field("timeSheetID")
      );

    $this->permissions[PERM_FINANCE_WRITE_INVOICE_TRANSACTION] = "Add/update/delete invoice transaction";
    $this->permissions[PERM_FINANCE_WRITE_FREE_FORM_TRANSACTION] = "Add/update/delete free-form transaction";
    $this->permissions[PERM_FINANCE_WRITE_WAGE_TRANSACTION] = "Add/update/delete wage transaction";
    $this->permissions[PERM_FINANCE_CREATE_TRANSACTION_FROM_REPEAT] = "Create from repeating transactions";
    $this->permissions[PERM_FINANCE_WRITE_APPROVED_TRANSACTION] = "Approve/Reject transactions";
    $this->permissions[PERM_FINANCE_UPLOAD_EXPENSES_FILE] = "Upload expenses file";
    $this->permissions[PERM_FINANCE_RECONCILIATION_REPORT] = "View reconciliation report";

    $this->set_value("quantity", 1);
  }

  function check_write_perms() {
    if ($this->get_value("status") != "pending") {
      $this->check_perm(PERM_FINANCE_WRITE_APPROVED_TRANSACTION);
    }
    if ($this->get_value("transactionType") == "invoice") {
      $this->check_perm(PERM_FINANCE_WRITE_INVOICE_TRANSACTION);
    }
    if ($this->get_value("transactionType") == "salary") {
      $this->check_perm(PERM_FINANCE_WRITE_WAGE_TRANSACTION);
    }
  }

  function insert() {
    $this->set_value("dateEntered", date("Y-m-d"));
    $this->check_write_perms();
    db_entity::insert();
  }

  function update() {
    $this->check_write_perms();
    db_entity::update();
  }

  function delete() {
    $this->check_write_perms();
    db_entity::delete();
  }

  function is_owner($person = "") {
    global $current_user;
    if ($person == "") {
      $person = $current_user;
    }

    $tf = $this->get_foreign_object("tf");
    return $tf->is_owner($person);
  }
}



?>
