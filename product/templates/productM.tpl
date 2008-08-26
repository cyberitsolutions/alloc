{show_header()}
{show_toolbar()}

<script type="text/javascript" language="javascript">
$(document).ready(function() \{
  $("#buyCost").change(function(event) \{
    $("#buyCostLine").text($("#buyCost").val());
  \});


{if $TPL["taxRate"]}  
  var incFactor = 1.0 + {$taxRate};
  var exFactor = 1.0/incFactor;
  var taxRate = 1 / (1 + 1/{$taxRate});

  var cost = $('#buyCost').val() * exFactor;
  $('#buyCost_ex').val(cost.toFixed(2));
  var price = $('#sellPrice').val() * exFactor;
  $('#sellPrice_ex').val(price.toFixed(2));

  // Stuff to update inc and ex fields
  $('#buyCost').change(function(event) \{
    var cost = $('#buyCost').val() * exFactor;
    $('#buyCost_ex').val(cost.toFixed(2));
    $('#buyCostLine').val($('#buyCost').val());
  \});
  $('#buyCost_ex').change(function(event) \{
    var cost = $('#buyCost_ex').val() * incFactor;
    $('#buyCost').val(cost.toFixed(2));
    $('#buyCostLine').val(cost.toFixed(2));
  \});
  $('#sellPrice').change(function(event) \{
    var price = $('#sellPrice').val() * exFactor;
    var tax = price * taxRate;
    $('#sellPrice_ex').val(price.toFixed(2));
    $('#taxLine').text(tax.toFixed(2));
  \});
  $('#sellPrice_ex').change(function(event) \{
    var price = $('#sellPrice_ex').val() * incFactor;
    var tax = $('#sellPrice_ex').val() * taxRate;
    $('#sellPrice').val(price.toFixed(2));
    $('#taxLine').text(tax.toFixed(2));
  \});
{/}
\});

</script>

{$table_box}
  <tr>
    <th colspan="2">Product</th>
  </tr>
  <tr>
    <td colspan="2">

    <form action="{$url_alloc_product}" method="post">
    <input type="hidden" name="productID" value="{$product_productID}" />
    <table>
      <tr>
        <td>Product Name{mandatory($product_productName)}</td>
        <td colspan="2"><input type="text" size="43" name="productName" value="{$product_productName}" tabindex="1" /></td>
      </tr>
      <tr>
        <td>Buy cost{mandatory($product_buyCost)}</td>
{if $TPL["taxRate"]}
        <td><input type="text" size="8" name="buyCost" id="buyCost" value="{$product_buyCost}" tabindex="2" /> (inc {$taxName})</td>
        <td><input type="text" size="8" name="buyCost_ex" id="buyCost_ex" /> (ex {$taxName})</td>
{else}
        <td colspan="2"><input type="text" size="8" name="buyCost" id="buyCost" value="{$product_buyCost}" tabindex="2" /></td>
{/}
      </tr>
      <tr>
        <td>Sell price{mandatory($product_sellPrice)}</td>
{if $TPL["taxRate"]}
        <td><input type="text" size="8" name="sellPrice" id="sellPrice" value="{$product_sellPrice}" tabindex="3" /> (inc {$taxName})</td>
        <td><input type="text" size="8" name="sellPrice_ex" id="sellPrice_ex" /> (ex {$taxName})</td>
{else}
        <td colspan="2"><input type="text" size="8" name="sellPrice" id="sellPrice" value="{$product_sellPrice}" tabindex="3" /></td>
{/}
      </tr>
      <tr>
        <td>Description</td><td colspan="2"><input type="text" size="50" name="description" value="{$product_description}" tabindex="4" /></td>
      </tr>
      <tr>
        <td>Comment (internal)</td><td colspan="2">
        <textarea id="comment" name="comment" cols="85" wrap="virtual" style="height:100px">{$product_comment}</textarea></td>
      </tr>
      <tr>
        <td colspan="3">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="3" class="center">
{if $TPL["product_productID"]}
        <input type="submit" name="save" value="Save" />
{else}
        <input type="submit" name="save" value="Create New Product" />
{/}
        <input type="submit" name="delete"
        value="Delete" value="Delete Record" onClick="return confirm('Are you sure you want to delete this record?')"/>
        </td>
      </tr>
  </table>
  </form>
