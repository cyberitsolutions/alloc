{$phoneNo1 && $phoneNo2 and $phoneNo1.= " / "}
<tr>
{if $_FORM["showName"]}    <td>{$name_link}</td>{/}
{if $_FORM["showActive"]}  <td>{$personActive_label}</td>{/}
{if $_FORM["showNos"]}     <td>{=$phoneNo1}{=$phoneNo2}</td>{/}
{if $_FORM["showSkills"]}  <td>{$skills_list}</td>{/}
{if $_FORM["showHours"]}   <td>{echo sprintf("%0.1f",$hoursSum)}</td>{/}
{if $_FORM["showHours"]}   <td>{echo sprintf("%0.1f",$hoursAvg)}</td>{/}
{if $_FORM["showLinks"]}   <td class="nobr noprint" align="right" width="1%">{$navLinks}</td>{/}
</tr>

