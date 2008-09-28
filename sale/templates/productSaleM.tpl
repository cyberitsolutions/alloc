{page::header()}
{page::toolbar()}

<script type="text/javascript">
$(document).ready(function() \{
  // For marking all boxes
  $('.allPending').bind("click", function(event) \{
    var myTable = $(this).parents('.innerTable');
    myTable.find('.txStatus').val("Pending");
    return false;
  \});
  $('.allApproved').bind("click", function(event) \{
    var myTable = $(this).parents('.innerTable');
    myTable.find('.txStatus').val("Approved");
    return false;
  \});
  $('.allRejected').bind("click", function(event) \{
    var myTable = $(this).parents('.innerTable');
    myTable.find('.txStatus').val("Rejected");
    return false;
  \});

  // Product creation auto-updating stuff
  $('.saleCreate').bind("change", function(event) \{
    update_values($(this));
  \}); 
\});

function update_values(target) \{
    var myTD = target.parents('.saleTD');
    var prodID = myTD.find('.prodID').val();
    var qty = myTD.find('.qty').val();
    
    if (prodID != 0 && qty > 0) \{
      $.get("{$url_alloc_updateCostPrice}product="+prodID+"&quantity="+qty, function(xml) \{
        myTD.find('.buyCost').val($("cost", xml).text());
        myTD.find('.sellPrice').val($("price",xml).text());
        myTD.find('.description').val($("description",xml).text());
      \}); 
   \}
\}

function newProductField() \{
  var newTR = $('#productContainer').append($('#new_lineItem').html());
  newTR.find('.saleCreate').bind("change", function(event)\{
    update_values($(this));
  \});
  return false;
\}
</script>

{if $TPL["status"] == "create"}
{$table_box}
  <tr>
    <th>Product Sale</th>
    <th class="right"><a href="#x" class="magic" onClick="newProductField()">
    New</a></th>
  </tr>
  <tr><td colspan="2">
    <form action="{$url_alloc_productSale}" method="post">
    <table>
    <thead>
      <tr>
        <td>Project</td>
        <td colspan="5"><select name="projectID">{get_project_list()}</select></td>
      </tr>
      <tr>
        <td>Product</td>
        <td>Quantity</td>
{if $TPL["taxRate"]}
        <td>Buy Cost (inc {$taxName})</td>
        <td>Sell Price (inc {$taxName})</td>
{else}
        <td>Buy Cost</td>
        <td>Sell Price</td>
{/}
        <td>Description</td>
        <td>&nbsp;</td>
      </tr>
    </thead>
    <tbody id="productContainer">
      <tr class="saleTD">
      <td><select name="new_productID[]" class="saleCreate prodID"><option value="">{$productList_dropdown}</select></td>
      <td><input type="text" size="5" name="new_quantity[]" class="saleCreate qty" value="{$ps_quantity}" /></td>
      <td><input type="text" size="5" name="new_buycost[]" value="" class="buyCost" /></td>
      <td><input type="text" size="5" name="new_sellprice[]" value="" class="sellPrice" /></td>
      <td><input type="text" size="43" name="new_description[]" value="" class="description" /></td>
      <td><a href="#x" class="magic" onClick="$(this).parent().parent().remove()">Remove</a>
      </tr>
    </tbody>
    <tbody id="new_lineItem" style="display:none">
    <tr class="saleTD">
      <td><select name="new_productID[]" class="saleCreate prodID"><option value="">{$productList_dropdown}</select></td>
      <td><input type="text" size="5" name="new_quantity[]" class="saleCreate qty" value="{$ps_quantity}" /></td>
      <td><input type="text" size="5" name="new_buycost[]" class="buyCost" value="" /></td>
      <td><input type="text" size="5" name="new_sellprice[]" class="sellPrice" value="" /></td>
      <td><input type="text" size="43" name="new_description[]" value="" /></td>
      <td><a href="#x" class="magic" onClick="$(this).parent().parent().remove()">Remove</a>
    </tr>
    </tbody>
    <tr><td colspan="6" class="center">{$statusText}</td></tr>
    <tr><td colspan="6" class="center"><input type="submit" name="create" value="Create Sale" /></td></tr>
    </table>
    </form>
  </td></tr>
