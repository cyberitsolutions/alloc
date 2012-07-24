<tr id="transactionRow{$transactionID}">
  <td>{$link}</td>
  <td><input name="amount[]" size="10" value="{$amount}"></td>
  <td width="15%"><select name="fromTfID[]" style="width:100%"><option value="">{$fromTfList_dropdown}</select></td>
  <td width="15%"><select name="tfID[]" style="width:100%"><option value="">{$tfList_dropdown}</select></td>
  <td width="40%"><input name="product[]" style="width:100%" value="{$product}"></td>
  <td>{page::calendar("transactionDate[]",$transactionDate)}</td>
  <td><select name="transactionType[]">{$transactionType_dropdown}</select></td>
  <td><select name='status[]' class='txStatus'>{$status_dropdown}</select></td>
  <td class="right nobr">
    <input type="checkbox" name=deleteTransaction[] value="{$transactionID}" id="deleteTransaction{$transactionID}">
    <label for="deleteTransaction{$transactionID}"> Delete</label>
    <input type="hidden" name="transactionID[]" value="{$transactionID}">
  </td>
</tr>
