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

include(ALLOC_MOD_DIR."/finance/lib/tf.inc.php");
include(ALLOC_MOD_DIR."/finance/lib/transaction.inc.php");
include(ALLOC_MOD_DIR."/finance/lib/expenseForm.inc.php");
include(ALLOC_MOD_DIR."/finance/lib/invoice.inc.php");
include(ALLOC_MOD_DIR."/finance/lib/invoiceItem.inc.php");
include(ALLOC_MOD_DIR."/finance/lib/tfPerson.inc.php");
include(ALLOC_MOD_DIR."/finance/lib/transactionRepeat.inc.php");




?>
