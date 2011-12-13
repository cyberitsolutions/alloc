{if $timeSheetListRows}
<table class="list sortable">
  <tr>
    <th>Project</th>
    <th>Status</th>
    <th class="right">Amount</th>
  </tr>
  {foreach $timeSheetListRows as $r}
  <tr>
    <td>{$r.hoursWarn}{$r.daysWarn}{$r.projectLink}</td>
    <td>{if $r["dateRejected"]}<span class="bad" title="This timesheet has been rejected.">{/}{$r.status}{if $r["dateRejected"]}</span>{/}</td>
    <td class="nobr right obfuscate">{page::money($r["currencyTypeID"],$r["amount"],"%s%m %c")}</td>
  </tr>
  {/}
  {if count($timeSheetListRows)>1}
  <tfoot>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td class="grand_total right nobr obfuscate">{$timeSheetListExtra.amount_tallies}</td>
  </tr>
  </tfoot>
  {/}
</table>
{else}
  <b>No Time Sheets Found.</b>
{/}

