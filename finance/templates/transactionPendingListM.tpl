{:show_header}
{:show_toolbar}
<form action="{url_alloc_transactionPendingList}" method="post">
{table_box}
  <tr>
    <th colspan="7">Transaction List</th>
  </tr>
  <tr>
    <td colspan="7">
      <table class="filter" cellspacing="0" cellpadding="3" align="center">
        <tr>
          <td>Transaction Type</td>
          <td>Owned By</td>
          <td>Project</td>
          <td></td>
        </tr>
        <tr>
          <td><select name="type"><option value=""> -- ALL -- {typeOptions}</select></td>
          <td>{tfIDOptions}</td>
          <td><select name="projectID"><option value=""> -- ALL -- {projectIDOptions}</select></td>
          <td><input type="submit" name="applyFilter" value="Filter"></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td align="center"><b><a href="{url_alloc_transactionPendingList}sort=transactionID">ID</a></b></td>
    <td align="center"><b><a href="{url_alloc_transactionPendingList}sort=transactionType">Type</a></b></td>
    <td align="center"><b><a href="{url_alloc_transactionPendingList}sort=tfName">Owner</b></a></td>
    <td align="center"><b><a href="{url_alloc_transactionPendingList}sort=projectName">Project</b></a></td>
    <td align="center"><b><a href="{url_alloc_transactionPendingList}sort=lastModified">Last Modified</a></b></td>
    <td align="center"><b><a href="{url_alloc_transactionPendingList}sort=username">Modified By</b></a></td>
    <td align="right"><b><a href="{url_alloc_transactionPendingList}sort=amount">Amount</a></b></td>
  </tr>
  {:show_transaction_list templates/transactionPendingListR.tpl}
</table>

</form>
{:show_footer}
