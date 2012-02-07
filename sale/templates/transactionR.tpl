<tr id="transactionRow{$transactionID}" style="{$display}">
  <td class="right">
    {if $amountClass == "tax"}
      {echo config::get_config_item("taxName")}
      <input type="hidden" name="transactionType[]" value="tax">
    {else if $amountClass == "sellPrice"}
      Price
      <input type="hidden" name="transactionType[]" value="sale">
    {else if $amountClass == "aCost"}
      Cost
      <input type="hidden" name="transactionType[]" value="sale">
    {else if $amountClass == "aPerc"}
      {// Hardcoded AUD because productCost table uses percent and dollars in same field}
      {page::money("AUD",$pc_amount,"%mo")}%
      <input type="hidden" name="transactionType[]" value="sale">
    {/}
  </td>
  <td class="nobr"><input data-pc-amount="{page::money("AUD",$pc_amount,"%mo")}" class="amountField {$amountClass}" name="amount[]" size="10" value="{page::money($currencyTypeID,$amount,"%mo")}">
      <select name="currencyTypeID[]">{$currencyOptions}</select>
  </td>
  <td><select name="fromTfID[]"><option value="">{$fromTfList_dropdown}</select></td>
  <td><select name="tfID[]"><option value="">{$tfList_dropdown}</select></td>
  <td><input name="product[]" style="width:95%" value="{$product}"></td>
  <td>{$status}</td>
  <td class="right nobr">
    <input type="checkbox" name=deleteTransaction[] value="{$transactionID}" id="deleteTransaction{$transactionID}">
    <label for="deleteTransaction{$transactionID}"> Delete</label>
    <input type="hidden" name="productCostID[]" value="{$pc_productCostID}">
    <input type="hidden" name="transactionID[]" value="{$transactionID}">
  </td>
</tr>
