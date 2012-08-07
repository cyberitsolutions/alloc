{page::header()}
<style>table.box td { text-align:left; border-top:1px solid #999999;  }</style>
<br>
{page::messages()}
<br>

<form action="{$url_alloc_patch}" method="post">
<button type="submit" name="apply_patches" value="1" class="save_button">Apply All Patches<i class="icon-ok-sign"></i></button>
<input type="hidden" name="sessID" value="{$sessID}">
</form>

<table class="box">
<tr>
  <th colspan="4">allocPSA Patch System</th>
</tr>

{$files = get_patch_file_list()}
{$applied_patches = get_applied_patches()}

{foreach $files as $file}
  {list($code,$comments) = parse_patch_file(ALLOC_MOD_DIR."patches/".$file)}

  {if !in_array($file,$applied_patches)}
  <form action="{$url_alloc_patch}" method="post">
  <tr>
    <td valign="top" class="nobr">{$file}&nbsp;</td>
    <td valign="top">{echo implode("<br>",$comments)}&nbsp;</td>
    <td valign="top">{page::to_html(implode("\n",$code))}&nbsp;</td>
    <td valign="top" class="nobr">
      <button type="submit" name="remove_patch" value="1" class="delete_button">Delete Patch<i class="icon-trash"></i></button>
      <button type="submit" name="apply_patch" value="1" class="save_button">Apply Patch<i class="icon-ok-sign"></i></button>
      <input type='hidden' name='patch_file' value='{$file}'>&nbsp;
      <input type="hidden" name="sessID" value="{$sessID}">
    </td>
  </tr>
  </form>
  {/}
{/}
</table>

<div><div>
{page::footer()}
