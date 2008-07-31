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
          <td align="center"><b>Source TF</td>
          <td align="center"><b>Dest TF</td>
          <td align="center"><b>Status</td>
          <td align="center"><b>Date in YYYY-MM-DD format</td>
          <td rowspan="3" align="center"></td>
          <td align="center"><b>Transaction ID</td>
          <td rowspan="3" align="center"></td>
          <td align="center"><b>Expense Form ID</td>
        </tr>
        <tr align="center">
          <td><input type="text" size="20" name="product" value="{$product}"></td>
          <td><select name="fromTfID">{$fromTfOptions}</select></td>
          <td><select name="tfID">{$tfOptions}</select></td>
          <td><select name="status" value={$status}>{$statusOptions}</select></td>
          <td>&nbsp;&nbsp;  
            {get_calendar("dateOne",$TPL["dateOne"])}
            &nbsp; to &nbsp;  
            {get_calendar("dateTwo",$TPL["dateTwo"])}
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
