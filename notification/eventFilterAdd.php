<?php
require_once("alloc.inc");

if (!isset($step)) {
  $step = 1;
}

switch ($step) {
case 1:
  // Class selection form
  $event_classes = get_event_classes();
  $TPL["classNameOptions"] = get_options_from_array($event_classes, "", false);
  include_template("templates/selectClassM.tpl");
  break;

case 2:
  // Event selection form
  $object = new $className;
  $event_names = $object->get_event_names();
  $TPL["className"] = $className;
  $TPL["eventNameOptions"] = get_options_from_array($event_names, "", false);
  include_template("templates/selectEventM.tpl");
  break;

case 3:
  // Filter entry form
  $object = new $className;
  $filter_class = $object->get_filter_class();

  if ($filter_class) {
    $skip_filter = false;
    $filter = new $filter_class;
    $TPL["filter_form"] = $filter->get_form();

    $TPL["className"] = $className;
    $TPL["eventName"] = $eventName;

    include_template("templates/objectFilterM.tpl");

    break;                      // Note we only break if we have a filter - otherwise we continue to the save case
  } else {
    $skip_filter = true;
  }

case 4:
  // Save and return to filter list
  $eventFilter = new eventFilter();
  $eventFilter->set_value("className", $className);
  $eventFilter->set_value("eventName", $eventName);
  $eventFilter->set_value("action", "email");
  $eventFilter->set_value("personID", $current_user->get_id());

  if (!$skip_filter) {
    $object = new $className;
    $filter_class = $object->get_filter_class();
    $object_filter = new $filter_class;
    $object_filter->read_form();
    $eventFilter->set_value("objectFilter", $object_filter);
  }

  $eventFilter->save();
  header("Location: ".$TPL["url_alloc_eventFilterList"]);
  break;

default:
  die("Unrecognized state");
}

page_close();



?>
