{page::header()}
{page::toolbar()}

<script type="text/javascript" language="javascript">
$(document).ready(function() {
  {if !$productID}
  $('.view').hide();
  $('.edit').show();
  $('#productName').focus();
  {else}
  $('#editProduct').focus();
  {/}

  $("#gstbutton").click(function(){
    $("#sellPrice").val(deduct_gst($("#sellPrice").val()));
  });

  $(".cost_gstbutton").on('click',function(){
    var elem = $(this).parent().find(".amount");
    $(elem).val(deduct_gst($(elem).val()));
  });

});
</script>

<form action="{$url_alloc_product}" method="post">
<input type="hidden" name="productID" value="{$productID}">
<table class="box">
  <tr>
    <th class="header">Product
      <span>
        <a href="{$url_alloc_productList}">Products</a>
        <a href="{$url_alloc_product}">New Product</a>
        <a href="{$url_alloc_productSale}">New Sale</a>
        {page::help("product")}
      </span>
    </th>
  </tr>
  <tr>
    <td colspan="2" valign="top">
      <div style="float:left; width:47%; padding:0px 12px; vertical-align:top;">

        <div class="view">
          <h6>Product Name{page::mandatory($productName)}</h6>
          <h2 style="margin-bottom:0px; display:inline;">{=$productName}</h2>
        </div>
        <div class="edit">
          <h6>Product Name{page::mandatory($productName)}</h6>
          <input type="text" style="width:100%" maxlength="255" id="productName" name="productName" value="{$productName}">
        </div>

        {if $description}
        <div class="view">
          <h6>Description</h6>
          {=$description}
        </div>
        {/}
        <div class="edit">
          <h6>Description</h6>
          <input type="text" style="width:100%" maxlength="255" id="description" name="description" value="{$description}">
        </div>


        {if $comment}
        <div class="view">
          <h6>Comment</h6>
          {page::to_html($comment)}
        </div>
        {/}
        <div class="edit">
          <h6>Comment</h6>
          {page::textarea("comment",$comment, array("width"=>"100%"))}
        </div>

      </div>
      
      <div style="float:right; width:47%; padding:0px 12px; vertical-align:top;">

        <div class="view">
          <h6>Sell Price{$taxName and print " (ex ".$taxName.")"}{page::mandatory($sellPrice)}<div>Active</div></h6>
          <div style="float:left; width:30%;">
            {$sellPrice} {$sellPriceCurrencyTypeID}
          </div>
          <div style="float:right; width:50%;">
            {if $productActive}Yes{else}No{/}
          </div>
        </div>
        <div class="edit">
          <h6>Sell Price{$taxName and print " (ex ".$taxName.")"}{page::mandatory($sellPrice)}<div>Active</div></h6>
          <div style="float:left; width:30%;" class="nobr">
            <input type="text" size="8" name="sellPrice" id="sellPrice" value="{$sellPrice}">
            <select name="sellPriceCurrencyTypeID">{$sellPriceCurrencyOptions}</select>
            {if $taxName}
            <button type="button" id="gstbutton" class="filter_button" style="font-size:70%;padding:1px;">&nbsp;- {$taxName}</button>
            {/}
          </div>
          <div style="float:right; width:50%;" class="nobr">
            <input type="checkbox" name="productActive" {if $productActive || !$productID}checked="checked"{/}>
          </div>
        </div>
    
      </div>
    </td>
  </tr>
  <tr>
    <td colspan="2" align="center" class="padded">
      <div class="view" style="margin-top:20px">
        <button type="button" id="editProduct" value="1" onClick="toggle_view_edit();">Edit Product<i class="icon-edit"></i></button>
      </div>
      <div class="edit" style="margin-top:20px">
        <input type="hidden" name="productSaleID" value="{$_REQUEST.productSaleID}">
        <button type="submit" name="save" value="1" class="save_button">Save<i class="icon-ok-sign"></i></button>
        {if $productID}
        <br><br>
        <a href="" onClick="return toggle_view_edit(true);">Cancel edit</a>
        {/}
      </div>
    </td>
  </tr>
</table>
<input type="hidden" name="sessID" value="{$sessID}">
</form>

{if $productID}

<form action="{$url_alloc_product}" method="post">
<input type="hidden" name="productID" value="{$productID}">
<table class="box">
  <tr>
    <th class="header">Product Costs
      <span>
        <a href="#x" class="magic" onClick="$('#product_cost_footer').before('<tr>'+$('#product_cost_row').html()+'</tr>');">New</a>
        {page::help("product_fixedCost")}
      </span>
    </th>
  </tr>
  <tr>
    <td>

      <table class="list">
        <tr>
          <th width="15%">Amount{if $taxName} (Ex {$taxName}){/}</th>
          <th width="35%">Source TF</th>
          <th width="35%">Destination TF</th>
          <th>Description</th>
          <th></th>
        </tr>
        {show_productCost_list($productID, "templates/productCostR.tpl")}
        {show_productCost_new("templates/productCostR.tpl")}
        <tr id="product_cost_footer">
          <th colspan="5" class="center">
            <button type="submit" name="save_costs" value="1" class="save_button">Save Costs<i class="icon-ok-sign"></i></button>
          </th>
        </tr>
      </table>

    </td>
  </tr>
</table>
<input type="hidden" name="sessID" value="{$sessID}">
</form>


<form action="{$url_alloc_product}" method="post">
<input type="hidden" name="productID" value="{$productID}">
<table class="box">
  <tr>  
    <th class="header">Product Commissions
      <span>
        <a href="#x" class="magic" onClick="$('#product_commission_footer').before('<tr>'+$('#product_commission_row').html()+'</tr>');">New</a>
        {page::help("product_percentageCost")}
      </span>
    </th>
  </tr>
  <tr>
    <td colspan="2">

      <table class="list">
        <tr>
          <th width="15%">Percentage</th>
          <th width="35%">Source TF</th>
          <th width="35%">Destination TF</th>
          <th>Description</th>
          <th></th>
        </tr>
        {show_productCost_list($productID, "templates/productCommissionR.tpl", true)}
        {show_productCost_new("templates/productCommissionR.tpl", true)}
        <tr id="product_commission_footer">
          <th colspan="5" class="center">
            <button type="submit" name="save_commissions" value="1" class="save_button">Save Commissions<i class="icon-ok-sign"></i></button>
          </th>
        </tr>
      </table>

    </td>
  </tr>
</table>
<input type="hidden" name="sessID" value="{$sessID}">
</form>

{/}
{page::footer()}
