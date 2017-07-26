// For videocontrol.php
// BLP 2014-07-16 -- New way of doing videos and submit.

var ajaxfile = "videocontrol.php";

function reload(which, callback) {
  $.ajax({
    url: ajaxfile,
    data: { page: 'reload', which: which },
    type: 'get',
    dataType: 'html',
    success: function(data) {
           console.log(which); //, data);
           $("#"+which).html(data);
           
           if(typeof callback == 'function')
             return callback();
         },
         error: function(err) {
           console.log("Error", which, err);
         }
  });
}

function reloadall() {
  reload('ads', function() {
    reload('items', function() {
      $(".status").not("[data-item='active']").closest('div').hide();
      doall(); // All the data must be loaded first
    });
  });
}

function doall() {
  // Setup so the current selectstatus is shown.

  var status = $("#selectstatus").val();
  $(".status").closest('div').show();
  $(".status").not("[data-item='"+status+"']").closest('div').hide();

  $(".dur").each(function(i, v) {
    var val =  $(this).attr("data-value");
    $(this).html("<label>Dur: \n"+
                 "<input type='text' value='"+
                 val+
                 "'/></label>\n"+
                 "<div class='durslider' data-value='"+val+"'></div>\n"
                );
  });

  $(".skip").each(function(i, v) {
    var val = $(this).attr("data-value");
    $(this).html("<label>Skip: \n"+
                 "<input type='text' value='"+
                 val+
                 "'/></label>\n"+
                 "<div class='skipslider' data-value='"+val+"'></div>\n"
                );
  });

  $(".durslider").slider({
    range: "max",
    min: 0,
    max: 200,
    slide: function(e, ui) {
             var p = $(this).parent(),
             inp = $("input", p);
             inp.val(ui.value*1000);
           }
  });

  $(".skipslider").slider({
    range: "max",
    min: 0,
    max: 20,
    slide: function(e, ui) {
             var p = $(this).parent(),
             inp = $("input", p);
             inp.val(ui.value);
           }
  });

  $(".durslider").each(function(i, v) {
    $(this).slider("option", "value", $(this).attr("data-value")/1000);
  });

  $(".skipslider").each(function(i, v) {
    $(this).slider("option", "value", $(this).attr("data-value"));
  });
}

// DOM Ready

jQuery(document).ready(function($) {
  $('body').append("<div id='show-youtube'>"+
                   "<iframe title='YouTube video player' width='700' height='500' "+
                   "webkitAllowFullScreen mozallowfullscreen allowFullScreen> "+
                   "</iframe><p></p></div>");

  $("body").append("<div id='show-video'>"+
                   "<video>"+
                   "Your browser does not support HTML5 video.</video>"+
                   "<p></p></div>");

  $("body").on('click', ".itemssubmit, .adssubmit", function(e) {
      // For each viddiv get the dur and skip for the itemId

    $("body").append("<div id='posted'>Posting</div>");

    var p = $(this).parent(); // This is the div that the button lives in
    var id = p.attr("id"),
    dur = $(".dur input", p).val(),
    desc = $(".desc input", p).val(),
    skip = $(".skip input", p).val(),
    status = $("select", p).val();

    if(this.className == "itemssubmit") {
      sql = "update items ";
    } else {
      sql = "update ads "
    }      
    sql += "set duration='"+dur+"', skip='"+skip+"', status='"+status+
           "', description='"+desc+"' where itemId='"+id+"'";
    
    console.log("sql:", sql);

    $.ajax({
      url: ajaxfile,
      data: { page: "doSql", sql: sql },
      type: "get",
      dataType: "json",
      async: false, // Do this sync so the Posting message stays up and nothing else can be done.
      success: function(data) {
             console.log("success: ", data);
           }, error: function(err) {
             console.log("error:", err);
           }
    });

    // Because the Ajax above is sync not async the POSTing stays up
    // until we are all done

    $("#posted").remove();

    reloadall();
    return false;
  });

  // A show button has been pressed. Make the video item

  $("body").on("click", ".divid", function(e) {
    var t = $(this);
    var src = t.attr('data-src');
    var type = t.attr('data-type');
    var mode = t.attr('data-mode');
    if(mode == 'html5') {
      $("#show-video video").attr({ type: type, src: src });
      $("#show-video").show();
    } else {
      $("#show-youtube iframe").attr({ src: "http://www.youtube.com/embed/" + src + "?controls=1" });
      $("#show-youtube p").text("Close");
      $("#show-youtube").show();
    }
  });
  
  // Set up the onchange event handler

  $("#selectstatus").change(function() {
    var status = $(this).val();
    $(".status").closest('div').show();
    $(".status").not("[data-item='"+status+"']").closest('div').hide();
  });

  // Start out with only active showing

  reloadall();

  $("video").on('loadedmetadata', function(e) {
    console.log(this.duration);
    var x = $('p', $(this).parent());
    x.text("Reported Durration: "+Math.ceil(this.duration)*1000+" ms");
  });

  $("video").on('canplay', function(e) {
    this.play();
  });
  
  $("video").on('ended', function(e) {
    $("#show-video").hide();
    $("#show-video p").text('');
  });

  $("#show-video, #show-youtube").on('click', function(e) {
    $(this).hide();
    $("#show-video p").text('');
  });

});
