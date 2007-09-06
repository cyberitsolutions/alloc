{show_header()}
{show_toolbar()}

{$table_box}
  <tr>
    <th colspan="5" width="50%"><nobr>{$title}</nobr></th>

    <th class="right" colspan="4" width="50%">{$switch_sort_views}</th>
  </tr>
  <tr>
    <td align="center" colspan="9">
      <form action="{$url_alloc_transactionList}" method="post">
      <table class="filter" align="center">
        <tr>
          <td align="center" colspan="10">
            <a href={$month_prev_link}>&lt;&lt; Prev Month</a>&nbsp;
            <a href={$month_curr_link}>{$now}</a>&nbsp;
            <a href={$month_next_link}>Next Month &gt;&gt;</a>
          </td>
        </tr>
        <tr>
          <td align="left">Year</td>
          <td align="left">Month 
          </td>
          <td align="left">Type</td>
          <td align="left">Status</td>
          <td align="left">&nbsp;</td>
        </tr>
        <tr>
          <td><select name="year">{$yearOptions}</select></td>
          <td><select name="month"><option value="ALL"> -- ALL -- {$monthOptions}</select></td>
          <td><select name="transactionType"><option value="ALL"> -- ALL -- {$transactionTypeOptions}</select></td>
          <td><select name="status"><option value="ALL"> -- ALL -- {$statusOptions}</select></td>
          <td><input type="hidden" name="tfID" value="{$tfID}">
              <input type="submit" name="download" value="Download">
              <input type="submit" name="filter" value="Filter">
          </td>
        </tr>
      </table>
			</form>
		</td>
  </tr>
  <tr>
    <td colspan="9">
    <table align="center">
       <tr>
        <td align="right" class="transaction-approved"><strong>Balance:</strong></td> 
        <td align="left" class="transaction-approved">${$balance}</td>
      </tr>
      <tr>
        <td align="right" class="transaction-pending"><strong>Pending:</strong></td> 
        <td align="left" class="transaction-pending">${$pending_amount}</td>
      </tr>
    </table>
    </td>
  </tr>
  <tr>
    <td width="1%">ID</td>
    <td width="1%">Type</td>
    <td width="1%">Status</td>
    <td width="1%">Date</td>
    <td width="1%">Last Modified</td>
    <td>Product</td>
    <td class="right" align="right" width="1%">Credit</td>
    <td class="right" align="right" width="1%">Debit</td>
    <td class="right" align="right" width="1%">Balance</td>
  </tr>
  <tr>
    <td colspan="8">&nbsp;</td>
    <td align="right">{$opening_balance}&nbsp;</td>
  </tr>
{show_transaction("templates/transactionListR.tpl")}
  <tr>
    <td colspan="6">&nbsp;</td>
    <td align="right" style="border-top:1px solid black;">{$total_amount_positive}&nbsp;</td>
    <td align="right" style="border-top:1px solid black;">{$total_amount_negative}&nbsp;</td>
    <td align="right" style="border-top:1px solid black;">{$running_balance}&nbsp;</td>
  </tr>










</table>









{show_footer()}
