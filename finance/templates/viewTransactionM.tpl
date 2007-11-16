{show_header()}
{show_toolbar()}
{$table_box}
  <tr>
    <th colspan="2">Transaction</th>
  </tr>
  <tr>
    <td>ID</td>
    <td>{$transactionID}</td>
  </tr>
  <tr>
    <td>Tagged Fund</td>
    <td>{$tf_link}</td>
  </tr>
  <tr>
    <td>Project</td>
    <td>{$project_link}</td>
  </tr>
  <tr>
    <td>Transaction Date</td>
    <td>{$transactionDate}</td>
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
    <td>Transaction Type</td>
    <td>{$transactionType}</td>
  </tr>
  <tr>
    <td>Invoice Number</td>
    <td>{$invoice_link}</td>
  </tr>
  <tr>
    <td>Expense Form</td>
    <td>{$expenseForm_link}</td>
  </tr>
  <tr>
    <td>Time Sheet</td>
    <td>{$timeSheet_link}</td>
  </tr>
  <tr>
    <td>Created</td>
    <td>{$transactionCreatedUser}&nbsp;{$transactionCreatedTime}</td>
  </tr>
  <tr>
    <td>Last Modified</td>
    <td>{$transactionModifiedUser}&nbsp;{$transactionModifiedTime}</td>
  </tr>
</table>
{show_footer()}
