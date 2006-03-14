{:show_header}
  {:show_toolbar}


{error}
<form method="post" action="{url_alloc_newLoan}">
{table_box}
  <tr>
    <th>New Loan</th>
    <th class="right" colspan="3"><a href="{url_alloc_loans}">Return To Main Items</a></th>
  </tr>
  <tr>
    <td><b>Item</b></td>
    <td><b>Type</b></td>
    <td><b>Status/Due Back</b></td>
    <td><b>Action</b></td>
  </tr>
  {:show_items templates/loanAndReturnR.tpl}

</table>
</form>


{:show_footer}
