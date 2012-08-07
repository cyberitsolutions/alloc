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
            <button type="submit" name="delete" value="1" class="delete_button">Delete<i class="icon-trash"></i></button>
            <button type="submit" name="save" value="1" class="save_button default">Save<i class="icon-ok-sign"></i></button>
          </td>
        </tr>
      </table>
    <input type="hidden" name="sessID" value="{$sessID}">
    </form>

{page::footer()}