</table>
{if $TPL["product_productID"]}
{list($fixed, $pct) = get_costs()}
{$table_box}
  <tr>
    <th>Commissions</th>
    <th class="right">{get_help("product_commissions")}</th>
  </tr>
  <tr><td colspan="2">
  <form action="{$url_alloc_product}" method="post">
  <input type="hidden" name="productID" value="{$product_productID}" />
  <table>
  <tr>
    <th colspan="3">Fixed costs</th>
    <th><a href="#x" class="magic" onClick="$('#fixedCostContainer').append($('#new_fixedCost').html());">New</a></th>
  </tr>
  
  <tr>
    <td>Recipient</td>
    <td>Amount</td>
    <td>Description</td>
    <td valign="top" align="right" rowspan="2">{get_help("product_fixedCost");}</td>
  </tr>
  <tr>
    <td>{$companyTF}</td>
    <td id="buyCostLine">{$product_buyCost}</td>
    <td>Product buy cost</td>
    <!-- <td> removed for help icon -->
  </tr>
{if $TPL["taxRate"]}
  <tr>
    <td>{$taxTF}</td>
    <td id="taxLine">{$product_tax}</td>
    <td>{$taxName}</td>
    <td>&nbsp;</td>
  </tr>
{/}
  <tbody id="fixedCostContainer">
{foreach $fixed as $cost}
  <tr id="fixedCost_{$cost.productCostID}">
    <td><input type="hidden" name="costID[{$cost.productCostID}]" value="{$cost.productCostID}" />
      <select name="tfID[{$cost.productCostID}]"><option value=0 />{tf_list($cost["tfID"])}</select>
    </td>
    <td>
      <input type="text" size="7" name="amount[{$cost.productCostID}]" value="{$cost.amount}" />
    </td>
    <td>
      <input type="text" size="43" name="description[{$cost.productCostID}]" value="{$cost.description}" />
    </td>
    <td>
      <input type="checkbox" name=deleteCost[{$cost.productCostID}] value="1" id="deletefixed{$cost.productCostID}" />
      <label for="deletefixed{$cost.productCostID}">Delete</label>
    </td>
  </tr>
{/}
  </tbody>

  <tbody id="new_fixedCost" style="display:none">
  <tr id="fixedCost_{$cost.productCostID}">
    <td><input type="hidden" name="new_fixedCostID[]" value="new" />
      <select name="new_fixed_tfID[]"><option value=0 />{tf_list(0)}</select>
    </td>
    <td>
      <input type="text" size="7" name="new_fixed_amount[]" value="" />
    </td>
    <td>
      <input type="text" size="43" name="new_fixed_description[]" value="" />
    </td>
    <td> </td>
  </tr>

  </tbody>
  <tr>
    <th colspan="3">Percentages</th>
    <th><a href="#x" class="magic" onClick="$('#pctCostContainer').append($('#new_pctCost').html());">New</a></th>
  </tr>
  <tr>
    <td>Recipient</td>
    <td>Percentage</td>
    <td>Description</td>
    <td valign="top" align="right" rowspan="2">{get_help("product_percentageCost");}</td>
  </tr>
  <tbody id="pctCostContainer">
{foreach $pct as $cost}
  <tr id="pctCost_{$cost.productCostID}">
    <td><input type="hidden" name="costID[{$cost.productCostID}]" value="{$cost.productCostID}" />
      <select name="tfID[{$cost.productCostID}]"><option value=0 />{tf_list($cost["tfID"])}</select>
    </td>
    <td>
      <input type="text" size="7" name="amount[{$cost.productCostID}]" value="{$cost.amount}" />
    </td>
    <td>
      <input type="text" size="43" name="description[{$cost.productCostID}]" value="{$cost.description}" />
    </td>
    <td>
      <input type="checkbox" name=deleteCost[{$cost.productCostID}] value="1" id="deletepct{$cost.productCostID}" />
      <label for="deletepct{$cost.productCostID}">Delete</label>
    </td>
  </tr>
{/}
  </tbody>
  <tbody id="new_pctCost" style="display:none">
  <tr id="pctCost_{$cost.productCostID}">
    <td><input type="hidden" name="new_pctCostID[]" value="new" />
      <select name="new_pct_tfID[]"><option value=0 />{tf_list(0)}</select>
    </td>
    <td>
      <input type="text" size="7" name="new_pct_amount[]" value="" />
    </td>
    <td>
      <input type="text" size="43" name="new_pct_description[]" value="" />
    </td>
    <td>
    </td>
  </tr>

  </tbody>

  <tr><td colspan="4"><br /></td></tr>
  <tr><td colspan="4" class="center"><input type="submit" name="save_commissions" value="Save Commissions" />
  </td></tr>

  </table></form>
  </td></tr>
</table>
{/}
{show_footer()}

