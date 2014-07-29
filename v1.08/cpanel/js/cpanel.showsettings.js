// Show Settings
// BLP 2014-07-23 -- add allowIFTTT

jQuery(document).on("pagebeforeshow", "#showsettings", function(e, data) {
  // Get slider value and update database

  $("#showsettings button").click(function(e) {
    // The slider is inside the div with class ui-field-contain.
    var bc = $("li").css("background-color");
    $("li").css({backgroundColor: 'green'});
    setTimeout(function() {$("li").css({backgroundColor: bc});}, 2000);

    var fieldcontain = $("li div[data-role='fieldcontain'] input.appinfo"),
    setlist = '';
    //setlist2 = '';

    var ext = $("#featureext1").prop("checked");
    if(ext) {
      var limit = $("#extlimit").val(),
      more = $("#extmorerecent").val(),
      less = $("#extlessrecent").val(),
      type = $("#exttype1").prop("checked") ? 'rand' : 'chron',
      ext = type+","+more+","+limit+","+less;
    } else {
      ext = 'no';
    }
    setlist = "featureExt='"+ext+"',";

    setlist += "allowAds=" + ($("#allowads1").prop("checked") ? "'yes'," : "'no',");
    setlist += "allowVideo=" + ($("#allowvid1").prop("checked") ? "'yes'," : "'no',");

    // BLP 2014-07-23 -- allowIFTTT
    setlist += "allowIFTTT=" + ($("#allowifttt1").prop("checked") ? "'yes'," : "'no',");
    
    fieldcontain.each(function(i, v) {
      var slider = $(v),
      name = slider.attr("name"),
      value = slider.val();

      switch(name) {
        case "whenPhotoAged":
          value = value + " day";
          break;
      }

      setlist += name+"='"+value+"',";
    });

    // remove the last comma
    setlist = setlist.replace(/,$/, '');
    //setlist2 = setlist2.replace(/,$/, '');
    
    // Now make the select up.
    var sql = "update appinfo set "+setlist+" where siteId='"+siteId+"'";
    doSql(sql, function(data) {
      console.log("sql: ", data);
    });
/*
    sql = "update sites set "+setlist2+" where siteId='"+siteId+"'";
    doSql(sql, function(date) {
      console.log("sql:", date);
    });
*/    
  });
    
  // Get appinfo data and set initial values of slider

  doSql("select * from appinfo where siteId='"+siteId+"'", function(data) {
    if(data.num) {
      var appinfo = data.rows[0];
      console.log(appinfo);

      $("input[type='number'].appinfo").each(function(i, v) {
        var inp = $(v);
        var name = inp.attr("name");
        var value = appinfo[name];
        
        if(name == "whenPhotoAged") {
          // format: 'n day' where n can be n, nn, nnn
          var value = value.match(/(\d+) day/)[1];
        }
       
        console.log(name, value);
        inp.val(value);
        inp.slider("refresh");
      });

    } else console.log("num:", data.num);
  });

  // if we click on either of the yes/no input items

  $("#featureext input").on("click", function(e) {
    if(this.id == "featureext1") {
      $("#featureExtYes").show();
    } else {
      $("#featureExtYes").hide();
    }
  });
  
  $("#featureExtYes").hide();
  
  doSql("select allowAds, allowVideo, allowIFTTT, featureExt from appinfo "+
        "where siteId='"+siteId+"'", function(data) {
    if(data.num) {
      var sites = data.rows[0];
      console.log(sites);
      
      var ar = sites['featureExt'].split(","); // ar[0]:type, ar[1]:more, ar[2]:limit, ar[3]:less
      switch(ar[0]) {
        case 'rand':
        case 'chron':
          $("#featureExtYes").show();
          $("#featureext1").attr("checked", true).checkboxradio("refresh");
          $("#extlimit").val(ar[2]).slider("refresh");
          $("#extmorerecent").val(ar[1]).slider("refresh");
          $("#extlessrecent").val(ar[3]).slider("refresh");
          // Keep going
        case 'rand':
          $("#exttype1").attr("checked", true).checkboxradio("refresh");
          break;
        case 'chron':
          $("#exttype2").attr("checked", true).checkboxradio("refresh");
          break;
        case 'no':
        default:
          $("#featureext2").attr("checked",true).checkboxradio("refresh");
          $("#featureExtYes").hide();
          break;
      }

      $("input[type='radio'].allows").each(function(i, v) {
        var inp = $(v);
        var name = inp.attr("name");
        var value = sites[name];

        if(inp.val() == value) {
          inp.attr("checked", true).checkboxradio("refresh");
        }
      });
    } else console.log("num:", data.num);
  });

});
