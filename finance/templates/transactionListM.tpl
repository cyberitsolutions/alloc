{page::header()}
{page::toolbar()}

<table class="box">
  <tr>
    <th class="nobr">{=$title}</th>
    <th class="right"><a class='magic toggleFilter' href=''>Show Filter</a></th>
  </tr>
  <tr>
    <td align="center" colspan="2">{show_filter()}</td>
  </tr>
  <tr>
    <td colspan="2">
    <table align="center">
       <tr>
        <td align="right" class="transaction-approved"><strong>Total Balance:</strong></td> 
        <td align="left" class="transaction-approved">{page::money(config::get_config_item("currency"),$balance,"%s%m %c")}</td>
      </tr>
      <tr>
        <td align="right" class="transaction-pending"><strong>Total Pending:</strong></td> 
        <td align="left" class="transaction-pending">{page::money(config::get_config_item("currency"),$pending_amount,"%s%m %c")}</td>
      </tr>
      <tr>
        <td align="right" class="transaction-approved"><strong>Opening Balance:</strong></td> 
        <td align="left" class="transaction-approved">{page::money(config::get_config_item("currency"),$totals["opening_balance"],"%s%m %c")}</td>
      </tr>
    </table>
    </td>
  </tr>
  <tr>
    <td colspan="2">{$list}</td>
  </tr>
</table>

{page::footer()}
