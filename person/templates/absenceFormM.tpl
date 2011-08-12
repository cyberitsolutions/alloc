{page::header()}
  {page::toolbar()}
    <form action="{$url_alloc_absence}" method=post>
      {$myMessage}
      <input type="hidden" name="absenceID" value="{$absence_absenceID}">
      <input type="hidden" name="returnToParent" value="{$returnToParent}">
      <input type="hidden" name="personID" value="{$absence_personID}">
      <table class="box">
        <tr>
          <th colspan="3">Absence Form</th> 
        </tr>
	      <tr>
	        <td>User</td> 
          <td>{=$personName}</td>
	      </tr>
        <tr>
          <td>Date From</td>
          <td>
            {page::calendar("absence_dateFrom",$absence_dateFrom)}
          </td>
	      </tr>
        <tr>
          <td>Date To</td>
          <td>
            {page::calendar("absence_dateTo",$absence_dateTo)}
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
          <td>Emergency contact details<br> while on leave.</td>
          <td>{page::textarea("absence_contactDetails",$absence_contactDetails)}</td>
        </tr>
        <tr>
          <td colspan="2" align="center">
            <input type="submit" name="save" value="Save">
            <input type="submit" name="delete" value="Delete">
          </td>
        </tr>
      </table>
    <input type="hidden" name="sessID" value="{$sessID}">
    </form>

{page::footer()}

