{page::header()}
{page::toolbar()}
<table class="box">
  <tr>
    <th class="header">Sales
      <b> - {print count($productSaleListRows)} records</b>
      <span>
        <a class='magic toggleFilter' href=''>Show Filter</a>
        <a href="{$url_alloc_productList}">Products</a>
        <a href="{$url_alloc_product}">New Product</a>
        <a href="{$url_alloc_productSale}">New Sale</a>
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
      {productSale::get_list_html($productSaleListRows)}
    </td>
  </tr>
</table>
{page::footer()}
