// Expunge Photos
jQuery(document).on("pagebeforeshow", "#expungephotos", function(e, data) {
  $("#expungeallnone").on("click", "button", function(e) {
    var id = $(this).attr("id"), cl, state;

    switch(id) {
      case "expungeall":
        state = true;
        break;
      case "expungeclear":
        state = false;
        break;
    }
    $("input[type='checkbox']").prop("checked", state);
    $("input").checkboxradio("refresh");
  });
  
  $("#expungecontent").on("click", "#expungeOK", function(e) {
    // loop through the tdapproveinput <td> and collect the radio
    // button info. The radio button values are 'yes' and 'no'

    $("input:checked").each(function(i, v) {
      var sql;
      // The values are "approved-$id" so we want what is after the -,
      // the image Id.

      var id = v.name;

      $.ajax({
        url: ajaxfile,
        data: { page: 'expunge', itemId: id, siteId: siteId }, 
        type: "post",
        success: function(data) {
               console.log(data);
               // Get the containing row <tr> and remove it.

               $(v).parents("tr").remove();

               $("#expungePostedOK").popup("open", {x: 200, y: 200});
               setTimeout(function() {
                 $("#expungePostedOK").popup("close");
               }, 3000);

               // Now see if we have removed all of the rows of the
               // table. If so Go home.

               if($("tr").length == 0) {
                 //checkForDelete();
                 $.mobile.changePage("cpanel.php?siteId="+siteId);  
               }
             }, error: function(err) {
               ajaxError(err);
             }
      });
    });
    console.log("Done");
  });

  // Fill up the page with the possibles
  
  doSql("select * from items where status='delete' and siteId='"+siteId+"'", function(data) {
    if(data.num == 0) return;
    // data has all the rows 0-n
    var tbl = '';
    $.each(data.rows, function(i, v) {
      var itemId = v['itemId'];
      var loc = v['location'];

      tbl += "<tr><td><img style='max-width: 400px' src='"+SITE_DOMAIN + loc+"'><br>\n"+
             "<div data-role='controlgroup' data-type='horizontal'>\n"+
             "<label for='"+itemId+"'>Remove</lable>\n"+
             "<input id='"+itemId+"' class='removercheckbox' type='checkbox' name='"+itemId+"'>\n"+
             "</div>\n"+
             "</td></tr>\n";
    });

    $("#expungephotoshere").html("<table border='1'>"+tbl+"</table>");
    $("#expungephotoshere").trigger("create");
  });
      
});
