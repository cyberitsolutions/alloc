<?

require_once("../alloc.php");

$db = new db_alloc;

$product = $_GET["product"];
$quantity = $_GET["quantity"];

// qr provides escaping
$values = $db->qr("SELECT buyCost, sellPrice, description FROM product WHERE productID = %d", $product);

// Probably not valid XML, but jQuery will parse it.
echo "<data>\n";
echo "<cost>".$values["buyCost"] * $quantity."</cost>\n";
echo "<price>".$values["sellPrice"] * $quantity."</price>\n";
echo "<description>".$values["description"]."</description>\n";
echo "</data>\n";

?>
