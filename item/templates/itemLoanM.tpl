{page::header()}
{page::toolbar()}
<table class="box">
  <tr>
    <th>Overdue Item Loans</th>
    <th class="right" colspan="5"><a href="{$url_alloc_loanAndReturn}">New Loan/Return Item</a>&nbsp;&nbsp;<a href="{$url_alloc_addItem}">Add/Edit/Remove Item</a></th>
  </tr>
  <tr>
    <td colspan="6">
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
