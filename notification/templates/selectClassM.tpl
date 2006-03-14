{:show_header}
{:show_toolbar}
  <h1>Add Event Filter - Select Object</h1>
  <form action="{url_alloc_eventFilterAdd}" method="post">
    Object:
    <select name="className">
      {classNameOptions}
    </select><br>
    <br>
    <input type="hidden" name="step" value="2">
    <input type="submit" name="submitClassName" value="Next">
  </form>
{:show_footer}
