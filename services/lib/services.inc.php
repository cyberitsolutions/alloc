<?php

/*
 * Copyright (C) 2006-2011 Alex Lance, Clancy Malcolm, Cyber IT Solutions
 * Pty. Ltd.
 * 
 * This file is part of the allocPSA application <info@cyber.com.au>.
 * 
 * allocPSA is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or (at
 * your option) any later version.
 * 
 * allocPSA is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public
 * License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with allocPSA. If not, see <http://www.gnu.org/licenses/>.
*/

/**
* alloc API
*
* A public interface for alloc code
* @author Alex Lance
* @version 1.0
*/
class services {

  public function __construct($sessID="") {
    $current_user = $this->get_current_user($sessID);
    singleton("current_user",$current_user);
  }

  /**
  * Perform an authentication check, start a new session
  * @param string $username
  * @param string $password
  * @return string the session key
  */
  public function authenticate($username,$password) {
    $person = new person();
    $sess = new session();
    $row = $person->get_valid_login_row($username,$password); 
    if ($row) {
      $sess->Start($row,false);
      $sess->UseGet();
      $sess->Save();
      return $sess->GetKey();
    } else {
      die("Authentication Failed(1)."); 
    }
  }  

  private function get_current_user($sessID) {
    $sess = new session($sessID);
    if ($sess->Started()) {
      $person = new person();
      $person->load_current_user($sess->Get("personID"));
      // update session_started, which affects session lifetime
      $sess->Save();
      return $person;
    }
  }

  /**
  * Get all the commments on a task
  * @param string $taskID
  * @return array an array of comments
  */
  public function get_task_comments($taskID) {
    if ($taskID) {
      $task = new task();
      $task->set_id($taskID);
      $task->select();
      return $task->get_task_comments_array();
    }
  }

  /**
  * Convert a comma separated string of names, into an array with email addresses
  * @param string $people
  * @param string $entity the related entity that can assist in the look up
  * @param integer $entityID the id of the related entity
  * @return array an array of people, indexed by their email address
  */
  public function get_people($options=array(), $entity="", $entityID="") {
    $person_table =& get_cached_table("person");
    $people = $options;

    if ($entity && $entityID) {
      $e = new $entity;
      $e->set_id($entityID);
      $e->select();
      in_array("default",$people)  and $default_recipients  = $e->get_all_parties();
      in_array("internal",$people) and $internal_recipients = $e->get_all_parties();
    }

    // remove default and internal from the array
    $clean_people = array_diff($people, array("default", "internal"));

    if (is_object($e)) {
      $projectID = $e->get_project_id();
      $p = new project();
      $p->set_id($projectID);
      $p->select();
      $client = $p->get_foreign_object("client");
      $clientID = $client->get_id();
    }

    foreach ((array)$default_recipients as $email => $info) {
      if ($info["selected"]) {
        $rtn[$email] = $this->reduce_person_info($info);
      }
    }

    foreach ((array)$internal_recipients as $email => $info) {
      if ($info["selected"] && !$info["external"]) {
        $rtn[$email] = $this->reduce_person_info($info);
      }
    }

    foreach ((array)$clean_people as $person) {
      $bad_person = true;
      $person = trim($person);

      // personID
      if (is_numeric($person)) {
        if ($person_table[$person]["personActive"]) {
          $rtn[$person_table[$person]["emailAddress"]] = $person_table[$person];
          $bad_person = false;
          continue;
        }

      // email addresses
      } else if (in_str("@",$person)) {
        foreach ($person_table as $pid => $data) {
          if (same_email_address($person,$data["emailAddress"]) && $data["personActive"]) {
            $rtn[$data["emailAddress"]] = $data;
            $bad_person = false;
            continue 2;
          }
        }
        
        if ($ccID = clientContact::find_by_email($person)) {
          $cc = new clientContact();
          $cc->set_id($ccID);
          $cc->select();
          $rtn[$cc->get_value("clientContactEmail")] = $cc->row();
          $bad_person = false;
          continue;
        }

        // If we get here, then return the email address entered
        list($e, $n) = parse_email_address($person);
        $rtn[$e] = array("emailAddress"=>$e, "name"=>$n);
        $bad_person = false;
        continue;

      // usernames, partial and full names
      } else {

        // Note the third check against partial: firstname." ".surname. Because $data["name"] will default back to username
        foreach ($person_table as $pid => $data) {
          if ((strtolower($person) == strtolower($data["username"])
            || strtolower($person) == strtolower($data["name"])
            || strtolower($person) == strtolower(substr(strtolower($data["firstName"]." ".$data["surname"]),0,strlen($person)))
          ) && $data["personActive"]) {
            $rtn[$data["emailAddress"]] = $data;
            $bad_person = false;
            continue 2;
          }
        }

        if ($ccID = clientContact::find_by_nick($person,$clientID)) {
          $cc = new clientContact();
          $cc->set_id($ccID);
          $cc->select();
          $rtn[$cc->get_value("clientContactEmail")] = $cc->row();
          $bad_person = false;
          continue;
        }

        if ($ccID = clientContact::find_by_name($person,$projectID)) {
          $cc = new clientContact();
          $cc->set_id($ccID);
          $cc->select();
          $rtn[$cc->get_value("clientContactEmail")] = $cc->row();
          $bad_person = false;
          continue;
        }
        if ($ccID = clientContact::find_by_partial_name($person,$projectID)) {
          $cc = new clientContact();
          $cc->set_id($ccID);
          $cc->select();
          $rtn[$cc->get_value("clientContactEmail")] = $cc->row();
          $bad_person = false;
          continue;
        }
      }

      if ($bad_person) {
        die("Unable to find person: ".$person);
      }
    }

    foreach ((array)$rtn as $id => $p) {
      $rtn[$id] = $this->reduce_person_info($p);
    }
    return (array)$rtn;
  }

