var alloc_http_request = new Array();

function makeAjaxRequest(url,entityid) {
  $("#"+entityid).html('<img src="../images/ticker2.gif" alt="Updating field..." title="Updating field...">');
  jQuery.get(url,'',function(data) {
    $("#"+entityid).hide();
    $("#"+entityid).html(data);
    $("#"+entityid).fadeIn("fast");
  })
}

// This is a generic show/hide for anything
function set_grow_shrink(id, id_to_hide, use_classes_instead_of_ids) {
  // toggle the other div - if any
  if (use_classes_instead_of_ids && id_to_hide) {
    $("."+id_to_hide).slideToggle("fast");
  } else if (id_to_hide) {
    $("#"+id_to_hide).slideToggle("fast");
  }
  // hide or show the actual div
  if (use_classes_instead_of_ids) {
    $("."+id).slideToggle("fast");
  } else {
    $("#"+id).slideToggle("fast");
  }
  return false;
}

function sidebyside_activate(id,arr) {
  if (id == "sbsAll") {
    for (var i=0; i<arr.length; i++) {
      if (arr[i] != "sbsAll") {
        //$("#"+arr[i]).show(); konqueror doesn't like this
        document.getElementById(arr[i]).style.display = 'inline';
        $('#sbs_link_'+arr[i]).removeClass("sidebyside_active").addClass("sidebyside");
      }
    }
    $('#sbs_link_' + id).addClass("sidebyside_active");

  } else {
    for (var i=0; i<arr.length; i++) {
      if (arr[i] != "sbsAll") {
        //$("#"+arr[i]).hide();  konqueror doesn't like this
        document.getElementById(arr[i]).style.display = 'none';
      }
      $('#sbs_link_' + arr[i]).removeClass("sidebyside_active").addClass("sidebyside");
    }
    $('#sbs_link_' + id).addClass("sidebyside_active");
    //$("#"+id).show(); konqueror doesn't like this
    document.getElementById(id).style.display = 'inline';
  }
}


// These global variables are for the setTimeout() below
var alloc_current_resizable_textarea = "";
var alloc_current_resizable_textarea_default_height = "";
var alloc_current_resizable_textarea_maximum_height = "";
var alloc_current_resizable_textarea_timer = "";

// this function dynamically resizes a text area as data is inputted
function adjust_textarea(textarea, default_height, maximum_height) {
  // a div is setup off screen, we use that div to determine the height of the textarea
  alloc_current_resizable_textarea = textarea;
  alloc_current_resizable_textarea_default_height = default_height;
  alloc_current_resizable_textarea_maximum_height = maximum_height;
  var shadow = document.getElementById("shadow_" + textarea.id);
  shadow.style.width=parseInt(textarea.clientWidth-8)+'px';
  shadow.innerHTML = textarea.value.replace(/[\n]/g,'<br />&nbsp;');
  var shadow_height = shadow.clientHeight;
  if(shadow_height < default_height) {
    var n = default_height;
  } else if(shadow_height > maximum_height) {
    var n = maximum_height;
  } else {
    var n = shadow_height+14;
  }
  textarea.style.height = n+'px';
  alloc_current_resizable_textarea_timer = setTimeout('adjust_textarea(alloc_current_resizable_textarea,alloc_current_resizable_textarea_default_height,alloc_current_resizable_textarea_maximum_height)', 1000);
}

function stop_textarea_timer() {
  clearTimeout(alloc_current_resizable_textarea_timer);
}

function help_text_on(id, str) {
  $('#main').append("<div id='helper' style='display:none'></div>");
  $('#helper').hide().html(str).corner();
  
  offset = $('#'+id).offset();
  
  x = offset.left -400;
  if (x < 0) {
    x = x + 380;
  } 
  $("#helper").css('left',x);
  
  y = offset.top - 50;
  if (y > 350) {
    y = y-$('#helper').height() -40;
  } 
  
  $("#helper").css('top',y);
  $("#helper").fadeIn("normal");
} 
function help_text_off(id) {
  $("#helper").fadeOut("normal");
  $('#helper').remove();
} 



// Preload mouseover images
if (document.images) {
  pic1= new Image(9,11);
  pic1.src="../images/arrow_blank.gif";
  pic2= new Image(9,11);
  pic2.src="../images/arrow_faded.gif";
  pic3= new Image(9,11);
  pic3.src="../images/arrow_down.gif";
  pic4= new Image(9,11);
  pic4.src="../images/arrow_up.gif";
  pic5= new Image(119,13);
  pic5.src="../images/ticker2.gif";
}


// When the document has loaded...
$(document).ready(function() {
  // Give the tables alternating stripes
  $(".list tr:nth-child(even)").addClass("even");
  $(".list tr:nth-child(odd)").addClass("odd");
  $(".corner").corner();
  $(".delete_button").bind("click", function(e){
    return confirm("Click OK to confirm deletion.");
  });
  $(".confirm_button").bind("click", function(e){
    return confirm("Click OK to confirm.");
  });
  $("input.datefield").bind("dblclick", function(e){
    var now = new Date();
    this.value=now.getFullYear()+'-'+(now.getMonth()+1)+'-'+now.getDate();
  });
  $("input.datefield").bind("focus", function(e){
    if (this.value == "YYYY-MM-DD") {
      this.style.color = "#333333";
      this.value = "";
    }
  });
  $("input.datefield").each(function(){
    if (this.value == "") {
      this.style.color = "#cccccc";
      this.value = "YYYY-MM-DD";
    }
  });
  $('form').submit(function(){
    $("input.datefield").each(function(){
      if (this.value == "YYYY-MM-DD") {
        this.value = "";
      }
    });
  });
  $('tr.clickrow').bind('click',function(e){                                                                                                     
    var id = this.id.split('_')[1]; // clickrow_43242
    if (id && !$(e.target).is('input:checkbox') && !$(e.target).is('a')) {
      $('#checkbox_'+id).attr('checked',!$('#checkbox_'+id).attr('checked'));
    }
  });
});

