{show_header()}
{show_toolbar()}

{$table_box}
  <tr>
    <th colspan="9" class="nobr">{$title}</th>
  </tr>
  <tr>
    <td colspan="9" class="center" align="center">{show_filter()}</td>
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
    <td colspan="9">{show_transaction_list()}</td>
  </tr>
</table>



{show_footer()}
