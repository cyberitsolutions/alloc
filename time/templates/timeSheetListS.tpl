{if $timeSheetListRows}
<table class="list sortable">
  <tr>
    <th class="sorttable_numeric">ID</th>
    <th>Project</th>
    <th>Owner</th>
    <th>Start Date</th>
    <th>End Date</th>
    <th>Status</th>
    <th>Duration</th>
    <th class="right">Amount</th>
    {if $timeSheetListExtra["showFinances"]}
    <th class="right">Client Billed</th>
    <th class="right">Sum &gt;0</th>
    <th class="right">Sum &lt;0</th>
    {/}
  </tr>
  {foreach $timeSheetListRows as $r}
  {$bad = $r["daysWarn"] or $bad = $r["hoursWarn"]}
  <tr>
    <td class="{$bad}">{$r.timeSheetID}</td>
    <td class="{$bad}">{$r.projectLink}</td>
    <td class="{$bad}">{=$r.person}</td>
    <td class="{$bad}">{$r.dateFrom}</td>
    <td class="{$bad}">{$r.dateTo}</td>
    <td class="{$bad}">{if $r["dateRejected"]}<span class="bad" title="This timesheet has been rejected.">{/}{$r.status}{if $r["dateRejected"]}</span>{/}</td>
    <td class="{$bad}">{$r.duration}</td>
    <td class="{$bad} nobr right">{page::money($r["currencyTypeID"],$r["amount"],"%s%m %c")}</td>
    {if $timeSheetListExtra["showFinances"]}
    <td class="{$bad} nobr right">{page::money($r["currencyTypeID"],$r["customerBilledDollars"],"%s%m %c")}</td>
    <td class="{$bad} nobr right">{$r.transactionsPos}</td>
    <td class="{$bad} nobr right">{$r.transactionsNeg}</td>
    {/}
  </tr>
  {/}
  <tfoot>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td class="grand_total left nobr">{echo sprintf("%0.2f", $timeSheetListExtra["totalHours"])} Hours</td>
    <td class="grand_total right nobr">{$timeSheetListExtra.amount_tallies}</td>
    {if $timeSheetListExtra["showFinances"]}
    <td class="grand_total right nobr">{$timeSheetListExtra.billed_tallies}</td>
    <td class="grand_total right nobr">{$timeSheetListExtra.positive_tallies}</td>
    <td class="grand_total right nobr">{$timeSheetListExtra.negative_tallies}</td>
    {/}
  </tr>
  </tfoot>
</table>
{else}
  <b>No Time Sheets Found.</b>
{/}

