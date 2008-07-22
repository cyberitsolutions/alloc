<div id="saved_views_subset">
  <select name="saved_filter" style="width: 65%">
    <option value="current">(current filter)</option>
    {$savedViewsOptions}
  </select>
  <input type="submit" name="loadFilter" value="Load"> {get_help("taskList_savedFilter")}
</div>
<div id="saved_views_superset" style="display: none;">
  <label for="new_filter_name"><b>Name</b>: <input type="text" name="new_filter_name" /></label>
  <input type="submit" name="saveFilter" value="Save"><br />
  <b>Delete</b>: <input type="submit" name="deleteFilter" value="Delete" id="deleteFilter" />
  <br />
</div>
