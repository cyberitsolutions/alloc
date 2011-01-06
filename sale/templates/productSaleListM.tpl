{page::header()}
{page::toolbar()}
<table class="box">
  <tr>
    <th class="header">Sales
      <span>
        <a href="{$url_alloc_productList}">Products</a>
        <a href="{$url_alloc_product}">New Product</a>
        <a href="{$url_alloc_productSale}">New Sale</a>
      </span>
    </th>
  </tr>
  <tr>
    <td>
      {echo productSale::get_list()}
    </td>
  </tr>
</table>
{page::footer()}
