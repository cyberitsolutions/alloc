{page::header()}
{page::toolbar()}
<table class="box">
  <tr>
    <th class="header">Overdue Item Loans
      <span>
        <a href="{$url_alloc_loanAndReturn}">New Loan/Return Item</a>
        <a href="{$url_alloc_addItem}">Add/Edit/Remove Item</a>
      </span>
    </th>
  </tr>
  <tr>
    <td>
      <table class="list sortable">
        <tr>  
          <th>Item Name</th>
          <th>Item Type</th>
          <th>Borrower</th>
          <th>Date Borrowed</th>
          <th>Date To Be Returned</th>
          <th>Status</th>
        </tr>
        {show_overdue("templates/itemLoanR.tpl")}
      </table>
    </td>
  </tr>
</table>

{page::footer()}
