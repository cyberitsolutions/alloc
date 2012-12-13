<form action="{$url_alloc_productSaleList}" method="get">
      <table class="filter corner" align="center">
        <tr>
          <td>Salesperson</td>
          <td>Status</td>
          <td colspan="3">Client</td>
        </tr>
        <tr>
           <td>
            <select name="personID[]" multiple="true">
              {$show_userID_options}
            </select>
          </td>
          <td><select name="status[]" multiple="true">{$show_status_options}</select></td>
          <td>
            <select name="clientID[]" multiple="true">{$clientOptions}</select>
          </td>
          <td class="right"><button type="submit" name="applyFilter" value="1" class="filter_button">Filter<i class="icon-cogs"></i></button></td>
        </tr>
      </table>
      <input type="hidden" name="sessID" value="{$sessID}">
      </form>

