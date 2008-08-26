{show_header()}
{show_toolbar()}
<form action="{$url_alloc_transaction}" method="post">
<input type="hidden" name="expenseFormID" value={$expenseFormID}>
<input type="hidden" name="quantity" value={$quantity}>
<input type="hidden" name="transactionID" value={$transactionID}>
{$table_box}
  <tr>
    <th colspan="3">Transaction</th>
  </tr>
  <tr>
    <td width="15%">ID</td>
    <td>{$transactionID}&nbsp;</td>
    <td width="1%"></td>
  </tr>
  <tr>
    <td>Source Tagged Fund{mandatory($fromTfID)}</td>
    <td><select name="fromTfID">
      <option value="">
      {$fromTfIDOptions}
      </select>
    </td>
    <td class="hint">{get_help("from_transaction_tf")}</td>
  </tr>

  <tr>
    <td>Destination Tagged Fund{mandatory($tfID)}</td>
    <td><select name="tfID">
      <option value="">
      {$tfIDOptions}
      </select>
    </td>
    <td class="hint">{get_help("transaction_tf")}</td>
  </tr>
  <tr>
  <tr>
    <td>Project</td>
    <td><select name="projectID">
      <option value="">
      {$projectIDOptions}
      </select>
    </td>
    <td class="hint"></td>
  </tr>
    <td>Transaction Date{mandatory($transactionDate)}</td>
    <td>{get_calendar("transactionDate",$TPL["transactionDate"])}</td>
    <td class="hint"></td>
  </tr>
  <tr>
    <td>Company Details</td>
    <td><input type="text" name="companyDetails" size="20" value="{$companyDetails}"></td>
    <td class="hint"></td>
  </tr>
  <tr>
    <td>Product/Description{mandatory($product)}</td>
    <td><input type="text" name="product" size="20" value="{$product}"></td>
    <td class="hint">{get_help("transaction_product")}</td>
  </tr>
  <tr>
    <td>Amount{mandatory($amount)}</td>
    <td><input type="text" name="amount" size="20" value="{$amount}"></td>
    <td class="hint">{get_help("transaction_amount")}</td>
  </tr>
  <tr>
    <td>Status{mandatory($status)}</td>
    <td><select name="status">
          {$statusOptions}
        </select>
    </td>      
    <td class="hint">{get_help("transaction_status")}</td>
  </tr>
  <tr>
    <td>Transaction Type{mandatory($transactionType)}</td>
    <td><select name="transactionType">
        <option value="">
        {$transactionTypeOptions}
</select>
    </td>      
    <td class="hint"></td>
  </tr>
  <tr>
    <td>Invoice Number</td>
    <td>{$invoice_link}&nbsp;</td>
    <td class="hint"></td>
  </tr>
  <tr>
    <td>Expense Form</td>
    <td>{$expenseForm_link}&nbsp;</td>
    <td class="hint"></td>
  </tr>
  <tr>
    <td>Time Sheet</td>
    <td>{$timeSheet_link}&nbsp;</td>
    <td class="hint"></td>
  </tr>
  <tr>
    <td>Created</td>
    <td>{$transactionCreatedUser}&nbsp;{$transactionCreatedTime}</td>
    <td class="hint"></td>
  </tr>
  <tr>
    <td>Last Modified</td>
    <td>{$transactionModifiedUser}&nbsp;{$transactionModifiedTime}</td>
    <td class="hint"></td>
  </tr>
  <tr>
    <td align="center" colspan="3">
      <input type="submit" name="save" value="Save">
      <input type="submit" name="saveAndNew" value="Save and New">
      <input type="submit" name="saveGoTf" value="Save and go to Dest TF">
      <input type="submit" name="delete" value="Delete">
    </td>
  </tr>
  
</table>

<br><br>


</form>


{show_footer()}






