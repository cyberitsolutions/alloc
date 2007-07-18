<tr class="{$row_class}">
  <td class="transaction-{$status}"><nobr><a href="{$url_alloc_transaction}transactionID={$transactionID}">{$transactionID}</a></nobr></td>
  <td class="transaction-{$status}"><nobr>{$transactionType}&nbsp;</nobr></td>
  <td class="transaction-{$status}"><nobr>{$status}&nbsp;</nobr></td>
  <td class="transaction-{$status}"><nobr>{$transactionDate}&nbsp;</nobr></td>
  <td class="transaction-{$status}"><nobr>{$transactionModifiedTime}&nbsp;</nobr></td>
  <td class="transaction-{$status}">{$product}&nbsp;</td>
  <td align="right" class="transaction-{$status}"><nobr>{$amount_positive}&nbsp;</nobr></td>
  <td align="right" class="transaction-{$status}"><nobr>{$amount_negative}&nbsp;</nobr></td>
  <td align="right" class="transaction-{$status}"><nobr>{$running_balance}&nbsp;</nobr></td>
</tr>
