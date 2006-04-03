<?php
class finance_module extends module {
  var $db_entities = array("tf", "transaction", "expenseForm", "invoice", "invoiceItem", "tfPerson", "transactionRepeat");

  function register_toolbar_items() {
    global $current_user;

    if (isset($current_user) && $current_user->is_employee()) {
      register_toolbar_item("financeMenu", "Finance");
    }
  }

}

include(ALLOC_MOD_DIR."/finance/lib/tf.inc");
include(ALLOC_MOD_DIR."/finance/lib/transaction.inc");
include(ALLOC_MOD_DIR."/finance/lib/expenseForm.inc");
include(ALLOC_MOD_DIR."/finance/lib/invoice.inc");
include(ALLOC_MOD_DIR."/finance/lib/invoiceItem.inc");
include(ALLOC_MOD_DIR."/finance/lib/tfPerson.inc");
include(ALLOC_MOD_DIR."/finance/lib/transactionRepeat.inc");




?>
