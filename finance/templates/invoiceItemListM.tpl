{show_header()}
{show_toolbar()}

{$table_box}
    <tr>
      <th colspan="6">Invoices to be {$mode_desc}</th>
    </tr>
    <tr>
      <td>ID</td>
      <td><a href="{$url_alloc_invoiceItemList}sort=invoiceDate&mode={$mode}">Date</a></td>
      <td><a href="{$url_alloc_invoiceItemList}sort=invoiceNum&mode={$mode}">Invoice Number</a></td>
      <td><a href="{$url_alloc_invoiceItemList}sort=invoiceName&mode={$mode}">Name</a></td>
      <td>Memo</td>
      <td align="right"><a href="{$url_alloc_invoiceItemList}sort=iiAmount&mode={$mode}">Amount</td>
    </tr>
    {show_invoices("templates/invoiceItemListR.tpl")}
  </table>
{show_footer()}
