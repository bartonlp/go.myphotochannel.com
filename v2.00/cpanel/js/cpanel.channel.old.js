jQuery(document).on("pagebeforeshow", "#channel", function(e, data) {
  // Get slider value and update database

  $("#channel button").click(function(e) {
    // The slider is inside the div with class ui-field-contain.
    var fieldcontain = $(this).parents(".ui-field-contain");
    var slider = fieldcontain.find("input");
    var name = slider.attr("name");
    var value = slider.val();

    if(name == "whenPhotoAged") {
      value = value + " day";
    }
    
    // Now make the select up.
    var sql = "update appinfo set " +name+ "='" +value+ "' where siteId='"+siteId+"'";
    console.log("sql", sql);

    var bc = fieldcontain.css("background-color");
    fieldcontain.css({backgroundColor: 'green'});
    
    doSql(sql, function(data) {
      setTimeout(function() {fieldcontain.css({backgroundColor: bc});}, 1000);
    });
  });
  
  // Get appinfo data and set initial values of slider

  doSql("select * from appinfo where siteId='"+siteId+"'", function(data) {
    console.log(data);
    if(data.num) {
      var appinfo = data.rows[0];
      console.log(appinfo);

      $("#channel input").each(function(i, v) {
        var inp = $(v);
        var name = inp.attr("name");
        var value = appinfo[name];
        if(name == "whenPhotoAged") {
          // format: 'n day' where n can be n, nn, nnn
          var value = value.match(/(\d+) day/)[1];
        }
        inp.val(value);
        inp.slider("refresh");
      });

    } else console.log("num:", data.num);
  });

});
