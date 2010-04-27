{page::header()}
{page::toolbar()}
<table class="box">
  <tr>
    <th>Products</th>
    <th class="right">
      <a href="{$url_alloc_product}">New Product</a>
      <a href="{$url_alloc_productSale}">New Sale</a>
    </th>
  </tr>
  <tr>
    <td colspan="2">
      {echo product::get_list()}
    </td>
  </tr>
</table>
{page::footer()}
