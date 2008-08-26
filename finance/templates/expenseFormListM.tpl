{show_header()}
  {show_toolbar()}

{$table_box}
  <tr>
    <th>Pending Expense Forms</th>
  </tr>
  <tr>
    <td>
      {$table_list}
        <tr>
          <th width="5%">ID</th>
          <th>Created Date</th>
          <th>Created By</th>
          <th>Payment Method</th>
          <th class="right">Form Total</th>
        </tr>
        {show_expense_form_list("templates/pendingExpenseFormListR.tpl")}
      </table>
    </td>
  </tr>
</table>


{$table_box}
  <tr>
    <th>Pending Repeat Transactions</th>
  </tr>
  <tr>
    <td>
      {$table_list}
        <tr>
          <th width="5%">ID</th>
          <th>Created Date</th>
          <th>Created By</th>
          <th>Transaction Type</th>
          <th class="right">Form Total</th>
        </tr>
        {show_pending_transaction_list("templates/pendingTransactionListR.tpl")}
      </table>
    </td>
  </tr>
</table>


{show_footer()}
