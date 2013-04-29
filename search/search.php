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

require_once("../alloc.php");


function format_display_fields($str="") {
  if ($str) {
    $lines = explode("|+|=|",$str); // arbitrary line delimiter, can't use newlines as data will contain newlines.
    $t = "<table class='list'>";
    foreach ($lines as $line) {
      $t.= "<tr>";
      $cells = explode("|",$line);
      foreach ($cells as $cell) {
        $t.= "<td>".str_replace(array("\n","\r","<br>","<br />")," ",substr($cell,0,200))."</td>";
      }
      $t.= "</tr>";
    }
    $t.= "</table>";
    return "<div>".$t."</div>";
  }
}


global $TPL;


$noRedirect = $_POST["idRedirect"]   or $noRedirect = $_GET["idRedirect"];
$search     = $_POST["search"]       or $search     = $_GET["search"];
$category   = $_POST["category"]     or $category   = $_GET["category"];
$needle     = trim($_POST["needle"]) or $needle     = trim($_GET["needle"]);

$db = new db_alloc();

// Project Search
if ($search && $needle && $category == "search_projects") {

  $TPL["search_title"] = "Project Search";

  if (!$noRedirect && is_numeric($needle)) {
    $query = prepare("SELECT projectID FROM project WHERE projectID = %d",$needle);
    $db->query($query);
    if ($db->next_record()) {
      alloc_redirect($TPL["url_alloc_project"]."projectID=".$db->f("projectID"));
    } 

  } else {

    $index = new Zend_Search_Lucene(ATTACHMENTS_DIR.'search/project');
    $query = Zend_Search_Lucene_Search_QueryParser::parse($needle);  
    $hits = $index->find($needle);
    $TPL["index_count"] = $index->count();
    $TPL["hits_count"] = count($hits);

    foreach ($hits as $hit) {
      $d = $hit->getDocument();
      $row = array();
      $row["idx"] = $hit->id;
      $row["score"] = sprintf('%d%%', $hit->score*100);
      $row["title"] = $d->getFieldValue('id')." ".sprintf("<a href='%sprojectID=%d'>%s</a>"
                      ,$TPL["url_alloc_project"], $d->getFieldValue('id'), page::htmlentities($d->getFieldValue('name')));
      $row["related"] = sprintf("<a href='%sclientID=%d'>%s</a>"
                      ,$TPL["url_alloc_client"], $d->getFieldValue('cid'), page::htmlentities($d->getFieldValue('client')));
      $row["desc"] = page::htmlentities($d->getFieldValue('desc'));
      $TPL["search_results"][] = $row;
    }
  }

// Clients Search
} else if ($search && $needle && $category == "search_clients") {

  $TPL["search_title"] = "Client Search";

  if (!$noRedirect && is_numeric($needle)) {
    $query = prepare("SELECT clientID FROM client WHERE clientID = %d",$needle);
    $db->query($query);
    if ($db->next_record()) {
      alloc_redirect($TPL["url_alloc_client"]."clientID=".$db->f("clientID"));
    } 
    
  } else {

    $index = new Zend_Search_Lucene(ATTACHMENTS_DIR.'search/client');
    $query = Zend_Search_Lucene_Search_QueryParser::parse($needle);  
    $hits = $index->find($needle);
    $TPL["index_count"] = $index->count();
    $TPL["hits_count"] = count($hits);

    foreach ($hits as $hit) {
      $d = $hit->getDocument();
      $row = array();
      $row["idx"] = $hit->id;
      $row["score"] = sprintf('%d%%', $hit->score*100);
      $row["title"] = $d->getFieldValue('id')." ".sprintf("<a href='%sclientID=%d'>%s</a>"
                      ,$TPL["url_alloc_client"], $d->getFieldValue('id'), page::htmlentities($d->getFieldValue('name')));
      //$row["related"] = sprintf("<a href='%sprojectID=%d'>%s</a>"
      //                ,$TPL["url_alloc_project"], $d->getFieldValue('pid'), $d->getFieldValue('project'));

      unset($num_contact);
      if ($d->getFieldValue('contact')) {
        $num_contact = count((array)explode("|+|=|",$d->getFieldValue('contact')));
        unset($s); $num_contact > 1 and $s = "s";
        $num_contact and $num_contact = "\n\n".$num_contact." contact".$s.".\n";
      }

      $desc = page::htmlentities($d->getFieldValue('desc'));

      $row["desc"] = $desc.$num_contact;
      $row["desc2"] = page::htmlentities($d->getFieldValue('contact'));

      $TPL["search_results"][] = $row;
    }


  }

// Tasks Search
} else if ($search && $needle && $category == "search_tasks") {

  $TPL["search_title"] = "Task Search";

  if (!$noRedirect && is_numeric($needle)) {
    $query = prepare("SELECT taskID FROM task WHERE taskID = %d",$needle);
    $db->query($query);
    if ($db->next_record()) {
      alloc_redirect($TPL["url_alloc_task"]."taskID=".$db->f("taskID"));
    } 

  } else {

    $index = new Zend_Search_Lucene(ATTACHMENTS_DIR.'search/task');
    $query = Zend_Search_Lucene_Search_QueryParser::parse($needle);  
    $hits = $index->find($needle);
    $TPL["index_count"] = $index->count();
    $TPL["hits_count"] = count($hits);

    foreach ($hits as $hit) {
      $d = $hit->getDocument();
      $row = array();
      $row["idx"] = $hit->id;
      $row["score"] = sprintf('%d%%', $hit->score*100);
      $row["title"] = $d->getFieldValue('id')." ".sprintf("<a href='%staskID=%d'>%s</a>"
                      ,$TPL["url_alloc_task"], $d->getFieldValue('id'), page::htmlentities($d->getFieldValue('name')));
      $row["related"] = sprintf("<a href='%sprojectID=%d'>%s</a>"
                      ,$TPL["url_alloc_project"], $d->getFieldValue('pid'), page::htmlentities($d->getFieldValue('project')));
      $row["desc"] = page::htmlentities($d->getFieldValue('desc'));
      $TPL["search_results"][] = $row;
    }
  }


// Item Search
} else if ($search && $needle && $category == "search_items") {

  $TPL["search_title"] = "Item Search";
  $today = date("Y")."-".date("m")."-".date("d");

  if (!$noRedirect && is_numeric($needle)) {
    $query = prepare("SELECT itemID FROM item WHERE itemID = %d",$needle);
    $db->query($query);
    if ($db->next_record()) {
      alloc_redirect($TPL["url_alloc_item"]."itemID=".$db->f("itemID"));
    }

  } else {

    //open the index
    $index = new Zend_Search_Lucene(ATTACHMENTS_DIR.'search/item');
    $query = Zend_Search_Lucene_Search_QueryParser::parse($needle);  
    $hits = $index->find($needle);
    $TPL["index_count"] = $index->count();
    $TPL["hits_count"] = count($hits);

    $p =& get_cached_table("person");

    foreach ($hits as $hit) {
      $d = $hit->getDocument();
      $item = new item();
      $item->set_id($d->getFieldValue('id'));
      $item->select();
      $row = array();
      $row["idx"] = $hit->id;
      $author = $item->get_value("itemAuthor");
      $author and $author = " by ".$author;
      $row["title"] = $item->get_id()." ".$item->get_link().$author;
      $row["score"] = sprintf('%d%%', $hit->score*100);
      $row["desc"] = page::htmlentities($d->getFieldValue('desc'));

      // get availability of loan
      $db2 = new db_alloc();
      $query = prepare("SELECT * FROM loan WHERE itemID = %d AND dateReturned='0000-00-00'",$item->get_id());
      $db2->query($query);
      if ($db2->next_record()) {
        $loan = new loan();
        $loan->read_db_record($db2);

        if ($loan->have_perm(PERM_READ_WRITE)) {
          // if item is overdue
          if ($loan->get_value("dateToBeReturned") < $today) {
            $status = "Overdue";
          } else {
            $status = "Due on ".$loan->get_value("dateToBeReturned");
          }
          $row["related"] = $status." <a href=\"".$TPL["url_alloc_item"]."itemID=".$item->get_id()."&return=true\">Return</a>";

        // Else you dont have permission to loan or return so just show status
        } else {
          
          $name = page::htmlentities($p[$loan->get_value("personID")]["name"]);

          if ($loan->get_value("dateToBeReturned") < $today) {
            $row["related"] = "Overdue from ".$name;
          } else {
            $row["related"] = "Due from ".$name." on ".$loan->get_value("dateToBeReturned");
          }
        }

      } else {
        $row["related"] = "Available <a href=\"".$TPL["url_alloc_item"]."itemID=".$item->get_id()."&borrow=true\">Borrow</a>";
      }
  
      $TPL["search_results"][] = $row;
    }
  }
 

// Time Sheet Search
} else if ($search && $needle && $category == "search_time") {

  $TPL["search_title"] = "Time Sheet Search";

  if (!$noRedirect && is_numeric($needle)) {
    $query = prepare("SELECT timeSheetID FROM timeSheet WHERE timeSheetID = %d",$needle);
    $db->query($query);
    if ($db->next_record()) {
      alloc_redirect($TPL["url_alloc_timeSheet"]."timeSheetID=".$db->f("timeSheetID"));
    } 
    
  } else {

    $index = new Zend_Search_Lucene(ATTACHMENTS_DIR.'search/timeSheet');
    $query = Zend_Search_Lucene_Search_QueryParser::parse($needle);  
    $hits = $index->find($needle);
    $TPL["index_count"] = $index->count();
    $TPL["hits_count"] = count($hits);

    foreach ($hits as $hit) {
      $d = $hit->getDocument();
      $row = array();
      $row["idx"] = $hit->id;
      $row["score"] = sprintf('%d%%', $hit->score*100);
      $c = (array)explode(" ",$d->getFieldValue('creator'));
      $creator = implode(" ",(array)array_slice($c,2));
      //$creator = implode(" ",array_shift(array_shift(explode(" ",$d->getFieldValue('creator')))));
      $row["title"] = $d->getFieldValue('id')." ".sprintf("<a href='%stimeSheetID=%d'>%s</a>"
                      ,$TPL["url_alloc_timeSheet"], $d->getFieldValue('id')
                      ,"Time Sheet for ".page::htmlentities($d->getFieldValue('project'))." by ".page::htmlentities($creator));
      $row["related"] = sprintf("<a href='%sprojectID=%d'>%s</a>"
                      ,$TPL["url_alloc_project"], $d->getFieldValue('pid'), page::htmlentities($d->getFieldValue('project')));

      $row["desc"] = page::htmlentities($d->getFieldValue('desc'));
      $TPL["search_results"][] = $row;
    }

  }

// Comment Search
} else if ($search && $needle && $category == "search_comment") {

  $TPL["search_title"] = "Comment Search";

  if (!$noRedirect && is_numeric($needle)) {
    $query = prepare("SELECT commentID FROM comment WHERE commentID = %d",$needle);
    $db->query($query);
    if ($db->next_record()) {
      alloc_redirect($TPL["url_alloc_comment"]."commentID=".$db->f("commentID"));
    } 
    
  } else {

    $index = new Zend_Search_Lucene(ATTACHMENTS_DIR.'search/comment');
    $query = Zend_Search_Lucene_Search_QueryParser::parse($needle);  
    $hits = $index->find($needle);
    $TPL["index_count"] = $index->count();
    $TPL["hits_count"] = count($hits);

    foreach ($hits as $hit) {
      $d = $hit->getDocument();
      $row = array();
      $row["idx"] = $hit->id;
      $row["score"] = sprintf('%d%%', $hit->score*100);
      $row["title"] = page::htmlentities($d->getFieldValue('name'));
      $row["related"] = sprintf("<a href='%s%sID=%d'>%s</a>"
                      ,$TPL["url_alloc_".$d->getFieldValue('type')], $d->getFieldValue('type')
                      ,$d->getFieldValue('typeid'), page::htmlentities($d->getFieldValue('typename')));
      $row["desc"] = page::htmlentities($d->getFieldValue('desc'));
      $TPL["search_results"][] = $row;
    }
  }

// Wiki Search
} else if ($search && $needle && $category == "search_wiki") {

  $TPL["search_title"] = "Wiki Search";

  $index = new Zend_Search_Lucene(ATTACHMENTS_DIR.'search/wiki');
  $query = Zend_Search_Lucene_Search_QueryParser::parse($needle);  
  $hits = $index->find($needle);
  $TPL["index_count"] = $index->count();
  $TPL["hits_count"] = count($hits);

  foreach ($hits as $hit) {
    $d = $hit->getDocument();
    $row = array();
    $row["idx"] = $hit->id;
    $row["score"] = sprintf('%d%%', $hit->score*100);
    $row["title"] = sprintf("<a href='%starget=%s'>%s</a>"
                    ,$TPL["url_alloc_wiki"], urlencode($d->getFieldValue('name'))
                    ,page::htmlentities($d->getFieldValue('name')));
    $row["desc"] = page::htmlentities($d->getFieldValue('desc'));
    $TPL["search_results"][] = $row;
  }

}


// setup generic values
$TPL["search_category_options"] = page::get_category_options($category);
$TPL["needle"] = $needle;
$TPL["needle2"] = $needle;
if (!$needle || $noRedirect) {
  $TPL["redir"] = "checked=\"1\"";
}

$TPL["main_alloc_title"] = "Search - ".APPLICATION_NAME;
include_template("templates/searchM.tpl");

?>
