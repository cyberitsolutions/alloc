{page::header()}
{page::toolbar()}

<table class="box">
  <tr>
    <th>Pending Expense Forms</th>
  </tr>
  <tr>
    <td>
      <table class="list sortable">
        <tr>
          <th width="5%" class="sorttable_numeric">ID</th>
          <th>Created Date</th>
          <th>Created By</th>
          <th>Payment Method</th>
          <th class="right">Form Total</th>
        </tr>
        {foreach (array)$expenseFormRows as $r}
        <tr>
          <td><a href="{$url_alloc_expenseForm}expenseFormID={$r.expenseFormID}&edit=true">{$r.expenseFormID}</a></td>
          <td>{$r.expenseFormCreatedTime}</td>
          <td>{=$r.expenseFormCreatedUser}</td>
          <td>{$r.rr_label}</td>
          <td align="right">&nbsp;{$r.formTotal}</td>
        </tr>
        {/}
      </table>
    </td>
  </tr>
</table>

<table class="box">
  <tr>
    <th>Pending Repeat Transactions</th>
  </tr>
  <tr>
    <td>
      <table class="list sortable">
        <tr>
          <th width="5%" class="sorttable_numeric">ID</th>
          <th>Created Date</th>
          <th>Created By</th>
          <th>Transaction Type</th>
          <th class="right">Form Total</th>
        </tr>
        {foreach (array)$transactionRows as $r}
        <tr>
          <td><a href="{$url_alloc_transaction}transactionID={$r.transactionID}">{$r.transactionID}</a></td>
          <td>{$r.transactionCreatedTime}</td>
          <td>{=$r.transactionCreatedUser}</td>
          <td>{$r.transactionType}</td>
          <td align="right">&nbsp;{page::money($r["currencyTypeID"],$r["formTotal"],"%s%mo")}</td>
        </tr>
        {/}
      </table>
    </td>
  </tr>
</table>


{page::footer()}
