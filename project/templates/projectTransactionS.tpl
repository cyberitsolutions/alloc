<table class="box">
  <tr>
    <th class="header">Transactions
      <span>
        <a href="{$url_alloc_expenseForm}">New Expense Form</a>
      </span>
    </th>
  </tr>
  <tr>
    <td>
      <table class="list">
        <tr>
          <th>User Name</th>
          <th>Date Modified</th>
          <th>Type</th>
          <th>Status</th>
          <th>Amount</th>
        </tr>
        {show_transaction("templates/project_transactionItemR.tpl")}
      </table>
    </td>
  </tr>
</table>

