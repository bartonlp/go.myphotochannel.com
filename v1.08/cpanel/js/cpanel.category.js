// Display (Category Duration)
jQuery(document).on("pagebeforeshow", "#category", function(e, data) {
  // Get slider value and update database

  $("#category button").click(function(e) {
    // The slider is inside the div with class ui-field-contain.
    var bc = $("li").css("background-color");
    $("li").css({backgroundColor: 'green'});
    setTimeout(function() {$("li").css({backgroundColor: bc});}, 2000);

    var fieldcontain = $("li div[data-role='fieldcontain']");
    var element = $("input, select", fieldcontain);
    
    element.each(function(i, v) {
      var el = $(v),
      name = el.attr("name"), // photo, announce ...
      item = el.attr("data-item"),
      value = el.val();
      if(item != "effect") {
        value *= 1000;
      } 

      // Now make the select up.
      var sql = "update categories set "+item+"='" +value+ "' where siteId='"+siteId+
              "' and category='"+name+"'";
    
      console.log("sql", sql);

      doSql(sql, function(data) {
        console.log("sql: ", data);
      });
    });
  });

  // Get appinfo data and set initial values of slider

  doSql("select * from categories where siteId='"+siteId+"'", function(data) {
    console.log(data);
    if(data.num) {
      var categories = {}; //{photo:{}, announce:{}, brand: {}, product:{}, info:{}};
      
      $.each(data.rows, function(i, d) {
        categories[d.category] = {duration: d.duration, transition: d.transition, effect: d.effect};
      });

      console.log(categories);

      //$("#appinfo #emailaddress").val(appinfo.photoNotifyEmail);

      $("#category input").each(function(i, v) {
        var inp = $(v),
        name = inp.attr("name"),
        item = inp.attr("data-item"),
        value = categories[name][item];
        inp.val(value/1000);
        inp.slider("refresh");
      });
      $("#category select").each(function(i, v) {
        var sel = $(v),
        name = sel.attr("name"),
        item = sel.attr("data-item"),
        value = categories[name][item];
        sel.val(value);
        sel.selectmenu("refresh");
      });

    } else console.log("num:", data.num);
  });
});