{page::header()}
{page::toolbar()}

{$table_box}
  <tr>
    <th colspan="4">Repeating Transactions</th>
    <th class="right"><a href="{$url_alloc_transactionRepeat}">New Repeating Expense</a></th>
  </tr>
  <tr>
    <td colspan="5">
      {$table_list}
        <tr>
          <th>Name</th>
          <th>Start Date</th>
          <th>End Date</th>
          <th>TF</th>
          <th>Status</th>
        </tr>
        {show_expenseFormList("templates/transactionRepeatListR.tpl")}
      </table>
    </td>
  </tr>
</table>
{page::footer()}
