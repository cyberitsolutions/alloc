<?php

include_once('../alloc.php');

$max_events = config::get_config_item('rssEntries');
$show_project = config::get_config_item('rssShowProject');

// generate the RSS feed based on the audit table and some task creation data
// output will be

$db = new db_alloc();
$events = array();

// create an artifical sort key. Creation/audit date is not unique, and creation should
// come before any status changes.
function gen_key($prefix = 0)
{
    static $subidx = 0;
    //this falls apart if the feed runs to more than 9999 items
    return '!' . $prefix . sprintf("%04d", $subidx++);
}

// a single '&' can't be inserted into the XML stream because it's used to
// create a character (&amp;, &lt;, etc). SimpleXML doesn't escape it, probably
// for that reason. There should be no HTML in task titles so this should be
// safe.
function escape_xml($string)
{
    return str_replace('&', '&amp;', $string);
}

$people =& get_cached_table('person');

// summary mode is the abbreviated version for the IRC bot
$summary = $_GET['summary'] ? true : false;

// can't be filtered in the query because it would break the history playback
$status_types = config::get_config_item('rssStatusFilter');

// find the last max_events audit events that are status change or reassignment
$query = prepare("SELECT audit.taskID, field, dateChanged, value, taskName, task.personID, task.projectID
                    FROM audit
               LEFT JOIN task AS task ON audit.taskID = task.taskID
                   WHERE field IN ('taskStatus', 'personID')
                ORDER BY dateChanged DESC
                   LIMIT %d", $max_events);
$db->query($query);

while ($row = $db->next_record()) {
    $key = $row['dateChanged'] . gen_key(1);
    $el = array("date" => $row['dateChanged']);

    if (!$row['personID']) {
        $name = "Unassigned";
    } else {
        $name = $people[$row['personID']]['username'];
    }

    // 'value' contains the true task status, whereas 'taskStatus' does not.
    if ($row['field'] != "taskStatus" || array_search($row['value'], $status_types) !== false) {
        $taskName = escape_xml($row['taskName']);
        $project = null;
        if ($show_project) {
            $project = new project();
            $project->set_id($row['projectID']);
            $project->select();
            $projectName = $project->get_value('projectShortName');
        }
        if ($summary) {
            $el['desc'] = sprintf('%s: %d %s "%s" %s', $name, $row['taskID'], $projectName, $taskName, $row['value']);
        } else {
            if ($row['field'] == "taskStatus") {
                $el['desc'] = sprintf('Task #%d "%s" (%s) status changed to %s', $row['taskID'], $taskName, $projectName, $row['value']);
            } else if ($row['field'] == "personID") {
                $el['desc'] = sprintf('Task #%d "%s" (%s) assigned to %s', $row['taskID'], $taskName, $projectName, $name);
            }
        }
        $events[$key] = $el;
    }
}

// generate the actual feed
$rss = new SimpleXMLElement('<rss version="2.0" />');

$channel = $rss->addChild("channel");
$channel->addChild("title", "allocPSA event feed");
$channel->addChild("link", config::get_config_item("allocURL")); // pull from config
$channel->addChild("description", "Alloc task event feed.");

// the RSS reader in supybot requires items be in reverse chronological order
$keys = array_keys($events);
rsort($keys);

// There are 20 events in the list, but older task creation events could show
// the wrong status. This would result in a change in the RSS feed, so readers
// may show it as a new event. Therefore, trim the list.
for ($i = 0; $i < $max_events; $i++) {
    $event = $events[$keys[$i]];
    $item = $channel->addChild("item");
    $item->addChild("title", $event['desc']);
    $date = strtotime($event['date']);
    $item->addChild("pubDate", strftime("%a, %d %b %Y %H:%M:%S %z", $date));
}

echo $rss->asXML();
