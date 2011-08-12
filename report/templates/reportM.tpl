{page::header()}
  {page::toolbar()}

<table class="box">
  <tr>
    <th>Reports</th>
  </tr>
  <tr>
    <td>

      <form action="{$url_alloc_report}" method="post">
        <select name="mod" size="1">{$module_options}</select>
        <input type="submit" value="Show Fields" name="do_step_2">
      <input type="hidden" name="sessID" value="{$sessID}">
      </form>

      <form action="{$url_alloc_report}" method="post">
      <br>
        <table width="100%">
            {$table_fields}
        </table>
      <input type="hidden" value="{$do_step_2}" name="do_step_2">
      <input type="hidden" value="{$mod}" name="mod">
      <br>
      {$dump_options}
      <input type="hidden" name="sessID" value="{$sessID}">
      </form>


      {$counter}<br>&nbsp;
      <table class="list sortable">
        {$result_row}
      </table>


    </td>
  </tr>
</table>

{page::footer()}

