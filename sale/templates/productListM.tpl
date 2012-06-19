{page::header()}
{page::toolbar()}
<table class="box">
  <tr>
    <th class="header">Products
      <b> - {print count($productListRows)} records</b>
      <span>
        <a href="{$url_alloc_product}">New Product</a>
        <a href="{$url_alloc_productSale}">New Sale</a>
      </span>
    </th>
  </tr>
  <tr>
    <td>
      {product::get_list_html($productListRows)}
    </td>
  </tr>
</table>
{page::footer()}