  private function reduce_person_info($person) {
    $rtn["personID"] = $person["personID"];
    $rtn["username"] = $person["username"];
    $rtn["name"]     = $person["name"]             or $rtn["name"] = $person["clientContactName"];
    $rtn["emailAddress"] = $person["emailAddress"] or $rtn["emailAddress"] = $person["clientContactEmail"] or $rtn["emailAddress"] = $person["email"];
    $rtn["clientContactID"] = $person["clientContactID"];
    return $rtn;
  }

  /**
  * Add a timesheet item
  * @param array $options
  * @return string a success message
  */
  public function add_timeSheetItem($options) {
    $rtn = timeSheet::add_timeSheetItem($options);
    if ($rtn["status"] == "yay") {
      return $rtn["message"];
    } else {
      die(print_r($rtn,1));
    }
  }

  /**
  * Move a time sheet to a different status
  * @param integer $timeSheetID the time sheet to change
  * @param string $direction the direction to move the timesheet eg "forwards" or "backwards"
  * @return string a success message
  */
  public function change_timeSheet_status($timeSheetID,$direction) {
    $timeSheet = new timeSheet();
    $timeSheet->set_id($timeSheetID);
    $timeSheet->select();
    $rtn = $timeSheet->change_status($direction);
    $timeSheet->save();
    return $rtn;
  }

  /**
  * Convert a tf from its name to its tf ID
  * @param mixed $name a tf name
  * @return integer the tf's ID
  */
  public function get_tfID($options) {
    return tf::get_tfID($options);
  } 

  /**
  * Get a list of entities, eg one of: tasks, comments, timeSheets, projects et al. See also this::get_list_help()
  * @param string $entity the entity of which to get a list
  * @param array $options the various filter options to apply see: ${entity}/lib/${entity}.inc.php -> get_list_filter().
  * @return array the list of entities
  */
  public function get_list($entity, $options=array()) {
    $current_user = &singleton("current_user");
    if (class_exists($entity)) {
      $options = obj2array($options);
      $e = new $entity;
      if (method_exists($e, "get_list")) {
        ob_start();
        $rtn = $e->get_list($options);
        $echoed = ob_get_contents();
        if (!$rtn && $echoed) {
          return array("error"=>$echoed);
        } else {
          if (isset($rtn["rows"])) {
            return $rtn["rows"];
          } else {
            return $rtn;
          }
        }
      } else {
        die("Entity method '".$entity."->get_list()' does not exist."); 
      }
    } else {
      die("Entity '".$entity."' does not exist."); 
    }
  }

