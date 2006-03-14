{:show_header}
  {:show_toolbar}
  <h1>Add Event Filter - Enter Object Filter</h1>
  <form action="{url_alloc_eventFilterAdd}" method="post">
	{filter_form}
    <input type="hidden" name="eventName" value="{eventName}">
    <input type="hidden" name="className" value="{className}">
    <input type="hidden" name="step" value="4">
    <input type="submit" name="submitEventName" value="Next">
  </form>
{:show_footer}
