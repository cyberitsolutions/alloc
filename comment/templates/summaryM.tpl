{page::header()}
{page::toolbar()}

<table class="box">
  <tr>
    <th class="nobr">Task Comment Summary</th>
    <th class="right nobr"><a class='magic toggleFilter' href=''>Show Filter</a></th>
  </tr>
  <tr>
    <td colspan="2">
          
        <form action="{$url_alloc_commentSummary}" method="get">
        <table align="center" class="filter corner">
          <tr>
            <td>Project</td><td>People</td><td>From Date</td><td>To Date</td><td>Task Status</td>
          </tr>
          <tr>
            <td>{echo project::get_list_dropdown("current",$_REQUEST["projectID"])}</td>
            <td>
            {$people = get_cached_table("person")}
            {foreach $people as $personID => $person}{if $person["personActive"]}{$ops[$personID] = $person["name"]}{/}{/}
              <select name="personID[]" multiple="true" size="9">
                {page::select_options($ops,$_REQUEST["personID"])}
              </select>
            </td>
            <td class="top">{page::calendar("fromDate",$_REQUEST["fromDate"]);}</td>
            <td class="top">{page::calendar("toDate",$_REQUEST["toDate"]);}</td>
            <td class="top">
              <select name="taskStatus">{page::select_options(task::get_task_statii_array(), $_REQUEST["taskStatus"])}</select>
              <br><br>
              Include Client Comments <input type="checkbox" name="clients" value="clients"{if $_REQUEST["clients"]} checked{/}>
            </td>
            <td class="top"><input type="submit" value="Filter" name="filter">
          </tr>
        </table>
        </form>

    </td>
  </tr>
  <tr>
    <td colspan="2">
      {if $_REQUEST["filter"]}
        {$_REQUEST["showTaskHeader"] = true}
        {echo comment::get_list_summary($_REQUEST)}
      {/}
    </td>
  </tr>
</table>


{page::footer()}
