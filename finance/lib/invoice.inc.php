<?php
class invoice extends db_entity {
  var $data_table = "invoice";
  var $display_field_name = "invoiceName";
  function invoice() {
    $this->db_entity();
    $this->key_field = new db_text_field("invoiceID");
    $this->data_fields = array("invoiceName"=>new db_text_field("invoiceName")
                               , "invoiceDate"=>new db_text_field("invoiceDate")
                               , "invoiceNum"=>new db_text_field("invoiceNum")
                               , "invoiceName"=>new db_text_field("invoiceName")
      );
  }

  function is_owner($person = "") {
    global $current_user;

    if ($person == "") {
      $person = $current_user;
    }

    $db = new db_alloc();
    $db->query("SELECT * FROM invoiceItem WHERE invoiceID=".$this->get_id());
    while ($db->next_record()) {
      $invoice_item = new invoiceItem();
      if ($invoice_item->read_db_record($db, false)) {
        if ($invoice_item->is_owner($person)) {
          return true;
        }
      }
    }
    return false;
  }

}



?>
