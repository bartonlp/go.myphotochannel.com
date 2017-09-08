// Commercial
jQuery(document).on("pagebeforeshow", "#commbreak", function(e, data) {
  // Get slider value and update database

  $("#commbreak button").click(function(e) {
    // The slider is inside the div with class ui-field-contain.
    var opt = $("#segselect");
    var seg = opt.val();

    $("#catlist input").each(function(i, v) {
      var cat = $(v).attr("name");
      var value = $(v).val();
      // category is what I selected so name=category
      // value = slide amount
      // the seg is the csX from the select box
    
      var sql = "update segments set " +seg+ "='" +value+ "' where siteId='"+siteId+
                "' and category='"+cat+"'";
    
      console.log("sql", sql);

      doSql(sql, function(data) {
        console.log(data);
      });
    });
    var bc = $("#commbreakcontent").css("background-color");
    $("#commbreakcontent").css({backgroundColor: 'green'});
    setTimeout(function() {$("#commbreakcontent").css({backgroundColor: bc});}, 1000);
    return false;
  });

  // Select Box for category selection

  $("#segselect").on("change", function(e) {
    var opt = $(this[this.selectedIndex]);
    var seg = opt.val();
    console.log("seg:", seg);
    
    // seg is "cs1 or cs2 etc.
    
    doSql("select category, "+seg+" from segments where siteId='"+siteId+"'", function(data) {
      console.log(data);
      // data is {num: n, rows: array of row

      $.each(data.rows, function(i, v) {
        // v is the row for category
        console.log("v", v, v.category, v[seg]);
        var inp = $("#catlist input[name='"+v.category+"']");
        inp.val(v[seg]);
        inp.slider("refresh");
      });
    });
  });

  $("select").trigger("change");

  // Change back to the main page

  $("#homejames").on("click", function() {
    $("#home").remove();
    $.mobile.changePage("cpanel.php?siteId="+siteId);
    return false;
  });
});
