{page::header()}
{page::toolbar()}

<table class="box">
  <tr>
    <th class="nobr">{$title}</th>
    <th class="right"><a class='magic toggleFilter' href=''>Show Filter</a></th>
  </tr>
  <tr>
    <td align="center" colspan="2">{show_filter()}</td>
  </tr>
  <tr>
    <td colspan="2">
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
    <td colspan="2">{show_transaction_list()}</td>
  </tr>
</table>

{page::footer()}
