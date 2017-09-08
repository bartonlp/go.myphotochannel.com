// Game Control
jQuery(document).on("pagebeforeshow", "#games", function(e, data) {
  $("#gamesOK").on("click", function(e) {
    var sqls = "update appinfo set ",
    sqlb="update playbingo set ",
    sqll="update playlotto set ",
    sqlt="update playtrivia set ";
    
    $("ul input:checked").each(function(i, v) {
      var state = $(v).val();
      var name = $(v).attr("name");
      sqls += name+"='"+state+"', ";
    });
    sqls = sqls.replace(/, $/, ' ');
    sqls += "where siteId='"+siteId+"'";
    
    var bingofreq = $("#bingofreq").val(),
    bingodraw = $("#bingodraw").val(),
    bingointerval = $("#bingointerval").val() * 60000,
    bingowhenwin = $("#bingowhenwin").val(),
    lottoexpires = "+"+ $("#lottoexpires").val()+" day";

    var trivianum = $("#trivianum").val(),
    triviaqtime = $("#triviaqtime").val(),
    //triviaatime = $("#triviaatime").val(),
    triviacat = $("#triviacat").val(),
    triviafontsize = $("#triviafontsize").val(),
    triviafontstyle = $("#triviafontstyle").val();

    sqlt += "trivianum='"+trivianum+
            "', triviaqtime='"+triviaqtime+
            //"', triviaatime='"+triviaatime+
            "', triviacat='"+triviacat+
            "', triviafontsize='"+triviafontsize+
            "', triviafontstyle='"+triviafontstyle+
            "' where siteId='"+siteId+"'";
            
    sqlb += "freq='"+bingofreq+"', drawnumber='"+bingodraw+
            "',intervals='"+bingointerval+"', whenWin='"+bingowhenwin+"' where siteId='"+siteId+"'";
    
    sqll += "expires='"+lottoexpires+"' where siteId='"+siteId+"'"; 
    console.log(sqls, sqlb, sqll);

    doSql(sqls, function(data) {
      console.log("post appinfo", data);
      var g = $("#gamescontent li");
      doSql(sqlb, function(data) {
        console.log("post bingo", data);
        doSql(sqll, function(data) {
          console.log("post lotto", data);
          doSql(sqlt, function(data) {
            console.log("post trivia", data);
            
            var bc = g.css("background-color");
            g.css("background-color", "green");
            setTimeout(function() {
              g.css("background-color", bc);
            }, 2000);
          });
        });
      });
    });
    return false;
  });

  // Initial values for radios
  
  doSql("select s.playbingo as playbingo, s.playLotto as playLotto, s.playtrivia as playtrivia, "+
        "b.freq as bingoFreq, b.intervals as bingoInterval, "+
        "b.drawnumber as drawnumber, b.whenWin as bingoWhenWin, "+
        "l.expires as lottoExpires, "+
        "t.trivianum as trivianum, "+
        "t.triviaqtime as triviaqtime, "+
        //"t.triviaatime as triviaatime, "+
        "t.triviacat as triviacat, "+
        "t.triviafontsize as triviafontsize, "+
        "triviafontstyle as triviafontstyle "+
        "from appinfo as s "+
        "left join playbingo as b on s.siteId = b.siteId "+
        "left join playlotto as l on s.siteId = l.siteId "+
        "left join playtrivia as t on s.siteId = t.siteId "+
        "where s.siteId='"+siteId+"'", function(data) {
    console.log(data);
    if(data.num) {
      var row = data.rows[0];
      var state = [row.playbingo, row.playLotto, row.playtrivia];
      console.log("state", state);

      $("#games ul li").each(function(i, v) {
        $("[value='"+state[i]+"']", v).prop("checked", true);
      });
      $("#bingofreq").val(row.bingoFreq);
      $("#bingointerval").val(row.bingoInterval/60000);
      $("#bingodraw").val(row.drawnumber);
      $("#bingowhenwin").val(row.bingoWhenWin);
      var expires = row.lottoExpires.match(/\+(\d+)/)[1];
      $("#lottoexpires").val(expires);
      $("#bingofreq, #bingointerval, #bingodraw, #bingowhenwin, #lottoexpires").slider('refresh');

      $("#trivianum").val(row.trivianum);
      $("#triviaqtime").val(row.triviaqtime);
      //$("#triviaatime").val(row.triviaatime);
      $("#triviacat").val(row.triviacat);
      $("#triviafontsize").val(row.triviafontsize);
      $("#triviafontstyle").val(row.triviafontstyle);

      $("#trivanum, #triviaqtime, #triviafontsize").slider('refresh');

      $("#triviacat, #triviafontstyle").selectmenu("refresh");

      $("#gamescontent").trigger('create');      
    }
  });

  // Change back to the main page

  $("#homejames").on("click", function() {
    $("#home").remove();
    $.mobile.changePage("cpanel.php?siteId="+siteId);
    return false;
  });
});
