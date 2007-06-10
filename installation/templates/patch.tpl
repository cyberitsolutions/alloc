{show_header()}
<style>table.box td \{text-align:left; border-top:1px solid #999999; font-size:11px;\}</style>
<form action="{$url_alloc_patch}" method="post">
<br>
{show_messages()}
<br>
{$table_box}
<tr>
  <th colspan="4">allocPSA Patch System</th>
</tr>

{$files = get_patch_file_list()}
{$applied_patches = get_applied_patches()}

{foreach $files as $file}
  {list($code,$comments) = parse_patch_file(ALLOC_MOD_DIR."patches/".$file)}
  {unset($go)}
  {if !in_array($file,$applied_patches)}
    {$go = true}
  {/}

  {if $go}
  <tr>
    <td valign="top"><input type='checkbox' name='patches_to_apply[]' value='{$file}'>&nbsp;</td>
    <td valign="top" class="nobr">{$file}&nbsp;</td>
    <td valign="top">{echo nl2br(htmlentities(implode("\n",$comments)))}&nbsp;</td>
    <td valign="top">{echo nl2br(htmlentities(implode("\n",$code)))}&nbsp;</td>
  </tr>
  {/}

{/}

<tr>
  <td colspan="4" class="center"><input type='submit' name='apply_patches' value='Apply Patches'></td>
</tr>
</table>

</form>

{msg}
<div><div>
{show_footer()}
