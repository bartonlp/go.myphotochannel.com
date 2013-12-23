// This is NOT the cpanel version this is the stand alone PC version!!!

// Updated here and cpanel.ajax.php May 15, 2013
// JavaScript for 'PC-cpanel.php'

var heightextra = 30, widthextra = 10;

$(window).resize(function() {
  var cssw = ".ui-content {\n"+
              "max-width: " +(window.innerWidth - widthextra)+ "px;\n" +
              "}\n";

  $("#cssw").html(cssw);
});

// Initialize the images and rotate images when clicked.

function imageClick() {
  // Image clicked so rotate image 90 degrees counter clockwise.
  
  $("table").on("click", "img", function() {
    var x = $(this);
    var imagename = x.prop('src');
    var id = x.attr('data-id');

    $.ajax({
      url: ajaxfile,
      data: {page: 'rotate', image: imagename, itemId: id, siteId: siteId },
      dataType: 'text',
      type: 'post',
      success: function(data) {
             console.log(data);
             var image = new Image;
             image.width = 550; // must match value in php file!
             $(image).attr('data-id', id);
             x.parent().prev().prev().find(".desc").text(data);
             image.src = SITE_DOMAIN + data;

             $(image).load(function() {
               var y = x.parent();
               y.html(this);
             });
           }
    });
  });
  
  // The frame class is the table inside of each row of the itemsTable.
  // It has the information under the image.
  // If admin mades changes to the information about the image put a submit button in the
  // 'frame' table.

  // There are checkboxes, radio buttons, and text/number boxes in the
  // 'frame' area. For the text/number inputs we want to check for any
  // change to the field so we use 'keyup'.

  $(".frame input[type='text']").on('keyup', function(e) {
    $(".frame").trigger('change'); // trigger below
    e.stopPropagation();
  });

  // This catches the checkboxes and radio buttons. It is also fired
  // when we leave a text/number input but who cares.

  $(".frame").on('change', function(e) {
    // Check to see if we already have a postitems butten set showing.

    if($(this).has(".postitems").length == 0) {
      // No buttons yet so show the postitems set.

      $(this).append("<span><button class='postitems' type='button'>Post Changes to Table</button>" +
                     "<button class='donotpost'>Do Not Post</button></span>");

      $("span button", this).buttonMarkup("refresh");

      // Also if there is no postall button show one.
      // The div2 div is fixed position upper right.

      if($("#div2").has(".postall").length == 0) {
        $("#div2").append("<button class='postall'>Post All</button><br>");
        $(".postall").buttonMarkup("refresh");
      }

      // On click for the postall button

      $(".postall").on("click", function(e) {
        // Find every item that needs to be updated

        $(".postitems").each(function() {
          postItemsFrame(e, this);
        });
        $(".postall").remove(); // when done remove the button
      });

      // On click for the 'Do Not Post' button next to the 'Post
      // Changes to Table' button.

      $(".donotpost").click(function(e) {
        var y = $(this).parents();

        var span = $(y[0]);
        var table = $(y[1]);

        span.remove(); // remove the buttons for posting and not posting

        // Get the elements we need:

        var id = table.find("thead img").attr("data-id");

        // Get the original content of this frame and redraw it.

        $.ajax({
          url: ajaxfile,
          data: { page: 'getItem', itemId: id, siteId: siteId },
          dataType: 'html',
          type: 'post',
          username: 'barton',
          password: 'bartonl411',
          success: function(data) {
                 console.log("frame", data);
                 table.html(data);
                 table.trigger("create");

                 // If there are no postitems buttons then remove the postall
                 // button

                 if($(".postitems").length == 0) {
                   $(".postall").remove();
                 }
               }
        });
      });

      // On click for one items post button.
      $(".postitems").click(function() {
        postItemsFrame(e, this);

        if($(".postitems").length == 0) {
          $(".postall").remove(); // If no postitems left remove the postall button
        }
      });
    }
    e.stopPropagation();
    return false;
  });
}

