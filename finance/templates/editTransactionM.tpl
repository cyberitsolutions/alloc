{page::header()}
{page::toolbar()}
<form action="{$url_alloc_transaction}" method="post">
<input type="hidden" name="expenseFormID" value={$expenseFormID}>
<input type="hidden" name="quantity" value={$quantity}>
<input type="hidden" name="transactionID" value={$transactionID}>
<table class="box">
  <tr>
    <th colspan="3">Transaction</th>
  </tr>
  <tr>
    <td width="15%">ID</td>
    <td>{$transactionID}&nbsp;</td>
    <td width="1%"></td>
  </tr>
  <tr>
    <td>Source Tagged Fund{page::mandatory($fromTfID)}</td>
    <td><select name="fromTfID">
      <option value="">
      {$fromTfIDOptions}
      </select>
	  {$fromTfIDWarning}
    </td>
    <td class="hint">{page::help("from_transaction_tf")}</td>
  </tr>

  <tr>
    <td>Destination Tagged Fund{page::mandatory($tfID)}</td>
    <td><select name="tfID">
      <option value="">
      {$tfIDOptions}
      </select>
	  {$tfIDWarning}
    </td>
    <td class="hint">{page::help("transaction_tf")}</td>
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
    <td>Transaction Date{page::mandatory($transactionDate)}</td>
    <td>{page::calendar("transactionDate",$transactionDate)}</td>
    <td class="hint"></td>
  </tr>
  <tr>
    <td>Company Details</td>
    <td><input type="text" name="companyDetails" size="20" value="{$companyDetails}"></td>
    <td class="hint"></td>
  </tr>
  <tr>
    <td>Product/Description{page::mandatory($product)}</td>
    <td><input type="text" name="product" size="20" value="{$product}"></td>
    <td class="hint">{page::help("transaction_product")}</td>
  </tr>
  <tr>
    <td>Amount{page::mandatory($amount)}</td>
    <td><input type="text" name="amount" size="20" value="{$amount}"><select name="currencyTypeID">{$currencyOptions}</select></td>
    <td class="hint">{page::help("transaction_amount")}</td>
  </tr>
  <tr>
    <td>Status{page::mandatory($status)}</td>
    <td><select name="status">
          {$statusOptions}
        </select>
      {if $dateApproved} Date Approved: {$dateApproved}{/}
    </td>      
    <td class="hint">{page::help("transaction_status")}</td>
  </tr>
  <tr>
    <td>Transaction Type{page::mandatory($transactionType)}</td>
    <td><select name="transactionType"><option value="">{$transactionTypeOptions}</select></td>      
    <td class="hint"></td>
  </tr>
  <tr>
    <td>Exchange Rate{page::mandatory($exchangeRate)}</td>
    <td><input type="text" name="exchangeRate" size="20" value="{$exchangeRate}"> ({$currencyTypeID} to {echo config::get_config_item("currency")})</td>
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
      <button type="submit" name="delete" value="1" class="delete_button">Delete<i class="icon-trash"></i></button>
      <button type="submit" name="saveGoTf" value="1" class="save_button">Save &amp; go to Dest TF<i class="icon-arrow-right"></i></button>
      <button type="submit" name="saveAndNew" value="1" class="save_button">Save &amp; New<i class="icon-plus-sign"></i></button>
      <button type="submit" name="save" value="1" class="save_button default">Save<i class="icon-ok-sign"></i></button>
    </td>
  </tr>
  
</table>

<br><br>


<input type="hidden" name="sessID" value="{$sessID}">
</form>


{page::footer()}






