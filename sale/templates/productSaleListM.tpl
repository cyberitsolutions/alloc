{page::header()}
{page::toolbar()}
<table class="box">
  <tr>
    <th>Sales</th>
    <th class="right">
      <a href="{$url_alloc_productList}">Products</a>
      <a href="{$url_alloc_product}">New Product</a>
      <a href="{$url_alloc_productSale}">New Sale</a>
    </th>
  </tr>
  <tr>
    <td colspan="2">
      {echo productSale::get_list()}
    </td>
  </tr>
</table>
{page::footer()}
