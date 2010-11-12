{page::header()}
{page::toolbar()}

{$t = new meta($table)}
{$rows = $t->get_list(true)}
{$label = $t->get_label()}

<form action="{$url_alloc_metaEdit}" method="post">
<input type="hidden" name="configName" value="{$table}">
<table class="box">
  <tr>
    <th>{$label}</th>
  </tr>
  <tr>
    <td>

      <table class="list">
        <tr>
          <th>Value</th>
          <th>Sequence</th>
          {if $table == "taskStatus"}
          <th>Label</th>
          <th>Colour</th>
          {/}
          <th colspan="2">Active</th>
          <th class="right">
            <a href="#x" class="magic" onClick="$('#rows_footer').before('<tr>'+$('#row').html()+'</tr>');">New</a>
          </th>
        </tr>
      
        {foreach (array)$rows as $row}
        <tr>
          <td>
            <input type="text" name="{$table}ID[]" size="20" value="{echo $row[$table."ID"]}">
            <input type="hidden" name="{$table}IDOrig[]" size="20" value="{echo $row[$table."ID"]}">
          </td>
          <td><input type="text" name="{$table}Seq[]" size="20" value="{echo $row[$table."Seq"]}"></td>
          {if $table == "taskStatus"}
          <td><input type="text" name="{$table}Label[]" size="20" value="{echo $row[$table."Label"]}"></td>
          <td><input type="text" name="{$table}Colour[]" size="20" value="{echo $row[$table."Colour"]}"></td>
          {/}
          {unset($checked)}
          {$row[$table."Active"] and $checked = " checked"}
          <td colspan="2"><input type="checkbox" name="{$table}Active[]" size="20" value="{echo $row[$table."ID"]}"{$checked}></td>
          <td class="right nobr">
            <input type="checkbox" name=delete[] value="{echo $row[$table."ID"]}" id="delete{echo $row[$table."ID"]}">
            <label for="delete{echo $row[$table."ID"]}"> Delete</label>
          </td>
        </tr>
        {/}

        <tr id="row" class="hidden">
          <td>
            <input type="text" name="{$table}ID[]" size="20" value="">
          </td>
          <td><input type="text" name="{$table}Seq[]" size="20" value=""></td>
          {if $table == "taskStatus"}
          <td><input type="text" name="{$table}Label[]" size="20" value=""></td>
          <td><input type="text" name="{$table}Colour[]" size="20" value=""></td>
          {/}
          <td colspan="2">
          <!-- <input type="checkbox" name="{$table}Active[]" size="20" value="1"> -->
          </td>
          <td class="right nobr">
          </td>
        </tr>

        <tr id="rows_footer">
          <th colspan="50" class="center">
            <input type="submit" name="save" value="Save">
          </th>
        </tr>

      </table>

    </td>
  </tr>
</table>
</form>

{page::footer()}
