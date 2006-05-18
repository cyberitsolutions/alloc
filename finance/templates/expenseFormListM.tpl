{:show_header}
  {:show_toolbar}

{table_box}
  <tr>
    <th colspan="5">Pending Expense Forms</th>
  </tr>
  <tr>
    <td align="center"><b>ID</b></td>
    <td align="center"><b>Last Modified</b></td>
    <td align="center"><b>Modified By</b></td>
    <td align="center"><b>Payment Method</b></td>
    <td align="right"><b>Form Total</b></td>
  </tr>
  {:show_expense_form_list templates/pendingExpenseFormListR.tpl}
</table>


{table_box}
  <tr>
    <th colspan="5">Pending Repeat Transactions</th>
  </tr>
  <tr>
    <td align="center"><b>ID</b></td>
    <td align="center"><b>Last Modified</b></td>
    <td align="center"><b>Transaction Type</b></td>
    <td align="right"><b>Form Total</b></td>
  </tr>
  {:show_pending_transaction_list templates/pendingTransactionListR.tpl}
</table>


{:show_footer}
