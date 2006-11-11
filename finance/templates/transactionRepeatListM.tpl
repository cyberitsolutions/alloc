{show_header()}
{show_toolbar()}

{$table_box}
  <tr>
    <th colspan="4">Repeating Transactions</th>
    <th class="right"><a href="{$url_alloc_transactionRepeat}">New Repeating Expense</a></th>
  </tr>
    <tr>
      <td>Name</td>
      <td>Start Date</td>
      <td>End Date</td>
      <td>TF</td>
      <td>Status</td>
    </tr>
    {show_expenseFormList("templates/transactionRepeatListR.tpl")}
  </table>
{show_footer()}
