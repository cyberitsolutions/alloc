{page::header()}
{page::toolbar()}
<form action="{$url_alloc_search}" method="post">
{$table_box}
  <tr>
    <th>Search</th>
  </tr>
  <tr>
    <td align="center">


      <table class="filter" align="center">
        <tr>
          <td><input size="30" name="needle" value="{$needle2}"></td>
          <td><select size="1" name="category">{$search_category_options}</select></td>
          <td><input type="submit" name="search" value="Search"></td>
	  <td><input type="checkbox" name="idRedirect" {$redir} />Disable redirection by ID</td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      {$search_results}
    </td>
  </tr>
  </table>

</form>



{page::footer()}

