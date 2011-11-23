<tr>
  <td class="transaction-{$status}">{$transactionID}</td>
  <td class="transaction-{$status}">{page::money($currencyTypeID,$amount,"%s%mo %c")}</td>
  <td class="transaction-{$status}">{=$fromTfID_label}</td>
  <td class="transaction-{$status}">{=$tfID_label}</td>
  <td class="transaction-{$status}">{=$product}</td>
  <td class="transaction-{$status}">{$status}</td>
</tr>
