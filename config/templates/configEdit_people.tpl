{page::header()}
{page::toolbar()}

<table class="box">
  <tr>
    <th colspan="4">Setup Item Edit</th>
  </tr>
  {$rows = config::get_config_item($configName,true) or $rows=array()}
  {foreach $rows as $key => $value}

  <tr>
    <td>

      <form action="{$url_alloc_configEdit}" method="post">
      <table>
        <tr>
          <td></td>
          <td>
              {echo person::get_fullname($value)}
          </td>

          <td>
            <input type="hidden" name="value" value="{$value}">
            <input type="submit" name="delete" value="Delete">
            <input type="hidden" name="configName" value="{$configName}">
            <input type="hidden" name="configType" value="{$configType}">
          </td>
        </tr>
      </table>
      <input type="hidden" name="sessID" value="{$sessID}">
      </form>

    </td>
  </tr>
  {/}


  <tr>
    <td>

      <form action="{$url_alloc_configEdit}" method="post">
      <table>
        <tr>
          <td></td>
          <td>
            <select name="value">
              {page::select_options(person::get_username_list())};
            </select>
          <td>
            <input type="submit" name="save" value="Add">
            <input type="hidden" name="configName" value="{$configName}">
            <input type="hidden" name="configType" value="{$configType}">
          </td>
        </tr>
        <tr>
        </tr>
      </table>
      <input type="hidden" name="sessID" value="{$sessID}">
      </form>

    </td>
  </tr>


</table>

{page::footer()}
