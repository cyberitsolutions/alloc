{show_header()}
{show_toolbar()}


{$table_box}
  <tr>
    <td>Element</td>
  </tr>
  {$rows = htmlElement::get_list_children();}
  {foreach $rows as $htmlElementID => $row}
  <tr>
    <td style="padding-left:{$row.padding}">hey{$row.label}</td>
  </tr>


  {/}

</table>


{show_footer()}
