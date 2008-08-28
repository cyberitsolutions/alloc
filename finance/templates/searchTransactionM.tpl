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
        <tr>
          <td><b>Transaction Desc</td>
          <td><b>Source TF</td>
          <td><b>Dest TF</td>
          <td><b>Status</td>
          <td><b>From</td>
          <td><b>To</td>
          <td rowspan="3" align="center"></td>
          <td><b>Transaction ID</td>
          <td rowspan="3" align="center"></td>
          <td><b>Expense Form ID</td>
        </tr>
        <tr align="center">
          <td><input type="text" size="20" name="product" value="{$product}"></td>
          <td><select name="fromTfID"><option value="">{$fromTfOptions}</select></td>
          <td><select name="tfID"><option value="">{$tfOptions}</select></td>
          <td><select name="status" value={$status}>{$statusOptions}</select></td>
          <td>{get_calendar("dateOne",$TPL["dateOne"])}</td>
          <td>{get_calendar("dateTwo",$TPL["dateTwo"])}</td>
          <td><input type="text" size="10" name="transactionID" value="{$transactionID}"></td>
          <td><input type="text" size="10" name="expenseFormID" value="{$expenseFormID}"></td>
          <td colspan="5"><input type="submit" name="search" value="Filter"></td>
        </tr>
        <tr>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td colspan="7">
      {$table_list}
        <tr>
          <th>Transaction ID</th>
          <th>Source</th>
          <th>Dest</th>
          <th>Product</th>
          <th>Type</th>
          <th align="right">Amount</th>
          <th align="center">Transaction Date</th>
          <th>Status</th>
        </tr>
        {startSearch("templates/searchTransactionR.tpl")}
      </table>
    </td>
  </tr>
</table>
</form>
{show_footer()}
