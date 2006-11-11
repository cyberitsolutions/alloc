{show_header()}
{show_toolbar()}
{$table_box}
  <tr>
    <th>Item Loans</th>
    <th class="right" colspan="5"><a href="{$url_alloc_loanAndReturn}">New Loan/Return Item</a>&nbsp;&nbsp;<a href="{$url_alloc_addItem}">Add/Edit/Remove Item</a></th>
  </tr>
  <tr>
    <td colspan="6" align="center"><b>OVERDUE</b></td>
  </tr>
  <tr>
    <td>Item Name</td>
    <td>Item Type</td>
    <td>Borrower</td>
    <td>Date Borrowed</td>
    <td>Date To Be Returned</td>
    <td>Status</td>
  </tr>
  {show_overdue("templates/itemLoanR.tpl")}
</table>

{show_footer()}
