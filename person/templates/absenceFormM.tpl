{show_header()}
  {show_toolbar()}
    <form action="{$url_alloc_absence}" method=post>
      {$myMessage}
      <input type="hidden" name="absenceID" value="{$absence_absenceID}">
      <input type="hidden" name="returnToParent" value="{$returnToParent}">
      <input type="hidden" name="personID" value="{$absence_personID}">
      {$table_box}
        <tr>
          <th colspan="3">Absence Form</th> 
        </tr>
	      <tr>
	        <td>User</td> 
          <td>{$personName}</td>
	      </tr>
        <tr>
          <td>Date From</td>
          <td>
            <input type="text" size="10" name="absence_dateFrom" value="{$absence_dateFrom}">
            <input type="button" value="Today" onClick="absence_dateFrom.value='{$today}'">
          </td>
	      </tr>
        <tr>
          <td>Date To</td>
          <td>
            <input type="text" size="10" name="absence_dateTo" value="{$absence_dateTo}">
            <input type="button" value="Today" onClick="absence_dateTo.value='{$today}'">
          </td>
        </tr>
        <tr>
          <td>Absence type</td>
          <td>
            <select size = "1" name="absence_absenceType">
              {$absenceType_options}
            </select>
          </td>
        </tr>
        <tr>
          <td>Emergency contact details<br /> while on leave.</td>
          <td><textarea name="absence_contactDetails" rows="4" wrap="virtual" cols = "50">{$absence_contactDetails}</textarea></td>
        </tr>
        <tr>
          <td colspan="2" align="center">
            <input type="submit" name="save" value="Save">
            <input type="submit" name="delete" value="Delete">
          </td>
        </tr>
      </table>
    </form>

{show_footer()}

