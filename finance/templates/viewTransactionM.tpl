{page::header()}
{page::toolbar()}
<table class="box">
  <tr>
    <th colspan="2">Transaction</th>
  </tr>
  <tr>
    <td>ID</td>
    <td>{$transactionID}</td>
  </tr>
  <tr>
    <td>Source Tagged Fund</td>
    <td>{$from_tf_link}</td>
  </tr>
  <tr>
    <td>Destination Tagged Fund</td>
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
    <td>{$amount} {$currencyTypeID} {if $currencyTypeID != $destCurrencyTypeID}(exchange rate from {$currencyTypeID} to {$destCurrencyTypeID} is {$exchangeRate}){/}</td>
  </tr>
  <tr>
    <td>Status</td>
    <td>{$status} {$dateApproved}</td>
  </tr>
  <tr>
    <td>Transaction Type</td>
    <td>{$transactionType}</td>
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
{page::footer()}
