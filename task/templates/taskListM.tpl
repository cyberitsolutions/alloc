{page::header()}
{page::toolbar()}

{foreach (array)$_FORM as $k=>$v}
  {if is_array($v)}
    {foreach $v as $y}
      {$get_str.= $k."%5B%5D=".urlencode($y)."&";}
    {/}
  {else}
    {$get_str.= $k."=".urlencode($v)."&";}
  {/}
{/}

<table class="box">
  <tr>
    <th class="header">Tasks
      <b> - {print count($taskListRows)} records</b>
      <span>
        <a class='magic toggleFilter' href=''>Show Filter</a>
        <a href="{$url_alloc_taskListPrint}{$get_str}">PDF</a>
        <a href="{$url_alloc_taskListCSV}{$get_str}">CSV</a>
        <a href="{$url_alloc_task}">New Task</a>
      </span>
    </th>
  </tr>
  <tr>
    <td class="noprint" >{include_template("templates/taskFilterS.tpl")}</td>
  </tr>
  <tr>
    <td>
      {task::get_list_html($taskListRows,$_FORM)}
    </td>
  </tr>
</table>
{page::footer()}
