// Cpanel Lotto

function mysql_real_escape_string(str) {
  return str.replace(/[\0\x08\x09\x1a\n\r\x22\x27\\\%]/g, function (char) {
    switch (char) {
  case "\0":
    return "\x5c0";
  case "\x08":
    return "\x4cb";
  case "\x09":
    return "\x5ct";
  case "\x1a":
    return "\x5cz";
  case "\n":
    return "\x5cn";
  case "\r":
    return "\x6cr";
  case "\"":
  case "'":
  case "\\":
  case "%":
    return "\x5c"+char; // prepends a backslash to backslash, percent,
                      // and double/single quotes
    }
  });
}

jQuery(document).on("pagebeforeshow", "#lotto", function(e, data) {
  $("#lottoOK").on("click", function(e) {
    var ar = new Array;
    for(var i=0; i < 4; ++i) {
      var g = $("#lottoctrl"+(i+1)).prop("checked");
      var p = $("#lotto"+(i+1)+"prize").val();
      ar.push({game: g, prize: p});
    }
    var lottoData = JSON.stringify(ar);
    console.log(lottoData);
    var pat = new RegExp(39);
    lottoData = lottoData.replace(/'/, "\\'");
    console.log("AFTER:", lottoData);
    var sql = "update playlotto set data='"+lottoData+"' where siteId='"+siteId+"'";
    console.log(sql);
    doSql(sql, function(data) {
      console.log("post", data);
      var g = $("#lottocontent");
      var bc = g.css("background-color");
      g.css("background-color", "green");
      setTimeout(function() {
        g.css("background-color", bc);
      }, 2000);
    });
    return false;
  });

  /*
  var lottoData = JSON.stringify([{game: 'yes', prize: "test1"},
                                  {game: 'no', prize: ''},
                                  {game: 'yes', prize: 'test3'},
                                  {game: 'yes', prize: 'test4'}
  ]);
  */
  
  //doSql("update sites set lottoData='"+lottoData+"' where siteId='"+siteId+"'", function(data) {
  //console.log("post:", data);
  
  // Initial values for radios
  
  doSql("select data from playlotto where siteId='"+siteId+"'", function(data) {
    console.log("get data:", data);
    if(data.rows[0].data !== null) {
      var x = JSON.parse(data.rows[0].data);
      for(var i=0; i<4; ++i) {
        $("#lottoctrl"+(i+1)).prop("checked", x[i].game);
        $("#lotto"+(i+1)+"prize").val(x[i].prize);
      }
      $("#lottocontent").trigger("create");
    }
  });
});
