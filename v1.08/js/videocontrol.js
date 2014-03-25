// For videocontrol.php

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
  // Show select changed

  $("#selectstatus").change(function() {
    var status = $(this).val();
    $(".status").closest('div').show();
    $(".status").not("[data-item='"+status+"']").closest('div').hide();
  });

  $("video").on('loadedmetadata', function(e) {
    console.log(this.duration);
    var x = $(".reporteddur", $(this).parent());
    x.text("Reported Dur: "+Math.ceil(this.duration)*1000);
  });

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

  $("#adssubmit").on('click', function(e) {
      // For each viddiv get the dur and skip for the itemId

    $(".ads").each(function(i, v) {
      console.log("ads each:", i, v);

      var id = $(v).attr("id"),
      dur = $(".dur input", v).val(),
      desc = $(".desc input", v).val(),
      skip = $(".skip input", v).val(),
      status = $("select", v).val(),
      sql = "update ads set duration='"+dur+"', skip='"+skip+"', status='"+status+
            "', description='"+desc+"' where itemId='"+id+"'";

      console.log("sql:", sql);

      $.ajax({
        url: ajaxfile,
        data: { page: "doSql", sql: sql },
        type: "get",
        dataType: "json",
        async: false, // dont understand why but if not here then ajax does not fire.
        success: function(data) {
               console.log("success:", data);
             }, error: function(err) {
               console.log("error:", err);
             }
      });
    });

    $("body").append("<div id='posted'>Posted</div>");

    setTimeout(function() { $("#posted").remove(); }, 2000);
    reloadall();
    return false;
  });

  $("#itemssubmit").on('click', function(e) {
      // For each viddiv get the dur and skip for the itemId

    $(".items").each(function(i, v) {
      console.log("items each:", i, v);

      var id = $(v).attr("id"),
      dur = $(".dur input", v).val(),
      desc = $(".desc input", v).val(),
      skip = $(".skip input", v).val(),
      status = $("select", v).val(),
      sql = "update items set duration='"+dur+"', skip='"+skip+"', status='"+status+
            "', description='"+desc+"' where itemId='"+id+"'";

      console.log("sql:", sql);

      $.ajax({
        url: ajaxfile,
        data: { page: "doSql", sql: sql },
        type: "get",
        dataType: "json",
        async: false, // don't understand why but if not here then ajax does not fire.
        success: function(data) {
               console.log("success: ", data);
             }, error: function(err) {
               console.log("error:", err);
             }
      });
    });

    $("body").append("<div id='posted'>Posted</div>");

    setTimeout(function() { $("#posted").remove(); }, 2000);

    reloadall();
    return false;
  });
}

// DOM Ready

jQuery(document).ready(function($) {
  // Start out with only active showing
  reloadall();
});
