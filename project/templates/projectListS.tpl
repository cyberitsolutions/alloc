{if $projectListRows}
<table class="list sortable">
  <tr>
    <th>Project</th>
    <th>Nick</th>
    <th>Client</th>
    <th>Type</th>
    <th>Status</th>
    <th class="noprint">&nbsp;</th>
  </tr>
  {foreach $projectListRows as $r}
  <tr>
    <td>{$r.projectLink}</td>
    <td>{=$r.projectShortName}</td>
    <td>{=$r.clientName}</td>
    <td>{=$r.projectType}</td>
    <td>{=$r.projectStatus}</td>
    <td class="noprint" align="right">{$r.navLinks}</td>
  </tr>
  {/}
</table>
{/}
