{page::header()}
{page::toolbar()}
<table class="box">
  <tr>
    <th>Invoices</th>
    <th class="right">
      <a class='magic toggleFilter' href=''>Show Filter</a>
      {if $current_user->have_role("admin")}
        <a href="{$url_alloc_invoice}">New Invoice</a>
      {/}
    </th>
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
