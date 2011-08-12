{page::header()}
{page::toolbar()}
<form action="{=$url_alloc_importCSV}" method="post">
<input type="hidden" name="projectID" value="{=$projectID}">
<input type="hidden" name="filename" value="{=$filename}">
<table class="box">
  <tr>
  <th>CSV Import Results</th>
  </tr>
  <tr>
  <td>

  <table class="list">
  <tr><th>Task</th><th>Result</th></tr>
  <tr>
  {foreach $result as $id => $res}
  <td>
  {if $id == 0}
  None
  {else}
  <a href="{$url_alloc_task}taskID={$id}">{$id}</a>
  {/}
  </td><td>
  <ul>
  {foreach $res as $item}
  <li>{$item}</li>
  {/}
  </ul>
  </td>
  </tr>
  {/}
  </table>

  </td>
  </tr>
  <tr>
  <td class="padded" align="center">
  <a href="{$url_alloc_project}projectID={$projectID}">Return to project</a>
  </td></tr>
</table>
<input type="hidden" name="sessID" value="{$sessID}">
</form>
{page::footer()}
