{show_header()}
{show_toolbar()}

<form action="{$url_alloc_searchTransaction}" method="post">

{$table_box}
  <tr>
    <th colspan="7">Search All Transactions</th>
  </tr>
  <tr>
    <td align="center" colspan="7">
      <table align="center" class="filter">
        <tr align="center">
          <td align="center"><b>Transaction Desc</td>
          <td align="center"><b>Which TF</td>
          <td align="center"><b>Status</td>
          <td align="center"><b>Date in YYYY-MM-DD format</td>
          <td rowspan="3" align="center"></td>
          <td align="center"><b>Transaction ID</td>
          <td rowspan="3" align="center"></td>
          <td align="center"><b>Expense Form ID</td>
        </tr>
        <tr align="center">
          <td><input type="text" size="20" name="product" value="{$product}"></td>
          <td><select name="tfID" value={$tfID}>{$tfOptions}</select></td>
          <td><select name="status" value={$status}>{$statusOptions}</select></td>
          <td>&nbsp;&nbsp;  
            <input type="text" size="11" name="dateOne" value="{$dateOne}">
            <input type="button" onClick="dateOne.value='{$today}'" value="Today">
            &nbsp; to &nbsp;  
            <input type="text" size="11" name="dateTwo" value="{$dateTwo}">
            <input type="button" onClick="dateTwo.value='{$today}'" value="Today">
          </td>
          <td><input type="text" size="5" name="transactionID" value="{$transactionID}"></td>
          <td><input type="text" size="5" name="expenseFormID" value="{$expenseFormID}"></td>
          <td colspan="5"><input type="submit" name="search" value="Filter"></td>
        </tr>
        <tr>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td><b>Transaction ID </td>
    <td><b>Name </td>
    <td><b>Product </td>
    <td><b>Type </td>
    <td align="right"><b>Amount </td>
    <td align="center"><b>Transaction Date </td>
    <td><b>Status</td>
  </tr>
  {startSearch("templates/searchTransactionR.tpl")}
</table>



</form>
{show_footer()}






