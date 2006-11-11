{show_header()}
{show_toolbar()}

<form method="post" action="{$url_alloc_item}">
<input type="hidden" name="dateBorrowed" value="{$dateBorrowed}">
<input type="hidden" name="itemID" value="{$itemID}">
<input type="hidden" name="personID" value="{$personID}">
<input type="hidden" name="dateToBeReturned" value="{$dateToBeReturned}">

{$table_box}
  <tr>
    <th>Item</th>
    <th class="right"><a href="{$url_alloc_loanAndReturn}">Back to Loans and Returns</a></th>
  </tr>
  <tr>
    <td>Item Name</td>
    <td>{$itemName}</td>
  </tr>
  <tr>
    <td>Notes</td>
    <td>{$itemNotes}</td>
  </tr>
  <tr>
    <td>Borrower</td>
    <td>{$userSelect}</td>
  </tr>
  <tr>
    <td>Loan Duration (months)</td>
    <td><input type="text" size="3" name="timePeriod" value="1"></td>
  </tr>
  <tr>
    <td colspan="2"><input type="submit" name="borrowItem" value="Borrow Item"></td>
  </tr>
</table>



</form>

{show_footer()}

