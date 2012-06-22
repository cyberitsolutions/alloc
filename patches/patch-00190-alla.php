<?php

// We're dropping the product.buyCost fields, so we need to transfer them over to productCosts.
$db = new db_alloc();
$db2 = new db_alloc();
$q = "SELECT * FROM product";
$db->query($q);
while ($row = $db->row()) {
  $q = prepare("INSERT INTO productCost (productID,fromTfID,tfID,amount,currencyTypeID,description) VALUES ('%d','%d','%d','%d','%s','%s')"
              ,$row["productID"],config::get_config_item("mainTfID"),config::get_config_item("outTfID"),$row["buyCost"],$row["buyCostCurrencyTypeID"],"Product Acquisition");
  $db2->query($q);
}

?>
