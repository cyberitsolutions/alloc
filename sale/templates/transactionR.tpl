<tr id="transactionRow{$transactionID}" style="{$display}">
  <td><input name="amount[]" size="10" value="{page::money($currencyTypeID,$amount,"%mo")}">
      <select name="currencyTypeID[]">{$currencyOptions}</select></td>
  <td><select name="fromTfID[]"><option value="">{$fromTfList_dropdown}</select></td>
  <td><select name="tfID[]"><option value="">{$tfList_dropdown}</select></td>
  <td><input name="product[]" size="43" value="{$product}"></td>
  <td>{$status}</td>
  <td class="right nobr">
    <input type="checkbox" name=deleteTransaction[] value="{$transactionID}" id="deleteTransaction{$transactionID}">
    <label for="deleteTransaction{$transactionID}"> Delete</label>
    <input type="hidden" name="transactionID[]" value="{$transactionID}">
  </td>
</tr>
