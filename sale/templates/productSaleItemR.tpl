<tr id="productSaleItemRow{$productSaleItemID}" style="{$display}">
  <td><select name="productID[]" onchange="set_values(this);"><option value="">{$productList_dropdown}</select>
      {if !$productID}<a href="{$url_alloc_product}productSaleID={$psid}">New</a>{/}
  </td>
  <td><input type="text" size="5" name="quantity[]" value="{$quantity}" onkeyup="update_values(this);"></td>
  <td><input type="text" size="10" name="sellPrice[]" value="{$sellPrice}"> <label name="priceCurrency">{$sellPriceCurrencyTypeID}</label></td>
  <td><input type="text" size="43" name="description[]" value="{$description}"></td>
  <td class="right nobr">
    <input type="checkbox" name=deleteProductSaleItem[] value="{$productSaleItemID}" id="deleteProductSaleItem{$productSaleItemID}">
    <label for="deleteProductSaleItem{$productSaleItemID}"> Delete</label>
    <input type="hidden" name="productSaleItemID[]" value="{$productSaleItemID}">
    <input type="hidden" name="sellPriceCurrencyTypeID[]" value="{$sellPriceCurrencyTypeID}">
  </td>
</tr>
