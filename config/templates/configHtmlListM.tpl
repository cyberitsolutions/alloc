{show_header()}
{show_toolbar()}


{$table_box}
  <tr>
    <th>HTML Element List</th>
    <th class="right" colspan="4"><a href="{$url_alloc_configHtml}">New HTML Element</a></th>
  </tr>
  <tr>
    <td>Element</td>
    <td>Widget</td>
    <td>Help</td>
  </tr>
  {$rows = htmlElement::get_list_children();}
  {foreach $rows as $htmlElementID => $row}
  <tr>
    <td style="padding-left:{$row.padding}px">
      <a href="{$url_alloc_configHtml}htmlElementID={$row.htmlElementID}">{$row.label} ({$row.handle})</a>
      {if $row["hasChildElement"]}
        <a href="{$url_alloc_configHtml}htmlElementParentID={$row.htmlElementID}">New Child</a>
      {/}
    </td>
    <td>{echo get_html($row["handle"])}</td>
    <td>{echo get_help($row["handle"])}</td>
  </tr>


  {/}

</table>


{show_footer()}
