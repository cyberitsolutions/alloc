    <form action="{$url_alloc_clientList}" method="post">
      <table class="filter" align="center">
        <tr>
          <td width="20%">&nbsp;</td>
          <td>Status</td>
          <td>Name containing</td>
          <td>&nbsp;</td>
          <td width="20%">&nbsp;</td>
        <!--  <td rowspan="2">{get_help("clientListFilter")}</td> -->
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td><select name="clientStatus"><option value=""> -- ALL -- {$clientStatusOptions}</select></td>
          <td><input type="text" name="clientName" value="{$clientName}"></td>
          <td><input type="submit" name="applyFilter" value="Filter"></td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td align="center" colspan="5"><nobr>{$alphabet_filter}</nobr></td>
        </tr>
      </table>
    </form>