  /**
  * Run a search across all emails, using PHP's IMAP search syntax http://php.net/imap_search and RFC2060 6.4.4
  * @param string $str the search string
  * @return string of mbox format emails
  */
  public function search_emails($str) {
    if ($str) {
      $uids = $this->get_comment_email_uids_search($str);
      foreach ((array)$uids as $uid) {
        $emails.= $this->get_email($uid);
      }
    }
    return $emails;
  }

  /**
  * Grab all emails from a task mail box
  * @param integer $taskID the task (or other entity) id
  * @param string $entity the particular entity: task, client, project, etc
  * @return string of mbox format emails
  */
  public function get_task_emails($taskID, $entity="task") {
    $current_user = &singleton("current_user");
    $entity or $entity = "task";
    if ($taskID) {
      $folder = config::get_config_item("allocEmailFolder")."/".$entity.$taskID;
      $info = $this->init_email_info();
      $mail = new email_receive($info);
      $mail->open_mailbox($folder,OP_READONLY);
      $uids = $mail->get_all_email_msg_uids();
      foreach ((array)$uids as $uid) {
        list($header,$body) = $mail->get_raw_email_by_msg_uid($uid);
        if ($header && $body) {
          $m = new email_send();
          $m->set_headers($header);
          $timestamp = $m->get_header('Date');
          $str = "\r\nFrom allocPSA ".date('D M  j G:i:s Y',strtotime($timestamp))."\r\n".$header.$body;
          $emails.= utf8_encode(str_replace("\r\n","\n",$str));
        }
      }
      $mail->close();
    }
    return $emails;
  }

  /**
  * Get all time sheet item comments in a faked mbox format
  * @param integer $taskID which task the time sheet item comments relate to
  * @return string of mbox format emails
  */
  public function get_timeSheetItem_comments($taskID) {
    $people =& get_cached_table("person");
    has("time") and $rows = timeSheetItem::get_timeSheetItemComments($taskID);
    foreach ((array)$rows as $row) {
      $d = $row["timeSheetItemCreatedTime"] or $d = $row["date"];
      $timestamp = format_date("U",$d);
      $name = $people[$row["personID"]]["name"];
      $str.= $br."From allocPSA ".date('D M  j G:i:s Y',$timestamp);
      $str.= "\nFrom: ".$name;
      $str.= "\nDate: ".date("D, d M Y H:i:s O",$timestamp);
      $str.= "\n\n".$name." ".$row["duration"]." ".$row["comment"];
      $br = "\n\n";
    } 
    return $str;
  }

  private function init_email_info() {
    $current_user = &singleton("current_user");
    $info["host"] = config::get_config_item("allocEmailHost");
    $info["port"] = config::get_config_item("allocEmailPort");
    $info["username"] = config::get_config_item("allocEmailUsername");
    $info["password"] = config::get_config_item("allocEmailPassword");
    $info["protocol"] = config::get_config_item("allocEmailProtocol");
    if (!$info["host"]) {
      die("Email mailbox host not defined, assuming email fetch function is inactive.");
    }
    return $info;
  }

  /**
  * Get a single email, add an mbox date header line
  * @param integer $emailUID the IMAP UID of an email
  * @return string a single email in mbox format
  */
  public function get_email($emailUID) {
    $current_user = &singleton("current_user");
    //$lockfile = ATTACHMENTS_DIR."mail.lock.person_".$current_user->get_id();
    if ($emailUID) {
      $info = $this->init_email_info();
      $mail = new email_receive($info);
      $mail->open_mailbox(config::get_config_item("allocEmailFolder"),OP_READONLY);
      list($header,$body) = $mail->get_raw_email_by_msg_uid($emailUID);
      $mail->close();
      $m = new email_send();
      $m->set_headers($header);
      $timestamp = $m->get_header('Date');
      $str = "From allocPSA ".date('D M  j G:i:s Y',strtotime($timestamp))."\r\n".$header.$body;
      return utf8_encode(str_replace("\r\n","\n",$str));
    }
  }

