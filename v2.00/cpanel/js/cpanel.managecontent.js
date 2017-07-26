// for cpanel.photoadmin.php

// For Next 100/Prev 100 at start/end popup

function startendpopup(msg) {
  $("#startend").html("<p>"+msg+"</p>");
  $("#startend").popup("open", {x: 200, y: 50});
  setTimeout(function() {
    $("#startend").popup("close");
  }, 3000);
}

// Get next 100 thumbnails

var rows;

function getThumbnails(cat, status) {
  i = 0; // Global i reset to zero when we get new cat/status.
  
  $.ajax({
    url: ajaxfile,
    data: { page: 'getThumbnails', siteId: siteId, category: cat, status: status },
    type: 'post',
    dataType: 'json',
    success: function(data) {
           if(data == null) {
             $("#photos").html("NO PHOTOS");
           } else {
             $("#photos").html(data[i]);
           }
           rows = data; // rows is global
         }, error: function(err) {
           console.log(err);
         }
  });
}

function fillControlBox(data) {
  data = "<tbody>"+data+"</tbody>";
  $("#cpanel").html("<form>\n"+
                    "<div id='ctrlbuttons' data-role='controlgroup' data-type='horizontal'>\n"+
                    "<button id='submit' data-mini='true'>Post</button>\n"+
                    "<button id='donotsubmit' data-mini='true'>Do Not Post</button>\n"+
                    "</div><!--/ctrbuttons-->\n"+
                    "<table id='itemsTable'><!-- getItem goes here --></table>\n"+
                    "</form>");

  $("#itemsTable").html(data); // rewrite the whole table.

  $("#content").trigger("create");
  $("#cpanel").show();
  $("body,html,document").scrollTop(0);
}

jQuery(document).on("pagebeforeshow", "#photoadminpanel", function(e, data) {
  var i=0; // rows counter for 100 images per row.
  var cat='photo', status='active';
  var origimage;

  // Get the initial set of photos
  
  getThumbnails('photo', 'active');

  // pageselectctrl has the 'Select Category' and 'Select Status'
  // buttons.
  
  $("#pageselectctrl").on("change", "select", function(e) {
    var t = $(this);
    var id = t.attr("id");
    switch(id) {
      case "catselect":  // Select Category
        cat = t.val();
        break;
      case "statusselect":  // Select Status
        status = t.val();
        break;
    }
    if(cat == "" || status == "") return;
    getThumbnails(cat, status);
  });
  
  // Get the next 100 photos
  
  $("#next100").click(function(e) {
    if((i+1) >= rows.length) {
      startendpopup("At End of Pictures");
      return;
    }
    $("#photos").html(rows[++i]);
  });

  // Get the previous 100 photos
  
  $("#prev100").click(function(e) {
    if((i) < 1) {
      startendpopup("At Start of Pictures");
      return;
    }
    $("#photos").html(rows[--i]);
  });

  // If an htmlitem is selected. The HTML items do not popup an
  // enlarged image they go directly to the control panel.
  
  $("#photos").on("click", ".htmlitem", function(e) {
    origimage = this;
    var id = $(this).attr("name");
    //$("#pageselectctrl").hide();
    
    $.ajax({
      url: ajaxfile,
      data: {
             page: 'getItem', siteId: siteId,
             itemId: id
           },
           type: 'post',
           success: fillControlBox,
           error: function(err) {
             console.log(err);
           }
    });
    return false;
  });

  // Clicked on a thumbnail so pop up the control box.
  
  $("#photos").on("click", "img", function(e) {
    if(e.ctrlKey) {
      $("#donotsubmit").trigger("click");
      
      origimage = this;
      var image = $(this).clone();
      var id = image.attr("name");
      $("#popup").attr("name", id);
      $("#popup").show();
      $("#popup").html(image);
      return false;
    }

    $("#popup").hide();
        
    origimage = this;
    var image = $(this).clone();
    var id = image.attr("name");

    $.ajax({
      url: ajaxfile,
      data: {
             page: 'getItem', siteId: siteId,
             itemId: id
           },
           type: 'post',
           success: fillControlBox,
           error: function(err) {
             console.log(err);
           }
    });
    return false;
  });

  // Close popup on click

  $("#popup").on("click", function(e) {
    $(this).hide();
  });      
  
  // Post Items Frame
  // Gather all the objects in the frame and post to the items table.
  // 'self' is the this of the sender who is a button.

  $("#cpanel").on("click", "#submit", function(e) {
    // Get the parents of the button.
    // The parent we are interested in is the table the button lives in (the 'frame' table).

    var table = $(".frame");

    // Get the elements we need:

    var id, touch, status, dur, desc, cat;

    id = table.find("thead img").attr("data-id");
    if(typeof id == 'undefined') {
      id = table.find("thead div").attr("data-id");
    }

    touch = table.find("input[name='touch']:checked").length != 0 ? 'yes' : 'no';
    cat = table.find("select").val();
    status = table.find(".status:checked").val();
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
             // Done so remove the panel
             $("#cpanel").hide();
             $("#itemsTable").empty();

             // refresh the full group of thumbnails
             
             cat = $("#catselect").val();
             status = $("#statusselect").val();
             
             getThumbnails(cat, status);
           }, error: function(err) {
             console.log(err);
           }
    });
    return false;
  });

  // Don't Post button
  
  $("#cpanel").on("click", "#donotsubmit", function(e) {
    $("#cpanel").hide();
    $("#itemsTable").empty();
    return false;
  });

  // Click the text area for type html.
  // This lets us edit the contents in a textarea.

  $("#cpanel").on("click", ".typehtml", function(e) {
    var textarea = $("<div id='textareadiv'><textarea rows='10' cols='100'>"+
                     $(this).html()+
                     "</textarea><br><button id='textareaOK'>Submit</button></div>");
    $("body").append(textarea);
    return false;
  });

  // Click submit for textarea above

  $(document).on("click", "#textareaOK", function(e) {
    var text = $("#textareadiv textarea").val();
    $(".typehtml").html(text);
    var id = $(".typehtml").attr("data-id");
    doSql("update items set location='"+text+"' where itemId="+id, function(data) {
      console.log(data);
      // Update the original thumbnail
      $(origimage).text(text);
    });
    $("#textareadiv").remove();
    return false;
  });
  
  // Click the image to rotate it

  $("#cpanel").on("click", "img", function(e) {
    var x = $(this);
    var imagename = x.prop('src');
    var id = x.attr('data-id');

    $.ajax({
      url: ajaxfile,
      data: { page: 'rotate', image: imagename, itemId: id },
      type: 'post',
      dataType: 'text',
      success: function(data) {
             console.log(data);
             var image = new Image;
             image.width = 550; // must match value in php file!
             $(image).attr('data-id', id);
             x.parent().prev().prev().find(".desc").text(data);
             image.src = SITE_DOMAIN + data;
             origimage.src = SITE_DOMAIN + data;

             $(image).load(function() {
               var y = x.parent();
               y.html(this);
             });
           }
    });
    return false;
  });
});
