{page::header()}
{page::toolbar()}
{$table_box}
  <tr>
    <th>Clients</th>
    <th class="right"><a href="{$url_alloc_client}">New Client</a></th>
  </tr>
  <tr>
    <td colspan="2" align="center">{show_filter()}</td>
  </tr>
  <tr>
    <td colspan="2">
      {show_client_list()}
    </td>
  </tr>
</table>
{page::footer()}
