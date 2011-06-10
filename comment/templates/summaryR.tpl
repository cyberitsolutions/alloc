{if $row["duration"]}{$style='; font-weight:bold;'}{/}
<tr>
  <td style='width:1%{$style}' class='nobr{$class}'>{$row.date}</td>
  <td style='width:1%{$style}' class='nobr{$class}'>{=$row.person}</td>
  <td style='{$style}' class='{$class}' onClick='return set_grow_shrink("longText_{$row.id}","shortText_{$row.id}")'>
    <div id='shortText_{$row.id}'>{echo substr(trim(page::htmlentities($row["comment_text"])),0,100)}</div>
    <div style='display:none' id='longText_{$row.id}'>{echo nl2br(trim(page::htmlentities($row["comment_text"])))}</div>
  </td>
  <td style='width:1%{$style}' class='nobr right{$class}'>{$row.duration}</td>
</tr>
