<tr class="{odd_even}">
  <td><a href="{url_alloc_person}personID={person_personID}">{person_username}</a></td>
  <td>{person_personActive}</td>
  <td><nobr>&nbsp;{person_lastLoginDate}&nbsp;</nobr></td>
  <td>{person_phoneNo1}{person_phoneNo2}&nbsp;</td>
  <td>
    <nobr>
		<a href="{url_alloc_taskSummary}personID={person_personID}&taskView=byProject&applyFilter=1&taskStatus=not_completed">Task Summary</a>&nbsp;&nbsp;
		<a href="{url_alloc_personGraphs}personID={person_personID}">Graph</a>
    </nobr>
  </td>
  <td>{person_absence}&nbsp;</td>
  <td>{ts_hrs_col_1}</td>
  <td>{ts_hrs_col_2}</td>
{optional:show_skills_list}
  <td>{senior_skills}{advanced_skills}{intermediate_skills}{junior_skills}{novice_skills}&nbsp;</td>
{/optional}
</tr>
