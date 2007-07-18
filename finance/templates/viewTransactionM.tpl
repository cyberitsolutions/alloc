{show_header()}
{show_toolbar()}
{$table_box}
  <tr>
    <th colspan="2">Transaction {$transactionID}</th>
  </tr>
  <tr>
    <td>Transcation ID</td>
    <td>{$transactionID}</td>
  </tr>
  <tr>
    <td>Company Details</td>
    <td>{$companyDetails}</td>
  </tr>
  <tr>
    <td>Product/Description</td>
    <td>{$product}</td>
  </tr>
  <tr>
    <td>Amount</td>
    <td>{$amount}</td>
  </tr>
  <tr>
    <td>Status</td>
    <td>{$status}</td>
  </tr>
  <tr>
    <td>Invoice</td>
    <td>#{$invoiceNum} {$invoiceDate}</td>
  </tr>
  <tr>
    <td>Expense Form ID</td>
    <td>{$expenseFormID}</td>
  </tr>
  <tr>
    <td>Time Sheet ID</td>
    <td>{$timeSheetID}</td>
  </tr>
  <tr>
    <td>Last Modfied By User ID</td>
    <td>{$transactionModifiedUser}</td>
  </tr>
  <tr>
    <td>Last Modfied On</td>
    <td>{$transactionModifiedTime}</td>
  </tr>
</table>
{show_footer()}
