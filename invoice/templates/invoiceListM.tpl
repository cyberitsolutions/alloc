{page::header()}
{page::toolbar()}
<table class="box">
  <tr>
    <th>Invoices</th>
    <th class="right">{$invoice_links}</th>
  </tr>
  <tr>
    <td align="center" colspan="2">
      {show_filter()}
    </td>
   </tr>
   <tr>
    <td colspan="2">
      {show_invoice_list()}
    </td>
  </tr>
</table>
{page::footer()}