</table>

{else if $TPL["status"] == "edit" || ($TPL["status"] == "admin" && $TPL["editTransactions"])}
<form action="{$url_alloc_productSale}productSaleID={$productSaleID}" method="post">
{$table_box}
  <tr>
    <th>Product Sale</th>
  </tr>

{list($psItems, $staticrows, $pctrows, $transactions) = get_productSale_costs()}
{$TPL["productidx"] = 0}
{foreach $psItems as $product}
  <tr><td>
    <table class="innerTable">
      <tr>
        <th colspan="4">{$product.quantity} x {$product.productName}</th>
      </tr>
      <tr>
        <td>Description:</td>
        <td colspan="3">{$product.description}</td>
      </tr>
      <tr>
        <td>Total buy cost:</td>
        <td>{$product.buyCost}</td>
        <td>Total sell price:</td>
        <td>{$product.sellPrice}</td>
      </tr>
      <tr>
        <td colspan="4"><hr /></td>
      </tr>
      <tr>
        <th colspan="3">Fixed costs</th>
        <th class="right"><a href="#x" class="magic"
        onClick="$('#staticCostContainer{$productidx}').append($('#new_fixedcost{$productidx}').html());">New</a></th>
      </tr>
      <tr>
        <td>Destination TF</td>
        <td>Amount
        {if $product["fixedErr"]}
        	<span class="bad"><br />(Funds over-allocated by {$product.fixedErr})</span>
        {/}
      	</td>
        <td>Description</td>
        <td>&nbsp;</td>
      </tr>
{if $TPL["taxRate"]}
      <tr>
        <td>{tf_name($TPL["tax_tfID"])}</td>
        <td>{$product.tax}</td>
        <td>{$taxName}</td>
        <td>&nbsp;</td>
      </tr>
{/}
    <tbody id="staticCostContainer{$productidx}">
    {foreach $staticrows[$product["productSaleItemID"]] as $row}
    <tr>
      <td><select name="tfID[{$row.productSaleTransactionID}]">{tf_list($row["tfID"])}</select></td>
