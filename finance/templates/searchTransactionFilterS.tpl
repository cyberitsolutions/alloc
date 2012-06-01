<form action="{$url_alloc_searchTransaction}" method="get">
  <!-- we need to have display:table because the default is for .filter's to be hidden -->
  <table align="center" class="filter corner" style="display:table">
    <tr>
      <td>Source TF</td>
      <td><select name="fromTfID"><option value="">{$fromTfOptions}</select></td>
      <td>From</td>
      <td>{page::calendar("startDate",$startDate)}</td>
      <td>Transaction Desc</td>
      <td>Transaction ID</td>
      <td>Amount</td>
      <td>Status</td>
      <td>Sort By</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Dest TF</td>
      <td><select name="tfID"><option value="">{$tfOptions}</select></td>
      <td>To</td>
      <td>{page::calendar("endDate",$endDate)}</td>
      <td><input type="text" size="20" name="product" value="{$product}"></td>
      <td><input type="text" size="10" name="transactionID" value="{$transactionID}"></td>
      <td><input type="text" size="10" name="amount" value="{$amount}"></td>
      <td><select name="status">{$statusOptions}</select></td>
      <td>
        <input type="radio" id="st_sd" name="sortTransactions" value="transactionSortDate"{$checked_transactionSortDate}>
        <label for="st_sd">Last Modified</label><br>
        <input type="radio" id="st_td" name="sortTransactions" value="transactionDate"{$checked_transactionDate}>
        <label for="st_td">Transaction Date</label>
      </td>
      <td>
        <button type="submit" name="download" value="1" class="filter_button">CSV<i class="icon-download"></i></button>
        <button type="submit" name="applyFilter" value="1" class="filter_button">Filter<i class="icon-cogs"></i></button>
      </td>
    </tr>
    <tr>
    </tr>
  </table>
<input type="hidden" name="sessID" value="{$sessID}">
</form>

