{show_header()}
  {show_toolbar()}
  <h1>Add Event Filter - Select Event</h1>
  <form action="{$url_alloc_eventFilterAdd}" method="post">
    Event:
    <select name="eventName">
      {$eventNameOptions}
    </select><br>
    <br>
    <input type="hidden" name="className" value="{$className}">
    <input type="hidden" name="step" value="3">
    <input type="submit" name="submitEventName" value="Next">
  </form>
{show_footer()}
