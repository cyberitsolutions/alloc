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
});
</script>

<form action="{$url_alloc_product}" method="post">
<input type="hidden" name="productID" value="{$productID}">
<table class="box">
  <tr>
    <th>Product</th>
    <th class="right">{page::help("product")}</th>
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

      {if $taxName}
        {$sellPrice_check = sprintf("<input type='checkbox' name='sellPriceIncTax' value='1'%s> inc %s" ,$sellPriceIncTax ? ' checked':'',$taxName)}
        {if $sellPriceIncTax}
          {$sellPrice_label = " (inc ".$taxName.")"}
        {else}
          {$sellPrice_label = " (ex ".$taxName.")"}
        {/}
      {/}

        <div class="view">
          <h6>Sell Price{$sellPrice_label}{page::mandatory($sellPrice)}</h6>
          <div style="float:left; width:50%;">
            {$sellPrice} {$sellPriceCurrencyTypeID}
          </div>
        </div>
        <div class="edit">
          <h6>Sell Price{$taxLabel}{page::mandatory($sellPrice)}</h6>
          <div style="float:left; width:50%;" class="nobr">
            <input type="text" size="8" name="sellPrice" id="sellPrice" value="{$sellPrice}">
            <select name="sellPriceCurrencyTypeID">{$sellPriceCurrencyOptions}</select>
            {$sellPrice_check} 
          </div>
        </div>
    
      </div>
    </td>
  </tr>
  <tr>
    <td colspan="2" align="center" class="padded">
      <div class="view" style="margin-top:20px">
        <input type="button" id="editProduct" value="Edit Product" onClick="$('.view').hide();$('.edit').show();">
      </div>
      <div class="edit" style="margin-top:20px">
        <input type="submit" name="save" value="Save">
        <input type="submit" name="delete" value="Delete" class="delete_button">
        <input type="button" value="Cancel Edit" onClick="$('.edit').hide();$('.view').show();">
      </div>
    </td>
  </tr>
</table>
</form>

{if $productID}

<form action="{$url_alloc_product}" method="post">
<input type="hidden" name="productID" value="{$productID}">
<table class="box">
  <tr>
    <th>Product Costs</th>
    <th width="1%">{page::help("product_fixedCost")}</th>
  </tr>
  <tr>
    <td colspan="2">

      <table class="list">
        <tr>
          <th width="15%">Amount</th>
          <th width="35%">Source TF</th>
          <th width="35%">Destination TF</th>
          <th>Description</th>
          <th class="right"><a href="#x" class="magic" onClick="$('#product_cost_footer').before('<tr>'+$('#product_cost_row').html()+'</tr>');">New</a></th>
        </tr>
        {show_productCost_list($productID, "templates/productCostR.tpl")}
        {show_productCost_new("templates/productCostR.tpl")}
        <tr id="product_cost_footer">
          <th colspan="5" class="center"><input type="submit" name="save_costs" value="Save Costs"></th>
        </tr>
      </table>

    </td>
  </tr>
</table>
</form>


<form action="{$url_alloc_product}" method="post">
<input type="hidden" name="productID" value="{$productID}">
<table class="box">
  <tr>  
    <th>Product Commissions</th>
    <th width="1%">{page::help("product_percentageCost")}</th>
  </tr>
  <tr>
    <td colspan="2">

      <table class="list">
        <tr>
          <th width="15%">Percentage</th>
          <th width="35%">Source TF</th>
          <th width="35%">Destination TF</th>
          <th>Description</th>
          <th class="right"><a href="#x" class="magic" onClick="$('#product_commission_footer').before('<tr>'+$('#product_commission_row').html()+'</tr>');">New</a></th>
        </tr>
        {show_productCost_list($productID, "templates/productCommissionR.tpl", true)}
        {show_productCost_new("templates/productCommissionR.tpl")}
        <tr id="product_commission_footer">
          <th colspan="5" class="center"><input type="submit" name="save_commissions" value="Save Commissions"></th>
        </tr>
      </table>

    </td>
  </tr>
</table>
</form>

{/}
{page::footer()}
