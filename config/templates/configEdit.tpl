{page::header()}
{page::toolbar()}

<table class="box">
  <tr>
    <th colspan="4">Setup Item Edit</th>
  </tr>
  {$rows = config::get_config_item($configName,true) or $rows=array()}
  {foreach $rows as $key => $value}

  {if is_array($value) && !isset($count_array)}
    {$count_array = $value}
  {/}
  <tr>
    <td>

      <form action="{$url_alloc_configEdit}" method="post">
      <table>
        <tr>
          <td></td><td><input type="text" name="key" size="20" value="{$key}"></td>
          {if is_array($value)}
            <td>
            {foreach $value as $k => $v}
              <input type="text" name="value[{$k}]" size="20" value="{$v}">&nbsp;&nbsp;
            {/}
            </td>
          {else}
          <td><input type="text" name="value" size="20" value="{$value}"></td>
          {/}
          <td>
            <input type="submit" name="delete" value="Delete" class="delete_button">
            <input type="submit" name="save" value="Save" class="default">
            <input type="hidden" name="configName" value="{$configName}">
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
          <td></td><td><input type="text" name="key" size="20"></td>
          {if is_array($value)}
            <td>
            {foreach $count_array as $value_k => $blah}
              <input type="text" name="value[{$value_k}]" size="20">&nbsp;&nbsp;
            {/}
            </td>
          {else}
            <td><input type="text" name="value" size="20"></td>
          {/}
          <td>
            <input type="submit" name="save" value="Save">
            <input type="hidden" name="configName" value="{$configName}">
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
