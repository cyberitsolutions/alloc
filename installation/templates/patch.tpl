{show_header()}
<style>table.box td \{text-align:left; border-top:1px solid #999999; font-size:11px;\}</style>
<br>
{show_messages()}
<br>

<form action="{$url_alloc_patch}" method="post">
<input type='submit' name='apply_patches' value='Apply All Patches'>
</form>

{$table_box}
<tr>
  <th colspan="4">allocPSA Patch System</th>
</tr>

{$files = get_patch_file_list()}
{$applied_patches = get_applied_patches()}

{foreach $files as $file}
  {list($code,$comments) = parse_patch_file(ALLOC_MOD_DIR."patches/".$file)}

  {if !in_array($file,$applied_patches)}
  <tr>
    <td valign="top">
      <form action="{$url_alloc_patch}" method="post">
      <input type='submit' name='apply_patch' value='Apply Patch'>
      <input type='hidden' name='patch_file' value='{$file}'>&nbsp;
      </form>
    </td>
    <td valign="top" class="nobr">{$file}&nbsp;</td>
    <td valign="top">{echo nl2br(htmlentities(implode("\n",$comments)))}&nbsp;</td>
    <td valign="top">{echo nl2br(htmlentities(implode("\n",$code)))}&nbsp;</td>
  </tr>
  {/}
{/}
</table>

<div><div>
{show_footer()}
