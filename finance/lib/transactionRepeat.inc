<?php
class transactionRepeat extends db_entity {
  var $data_table = "transactionRepeat";
  var $display_field_name = "product";


  function transactionRepeat() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_text_field("transactionRepeatID");
    $this->data_fields = array("companyDetails"=>new db_text_field("companyDetails", "Company Details", "", array("empty_to_null"=>false))
                               , "payToName"=>new db_text_field("payToName", "Pay To Name", "", array("empty_to_null"=>false))
                               , "payToAccount"=>new db_text_field("payToAccount", "Pay To Account", "", array("empty_to_null"=>false))
                               , "tfID"=>new db_text_field("tfID")

                               , "emailOne"=>new db_text_field("emailOne")
                               , "emailTwo"=>new db_text_field("emailTwo")

                               , "transactionRepeatModifiedUser"=>new db_text_field("transactionRepeatModifiedUser")
                               , "reimbursementRequired"=>new db_text_field("reimbursementRequired", "Reimbursement Required", "", array("empty_to_null"=>false))
                               , "lastModified"=>new db_text_field("lastModified")
                               , "dateEntered"=>new db_text_field("dateEntered")
                               , "transactionStartDate"=>new db_text_field("transactionStartDate")
                               , "transactionFinishDate"=>new db_text_field("transactionFinishDate")

                               , "paymentBasis"=>new db_text_field("paymentBasis")
                               , "amount"=>new db_text_field("amount")
                               , "product"=>new db_text_field("product")
                               , "status"=>new db_text_field("status")

                               , "transactionType"=>new db_text_field("transactionType")


      );

  }

  function is_owner() {
    $tf = new tf;
    $tf->set_id($this->get_value("tfID"));
    $tf->select();
    return $tf->is_owner();
  }


  function insert() {
    $this->set_value("dateEntered", date("Y-m-d"));
    db_entity::insert();
  }

}



?>
