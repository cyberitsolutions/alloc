{show_header()}
{show_toolbar()}
{$error}
<form method="post" action="{$url_alloc_newLoan}">
{$table_box}
  <tr>
    <th>New Loan</th>
    <th class="right" colspan="3"><a href="{$url_alloc_loans}">Return To Main Items</a></th>
  </tr>
  <tr>
    <td colspan="4">
      {$table_list}
        <tr>
          <th>Item</th>
          <th>Type</th>
          <th>Status/Due Back</th>
          <th>Action</th>
        </tr>
        {show_items("templates/loanAndReturnR.tpl")}
      </table>
    </td>
  </tr>
</table>
</form>


{show_footer()}
