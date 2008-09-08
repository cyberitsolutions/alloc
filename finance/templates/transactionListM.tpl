{page::header()}
{page::toolbar()}

{$table_box}
  <tr>
    <th class="nobr">{$title}</th>
  </tr>
  <tr>
    <td align="center">{show_filter()}</td>
  </tr>
  <tr>
    <td>
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
    <td>{show_transaction_list()}</td>
  </tr>
</table>

{page::footer()}
