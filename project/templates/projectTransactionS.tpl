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
          <th>ID</th>
          <th>Type</th>
          <th>TF</th>
          <th>Description</th>
          <th>Date Modified</th>
          <th>Status</th>
          <th>Amount</th>
        </tr>
        {show_transaction("templates/project_transactionItemR.tpl")}
      </table>
    </td>
  </tr>
</table>

