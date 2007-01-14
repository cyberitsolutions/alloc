<tr class="{$odd_even}">
  <td><a href="{$url_alloc_person}personID={$person_personID}">{$person_username}</a></td>
  <td>{$person_personActive}</td>
  <td>{$person_phoneNo1}{$person_phoneNo2}&nbsp;</td>
  <td>
    <nobr>
		<a href="{$url_alloc_taskList}personID={$person_personID}&taskView=byProject&applyFilter=1&dontSave=1&taskStatus=not_completed&projectType=curr">Tasks</a>&nbsp;&nbsp;
		<a href="{$url_alloc_personGraph}personID={$person_personID}">Graph</a>&nbsp;&nbsp;
		<a href="{$url_alloc_taskCalendar}personID={$person_personID}">Calendar</a>
    </nobr>
  </td>
  <td>{$ts_hrs_col_1}</td>
  <td>{$ts_hrs_col_2}</td>
{if check_optional_show_skills_list()}
  <td>{$senior_skills}{$advanced_skills}{$intermediate_skills}{$junior_skills}{$novice_skills}&nbsp;</td>
{/}
</tr>
