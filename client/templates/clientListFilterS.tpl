    <form action="{$url_alloc_clientList}" method="get">
      <table class="filter corner" align="center">
        <tr>
          <td>&nbsp;</td>
          <td>Status</td>
          <td>Client Name</td>
          <td>Contact Name</td>
          <td>Category</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td><select name="clientStatus"><option value="">{$clientStatusOptions}</select></td>
          <td><input type="text" name="clientName" value="{$clientName}"></td>
          <td><input type="text" name="contactName" value="{$contactName}"></td>
          <td><select name="clientCategory"><option value="">{$clientCategoryOptions}</select></td>
          <td><input type="submit" name="applyFilter" value="Filter"></td>
          <td>{page::help("clientListFilter")}</td> 
        </tr>
        <tr>
          <td align="center" colspan="6"><nobr>{$alphabet_filter}</nobr></td>
        </tr>
      </table>
    </form>
