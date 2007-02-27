{show_header()}
{show_toolbar()}

{$table_box}
  <tr>
    <th colspan="4">Setup Item Edit</th>
  </tr>
  {$rows = config::get_config_item($TPL["configName"])}
  {foreach $rows as $key => $value}
  <tr>
    <td>

      <form action="{$url_alloc_configEdit}" method="post">
      <table>
        <tr>
          <td></td><td><input type="text" name="key" size="40" value="{$key}"></td>
          <td><input type="text" name="value" size="40" value="{$value}"></td>
          <td>
            <input type="submit" name="save" size="40" value="Save">
            <input type="submit" name="delete" size="40" value="Delete">
            <input type="hidden" name="configName" value="{$configName}">
          </td>
        </tr>
      </table>
      </form>

    </td>
  </tr>
  {/}
  <tr>
    <td>

      <form action="{$url_alloc_configEdit}" method="post">
      <table>
        <tr>
          <td></td><td><input type="text" name="key" size="40"></td>
          <td><input type="text" name="value" size="40"></td>
          <td>
            <input type="submit" name="save" value="Save">
            <input type="hidden" name="configName" value="{$configName}">
          </td>
        </tr>
        <tr>
        </tr>
      </table>
      </form>

    </td>
  </tr>


</table>

{show_footer()}
