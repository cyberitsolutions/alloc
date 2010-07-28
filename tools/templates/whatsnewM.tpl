{page::header()}
{page::toolbar()}

<table class="box">
<tr>
  <th colspan="4">allocPSA Deployment Changelog</th>
</tr>

{$rows = get_attachments("whatsnew","0")}

{if $rows}
<tr>
  <td>Filename</td>
  <td>Created on</td>
  <td>Size</td>
</tr>

{foreach $rows as $row}
<tr>
  <td>{$row.file}</td>
  <td>{$row.mtime}</td>
  <td>{$row.size}</td>
</tr>
{/}

{/}
</table>
{page::footer()}
