<table class="list sortable">
  <tr>
    <th width="5%" class="sorttable_numeric">ID</th>
    <th>Created By</th>
    <th class="right">Form Total</th>
  </tr>
  {foreach (array)$expenseFormRows as $r}
  <tr>
    <td><a href="{$url_alloc_expenseForm}expenseFormID={$r.expenseFormID}&edit=true">{$r.expenseFormID}</a></td>
    <td>{=$r.expenseFormCreatedUser}</td>
    <td align="right" class="obfuscate">&nbsp;{$r.formTotal}</td>
  </tr>
  {/}
</table>
