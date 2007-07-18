{show_header()}
{show_toolbar()}
<form action="{$url_alloc_transaction}" method="post">
<input type="hidden" name="expenseFormID" value={$expenseFormID}>
<input type="hidden" name="dateEntered" value={$dateEntered}>
<input type="hidden" name="quantity" value={$quantity}>
<input type="hidden" name="transactionID" value={$transactionID}>
{$table_box}
  <tr>
    <th colspan="3">Transaction {$transactionID}</th>
  </tr>
  <tr>
    <td width="15%">Transaction ID</td>
    <td>{$transactionID}&nbsp;</td>
    <td class="hint">This is an automatically generated number that uniquely identifies the transaction</td>
  </tr>
  <tr>
    <td>TF</td>
    <td><select name="tfID">
      <option value="">Select TF
      {$tfIDOptions}
      </select>
    </td>
    <td class="hint">Select the TF which this transaction is to be recorded against</td>
  </tr>
  <tr>
  <tr>
    <td>Project</td>
    <td><select name="projectID">
      <option value="">Select Project
      {$projectIDOptions}
      </select>
    </td>
    <td class="hint">Select the Project which this transaction is to be recorded against</td>
  </tr>
    <td>Transaction Date</td>
    <td><input type="text" name="transactionDate" size="10" value={$transactionDate}><input type="button" onClick="transactionDate.value='{$today}'" value="Today"></td>
    <td class="hint">This is the date of the transaction itself (e.g. the receipt date) in the format YYYY-MM-DD</td>
  </tr>
  <tr>
    <td>Company Details</td>
    <td><input type="text" name="companyDetails" size="20" value="{$companyDetails}"></td>
    <td class="hint">This field may be replaced in the future to allow easy selection of the company (client or supplier), but for now is free text.</td>
  </tr>
  <tr>
    <td>Product/Description</td>
    <td><input type="text" name="product" size="20" value="{$product}"></td>
    <td class="hint">Enter the description that will appear on the TF statement</td>
  </tr>
  <tr>
    <td>Amount</td>
    <td><input type="text" name="amount" size="20" value="{$amount}"></td>
    <td class="hint">Enter the amount of the transaction.  Expenses and salaries should generally be negative amounts.  Do no include the $ character, spaces or commas</td>
  </tr>
  <tr>
    <td>Status</td>
    <td><select name="status" value={$status}>
        <option value="">Select status
      {$statusOptions}
</select>
    </td>      
    <td class="hint">Select the status of this transaction.  Pending transactions indicate that the system has registered the transaction but it has not yet been finalised (e.g. an invoice that is due to be paid.  Approved transactions are completed transactions.  Rejected transactions are transactions that were never finalised (e.g. invoices which are no longer being chased).</td>
  </tr>
  <tr>
    <td>Transaction Type</td>
    <td><select name="transactionType">
        <option value="">Select Type
        {$transactionTypeOptions}
</select>
    </td>      
    <td class="hint">Select salary, invoice or expense</td>
  </tr>
  <tr>
    <td>Invoice</td>
    <td>#{$invoiceNum} {$invoiceDate}&nbsp;</td>
    <td class="hint">If this transaction is associated with an invoice the invoice number and date are shown.</td>
  </tr>
  <tr>
    <td>Expense Form ID</td>
    <td>{$expenseFormID}&nbsp;</td>
    <td class="hint">If this transaction is associated with an expense form, the expense form's identification number is shown.</td>
  </tr>
  <tr>
    <td>Time Sheet ID</td>
    <td>{$timeSheetID}&nbsp;</td>
    <td class="hint">If a time sheet is associated with this transaction, the time sheet's identification number is shown.</td>
  </tr>
  <tr>
    <td>Last Modified By User ID</td>
    <td>{$transactionModifiedUser}&nbsp;</td>
    <td class="hint">The user who last modified this record</td>
  </tr>
  <tr>
    <td>Last Modified On</td>
    <td>{$transactionModifiedTime}&nbsp;</td>
    <td class="hint">The date that this record was last modified</td>
  </tr>

  <tr>
    <td align="center" colspan="3">
      <input type="submit" name="save" value="Save">
      <input type="submit" name="saveAndNew" value="Save and New">
      <input type="submit" name="saveGoTf" value="Save and go to TF">
      <input type="submit" name="delete" value="Delete">
    </td>
  </tr>
  
</table>

<br><br>


</form>


{show_footer()}






