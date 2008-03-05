<?php

/*
 * Copyright (C) 2006, 2007, 2008 Alex Lance, Clancy Malcolm, Cybersource
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

class comment extends db_entity {
  var $data_table = "comment";

  function comment() {
    $this->db_entity();
    $this->key_field = new db_field("commentID");
    $this->data_fields = array("commentType"=>new db_field("commentType")
                              ,"commentLinkID"=>new db_field("commentLinkID")
                              ,"commentCreatedUser"=>new db_field("commentCreatedUser")
                              ,"commentCreatedTime"=>new db_field("commentCreatedTime")
                              ,"commentModifiedTime"=>new db_field("commentModifiedTime")
                              ,"commentModifiedUser"=>new db_field("commentModifiedUser")
                              ,"commentCreatedUserClientContactID"=>new db_field("commentCreatedUserClientContactID")
                              ,"commentCreatedUserText"=>new db_field("commentCreatedUserText")
                              ,"commentEmailRecipients"=>new db_field("commentEmailRecipients")
                              ,"commentEmailUID"=>new db_field("commentEmailUID")
                              ,"comment"=>new db_field("comment")
                              );
  }

  function delete() {
  
    if ($this->get_id()) {
      $dir = ATTACHMENTS_DIR."comment".DIRECTORY_SEPARATOR.$this->get_id();
      if (is_dir($dir)) {
        $handle = opendir($dir);
        clearstatcache();
        while (false !== ($file = readdir($handle))) {
          if ($file != "." && $file != ".." && file_exists($dir.DIRECTORY_SEPARATOR.$file)) {
            unlink($dir.DIRECTORY_SEPARATOR.$file);
            clearstatcache();
          }
        }
        is_dir($dir) && rmdir($dir);
      }
    }
    parent::delete();
  }

  function is_owner() {
    global $current_user;
    $entity = $this->get_value("commentType");
    $e = new $entity;
    $e->set_id($this->get_value("commentLinkID"));
    $e->select();
    return $e->is_owner($current_user);
  }

  function has_attachment_permission($person) {
    return $this->is_owner();
  }

  function has_attachment_permission_delete($person) {
    return $this->is_owner();
  }

  function get_comments($commentType="",$commentLinkID="") {
    $rows = array();
    if ($commentType && $commentLinkID) {
      $q = sprintf("SELECT commentID, commentLinkID, commentType,
                           commentCreatedUser as personID, 
                           commentCreatedTime as date, 
                           commentModifiedTime, 
                           commentModifiedUser, 
                           comment, 
                           commentCreatedUserClientContactID as clientContactID,
                           commentCreatedUserText,
                           commentEmailRecipients,
                           commentEmailUID
                      FROM comment 
                     WHERE commentType = '%s' AND commentLinkID = %d 
                  ORDER BY commentCreatedTime"
                  ,$commentType, $commentLinkID);
      $db = new db_alloc;
      $db->query($q);
      while ($row = $db->row()) {
        $rows[] = $row;
      }
    }
    return $rows;
  }

  function util_get_comments_array($entity, $id, $options=array()) {
    global $TPL, $current_user;
    $rows = array();
    $new_rows = array();
    // Need to get timeSheet comments too for task comments
    if ($entity == "task") {
      $rows = comment::get_comments($entity,$id);
      $rows2 = timeSheetItem::get_timeSheetItemComments($id);
      $rows or $rows = array();
      $rows2 or $rows2 = array();
      $rows = array_merge($rows,$rows2);
      if (is_array($rows)) {
        usort($rows, array("comment","sort_task_comments_callback_func"));
      }
    } else {
      $rows = comment::get_comments($entity,$id);
    }

    foreach ($rows as $v) {
      $new = $v;

      if (!$v["comment"])
        continue ;
  
      unset($children);
      $children = comment::util_get_comments_array("comment", $v["commentID"], $options);
      is_array($children) && count($children) and $new["children"] = $children;

      $new["attribution"] = comment::get_comment_attribution($v);


      if ($v["timeSheetID"]) {
        $new["ts_label"] = " (Time Sheet Comment)";

      } else if (($v["personID"] == $current_user->get_id()) && $options["showEditButtons"] && !$new["commentEmailUID"]) {
        $new["comment_buttons"] = "<input type=\"submit\" name=\"comment_edit\" value=\"Edit\"><input type=\"submit\" name=\"comment_delete\" value=\"Delete\" onClick=\"return confirm('Are you sure you want to delete this comment?')\">";
      }

      if (!$_GET["commentID"] || $_GET["commentID"] != $v["commentID"]) {

        if ($options["showEditButtons"]) {
          $new["form"] = '<form action="'.$TPL["url_alloc_comment"].'" method="post">';
          $new["form"].= '<input type="hidden" name="entity" value="'.$v["commentType"].'">';
          $new["form"].= '<input type="hidden" name="entityID" value="'.$v["commentLinkID"].'">';
          $new["form"].= '<input type="hidden" name="commentID" value="'.$v["commentID"].'">';
          $new["form"].= '<input type="hidden" name="comment_id" value="'.$v["commentID"].'">';
          $new["form"].= $new["comment_buttons"];
          $new["form"].= '</form>';
        }
    
        if ($new["commentEmailUID"] && config::get_config_item("allocEmailHost")) { 
          $new['downloadEmail'] = '<div style="float:right" class="noprint"><a href="'.$TPL["url_alloc_downloadEmail"].'msg_uid='.$new["commentEmailUID"].'">';
          $new['downloadEmail'].= '<img border="0" title="Download Email" src="'.$TPL["url_alloc_images"].'download_email.gif">';
          $new['downloadEmail'].= '<br>Download</a></div>';
        }

        $files = get_attachments("comment",$v["commentID"],array("sep"=>"<br>"));
        if (is_array($files)) {
          foreach($files as $key => $file) {
            $new["files"].= '<div align="center" style="float:left; display:inline; margin-right:14px;">'.$file["file"].'</div>';
          }
        }

        $v["commentEmailRecipients"] and $new["emailed"] = "<br>Emailed to ".htmlentities($v["commentEmailRecipients"]);

        $new_rows[] = $new;
      }
    }

    return $new_rows;
  }

  function util_get_comments($entity, $id, $options=array()) {
    global $TPL, $current_user;
    $rows = comment::util_get_comments_array($entity, $id, $options);
    $rows or $rows = array();
    foreach ($rows as $row) {
      $rtn.= comment::get_comment_html_table($row);
    }
    return $rtn;
  }

  function get_comment_html_table($row=array()) {
    global $TPL;
    $comment = text_to_html($row["comment"]);
    $onClick = "return set_grow_shrink('comment_".$row["commentID"]."','button_comment_".$row["commentID"]."','true');";
    $rtn[] = '<table width="100%" cellspacing="0" border="0" class="comments">';
    $rtn[] = '<tr>';
    $rtn[] = '  <th valign="top" class="magic" onClick="'.$onClick.'">'.$row["attribution"].$row["emailed"].'</th>';
    $rtn[] = '  <td valign="top" width="1%" class="nobr" align="right">'.$row["form"].'</td>';
    $rtn[] = '</tr>';
    $rtn[] = '<tr>';
    $rtn[] = '  <td class="magic" onClick="'.$onClick.'">'.$comment.'</td>';
    $row["files"] or $rtn[] = '  <td valign="bottom" align="center">'.$row["downloadEmail"].'</td>';
    $rtn[] = '</tr>';
    $row["children"] and $rtn[] = comment::get_comment_children($row["children"]);
    $row["files"] and $rtn[] = '<tr>';
    $row["files"] and $rtn[] = '  <td valign="bottom" align="left">'.$row["files"].'</td>';
    $row["files"] and $rtn[] = '  <td valign="bottom" align="center">'.$row["downloadEmail"].'</td>';
    $row["files"] and $rtn[] = '</tr>';
    $rtn[] = '</table>';
    return implode("\n",$rtn);
  }

  function get_comment_attribution($comment=array()) {
    $str = 'Comment by <b>'.comment::get_comment_author($comment).'</b> '.$comment["date"].$comment["ts_label"];
      if ($comment["commentModifiedTime"] || $comment["commentModifiedUser"]) {
        $str.= ", last modified by <b>".person::get_fullname($comment["commentModifiedUser"])."</b> ".$comment["commentModifiedTime"];
      }
    return $str;
  }

  function get_comment_children($children=array(), $padding=1) {
    $rtn = array();
    foreach($children as $child) {
      $rtn[] = "<tr><td style=\"padding-left:".($padding*15+3)."px\">".comment::get_comment_html_table($child)."</td></tr>";
      if (is_array($child["children"]) && count($child["children"])) {
        $padding += 1;
        $rtn[] = comment::get_comment_children($child["children"],$padding);
        $padding -= 1;
      } 
    } 
    return implode("\n",$rtn);
  }

  function get_comment_author($comment=array()) {
    if ($comment["commentCreatedUserText"]) {
      $author = htmlentities($comment["commentCreatedUserText"]);
    } else if ($comment["clientContactID"]) {
      $cc = new clientContact;
      $cc->set_id($comment["clientContactID"]);
      $cc->select();
      #$author = " <a href=\"".$TPL["url_alloc_client"]."clientID=".$cc->get_value("clientID")."\">".$cc->get_value("clientContactName")."</a>";
      $author = $cc->get_value("clientContactName");
    } else {
      $person = new person;
      $person->set_id($comment["personID"]);
      $person->select();
      $author = $person->get_username(1);
    }
    return $author;
  }

  function sort_task_comments_callback_func($a, $b) {
    return $a["date"] > $b["date"];
  }


}



?>
