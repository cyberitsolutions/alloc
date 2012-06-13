<tr id="product_cost_row{$productCostID}" style="{$display}">
  <td class="nobr">
    <input type="hidden" name="productCostID[]" value="{$productCostID}"><input type="text" class="amount" size="7" name="amount[]" value="{$amount}">
      <select name="currencyTypeID[]">{$currencyOptions}</select>
      {if $taxName}
         <button type="button" class="cost_gstbutton" class="filter_button" style="font-size:70%;padding:1px;">&nbsp;- {$taxName}</button>
      {/}
  </td>
  <td><select name="tfID[]"><option value="">{tf_list($tfID,array(config::get_config_item("outTfID")))}</select></td>
  <td>Sale TF</td>
  <td><input type="text" size="43" name="description[]" value="{$description}"></td>
  <td class="right nobr">
    <input type="checkbox" name=deleteCost[] value="{$productCostID}" id="deletefixed{$productCostID}">
    <label for="deletefixed{$productCostID}"> Delete</label>
  </td>
</tr>
