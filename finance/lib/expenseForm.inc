<?php
class expenseForm extends db_entity {
  var $data_table = "expenseForm";
  var $fire_events = true;

  function expenseForm() {
    $this->db_entity();
    $this->key_field = new db_text_field("expenseFormID");
    $this->data_fields = array("expenseFormModifiedUser"=>new db_text_field("expenseFormModifiedUser")
                               , "lastModified"=>new db_text_field("lastModified")
                               , "paymentMethod"=>new db_text_field("paymentMethod")
                               , "reimbursementRequired"=>new db_text_field("reimbursementRequired", "Reimbursement Required", "", array("empty_to_null"=>false))
                               , "transactionRepeatID"=>new db_text_field("transactionRepeatID", "Transaction Repeat", "", array("empty_to_null"=>false))
                               , "chequeNumber"=>new db_text_field("chequeNumber")
                               , "chequeDate"=>new db_text_field("chequeDate")
                               , "enteredBy"=>new db_text_field("enteredBy")
                               , "expenseFormFinalised"=>new db_text_field("expenseFormFinalised", "Expense Finalised", "", array("empty_to_null"=>false))
      );
  }

  function is_owner($person = "") {
    global $current_user;

    if ($person == "") {
      $person = $current_user;
    }
    // Return true if this user created the expense form
    if ($person->get_id() == $this->get_value("enteredBy", DST_VARIABLE)) {
      return true;
    }
    // Return true if any of the transactions on the expense form are accessible by the current user
    $query = "SELECT * FROM transaction WHERE expenseFormID=".$this->get_id();
    $db = new db_alloc;
    $db->query($query);
    while ($db->next_record()) {
      $transaction = new transaction;
      $transaction->read_db_record($db, false);
      if ($transaction->is_owner($person)) {
        return true;
      }
    }

    if ($current_user->have_role("admin") || $current_user->have_role("god")) {
      return true;
    }

    return false;
  }

  // This sets the status of the expense form.
  // Actually, the expense form doesn't have its own status - this sets the status of the transactions on the expense form
  function set_status($status) {
    global $current_user;

    $transactions = $this->get_foreign_objects("transaction");
    while (list(, $transaction) = each($transactions)) {
      $transaction->set_value("status", $status);
      $transaction->save();
    }
  }

  function insert() {
    global $current_user;
    $this->set_value("enteredBy", $current_user->get_id());
    db_entity::insert();
  }

  function delete_transactions($transactionID="") {
    global $TPL;

    $transactionID and $extra_sql = sprintf("AND transactionID = %d",$transactionID);

    $db = new db_alloc;
    if ($this->is_owner()) {
      $db->query(sprintf("DELETE FROM transaction WHERE expenseFormID = %d %s",$this->get_id(),$extra_sql));
      $transactionID and $TPL["message_good"][] = "Expense Form Line Item deleted.";
    }
  }


  
}



?>
