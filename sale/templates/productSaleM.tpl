{page::header()}
{page::toolbar()}

<script type="text/javascript">

$(document).ready(function() {
  // For marking all boxes
  $('.allPending').bind("click", function(event) {
    $(this).parent().parent().parent().find('.txStatus').val("pending");
    return false;
  });
  $('.allApproved').bind("click", function(event) {
    $(this).parent().parent().parent().find('.txStatus').val("approved");
    return false;
  });
  $('.allRejected').bind("click", function(event) {
    $(this).parent().parent().parent().find('.txStatus').val("rejected");
    return false;
  });
});

function update_values(target) {
    var myTD = $(target).parent().parent();
    var productID = myTD.find('select[name="productID[]"]').val();
    var quantity = myTD.find('input[name="quantity[]"]').val();
    
    if (productID != 0) {
      $.get("{$url_alloc_updateCostPrice}product="+productID+"&quantity="+quantity, function(xml) {
        myTD.find('input[name="buyCost[]"]').val($("cost", xml).text());
        myTD.find('input[name="sellPrice[]"]').val($("price",xml).text());
        myTD.find('input[name="description[]"]').val($("description",xml).text());
        myTD.find('input[name="buyCostIncTax[]"]').attr('checked', $("costTax",xml).text());
        myTD.find('input[name="sellPriceIncTax[]"]').attr('checked',$("priceTax",xml).text());
      }); 
   }
}
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
    <td>{echo (person::get_fullname($productSaleCreatedUser))} {$productSaleCreatedTime}</td>
    <td class="right">Project:</td>
    <td width="30%">{$show_project_options}</td>
  </tr>
  <tr>
    <td class="right">Total Buy Cost:</td>
    <td>{$total_buyCost}</td>
  </tr>
  <tr>
    <td class="right">Total Sell Price:</td>
    <td>{$total_sellPrice}</td>
  </tr>
  <tr>
    <td class="right">Total Margin:</td>
    <td>{$total_margin}</td>
  </tr>
    {$class = "good"}
    {$total_unallocated != 0 and $class="bad"}
  <tr>
    <td class="right {$class}">Total Unallocated:</td>
    <td class="{$class}">{$total_unallocated}</td>
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
    {else if $status == "finished" && CAN_APPROVE_TRANSACTIONS}
    <input type="submit" name="move_backwards" value="&lt;-- Administrator">
    {/}
    <br><br>
    {$statusText}
    </td>
  </tr>
</table>
</form>


{if DISPLAY == DISPLAY_PRODUCT_SALE_ITEM_EDIT}
<form action="{$url_alloc_productSale}" method="post">
<input type="hidden" name="productSaleID" value="{$productSaleID}">
<table class="box">
  <tr>
    <th>Sale Items</th>
    <th class="right"></th>
  </tr>
  <tr>
    <td colspan="2">

      <table class="list">
        <tr>
          <th>Product</th>
          <th>Quantity</th>
          <th>Buy Cost</th>
          <th>Sell Price</th>
          <th>Description</th>
          <th width="1%" class="right"><a href="#x" class="magic" onClick="$('#productSaleItem_footer').before('<tr>'+$('#productSaleItemRow').html()+'</tr>');">New</a></th>
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

