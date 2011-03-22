{page::header()}
{page::toolbar()}
<table class="box">
  <tr>
    <th class="header">Search Transactions
      <b> - {print count($transactionListRows)} records</b>
    </th>
  </tr>
  <tr>
    <td align="center">
      {show_filter()}
    </td>
  </tr>
  <tr>
    <td>
      {if $transactionListRows}
      <table class="list sortable">
      <tr>
        <th width="1%">ID</th>
        <th width="1%">Type</th>
        <th width="1%">Source TF</th>
        <th width="1%">Dest TF</th>
        <th width="1%">Date</th>
        <th width="1%">Modified</th>
        <th>Product</th>
        <th width="1%">Status</th>
        <th class="right" width="1%">Credit</th>
        <th class="right" width="1%">Debit</th>
        <th class="right" width="1%">Balance</th>
      </tr>
      {foreach $transactionListRows as $r}
      <tr class="{$r.class}">
        <td class="transaction-{$r.status} nobr"><a href={$url_alloc_transaction}transactionID={$r.transactionID}>{$r.transactionID}</a></td>
        <td class="transaction-{$r.status} nobr">{$r.transactionTypeLink}&nbsp;</td>
        <td class="transaction-{$r.status} nobr">{$r.fromTfIDLink}&nbsp;</td>
        <td class="transaction-{$r.status} nobr">{$r.tfIDLink}&nbsp;</td>
        <td class="transaction-{$r.status} nobr">{$r.transactionDate}&nbsp;</td>
        <td class="transaction-{$r.status} nobr">{$r.transactionSortDate}&nbsp;</td>
        <td class="transaction-{$r.status}">{=$r.product}&nbsp;</td>
        <td class="transaction-{$r.status} nobr">{$r.status}&nbsp;</td>
        <td class="transaction-{$r.status} nobr right">{$r.amount_positive}&nbsp;</td>
        <td class="transaction-{$r.status} nobr right">{$r.amount_negative}&nbsp;</td>
        <td class="transaction-{$r.status} nobr right">{$r.running_balance}&nbsp;</td>
      </tr>
      {/}
      <tr>
        <td colspan="8">&nbsp;</td>
        <td class="grand_total nobr right">{$totals.total_amount_positive}&nbsp;</td>
        <td class="grand_total nobr right">{$totals.total_amount_negative}&nbsp;</td>
        <td class="grand_total nobr right transaction-approved">{$totals.running_balance}&nbsp;</td>
      </tr>
      </table>
      {/}
    </td>
  </tr>
</table>
{page::footer()}
