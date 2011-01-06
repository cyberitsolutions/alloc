{page::header()}
{page::toolbar()}

<table class="box">
  <tr>
    <th class="header">Repeating Transactions
      <span>
        <a href="{$url_alloc_transactionRepeat}">New Repeating Expense</a>
      </span>
    </th>
  </tr>
  <tr>
    <td>
      <table class="list sortable">
        <tr>
          <th>Name</th>
          <th>Start Date</th>
          <th>End Date</th>
          <th>From TF</th>
          <th>Dest TF</th>
          <th>Status</th>
        </tr>
        {show_expenseFormList("templates/transactionRepeatListR.tpl")}
      </table>
    </td>
  </tr>
</table>
{page::footer()}
