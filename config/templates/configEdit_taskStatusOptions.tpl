{page::header()}
{page::toolbar()}

<form action="{$url_alloc_configEdit}" method="post">
<table class="box">
  <tr>
    <th>Setup Task Status Options</th>
  </tr>
  <tr>  
    <td>

      <table class="list">
        <tr>
          <th>Status</th>
          <th>Sub-Status</th>
          <th>Label</th>
          <th>CSS Decoration</th>
          <th class="right"><a href="#x" class="magic" onClick="$('#tso_footer').before('<tr>'+$('.tso_row:last').html()+'</tr>');">New</a></th>
        </tr>
        {$rows = config::get_config_item($TPL["configName"],true) or $rows=array()}
        {foreach $rows as $key => $value}
          {foreach $value as $subStatus => $data}
        <tr class="tso_row">
          <td><input type="text" name="status[]" value="{$key}"></td>
          <td><input type="text" name="subStatus[]" value="{$subStatus}"></td>
          <td><input type="text" name="label[]" value="{$data.label}"></td>
          <td><input type="text" name="colour[]" value="{$data.colour}" size="40"></td>
          <td><a href="#x" class="magic" onClick="$(this).parent().parent().remove();">Remove</a></td>
        </tr>
          {/}
        {/}
        <tr id="tso_footer">
          <th colspan="5" class="center">
            <input type="submit" name="save" value="Save">
            <input type="hidden" name="configName" value="{$configName}">
          </th>
        </tr>
      </table>
    
    </td>
  </tr>
</table>
</form>

{page::footer()}
