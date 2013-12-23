  // Clicked on a thumbnail so popup an enlarged image

$("#photos").on("click", "img", function(e) {
  origimage = this;
  var image = $(this).clone();
  var id = image.attr("name");
  $("#popup").attr("name", id);
  $("#popup").show();
  $("#popup").html(image);
    //$("#pageselectctrl").hide();
  return false;
});

  // If we move the mouse out the enlarged image it just disapears with NO
  // control panel

$(document).on("mouseout", "#popup", function(e) {
  $("#popup").hide();
  $("#pageselectctrl").show();
  return false;
});

  // Close the enlarged image popup and load the cpanel for this image

$(document).on("click", "#popup", function(e) {
  var id = $("#popup").attr("name");
  $("#popup").hide();

  $.ajax({
    url: ajaxfile,
    data: {
           page: 'getItem', siteId: siteId,
           itemId: id
         },
         type: 'post',
         success: function(data) {
           data = "<tbody>"+data+"</tbody>";
           $("#itemsTable").html(data); // rewrite the whole table.
             //$("#itemsTable").trigger("create");
           $("#content").trigger("create");
             //$(".status").checkboxradio("refresh");
           $("#cpanel").show();
           $("#ctrlbuttons").show();
           $("body").scrollTop(0);
         }, error: function(err) {
           console.log(err);
         }
  });
  return false;
});

