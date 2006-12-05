{show_header()}
{show_toolbar()}
{$table_box}
  <tr>
    <th>Allocate Invoice Item</th>
    <th class="right">{$next_link}
                      <a href="{$url_alloc_invoiceItemList}mode={$mode}">Invoice Item List</a>
    </th>
    
  </tr>  
  <tr>
    <td align="right" width="30%">ID: </td>
    <td>{$invoiceItemID}</td>
  </tr>
  <tr>
    <td align="right" width="30%">Invoice Number: </td>
    <td>{$invoiceNum}</td>
  </tr>
  <tr>
    <td align="right">Invoice Date: </td>
    <td>{$invoiceDate}</td>
  </tr>
  <tr>
    <td align="right">Status: </td>
    <td>{$status}</td>
  </tr>
  <tr>
    <td align="right">Invoice Name: </td>
    <td>{$invoiceName}</td>
  </tr>
  <tr>
    <td align="right">Line Item: </td>
    <td>{$iiMemo}</td>
  </tr>
  <tr>
    <td align="right">Amount:</td><td>${$iiAmount}</td>
  </tr>
  <tr>
    <td align="right">Allocated:</td><td>${$allocated_amount}</td>
  </tr>
  <tr>
    <td align="right">Unallocated:</td><td>${$unallocated_amount}</td>
  </tr>
  <tr>
    <td colspan="2" align="center">
    <form action="{$url_alloc_invoiceItem}" method="post">
    <input type="hidden" name="invoiceItemID" value="{$invoiceItemID}">
    <input type="hidden" name="mode" value="{$mode}">
        <table width="100%" align="center">
          <tr>
            <td align="center">
              <input type="submit" name="mark_pending" value="Invoice Item Pending">&nbsp;
              <input type="submit" name="mark_allocated" value="Invoice Item Allocated" onClick="return checkAllocated()">&nbsp;
              <input type="submit" name="mark_paid" value="Invoice Item Paid" onClick="return checkPaid()"></td>
          </tr>
        </table>
    </form>


</table>

<br>

{$table_box}
	<tr>
    <th colspan="3">Time Sheets</th>
  </tr>
  <tr>
    <td>Owner</td>
    <td>Amount</td>
    <td>Time Sheet</td>
  </tr>
	{show_timeSheets("templates/invoiceItemTimeSheetsR.tpl")}
</table>

<br>

{$table_box}
  <tr>
    <th colspan="6">Transactions</th>
	</tr>
  <tr>
    <td>Time Sheet ID</td>
    <td>Date</td>
    <td>TF</td>
    <td>Amount</td>
    <td><form method="post" action="{$url_alloc_invoiceItem}mode={$mode}">
    <input type="hidden" name="invoiceItemID" value="{$invoiceItemID}">
    {$p_button}{$a_button}{$r_button}&nbsp;</form></td>
    <td>Action</td>
  </tr>
  {show_transaction_list("templates/invoiceItemTransactionR.tpl")}
  {show_new_transaction("templates/invoiceItemTransactionR.tpl")}
</table><br>

<br>
  <script>
    <!--
    function checkAllocated() \{
      if ({$unallocated_amount} != 0)
        return confirm('There is still ${$unallocated_amount} unallocated.  Are you sure you want to mark this invoices as allocated?');
      else
        return true;
    \}

    function checkPaid() \{
      if (!checkAllocated()) \{
        return false;
      \}

      if (!{$all_approved}) \{
        return confirm('There are still transactions marked as pending.  Are you sure you want to mark this invoice as paid?');
      \} else \{
        return true;
      \}
    \}
    // -->
  </script>
{show_footer()}
