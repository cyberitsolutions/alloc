<?php
class date_home_item extends home_item {
  function date_home_item() {
    home_item::home_item("date", "Today", "home", "dateH.tpl", "narrow");
  }

  function show_date() {


    $date = explode(" ", date("r"));
    echo $date[0]." ".$date[1]." ".$date[2]." ".$date[3];

  }
}



?>
