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


?>
