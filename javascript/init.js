
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
  pic6= new Image(9,9);
  pic6.src="../images/small_shrink.gif";
  pic7= new Image(9,9);
  pic7.src="../images/small_grow.gif";
}


// When the document has loaded...
$(document).ready(function() {
  // Give the tables alternating stripes
  $(".tasks tr:nth-child(even)").addClass("even");
  $(".tasks tr:nth-child(odd)").addClass("odd");
  $("div.message").corner();
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

});

