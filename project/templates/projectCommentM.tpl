{$table_box}
  <tr>
    <th colspan="2">Comments</th>
  </tr>
  <tr>
    <td colspan="2">
      <form action="{$url_alloc_comment}" method="post">
      <table width="100%">
        <tr>
          <td>
            <input type="hidden" name="entity" value="project">
            <input type="hidden" name="entityID" value="{$project_projectID}">
            <textarea name="comment" cols="85" rows="4" wrap="virtual">{$comment}</textarea>&nbsp;
          </td>
          <td align="right" valign="top">
            {$comment_buttons}
          </td>
        </tr>
      </table>
      </form>
    </td>
  </tr>
  <tr>
    <td>
      {$commentsR}
    </td>
  </tr>
</table>


