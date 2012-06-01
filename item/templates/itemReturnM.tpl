{page::header()}
  {page::toolbar()}

<form method="post" action="{$url_alloc_item}">


<input type="hidden" name="dateBorrowed" value="{$dateBorrowed}">
<input type="hidden" name="itemID" value="{$itemID}">
<input type="hidden" name="personID" value="{$personID}">
<input type="hidden" name="dateToBeReturned" value="{$dateToBeReturned}">

<table class="box">
  <tr>
    <th class="header">Return Item
      <span>
        <a href="{$url_alloc_loanAndReturn}">Back to Loans and Returns</a>
      </span>
    </th>
  </tr>
  <tr>
    <td>
      <table class="list">
        <tr>
          <th>Item</th>
          <th>Notes</th>
          <th></th>
        </tr>
        <tr>
          <td>{=$itemName}</td>
          <td>{=$itemNotes}</td>
          <td class="right">
            <button type="submit" name="returnItem" value="1" class="save_button">Return Item<i class="icon-ok-sign"></i></button>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<input type="hidden" name="sessID" value="{$sessID}">
</form>












{page::footer()}
