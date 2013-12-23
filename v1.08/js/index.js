// Javascript for index.php

var showclosed = false, positionTimeout;

// Get the startup table

function getTable(callback) {
  $.ajax({
    url: 'index.php',
    type: 'get',
    dataType: 'html',
    data: { name: 'gettable' },
    success: function(data) {
           console.log(data);
           $("#startup-table").html(data);
           if(showclosed === false) {
             if($(".status:contains('closed')").parent().hide().length) {
               $("#startup-table button").html("Show All");
             }
           } else {
             if($(".status:contains('closed')").parent().show().length) {
               $("#startup-table button").html("Show Only Open");
             }
           }
           if(typeof callback === 'function') return callback();
         }
  });
}

// Every 30 seconds load the log files via Ajax and reposition the 'Clear Log' buttons.

function positionClearlog() {
  // class clearlog does not exist if this is not superuser.
  
  if($(".clearlog").length == 0)
    return;
  
  $("[href*='emailphoto.log']").siblings(".size").load("index.php",
    {page: 'filesize', file: '/emailphoto.log'}, function(data) {
    console.log("emailphoto.log", data);
    $("[href*='/database.log']").siblings(".size").load("index.php",
      {page: 'filesize', file: '/database.log'}, function(data) {
      console.log("database.log", data);  
      var x = $(".clearlog"); // there are two elements
      var y = $(x[0]).position().left;
      $(x[1]).css({position:'absolute', left: y+"px"});
    });
  });
  positionTimeout = setTimeout(positionClearlog, 300000); // Every five minutes update
}

// READY

jQuery(document).ready(function($) {
  // Get the startup table and hide any closed sites.
  
  getTable();

  // Clear the logfiles.
  // Position the two 'Clear Log' buttons horizontally over each other
  // Get the left postion of the first button and assign it to the second button

  positionClearlog();

  // Setup Pusher

  Pusher.log = function(message) {
    if (window.console && window.console.log) {
      window.console.log(message);
    }
  };

  // Our key
  var key = '2aa0c68479472ef92d2a';
  var pusher = new Pusher(key);
  var slideshow = pusher.subscribe('slideshow');

  // When a site starts
  
  slideshow.bind('startup', function(data) {
    console.log('startup', data);
    getTable();
  });

  // When a site updates, this happens every slowCall time.
  
  slideshow.bind('startup-update', function(data) {
    console.log('startup-update', data);
    getTable();
  });

  // When a site shuts down.
  
  slideshow.bind('unload', function(data) {
    console.log('unload', data);
    getTable()
  });

  // Add the select for old/current/working version of linkversion

  $("#linkversion").before("<select id='versiontype'>"+
                           "<option value='current'>Current Version</option>"+
                           "<option value='working'>Working Version</option>"+
                           "<option value='old'>Old Version</option>"+
                           "</select>");

  // When the select changes get the new linkversion
  
  $("#versiontype").change(function(e) {
    var $type = $(this).val();
    $.ajax({
      url: "index.php",
      data: {page: 'getlink', linkversion: $type },
      success: function(data) {
             console.log(data);
             $("#linkversion").html(data);
           },
           error: function(err) {
             console.log(err);
           }
    });
  });
  
  // When we click on one of the items in the #links section we need to check if the user is a
  // super user. If so add the search query 'debug=' and the super user number. Open a new tab for
  // the program.

  $("#links").on("click", "a", function(e) {
    var s = $("#superuser").val();
    if(!s) s = 'true';
    var cache = '';
    if($("#cache").prop("checked")) {
      cache = "&cache=true";
    }
    window.open($(this).attr("href")+"?debug="+s+cache);
    return false;
  });

  // On clicking either of the clear log buttons we want to call the Ajax that clears the log file
  // and then put up a 'posted' message for 2 seconds.

  $(".clearlog").on("click", function(e) {
    var file = $(this).attr("data-logname");
    $.ajax({
      url: 'index.php',
      data: {page: 'clearlog', logfile: file},
      success: function(data) {
             console.log(data);
             $("body").append("<div id='posted'>Posted</div>");
             setTimeout(function() { $("#posted").remove(); }, 2000);

             clearTimeout(positionTimeout); // Clear any pending timeout before calling positionClearlog

             if(file != 'database.log') {
               $("[href*='emailphoto.log']").siblings(".size").load("index.php",
                 {page: 'filesize', file: file},
                 positionClearlog());
             } else {
               $("[href*='database.log']").siblings(".size").load("index.php",
                 {page: 'filesize', file: file},
                 positionClearlog());
             }
           },
           error: function(err) {
             console.log(err);
           }
    });
    return false;
  });

  // When we click on the anchor for the log files we need to make sure we don't get the old cached
  // version again. Add the time as a search query.

  $(".showlog").on("click", function(e) {
    var file = $(this)[0];
    var d = new Date();
    file.search = "?t=" + d.getTime();
    // Don't let this propogate up to the #link click we don't want to add 'debug='

    e.stopPropagation();
    return true;
  });

  $(".demonodebug").click(function() {
    var d = new Date().getTime();
    window.open($(this).attr("href")+d);
    return false;
  });

  // The Show/Hide button for the startup table. 'Show All' shows
  // closed sites as well as opened.
  
  $("#startup-table").on("click", "button", function() {
    if(showclosed) {
      // hide
      $(".status:contains('closed')").parent().hide();
      $("#startup-table button").html("Show All");
      showclosed = false;
    } else {
      $(".status:contains('closed')").parent().show();
      $("#startup-table button").html("Show Open Only");
      showclosed = true;
    }
  });

  $("#normaluser a").click(function(e) {
    window.open($(this).attr("href")+"&userId="+userId);
    return false;
  });

  $("#cutting-cpanel table").hide();
  
  $("#cutting-cpanel").on("click", "button", function(e) {
    if(!this.flag) {
      $("#cutting-cpanel table").show();
      $(this).text("Hide Table");
    } else {
      $("#cutting-cpanel table").hide();
      $(this).text("Show Rename Table");
    }
    this.flag = !this.flag;
  });
});


