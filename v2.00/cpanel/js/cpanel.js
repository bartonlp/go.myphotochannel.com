// Main JavaScript file for cpanel.php the main menue
// This JavaScript file is loaded in the <head> so it is available to
// All sub-menus

var version = " (" + lastMod +")";
// July 3 made changes to submit in commercial section.
// July 13 cpanel.tv/cpanel.js fix width

// The Main Ajax file

var ajaxfile = "cpanel.ajax.php";
var startupId = null;

// Insert into the startup table and start Pusher
// Returns the lastid from the insert.

$.ajax({
  url: ajaxfile,
  data: { page: 'startup', siteId: siteId, unit: 'cpanel id='+userId, version: version },
  dataType: 'json',
  type: 'get',
  success: function(data) {
         console.log("startup", data);
         startupId = data.id;
       },
       error: function(err) {
         console.log("startup error:", err);
       }
});

// Update startup table and register 'unload' with Pusher
// Returns the sql statement

$(window).bind("unload", function(e) {
  $.ajax({
    url: ajaxfile,
    data: {page: 'unload', siteId: siteId, unit: 'cpanel id='+userId, startupId: startupId},
    type: "get",
    async: false
  });
});

// Update startup table register 'startup-update' with Pusher
// Returns sql statement

function sendstartupupdate() {
  $.ajax({
    url: ajaxfile,
    data: {page: 'startup-update', siteId: siteId, unit: 'cpanel id='+userId, startupId: startupId},
    type: "get",
    success: function(data) {
           console.log("startup-update", data);
           setTimeout(sendstartupupdate, 60000);
         },
         error: function(err) {
           console.log("startup-update error", err);
         }
  });
}

var newDeleteTimeout;

// This is for the delete and approve items on the main page.
// When we hide the page we stop doing 'checkNewDelete()'

jQuery(document).on("pagehide", "#home", function(e, data) {
  console.log("PAGEHIDE", e, data);
    
  clearTimeout(newDeleteTimeout);
  newDeleteTimeout = null;
});

// ***********************
// Get/Set stuff from/in database
// The Ajax simply executes a sql statement and if a 'select' returns
// rows.

function doSql(sql, callback) {
  // use Ajax to call the ajaxfile with page=doSql

  $.ajax({
    url: ajaxfile,
    data: { page: 'doSql', sql: sql },
    dataType: 'json',
    type: 'post',
    success: function(data) {
           if(typeof callback == 'function') return callback(data);
           console.log("NO Callback", data)
         },
         error: function(err) {
           ajaxError(err);
         }
  });
}

function ajaxError(err) {
  console.log(err.responseText);
}

// **********************
// Create an Announcement
// Given a text message create an image.

function createImage(message) {
  var c = document.createElement('canvas');
  var y = 50, x = 400;
  var inc = 45;
  var linelen = 55;
  var lines = message.split(String.fromCharCode(10));

  var line = '', tlines = new Array;

  // Build the lines array with we have done word wrapping
  
  var n = lines.length;
  
  for(var i=0; i < n; ++i) {
    var words = lines[i].split(" ");
    line = words[0];
    for(var j=1; j < words.length; ++j) {
      if((((line.length + words[j].length +1) > linelen))) {
        tlines.push(line)
        line = '';
        --j;
      } else {
        line += " " + words[j];
      }
    }
    if(line.length) {
      tlines.push(line);
    }
  }

  var llen = 0, l;
  
  for(l in tlines) {
    if(tlines[l].length > llen) llen = tlines[l].length;
  }
  
  lines = tlines;

  var n = lines.length;
  var h = n * inc + y;

  console.log("lines, lines.length, h", lines, lines.length, h);

  // NOTE the width and height must be set before we get the context!!!

  //c.width=800;
  c.width = llen * 20;
  x = c.width / 2;
  
  c.height = h;
  
  var ctx=c.getContext("2d");
  ctx.font="30px Arial";
  ctx.textAlign = 'center';
  ctx.fillStyle = "#000000";
  ctx.fillRect(0, 0, c.width, c.height);

  ctx.fillStyle = "white";

  // Output the lines
  
  for(var i=0; i < lines.length; ++i) {
    ctx.fillText(lines[i], x, y);
    y += inc;
  }
  
  dataUri = c.toDataURL(); // get the base64 URI

  return dataUri;
}

// Check the 'Approve Photos' and the 'Remove Photos' links.

function checkNewDelete() {
  // Check for Deletes
  
  var sql = "select itemId from items where status='delete' and siteId='"+siteId+"'";
  doSql(sql, function(data) {
    console.log("checkNewDelete: delete", data);
    // CheckForDelete callback
    // returns an object {num: n, rows: rows:}

    if(data.num) {
      $("#deletephotos").removeClass("ui-screen-hidden");
      $("#numtodelete").text(" "+data.num+" ");
    } else {
      $("#deletephotos").addClass("ui-screen-hidden");
    }

    // Check for Approve
    
    var sql = "select itemId from items where status='new' and siteId='"+siteId+"'";
    doSql(sql, function(data) {
      console.log("checkNewDelete: new", data);
      // CheckForApprove callback 
      // returns an object {num: n, rows: rows:}
          
      if(data.num) {
        $("#approvephotos").removeClass("ui-screen-hidden");
        $("#numtoapprove").text(" "+data.num+" ");
      } else {
        $("#approvephotos").addClass("ui-screen-hidden");
      }
      $("#homelist").listview('refresh');
    });
  });
  
  // Look again in 60 seconds

  newDeleteTimeout = setTimeout(checkNewDelete, 60000);
}

// *******************************************************************

jQuery(document).on("pagebeforeshow", "#home", function(e, data) {
  // When we first arrive start checkNewDelete()
  console.log("HOME", e, data);
  clearTimeout(newDeleteTimeout);
  newDeleteTimeout = null;
  checkNewDelete();
});

// Do this on every page

jQuery(document).bind('pageinit', function() {
  // Show which site we are

  setTimeout(sendstartupupdate, 60000); // after on minute.
  
  var banner = $("div[data-role='header'] h1 span");
  banner.html(" for "+siteId);

  $(".curtime").text(version);
});