</td>
      <td><input type="text" name="amount[{$row.productSaleTransactionID}]" value="{$row.amount}" /></td>
      <td><input type="text" size="43" name="description[{$row.productSaleTransactionID}]" value="{$row.description}" /></td>
      <td><input type="checkbox" name="delete_cost[{$row.productSaleTransactionID}]" id=deletefixed{$row.productSaleTransactionID} />
        <label for="deletefixed{$row.productSaleTransactionID}">Delete</label></td>
    </tr>
    {/}
    </tbody>
    <tr>
      <th colspan="3">Percentage costs</th>
      <th class="right"><a href="#x" class="magic"
      onClick="$('#pctCostContainer{$productidx}').append($('#new_pctcost{$productidx}').html());">New</a></th>
    </tr>
    <tr>
      <td>Margin:</td>
      <td colspan="3">${$product.margin}</td>
    </tr>
    <tr>
      <td>Destination TF</td>
      <td>Percentage
      {if $product["pctRemaining"]}
      <span class="bad">{$product.pctRemainingText}{$product.pctRemaining}%</span>
      {/}
      </td>
      <td>Description</td>
      <td>&nbsp;</th>
    </tr>
    <tbody id="pctCostContainer{$productidx}">
    {foreach $pctrows[$product["productSaleItemID"]] as $row}
    <tr>
      <td><select name="tfID[{$row.productSaleTransactionID}]">{tf_list($row["tfID"])}</select></td>
      <td><input type="text" name="amount[{$row.productSaleTransactionID}]" value="{$row.amount}" /></td>
      <td><input type="text" size="43" name="description[{$row.productSaleTransactionID}]" value="{$row.description}" /></td>
      <td><input type="checkbox" name="delete_cost[{$row.productSaleTransactionID}]" id="deletepct{$row.productSaleTransactionID}" />
      <label for="deletepct{$row.productSaleTransactionID}">Delete</label></td>
    </tr>
    {/}
    </tbody>

  <tbody id="new_pctcost{$productidx}" style="display:none">
    <tr>
      <td><input type="hidden" name="new_transaction_itemID[]" value="{$product.productSaleItemID}" />
          <input type="hidden" name="new_isPercent[]" value="1" class="isPct" />
        <select name="new_tfID[]">{tf_list(0)}</select></td>
      <td><input type="text" name="new_amount[]" value="" /></td>
      <td><input type="text" name="new_description[]" size="43" value="" /></td>
      <td></td>
    </tr>
    </tbody>
    <tbody id="new_fixedcost{$productidx}" style="display:none">
    <tr>
      <td><input type="hidden" name="new_transaction_itemID[]" value="{$product.productSaleItemID}" />
          <input type="hidden" name="new_isPercent[]" value="0" class="isPct" />
        <select name="new_tfID[]">{tf_list(0)}</select></td>
      <td><input type="text" name="new_amount[]" value="" /></td>
      <td><input type="text" name="new_description[]" size="43" value="" /></td>
      <td></td>
    </tr>
    </tbody>
    <!-- }}} -->
  </table>
  </td></tr>
  {if $TPL["status"] == "admin" && $TPL["editTransactions"]}
  <tr><td>
    <table class="innerTable">
    <tr><th colspan="4">Transactions</th>
    	<th class="right"><a href="#x" class="magic" onClick="$('#transactionContainer{$productidx}').append($('#new_transaction{$productidx}').html());">New</a></th></tr>
      <tr>
        <td>Destination TF</td>
        <td>Amount
        {if $product["txRemaining"]}
        <span class="bad"><br />(To allocate: {$product.txRemaining})</span></td>
        {/}
        <td>Description</td>
        <td>
	<a href="##" class="magic allPending">P</a>&nbsp;
	<a href="##" class="magic allApproved">A</a>&nbsp;
	<a href="##" class="magic allRejected">R</a>&nbsp;
        </td>
        <td>&nbsp;</td>
      </tr>
  <tbody id="transactionContainer{$productidx}">
  {foreach $transactions[$product["productSaleItemID"]] as $transaction}
  <tr>
      <td>
        <select name="tx_tfID[{$transaction.transactionID}]">{tf_list($transaction["tfID"])}</select></td>
      <td><input type="text" size="8" name="tx_amount[{$transaction.transactionID}]" value="{$transaction.amount}" /></td>
      <td><input type="text" size="43" name="tx_description[{$transaction.transactionID}]" value="{$transaction.product}" /></td>
      <td>
      <select name="tx_status[{$transaction.transactionID}]" class="txStatus">
      {transaction_status_list($transaction["status"])}
      </select>
      </td>
      <td><input type="checkbox" name="tx_delete[{$transaction.transactionID}]" id="deleteTx{$transaction.transactionID}" />
          <label for="deleteTx{$transaction.transactionID}">Delete</label>
      </td>
  </tr>
  {/}
  </tbody>
  <tbody id="new_transaction{$productidx}" style="display:none">
  <tr>
    <td><input type="hidden" name="new_tx_itemID[]" value="{$product.productSaleItemID}" />
      <select name="new_tx_tfID[]">{tf_list(0)}</select></td>
    <td><input type="text" name="new_tx_amount[]" value="" size="8" /></td>
    <td><input type="text" name="new_tx_description[]" size="43" value="" /></td>
    <td>
    <select name="new_tx_status[]" class="txStatus">
    {transaction_status_list("pending")}
    </select>
    </td>
    <td>&nbsp;</td>
  </tr>
  </tbody>

  <tr>
    <td colspan="5" class="center">
    <input type="submit" name="create_default_transactions[{$product.productSaleItemID}]" value="Create Default Transactions" />
    <input type="submit" name="delete_all_transactions[{$product.productSaleItemID}]" value="Delete All Transactions" class="delete_button"/>
    </td>
  </tr>

  </table></td></tr>
  {/}
{$TPL["productidx"]++}
{/}
<tr><td class="center">{$statusText}</td></tr>
  <tr><td class="center">
  {if $TPL["status"] == "edit"}
    <input type="submit" name="delete_productSale" value="Delete" class="delete_button" />
    <input type="submit" name="save_transactions" value="Save" />
    <input type="submit" name="move_to_admin" value="Save and Move to Administrator" />
  {else}
    <input type="submit" name="back_to_edit" value="Back to Edit" />
    <input type="submit" name="delete_productSale" value="Delete" class="delete_button" />
    <input type="submit" name="save_transactions" value="Save" />
    <input type="submit" name="finish" value="Complete Product Sale" />
  {/}
  </td></tr>
