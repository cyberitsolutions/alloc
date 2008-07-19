{$table_box}
  <tr>
    <th>Product Sales</th>
    <th class="right"><a href="{$url_alloc_productSale}projectID={$project_projectID}">New Product Sale</a></th>
  </tr>
  <tr>
    <td colspan="2">
      <table>
        <tr>
          <td>Product Sale ID</td>
          <td>Status</td>
          <td>Date created</td>
        </tr>
    {$productSales = get_product_sales()}
    {foreach $productSales as $productSale}
        <tr>
          <td><a href="{$url_alloc_productSale}productSaleID={$productSale.productSaleID}">{$productSale.productSaleID}</a></td>
          <td>{$productSale.status}</td>
          <td>{$productSale.productSaleCreatedTime}</td>
        </tr>
    {/}
      </table>
    </td>
  </tr>
</table>




