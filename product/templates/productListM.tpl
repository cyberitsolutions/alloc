{page::header()}
{page::toolbar()}
{$table_box}
  <tr>
    <th>Products</th>
    <th class="right"><a href="{$url_alloc_product}">New Product</a></th>
  </tr>
  {$products = get_products()}
  {foreach $products as $product}
  <tr>
    <td colspan="2"><a href="{$product.url}">{$product.name}</a></td>
  </tr>
  {/}
</table>
{page::footer()}
