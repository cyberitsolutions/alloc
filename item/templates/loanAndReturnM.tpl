{page::header()}
{page::toolbar()}
{$error}
<form method="post" action="{$url_alloc_newLoan}">
<table class="box">
  <tr>
    <th class="header">New Loan
      <span>
        <a href="{$url_alloc_loans}">Return To Main Items</a>
      </span>
    </th>
  </tr>
  <tr>
    <td>
      <table class="list sortable">
        <tr>
          <th>Item</th>
          <th>Type</th>
          <th>Status/Due Back</th>
          <th>Action</th>
        </tr>
        {show_items("templates/loanAndReturnR.tpl")}
      </table>
    </td>
  </tr>
</table>
<input type="hidden" name="sessID" value="{$sessID}">
</form>


{page::footer()}
