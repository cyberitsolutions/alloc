{$fields = $TPL["this"]->time_sheet_items()}
{if count($fields["lines"]) > 0}
<table border="0" cellspacing="0" cellpadding="2" width="100%">
{foreach $fields["lines"] as $line}
<tr>
  <td>{$line.projectName}</td>
  <td class="nobr">{$line.dateFrom}</td>
  <td class="nobr right">{$line.total_dollars}</td>
  <td class="noprint">{$line.status}</td>
</tr>
{/}
{if count($fields["lines"]) > 1}
<tr>
  <td></td>
  <td colspan="2" class="grand_total"> ${echo $fields["total"]}</td>
</tr>
{/}
</table>
{/}
