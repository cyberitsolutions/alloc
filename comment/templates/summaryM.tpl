{page::header()}
{page::toolbar()}

<table class="box">
  <tr>
    <th class="header nobr">Task Comment Summary
      <span>
        <a class='magic toggleFilter' href=''>Show Filter</a>
      </span>
    </th>
  </tr>
  <tr>
    <td>
          
        <form action="{$url_alloc_commentSummary}" method="get">
        <table align="center" class="filter corner">
          <tr>
            <td>Project</td><td>People</td><td>From Date</td><td>To Date</td><td>Task Status</td>
          </tr>
          <tr>
            <td style='vertical-align:top'>{echo project::get_list_dropdown("current",$_REQUEST["projectID"])}</td>
            <td style='vertical-align:top'>
            {$people =& get_cached_table("person")}
            {foreach $people as $personID => $person}{if $person["personActive"]}{$ops[$personID] = $person["name"]}{/}{/}
              <select name="personID[]" multiple="true" size="9">
                {page::select_options($ops,$_REQUEST["personID"])}
              </select>
            </td>
            <td class="top">{page::calendar("fromDate",$_REQUEST["fromDate"]);}</td>
            <td class="top">{page::calendar("toDate",$_REQUEST["toDate"]);}</td>
            <td class="top">
              <select name="taskStatus[]" multiple="true">{page::select_options(task::get_task_statii_array(), $_REQUEST["taskStatus"])}</select>
            </td>
            <td class="top">
              Include Client Comments <input type="checkbox" name="clients" value="clients"{if $_REQUEST["clients"]} checked{/}>
            </td>
            <td class="top">
              <button type="submit" name="filter" value="1" class="filter_button">Filter<i class="icon-cogs"></i></button>
            </td>
          </tr>
        </table>
        <input type="hidden" name="sessID" value="{$sessID}">
        </form>

    </td>
  </tr>
  <tr>
    <td>
      {if $_REQUEST["filter"]}
        {$_REQUEST["showTaskHeader"] = true}
        {echo comment::get_list_summary($_REQUEST)}
      {/}
    </td>
  </tr>
</table>


{page::footer()}