  /**
  * Get a list of IMAP email UIDs, based on a string search
  * @param string $str the search string
  * @return array an array of email UIDs
  */
  public function get_comment_email_uids_search($str) {
    if ($str) { 
      $current_user = &singleton("current_user");
      $info = $this->init_email_info();
      $mail = new email_receive($info);
      $mail->open_mailbox(config::get_config_item("allocEmailFolder"),OP_READONLY);
      $rtn = $mail->get_emails_UIDs_search($str);
      $mail->close();
    }
    return (array)$rtn;
  }

  /**
  * A now defunct method to obtain help about this class
  * @param string $topic the name of the class method, eg "get_list"
  * @return string the help information
  */
  public function get_help($topic="") {
    $this_methods = get_class_methods($this);

    if (!$topic) {
      foreach ($this_methods as $method) {
        $m = $method."_help";
        if (method_exists($this,$m)) {
          $available_topics.= $commar.$method;
          $commar = ", ";
        }
      }
      die("Help is available for the following methods: ".$available_topics);

    } else {
      $m = $topic."_help";
      if (method_exists($this,$m)) {
        return $this->$m();
      } else {
        die("No help exists for this method: ".$topic); 
      }
    }
  }

  /**
  * Add an interested party
  * @param array $options see shared/lib/interestedParty.inc.php [add|delete]_interested_party()
  */
  public function save_interestedParty($options) {
    // Python will submit None instead of ''
    foreach ($options as $k=>$v) { strtolower($v) != 'none' and $data[$k] = $v; }

    // Check we have the minimum of fields
    if ($data["entity"] && $data["entityID"] && $data["emailAddress"]) {
      interestedParty::delete_interested_party($data["entity"],$data["entityID"],$data["emailAddress"]);
      interestedParty::add_interested_party($data);
    }
  }

  /**
  * Deactivate (not delete) an interested party
  * @param array $options see shared/lib/interestedParty.inc.php [add|delete]_interested_party()
  */
  public function delete_interestedParty($options) {
    // Python will submit None instead of ''
    foreach ($options as $k=>$v) { strtolower($v) != 'none' and $data[$k] = $v; }

    // Delete existing entries
    if ($data["entity"] && $data["entityID"] && $data["emailAddress"]) {
      interestedParty::delete_interested_party($data["entity"],$data["entityID"],$data["emailAddress"]);
    }
  }

  /**
  * An introspective method to display all the various get_list options across all the different entities
  * @return string the help text
  */
  private function get_list_help() {
    global $modules;
    foreach ($modules as $name => $object) {  
      if (is_object($object) && is_array($object->db_entities)) {
        foreach ($object->db_entities as $entity) {
          unset($commar2);
          if (class_exists($entity)) {
            $e = new $entity;
            if (method_exists($e, "get_list")) {
              $rtn.= "\n\nEntity: ".$entity."\nOptions:\n";
              if (method_exists($e, "get_list_vars")) {
                $options = $e->get_list_vars();
                foreach ($options as $option=>$help) {
                  $padding = 30 - strlen($option);
                  $rtn.= $commar2."    ".$option.str_repeat(" ",$padding).$help;
                  $commar2 = "\n";
                }
              }
            }
          }
        }
      }
    }
    die("Usage: get_list(entity, options). The following entities are available: ".$rtn);
  }

  /**
  * A generic method to edit entities
  * @param string $entity which type of entity to edit
  * @param integer $id the id of the entity
  * @param array $options the edit options see email/lib/command.inc.php for the various options
  * @return array success or failure object
  */
  public function edit_entity($entity,$id,$options=false) {
    $options[$entity] = $id;
    if (strtolower($options[$entity]) == "help") {
      return array("status"=>"msg","message"=>command::get_help($entity));
    } else if ($options) {
      $command = new command();
      return $command->run_commands($options);
    }
  }

} 

?>