// Post Items Frame
// Gather all the objects in the frame and post to the items table.
// 'self' is the this of the sender who is a button.

function postItemsFrame(e, self) {
  // Get the parents of the button.
  // The parents we are interested in are 1) the span the button lives
  // in. 2) the table the button lives in (the 'frame' table).

  var y = $(self).parents();

  var span = $(y[0]);
  var table = $(y[1]);

  span.remove(); // remove the buttons for posting and not posting now that we are posting.

  // Get the elements we need:

  var id, touch, status, dur, desc, cat;

  id = table.find("thead img").attr("data-id");
  if(typeof id == 'undefined') {
    id = table.find("thead div").attr("data-id");
  }
  
  touch = table.find("input[name='touch']:checked").length != 0 ? 'yes' : 'no';
  cat = table.find("select").val();
  status = table.find(".status:checked").val(); //attr("name").replace(/:.*/, '');
  dur = table.find("input[name='dur']").val();
  desc = table.find("input[name='desc']").val();

  $.ajax({
    url: ajaxfile,
    data: {
           page: 'itemsUpdate',
           id: id,
           touch: touch,
           cat: cat,
           status: status,
           dur: dur * 1000,
           desc: desc,
           siteId: siteId
         },
         type: 'post',
         success: function(data) {
           console.log(data);
           var x = table.find("input[name='touch']:checked").prop("checked", false);
           x.checkboxradio("refresh");
         }, error: function(err) {
           console.log(err);
         }
  });
  return false;
}

// Refresh Images

function refreshImages(e, limit, startId) {
  // Which of the select options is active? Class category is the
  // select button.

  var cat = $($(".category").find(":selected")[0]).text();
  var status = $($(".showstatus").find(":selected")[0]).text();
  
  if(cat == 'All') cat = '';
  if(status == 'All') status = '';
  
    // Cat now has one of the categories.
    // Contact the server and refresh the table information

  $.ajax({
    url: ajaxfile,
    data: {
           page: 'getItems', siteId: siteId,
           category: cat, status: status,
           limit: limit, startId: startId
         },
    type: 'post',
    success: function(data) {
           $("#itemsTableDiv").html(data); // rewrite the whole table.
           //console.log("itemsTable loaded", data);
           // The whole itemsTable has changed so update all of the event
           // handlers.
           imageClick();
           $("#adminpanel").trigger("create");
         }, error: function(err) {
           console.log(err);
         }
  });

  if(e) e.stopPropagation();
  return false;
}

// **********************
// Once the DOM is loaded

jQuery(document).bind('pageinit', function() {
  var styles = "<style>\n"+
               "body {width: 100%;}\n"+
               "img {width: 100%;}\n"+
               "#itemsTable {width: 100%;}\n"+
               "table.frame {width: 100%; border: none;}\n"+
               "td {padding: 5px;}\n"+
               "table.statustbl td {width: 100%; border: none;}\n"+
               ".ui-btn.postitems .ui-btn-inner {\n"+
               "background-color: green;color: white;}\n" +
               ".postall .ui-btn-inner {\n" +
               "background-color: green;color: white;}\n" +
               "</style>";

  $("head").append(styles);
  var cssw = "<style id='cssw'>\n"+
              ".ui-content {max-width: " + (window.innerWidth - widthextra)+ "px;\n" +
              "</style>";
  
  $("head").append(cssw);

  imageClick(); // Init the buttons etc.

  // Load the initial table into the page

  refreshImages(null, 20);
          
  // Get Next Set
  
  $("#itemsTableDiv").on('click', "#getNextSet", function(e) {
    var num = $(this).val();
    refreshImages(e, 20, num);
    return false;
  });

  // Show Images of a specified Status, or all.

  $(".showstatus").change(function(e) {
    refreshImages(e, 20)
  });
  
  // Reload the items table on a change of category above

  $(".category").change(function(e) {
    refreshImages(e, 20)
  });    
});
