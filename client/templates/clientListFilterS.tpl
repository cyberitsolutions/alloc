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
          <td><select name="clientStatus[]" multiple="true">{$clientStatusOptions}</select></td>
          <td><input type="text" name="clientName" value="{$clientName}"></td>
          <td><input type="text" name="contactName" value="{$contactName}"></td>
          <td><select name="clientCategory[]" multiple="true">{$clientCategoryOptions}</select></td>
          <td><button type="submit" name="applyFilter" value="1" class="filter_button">Filter<i class="icon-cogs"></i></button></td>
          <td>{page::help("clientListFilter")}</td> 
        </tr>
        <tr>
          <td align="center" colspan="6"><nobr>{$alphabet_filter}</nobr></td>
        </tr>
      </table>
    <input type="hidden" name="sessID" value="{$sessID}">
    </form>
