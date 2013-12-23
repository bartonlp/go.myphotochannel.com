// Show Settings

jQuery(document).on("pagebeforeshow", "#showsettings", function(e, data) {
  // Get slider value and update database

  $("#showsettings button").click(function(e) {
    // The slider is inside the div with class ui-field-contain.
    var bc = $("li").css("background-color");
    $("li").css({backgroundColor: 'green'});
    setTimeout(function() {$("li").css({backgroundColor: bc});}, 2000);

    var fieldcontain = $("li div[data-role='fieldcontain'] input"),
    setlist = '';
    setlist2 = '';
    
    fieldcontain.each(function(i, v) {
      var slider = $(v),
      name = slider.attr("name"),
      value = slider.val();

      switch(name) {
        case "allowAds":
          if(slider.prop("checked")) {
            setlist2 += name+"='"+value+"',";
          }
          return true;
        case "allowVideo":
          if(slider.prop("checked")) {
            setlist2 += name+"='"+value+"',";
          }
          return true;
        case "whenPhotoAged":
          value = value + " day";
          break;
      }

      setlist += name+"='"+value+"',";
    });

    // remove the last comma
    setlist = setlist.replace(/,$/, '');
    setlist2 = setlist2.replace(/,$/, '');
    
    // Now make the select up.
    var sql = "update appinfo set "+setlist+" where siteId='"+siteId+"'";
    doSql(sql, function(data) {
      console.log("sql: ", data);
    });

    sql = "update sites set "+setlist2+" where siteId='"+siteId+"'";
    doSql(sql, function(date) {
      console.log("sql:", date);
    });
  });

  // Get appinfo data and set initial values of slider

  doSql("select * from appinfo where siteId='"+siteId+"'", function(data) {
    if(data.num) {
      var appinfo = data.rows[0];
      console.log(appinfo);

      $("#showsettingscontent input[type='number']").each(function(i, v) {
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

  doSql("select allowAds, allowVideo from sites where siteId='"+siteId+"'", function(data) {
    if(data.num) {
      var sites = data.rows[0];
      console.log(sites);

      $("#showsettingscontent input[type='radio']").each(function(i, v) {
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
