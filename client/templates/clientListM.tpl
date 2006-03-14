{:show_header}
{:show_toolbar}
{table_box}
  <tr>
    <th>Clients</th>
    <th class="right" colspan="4">{nav_links}</th>
  </tr>
  <tr>
    <td colspan="4" align="center">{:show_filter templates/clientListFilterS.tpl}</td>
  </tr>
  <tr>
    <td>Client Name</td>
    <td>Contact Name</td>
    <td>Contact Phone</td>
    <td>Contact E-mail</td>
  </tr>
  {:show_client templates/clientListR.tpl}
</table>
{:show_footer}
