// Game Control
jQuery(document).on("pagebeforeshow", "#games", function(e, data) {
  $("#gamesOK").on("click", function(e) {
    var sqls = "update sites set ", sqlb="update playbingo set ", sqll="update playlotto set ";
    
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
    
    sqlb += "freq='"+bingofreq+"', drawnumber='"+bingodraw+
            "',intervals='"+bingointerval+"', whenWin='"+bingowhenwin+"' where siteId='"+siteId+"'";
    
    sqll += "expires='"+lottoexpires+"' where siteId='"+siteId+"'"; 
    console.log(sqls, sqlb, sqll);
    doSql(sqls, function(data) {
      console.log("post sites", data);
      var g = $("#gamescontent li");
      doSql(sqlb, function(data) {
        console.log("post bingo", data);
        doSql(sqll, function(data) {
          console.log("post lotto", data);
          var bc = g.css("background-color");
          g.css("background-color", "green");
          setTimeout(function() {
            g.css("background-color", bc);
          }, 2000);
        });
      });
    });
    return false;
  });

  // Initial values for radios
  
  doSql("select s.playbingo as playbingo, s.playLotto as playLotto, "+
        "b.freq as bingoFreq, b.intervals as bingoInterval, "+
        "b.drawnumber as drawnumber, b.whenWin as bingoWhenWin, "+
        "l.expires as lottoExpires from sites as s left join playbingo as b "+
        "on s.siteId = b.siteId left join playlotto as l on s.siteId = l.siteId "+
        "where s.siteId='"+siteId+"'", function(data) {
    console.log(data);
    if(data.num) {
      var row = data.rows[0];
      var state = [row.playbingo, row.playLotto];
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
      
      $("#gamescontent").trigger('create');      
    }
  });
});
