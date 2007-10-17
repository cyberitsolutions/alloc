{$table_box}
  <tr>
    <th colspan="5">Transactions</th>
    <th class="right"><a href="{$url_alloc_expenseForm}">New Expense Form</a></th>
  </tr>
  <tr>
    <td>User Name</td>
    <td>Date Modified</td>
    <td>Type</td>
    <td>Status</td>
    <td>Amount</td>
  </tr>
{show_transaction("templates/project_transactionItemR.tpl")}
</table>

