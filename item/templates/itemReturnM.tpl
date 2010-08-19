{page::header()}
  {page::toolbar()}

<form method="post" action="{$url_alloc_item}">


<input type="hidden" name="dateBorrowed" value="{$dateBorrowed}">
<input type="hidden" name="itemID" value="{$itemID}">
<input type="hidden" name="personID" value="{$personID}">
<input type="hidden" name="dateToBeReturned" value="{$dateToBeReturned}">

<table class="box">
  <tr>
    <th>Return Item</th>
    <th class="right"><a href="{$url_alloc_loanAndReturn}">Back to Loans and Returns</a></th>
  </tr>
  <tr>
    <td>Item</td>
    <td>{=$itemName}</td>
  </tr>
  <tr>
    <td>Notes</td>
    <td>{=$itemNotes}</td>
  </tr>
  <tr>
    <td colspan="2"><input type="submit" name="returnItem" value="Return Item"></td>
  </tr>
</table>

</form>












{page::footer()}
