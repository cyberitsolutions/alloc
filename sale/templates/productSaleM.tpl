{page::header()}
{page::toolbar()}

<script type="text/javascript">

var tdNum = 0;

function add_row() {
    tdNum++;
    var newId = "new" + tdNum;
    $('#productSaleItem_footer').before('<tr id="' + newId + '">'+$('#productSaleItemRow').html()+'</tr>');
    $('#' + newId).find('input[name="sellPriceIncTax[]"]').val(newId);
    $('#' + newId).find('input[name="productSaleItemID[]"]').val(newId);
}

// AJAX request to fill in product fields
function update_values(target) {
    var myTD = $(target).parent().parent();
    var productID = myTD.find('select[name="productID[]"]').val();
    var quantity = myTD.find('input[name="quantity[]"]').val();
    
    if (productID != 0) {
      $.get("{$url_alloc_updateCostPrice}product="+productID+"&quantity="+quantity, function(xml) {
        myTD.find('input[name="sellPrice[]"]').val($("price",xml).text());
        myTD.find('input[name="description[]"]').val($("description",xml).text());
        myTD.find('input[name="sellPriceIncTax[]"]').attr('checked',$("priceTax",xml).text());
        myTD.find('label[name="priceCurrency"]').html($("priceCurrency",xml).text());
        myTD.find('input[name="sellPriceCurrencyTypeID[]"]').val($("priceCurrency",xml).text());
      }); 
   }
}

// When a product is selected set quantity and pre-fill
function set_values(target) {
    var myTD = $(target).parent().parent();
    var productID = myTD.find('select[name="productID[]"]').val();
    var quantityField = myTD.find('input[name="quantity[]"]');
    var quantity = quantityField.val();

    if (quantity == "") {
        quantityField.val(1);
    }
    update_values(target);
}


function get_item_margin(obj) {
  // then get sum of all costs
  var sum_of_costs = 0
  $(obj).find("input.aCost").each(function(){
    sum_of_costs += parseFloat($(this).val());
  });
  // calculate the new margin: sellPrice - gst - sum_of_costs
  var sellPrice = $(obj).find("input.sellPrice").val();
  var tax = $(obj).find("input.tax").val();
  if (tax) {
    sellPrice = parseFloat(sellPrice) - parseFloat(tax);
  }
  return parseFloat(sellPrice) - parseFloat(sum_of_costs);
}


$(document).ready(function() {
  $("input.amountField").live('keyup', function(event){

    // Only update when a number or period is entered
    var c = String.fromCharCode(event.keyCode);
    if (!c.match(/\d/) && !c.match(/\./)) {
      return true;
    }

    // If we've changed a fixed product cost
    if ($(this).hasClass("sellPrice") || $(this).hasClass("aCost") || $(this).hasClass("tax")) {
      var margin = get_item_margin($(this).parent().parent().parent());

      // calculate the new product commissions based on that new margin
      $(this).parent().parent().parent().find("input.aPerc").each(function(){
        var percent = parseFloat($(this).attr("data-pc-amount"));
        var newval = percent/100*margin;
        if (!isNaN(newval)) {
          $(this).css({ "background-color":"#fffaa2" })
          $(this).val(newval);
        }
      });
    }
  });
});

</script>

<form action="{$url_alloc_productSale}" method="post">
<input type="hidden" name="productSaleID" value="{$productSaleID}">
<table class="box">
  <tr>
    <th colspan="4">Sale</th>
  </tr>
  <tr>
    <td width="20%" class="right">Sale ID:</td>
    <td>{$productSaleID}</td>
    <td class="right">Client:</td>
    <td width="30%">{$show_client_options}</td>
  </tr>
  <tr>
    <td class="right">Created:</td>
    {$n = person::get_fullname($productSaleCreatedUser)}
    <td>{=$n} {$productSaleCreatedTime}</td>
    <td class="right">Project:</td>
    <td width="30%">{$show_project_options}{$project_tfID}</td>
  </tr>
  <tr>
    <td class="right">Total Sell Price:</td>
    <td>{$total_sellPrice}</td>
    <td class="right">Salesperson:</td>
    <td>{$show_person_options}</td>
  </tr>
  <tr>
    <td class="right">Total Margin:</td>
    <td>{$total_margin}</td>
    <td class="right">Sale Date:</td>
    <td>{$show_date}</td>
  </tr>
  <tr>
    <td colspan="6" class="center">
    {if !$productSaleID}
    <input type="submit" name="save" value="Create Sale">
    {else if $status == "edit"}
    <input type="submit" name="delete_productSale" value="Delete" class="delete_button">
    <input type="submit" name="save" value="Save">
    <input type="submit" name="move_forwards" value="Allocate --&gt;">
    {else if $status == "allocate"}
    <input type="submit" name="move_backwards" value="&lt;-- Add Sale Items">
    <input type="submit" name="save" value="Save">
    <input type="submit" name="move_forwards" value="Administrator  --&gt;">
    {else if $status == "admin" && CAN_APPROVE_TRANSACTIONS}
    <input type="submit" name="move_backwards" value="&lt;-- Allocate">
    <input type="submit" name="save" value="Save">
    <input type="submit" name="move_forwards" value="Completed --&gt;">
    <select name='changeTransactionStatus'><option value="">Transaction Status<option value='approved'>Approve<option value="rejected">Reject</select>
    {else if $status == "finished" && CAN_APPROVE_TRANSACTIONS}
    <input type="submit" name="move_backwards" value="&lt;-- Administrator">
    {/}
    <br><br>
    {$statusText}
    </td>
  </tr>
</table>
<input type="hidden" name="sessID" value="{$sessID}">
</form>


{if DISPLAY == DISPLAY_PRODUCT_SALE_ITEM_EDIT}
<form action="{$url_alloc_productSale}" method="post">
<input type="hidden" name="productSaleID" value="{$productSaleID}">
<table class="box">
  <tr>
    <th class="header">Sale Items
      <span>
        <a href="#x" class="magic" onClick="add_row();">New</a>
      </span>
    </th>
  </tr>
  <tr>
    <td>

      <table class="list">
        <tr>
          <th>Product</th>
          <th>Quantity</th>
          <th>Sell Price</th>
          <th>Description</th>
          <th width="1%" class="right"></th>
        </tr>
        {show_productSale_list($productSaleID, "templates/productSaleItemR.tpl")}
        {show_productSale_new("templates/productSaleItemR.tpl")}
        <tr id="productSaleItem_footer">
          <th colspan="6" class="center"><input type="submit" name="save_items" value="Save Items"></th>
        </tr>
      </table>

    </td>
  </tr>
</table>
<input type="hidden" name="sessID" value="{$sessID}">
</form>

{else if DISPLAY == DISPLAY_PRODUCT_SALE_ITEM_TRANSACTION_EDIT}
<table class="box">
  <tr>
    <th>Sale Items</th>
    <th class="right">&nbsp;</th>
  </tr>
  <tr>
    <td colspan="2">
      {show_productSale_list($productSaleID, "templates/productSaleItemTransactionR.tpl")}
    </td>
  </tr>
</table>

{else if DISPLAY == DISPLAY_PRODUCT_SALE_ITEM_TRANSACTION_VIEW}
<table class="box">
  <tr>
    <th>Sale Items</th>
    <th class="right">&nbsp;</th>
  </tr>
  <tr>
    <td colspan="2">
      {show_productSale_list($productSaleID, "templates/productSaleItemTransactionViewR.tpl")}
    </td>
  </tr>
</table>
{/}



{page::footer()}

