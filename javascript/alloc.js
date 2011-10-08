function redraw_multiple_selects(container) {
  if (container) {
    var c = "#"+container
  }
  $("select[multiple]",c).dropdownchecklist("destroy");
  $("select[multiple]",c).dropdownchecklist( { "maxDropHeight":450 } );
}

function toggle_view_edit() {
  $(".view").toggle();
  $(".edit").toggle();
  redraw_multiple_selects();
}

function makeAjaxRequest(url,entityid,extra_fields,redraw) {
  $("#"+entityid).html('<img class="ticker" src="../images/ticker2.gif" alt="Updating field..." title="Updating field...">');
  jQuery.get(url,extra_fields,function(data) {
    $("#"+entityid).hide();
    $("#"+entityid).html(data);
    $("#"+entityid).fadeIn("fast");

    // We may need to redraw the select widgets
    if (redraw) {
      redraw_multiple_selects(entityid);
    }
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

function sidebyside_activate(id) {
  var arr = [];
  $.each($(".sidebyside"), function(k,v) {
    arr[arr.length] = v.id.replace("sbs_link_","");
  });

  if (!id) {
    id = get_alloc_var("side_by_side_link");
  }
  if (!id) {
    id = arr[0];
  }

  if (id == "sbsAll") {
    for (var i=0; i<arr.length; i++) {
      if (arr[i] != "sbsAll") {
        $("#"+arr[i]).show();
        $('#sbs_link_'+arr[i]).removeClass("sidebyside_active").addClass("sidebyside");
      }
    }
    $('#sbs_link_' + id).addClass("sidebyside_active");

  } else {
    for (var i=0; i<arr.length; i++) {
      if (arr[i] != "sbsAll") {
        $("#"+arr[i]).hide();
      }
      $('#sbs_link_' + arr[i]).removeClass("sidebyside_active").addClass("sidebyside");
    }
    $('#sbs_link_' + id).addClass("sidebyside_active");
    $("#"+id).show();
  }
}

function help_text_on(id, str) {
  $('#main').append("<div id='helper' class='corner' style='display:none'></div>");
  $('#helper').hide().html(str);
  
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
function preload_field(element, text) {
  $(document).ready(function() {
    $(element).bind("focus", function(e){
      if (this.value == text) {
        this.style.color = "#333333";
        this.value = "";
      }
    });
    $(element).each(function(){
      if (this.value == "") {
        this.style.color = "#bbbbbb";
        this.value = text;
      }
    });
    $('form').submit(function(){
      $(element).each(function(){
        if (this.value == text) {
          this.value = "";
        }
      });
    });
  });
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

  // Focus the login form fields
  if ($("#login_form").length) {
    if (!$("#username").val()) {
      $("#username").focus();
    } else {
      $("#password").focus();
    }
  }

  // Give the tables alternating stripes
  $(".list tr:nth-child(even)").addClass("even");
  $(".list tr:nth-child(odd)").addClass("odd");
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

  $('tr.clickrow').bind('click',function(e){                                                                                                     
    var id = this.id.split('_')[1]; // clickrow_43242
    if (id && !$(e.target).is('input:checkbox') && !$(e.target).is('a')) {
      $('#checkbox_'+id).attr('checked',!$('#checkbox_'+id).attr('checked'));
    }
  });

  // This loads up certain textboxes with faint help text that vanishes upon focus
  preload_field("#menu_form_needle", "Enter Search...");
  preload_field("input.datefield", "YYYY-MM-DD");

  // Add resize grippies to all textareas
  $('textarea:not(.processed)').TextAreaResizer();

  // Add toggles for filters
  $(".toggleFilter").click(function(){
    var d = $(".filter").css("display");
    if (d == "table") {
      var l = "Show Filter";
      var d = "none";
    } else {
      var l = "Hide Filter";
      var d = "table";
    }
    $(".filter").css("display",d);
    $(this).text(l);
    redraw_multiple_selects();
    return false;
  });

  // Activate user preference for displaying filters
  if (get_alloc_var("show_filters") != "no") {
    $(".toggleFilter").trigger("click");
  }


  $(".calendar_links").hide();
  $(".calendar_day").bind('mouseover',function(){
    $(".calendar_links").hide();
    $(this).find(".calendar_links").show();
  });
  $(".alloc_calendar").bind('mouseout',function() {
    $(".calendar_links").hide();
  });

  $('input.toggler').click(function(){
    return $('.task_checkboxes').each(function() {
      this.checked = !this.checked 
    });
  });

  // Activate side by side links/tabs, if any
  $(".sidebyside").click(function(e) {
    sidebyside_activate(e.target.id.replace("sbs_link_",""));
    return false;
  });
  sidebyside_activate();

  // unfortunately the multi-select-checkbox functionality doesn't    s.
  // render correctly in hidden container so first render the selects,s.
  // and then hide the .edit items.
  redraw_multiple_selects("");
  $(".edit").hide();
});


