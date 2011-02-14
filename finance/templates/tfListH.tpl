{if $tfListRows}
<table class="list sortable">
  <tr>
    <th>Account</th>
    <th class="right">Pending</th>
    <th class="right">Balance</th>
  </tr>
  {foreach $tfListRows as $r}
  <tr>
    <td><a href="{$url_alloc_transactionList}tfID={$r.tfID}">{=$r.tfName}</a></td>
    <td class="right nobr transaction-pending">{$r.tfBalancePending}</td>
    <td class="right nobr transaction-approved">{$r.tfBalance}</td>
  </tr>
  {$grand_total += $r["total"]}
  {$grand_total_pending += $r["pending_total"]}
  {/}
  {if count($tfListRows) > 1}
  <tr>
    <td>&nbsp;</td>
    <td class="grand_total right transaction-pending">{page::money(config::get_config_item("currency"),$grand_total_pending,"%s%m %c")}</td>
    <td class="grand_total right transaction-approved">{page::money(config::get_config_item("currency"),$grand_total,"%s%m %c")}</td>
  </tr>
  {/}
</table>

{else}
  <b>No Accounts Found.</b>
{/}
