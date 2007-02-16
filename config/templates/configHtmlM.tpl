{show_header()}
{show_toolbar()}


{if $TPL["htmlElementID"]}
{$table_box}
  <tr>
    <th colspan="4">Preview</th>
  </tr>
  <tr>
    <td>Label</td>
    <td>Widget</td>
    <td>Help</td>
    <td>Code</td>
  </tr>
  <tr>
    <td>{echo get_text($TPL["handle"])}</td>
    <td>{echo get_html($TPL["handle"])}</td>
    <td>{echo get_help($TPL["handle"])}</td>
    <td>{echo htmlentities(get_html($TPL["handle"]))}</td>
  </tr>
</table>
{/}



<form action="{$url_alloc_configHtml}" method="post">

{$table_box}
  <tr>
    <th colspan="2">Html Element</th>
  </tr>
  <tr>
    <td>Element Type</td><td><select name="htmlElementTypeID">{$htmlElementType_options}</select></td>
  </tr>
  <tr>
    <td>Unique Name</td><td><input type="text" name="handle" size="40" value="{$handle}"></td>
  </tr>
  <tr>
    <td>Label</td><td><input type="text" name="label" size="40" value="{$label}"></td>
  </tr>
  <tr>
    <td>Help Text</td><td><textarea rows="4" name="helpText" cols="50">{$helpText}</textarea></td>
  </tr>
  <tr>
    <td>Default Value</td><td>{$defaultValue}</td>
  </tr>
  <tr>
    <td>Sequence</td><td>{$sequence}</td>
  </tr>
  <tr>
    <td>Enabled</td><td><input type="checkbox" name="enabled" value="1"{$enabled_checked}></td>
  </tr>
  <tr>
    <td></td><td><input type="submit" name="save" value="Save"></td>
  </tr>
</table>

<input type="hidden" name="htmlElementID" value="{$htmlElementID}">

</form>


{if $TPL["htmlElementID"]}

{$table_box}
  <tr>
    <th colspan="4">Html Element Attributes</th>
  </tr>
  {$rows = htmlAttribute::get_list($TPL["htmlElementID"])}
  {foreach $rows as $row}
  <tr>
    <td>

      <form action="{$url_alloc_configHtml}" method="post">
      <table>
        <tr>
          <td>Name</td><td><input type="text" name="name" size="40" value="{$row.name}"></td>
          <td>Value</td><td><input type="text" name="value" size="40" value="{$row.value}"></td>
          <td>
            <input type="submit" name="save_attribute" size="40" value="Save">
            <input type="submit" name="delete_attribute" size="40" value="Delete">
            <input type="hidden" name="htmlAttributeID" value="{$row.htmlAttributeID}">
            <input type="hidden" name="htmlElementID" value="{$htmlElementID}">
          </td>
        </tr>
      </table>
      </form>

    </td>
  </tr>
  {/}
  <tr>
    <td>

      <form action="{$url_alloc_configHtml}" method="post">
      <table>
        <tr>
          <td>Name</td><td><input type="text" name="name" size="40"></td>
          <td>Value</td><td><input type="text" name="value" size="40"></td>
          <td>
            <input type="submit" name="save_attribute" value="Save">
            <input type="hidden" name="htmlElementID" value="{$htmlElementID}">
          </td>
        </tr>
        <tr>
        </tr>
      </table>
      </form>

    </td>
  </tr>


</table>



{/}

{show_footer()}
