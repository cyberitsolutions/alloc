{page::header()}
{page::toolbar()}


{foreach $star_entities as $entity => $e}
{$rows = array()}
{has($entity) and $rows = $entity::get_list($e["form"])}
{if $rows}
{$printed_something = true}
<table class="box">
  <tr>
    <th class="header">{$e.label}
      <b> - {print count($rows)} records</b>
    </th>
  </tr>
  <tr>
    <td>
      {$entity::get_list_html($rows,$e["form"])}
    </td>
  </tr>
</table>
{/}
{/}

{if !$printed_something}
  <br><br>
  No items have been starred yet.
  <br><br>
  Go and click the stars on the task list page (for example), and then use this page to quickly get back to your favourites.
  <br><br>
{/}

{page::footer()}
