{if $reminderRows}
<table class="sortable list">
  <tr>
    <th>When</th>
    <th>Subject</th>
    <th>Repeat</th>
    <th>Active</th>
  </tr>
  {foreach (array)$reminderRows as $r}
  <tr>
    <td>
        {$r.reminderTime} {$r.tokenAction}
    </td>
    <td><a href="{$url_alloc_reminder}step=3&reminderID={$r.rID}&returnToParent={$returnToParent}">{=$r.reminderSubject}</a></td>
    <td>
    {if $r["reminderRecuringValue"]}
      {$r["reminderRecuringValue"] > 1 and $plural = "s"}
      Every {$r.reminderRecuringValue} {$r.reminderRecuringInterval}{$plural}
    {/}
    </td>
    <td>{if $r["reminderActive"]}yes{else}no{/}</td>
  </tr>
  {/}
</table>
{/}
