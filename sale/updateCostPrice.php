<?php

require_once("../alloc.php");

$db = new db_alloc;

$product = $_GET["product"];
$quantity = $_GET["quantity"];

// qr provides escaping
$values = $db->qr("SELECT * FROM product WHERE productID = %d", $product);

// Probably not valid XML, but jQuery will parse it.
echo "<data>\n";
echo "<cost>".$values["buyCost"] * $quantity."</cost>\n";
echo "<costTax>".($values["buyCostIncTax"] ? "1" : "")."</costTax>\n";
echo "<price>".$values["sellPrice"] * $quantity."</price>\n";
echo "<priceTax>".($values["sellPriceIncTax"] ? "1" : "")."</priceTax>\n";
echo "<description>".$values["description"]."</description>\n";
echo "</data>\n";

?>