</table>
</form>

{else if $TPL["status"] == "finished" || ($TPL["status"] == "admin" && !$TPL["editTransactions"])}
{$table_box}
  <tr>
    <th>Product Sale</th>
  </tr>
{list($psItems, $staticrows, $pctrows, $transactions) = get_productSale_costs()}
{foreach $psItems as $product}
  <tr><td>
    <table class="innerTable">
      <tr>
        <th colspan="3">{$product.quantity} x {$product.productName}</th>
      </tr>
      <tr>
        <td>Description:</td>
        <td colspan="2">{$product.description}</td>
      </tr>
      <tr>
        <td>Total buy cost:</td>
        <td colspan="2">{$product.buyCost}</td>
      </tr>
      <tr>
        <td>Total sell price:</td>
        <td colspan="2">{$product.sellPrice}</td>
      </tr>
      <tr>
        <td colspan="3"><hr /></td>
      </tr>
      <tr>
        <td>Destination TF</td>
        <td>Amount</td>
        <td>Description</td>
      </tr>
    <tbody id="staticCostContainer{$productidx}">
    {foreach $staticrows[$product["productSaleItemID"]] as $row}
    <tr>
      <td>{tf_name($row["tfID"])}</td>
</td>
      <td>{$row.amount}</td>
      <td>{$row.description}</td>
    </tr>
    {/}
    </tbody>
    <tr>
      <td>Destination TF</td>
      <td>Percentage</td>
      <td>Description</td>
    </tr>
    <tbody id="pctCostContainer{$productidx}">
    {foreach $pctrows[$product["productSaleItemID"]] as $row}
    <tr>
      <td>{tf_name($row["tfID"])}</td>
</td>
      <td>{$row.amount}</td>
      <td>{$row.description}</td>
    </tr>
    {/}
    </tbody>
  </table>
  </td></tr>
  <tr><td>
    <table class="innerTable">
    <tr><th colspan="4">Transactions</th>
      <tr>
        <td>Destination TF</td>
        <td>Amount</td>
        <td>Description</td>
        <td>Status</td>
      </tr>
  <tbody id="transactionContainer{$productidx}">
  {foreach $transactions[$product["productSaleItemID"]] as $transaction}
  <tr>
      <td>{tf_name($transaction["tfID"])}</td>
      <td>{$transaction.amount}</td>
      <td>{$transaction.product}</td>
      <td>{$transaction.status}</td>
  </tr>
  {/}
  </tbody>
  </table></td></tr>
{/}
  <tr><td class="center">{$statusText}</td></tr>
  {if $TPL["editTransactions"]}
  <tr><td class="center">
    <form action="{$url_alloc_productSale}productSaleID={$productSaleID}" method="post">
    <input type="submit" name="back_to_admin" value="Back to Administrator" />
    </form>
  </td></tr>
  {/}
</table>
</form>
{/}

{page::footer()}

