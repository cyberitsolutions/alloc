<?php

include_once('../alloc.php');

$max_events = 10; //should probably be replaced by a config item later

// generate the RSS feed based on the audit table and some task creation data
// output will be

$db = new db_alloc;
$events = array();

//create an artifical sort key. Creation/audit date is not unique, and creation should 
//come before any status changes.
function gen_key($prefix = 0) {
  static $subidx = 0;
  //this falls apart if the feed runs to more than 9999 items
  return '!' . $prefix . sprintf("%04d", $subidx++);
}

$people = get_cached_table('person');

//the task history
$trace = array();

$summary = $_GET['summary'] ? true : false;
// summary mode is the abbreviated version for the IRC bot

// find the last max_events audit events that are status change or reassignment
$query = sprintf("SELECT entityID, fieldName, changeType, dateChanged, oldValue, taskName, taskStatus, task.personID
                    FROM auditItem LEFT JOIN task as task ON entityID = taskID
                   WHERE entityName = 'task' AND changeType = 'FieldChange' AND fieldName IN ('taskStatus', 'personID')
                ORDER BY dateChanged DESC
                   LIMIT %d", $max_events);
$db->query($query);
while ($row = $db->next_record()) {
  $key = $row['dateChanged'] . gen_key(1);
  $el = array("date" => $row['dateChanged']);

  //overwrite the new data (taskStatus, personID) with the correct (historical) data
  if ($trace[$row['entityID']]) {
    $row = array_merge($row, $trace[$row['entityID']]);
  }
  
  if (!$row['personID']) {
    $name = "Unassigned";
  } else {
    $name = $people[$row['personID']]['username'];
  }
  
  if ($summary) {
    $el['desc'] = sprintf('%s: %d "%s" %s', $name, $row['entityID'], $row['taskName'], $row['taskStatus']);
  } else {
    if ($row['fieldName'] == "taskStatus") {
      $el['desc'] = sprintf('Task #%d "%s" status changed to %s', $row['entityID'], $row['taskName'], $row['taskStatus']);
    } else if ($row['fieldName'] == "personID") {
      $el['desc'] = sprintf('Task #%d "%s" assigned to %s', $row['entityID'], $row['taskName'], $name);
    } else {
      $el['desc'] = "error!";
    }
  }

  //record the history 
  $trace[$db->f('entityID')][$db->f('fieldName')] = $db->f('oldValue');
  
  $events[$key] = $el;
}


// Task creation events aren't stored in the audit table, so they have to be 
// queried separately. This has to be done at the end, after the historical task 
// status has been retrieved.

$query = sprintf("SELECT taskID, dateCreated, taskName, personID, taskStatus
                   FROM task
               ORDER BY dateCreated DESC
                  LIMIT %d", $max_events);
$db->query($query);
while ($row = $db->next_record()) {
  if ($trace[$row['taskID']]) {
    $row = array_merge($row, $trace[$row['taskID']]);
  }
  if (!$row['personID']) {
    $name = "Unassigned";
  } else {
    $name = $people[$row['personID']]['username'];
  }
  
  if ($summary)
    $desc = sprintf('%s: %d "%s" %s', $name, $row['taskID'], $row['taskName'], $row['taskStatus']);
  else
    $desc = sprintf('Task #%d "%s" created.', $row['taskID'], $row['taskName']);

  $events[$row['dateCreated'].gen_key(0)]= array("date" => $row['dateCreated'],
    "desc" => $desc);
}

// generate the actual feed
$rss = new SimpleXMLElement('<rss version="2.0" />');

$channel = $rss->addChild("channel");
$channel->addChild("title", "allocPSA event feed");
$channel->addChild("link", config::get_config_item("allocURL")); //pull from config
$channel->addChild("description", "Alloc task event feed.");

// the RSS reader in supybot requires items be in reverse chronological order
$keys = array_keys($events);
rsort($keys);

// There are 20 events in the list, but older task creation events could show 
// the wrong status. This would result in a change in the RSS feed, so readers 
// may show it as a new event. Therefore, trim the list.
for ($i = 0;$i < $max_events;$i++) {
  $event = $events[$keys[$i]];
  $item = $channel->addChild("item");
  $item->addChild("title", $event['desc']);
  $date = strtotime($event['date']);
  $item->addChild("pubDate", strftime ("%a, %d %b %Y %H:%M:%S %z", $date));
}

echo $rss->asXML();
?>

