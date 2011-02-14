{if $projectListRows}
<table class="list sortable">
  <tr>
    <th>Project</th>
    <th>Nick</th>
    <th class="noprint">&nbsp;</th>
  </tr>
  {foreach $projectListRows as $r}
  <tr>
    <td>{$r.projectLink}</td>
    <td>{=$r.projectShortName}</td>
    <td class="noprint" align="right">{$r.navLinks}</td>
  </tr>
  {/}
</table>
{/}
