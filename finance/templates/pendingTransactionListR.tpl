<tr>
  <td><a href="{$url_alloc_transaction}transactionID={$transactionID}">{$transactionID}</a></td>
  <td>{$transactionCreatedTime}</td>
  <td>{=$transactionCreatedUser}</td>
  <td>{$transactionType}</td>
  <td align="right">&nbsp;{page::money($currencyTypeID,$formTotal,"%s%mo")}</td>
</tr>
