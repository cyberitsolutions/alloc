<tr id="product_commission_row{$productCostID}"  style="{$display}">
  <td><input type="hidden" name="productCostID[]" value="{$productCostID}"><input type="text" size="7" name="amount[]" value="{$amount}"></td>
  <td>Sale TF</td>
  <td><select name="tfID[]"><option value="">{tf_list($tfID)}</select></td>
  <td><input type="text" size="43" name="description[]" value="{$description}"></td>
  <td class="right nobr">
    <input type="checkbox" name=deleteCost[] value="{$productCostID}" id="deletepct{$productCostID}">
    <label for="deletepct{$productCostID}">Delete</label>
  </td>
</tr>
