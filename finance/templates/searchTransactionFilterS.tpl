<form action="{$url_alloc_searchTransaction}" method="get">
  <!-- we need to have display:table because the default is for .filter's to be hidden -->
  <table align="center" class="filter" style="display:table">
    <tr>
      <td>Source TF</td>
      <td><select name="fromTfID"><option value="">{$fromTfOptions}</select></td>
      <td>From</td>
      <td>{page::calendar("startDate",$TPL["startDate"])}</td>
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
      <td>{page::calendar("endDate",$TPL["endDate"])}</td>
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
        <input type="submit" name="download" value="CSV">
        <input type="submit" name="applyFilter" value="Filter">
      </td>
    </tr>
    <tr>
    </tr>
  </table>
</form>

