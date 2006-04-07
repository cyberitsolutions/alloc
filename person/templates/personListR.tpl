<tr class="{odd_even}">
 <!--  <td><input type="checkbox" name="selected_persons[]" value="{person_personID}"></td> -->
  <td><a href="{url_alloc_person}personID={person_personID}">{person_username}</a></td>
  <td>{person_personActive}</td>
  <td><nobr>&nbsp;{person_lastLoginDate}&nbsp;</nobr></td>
  <td>{person_availability}&nbsp;</td>
{optional:show_skills_list}
  <td>
    {senior_skills}{advanced_skills}{intermediate_skills}{junior_skills}{novice_skills}&nbsp;
  </td>
{/optional}
  <td>
    <nobr>
		<a href="{url_alloc_taskSummary}personID={person_personID}">Task Summary</a>&nbsp;&nbsp;
		<a href="{url_alloc_personGraphs}personID={person_personID}">Graph</a>
    </nobr>
  </td>
  <td>{person_absence}&nbsp;</td>
  <td>{ts_hrs_col_1}</td>
  <td>{ts_hrs_col_2}</td>
</tr>
