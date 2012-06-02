{page::header()}
{page::toolbar()}
<table class="box">
  <tr>
    <th class="header">Invoices
      <b> - {print count($invoiceListRows)} records</b>
      <span>
        <a class='magic toggleFilter' href=''>Show Filter</a>
        {if $current_user->have_role("admin")}
          <a href="{$url_alloc_invoice}">New Invoice</a>
        {/}
      </span>
    </th>
  </tr>
  <tr>
    <td align="center">
      {show_filter()}
    </td>
   </tr>
   <tr>
    <td>
      {invoice::get_list_html($invoiceListRows,$_FORM)}
    </td>
  </tr>
</table>
{page::footer()}
