{if $timeSheetListRows}
<table class="list sortable">
  <tr>
    <th></th>
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
    <th width="1%" style="font-size:120%"><i class="icon-star"></i></th>
  </tr>
  {foreach $timeSheetListRows as $r}
  <tr>
    <td style="width:1%" class="nobr">{$r.daysWarn}{$r.hoursWarn}</td>
    <td><a href="{$url_alloc_timeSheet}timeSheetID={$r.timeSheetID}">{$r.timeSheetID}</a></td>
    <td>{=$r.projectName}</td>
    <td>{=$r.person}</td>
    <td>{$r.dateFrom}</td>
    <td>{$r.dateTo}</td>
    <td>{if $r["dateRejected"]}<span class="bad" title="This timesheet has been rejected.">{/}{$r.status}{if $r["dateRejected"]}</span>{/}</td>
    <td>{$r.duration}</td>
    <td class="nobr right">{page::money($r["currencyTypeID"],$r["amount"],"%s%m %c")}</td>
    {if $timeSheetListExtra["showFinances"]}
    <td class="nobr right">{page::money($r["currencyTypeID"],$r["customerBilledDollars"],"%s%m %c")}</td>
    <td class="nobr right">{$r.transactionsPos}</td>
    <td class="nobr right">{$r.transactionsNeg}</td>
    {/}
    <td width="1%">
      {page::star("timeSheet",$r["timeSheetID"])}
    </td>
  </tr>
  {/}
  {if !$extra["noextra"]}
  <tfoot>
  <tr>
    <td></td>
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
    <td>&nbsp;</td>
  </tr>
  </tfoot>
  {/}
</table>
{else}
  <b>No Time Sheets Found.</b>
{/}

