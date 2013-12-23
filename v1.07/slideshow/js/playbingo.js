
var email, siteId, game=0, numbers, allnumbers='', autoplay, inx=-1;

// PUSHER Start
// Enable pusher logging - don't include this in production

Pusher.log = function(message) {
  if (window.console && window.console.log) {
    window.console.log(message);
  }
};

// Our key '2aa0c68479472ef92d2a'
var pusher = new Pusher('2aa0c68479472ef92d2a');

pusher.connection.bind("error", function(err) {
  console.log(err);
  $("body").html("<h1>Network Error</h1><p>Unable to play bingo at this time</p>");
});

var slideshow = pusher.subscribe('slideshow');

slideshow.bind('pusher:subscription_succeeded', function() {
  slideshow.bind('gameover', function(data) {
    console.log('gameover', data);
  });

  var playbingo = pusher.subscribe('playbingo');

  playbingo.bind('pusher:subscription_succeeded', function() {
    playbingo.bind('newinx', function(data) {
      console.log('newinx', data);
      // Only look if it is our game
      if(game != data.game)
        return;
  
      checkwin(data.inx);
    });
  });
});
console.log("HERE");

// PUSHER End

function checkwin(inx) {
  if(autoplay)
    numbers = allnumbers;

  $.ajax({
    url: 'playbingo.php',
    data: { page: 'checkwin', siteId: siteId, game: game,
    email: email, numbers: numbers, inx: inx },
    dataType: 'json',
    type: 'get',
    success: function(data) {
           console.log("checkwin:", data);
           var msg, hits;
           if(typeof data != 'string') {
             msg = data.msg;
             hits = data.hits;
           } else {
             msg = data;
           }

           if(hits) {
             var hitsdiv = "", hitcnt = 0;

             if(!autoplay) {
               $(".box").css("backgroundColor", "tomato");
             }

             var lastrow = hits.length % 3;

             var hitsdiv = "<hr><h3>Photos Shown So Far</h3>";

             var len = hits.length/3 | 0, n=0;

             for(var i=0; i < len; ++i) {
               // The rows
               hitsdiv += "<div class='row'>";
               for(var j=0; j < 3; ++j) {
                 // the columns
                 var hit = '';
                 if(hits[n].hit) {
                   hitcnt++;
                   hit = " style='background-color: green;'";
                 }

                 hitsdiv += "<div class='box'"+hit+"><img src='/"+hits[n].loc+"'/></div>";
                 if(autoplay) {
                   $("[data-id='"+hits[n].itemId+"']").parent().css("backgroundColor", "green");
                 }
                 ++n;
               }
               hitsdiv += "</div>";
             }
             if(lastrow) {
               hitsdiv += "<div class='row'>";
               for(j=0; j < lastrow; ++j) {
                 var hit = '';
                 if(hits[n].hit) {
                   hitcnt++;
                   hit = " style='background-color: green;'";
                 }

                 hitsdiv += "<div class='box'"+hit+"><img src='/"+hits[n].loc+"'/></div>";
                 if(autoplay) {
                   $("[data-id='"+hits[n].itemId+"']").parent().css("backgroundColor", "green");
                 }
                 ++n;
               }
               hitsdiv += "</div>";
             }

             $("#hits").html(hitsdiv);
             $("#hits").show();
             ++inx;
             $("#inx").html(inx+" Photo"+((inx > 1) ? 's' : '')+" "+hitcnt+" hit"+((hitcnt != 1) ? 's' : ''));
           }

           if(msg.match(/^You are a winner\.|^Game Over!/)) {
             $("#messages").html(msg);
             $("#messages").show();
             game = 0;
             return;
           }

           // If this isn't autoplay

           if(!autoplay) {
             $("#messages").html(msg);
             $("#messages").show();
             setTimeout(function() { $("#messages").hide(); }, 5000);
           }
         },
         error: function(err) {
           console.log("error:", err);
         }
  });
}

jQuery(document).ready(function($) {
  $(".row").hide();

  // submit the email, siteId and game number

  $("#ok").click(function(e) {
    email = $("#email").val();
    siteId = $("#siteId").val();
    game = $("#game").val();
    autoplay = $("#autoplay").prop("checked");

    // Build the 3x3 photo array

    $.ajax({
      url: 'playbingo.php',
      type: 'get',
      data: { page: 'getgame', siteId: siteId, game: game, email: email},
      dataType: 'json',
      success: function(data) {
             console.log("getgame:", data);
             if(typeof data == 'string') {
               $("#messages").html(data);
               $("#messages").show();
               setTimeout(function() { $("#messages").hide(); }, 5000);
               $("#game").focus();
               return;
             }

             var items = data.items, rules = data.rules;

             $("#firstup").hide();

             var n=0;

             for(var i=0; i < 3; ++i) {
          // The rows
               for(var j=0; j < 3; ++j) {
            // the columns
                 allnumbers += items[n][0]+',';
                 $("#row"+(i+1)+" .box:nth-child("+(j+1)+")")
                     .html("<img data-id='"+items[n][0]+"' src='../"+items[n][1]+"'/>");
                 ++n;
               }
             }
             $(".row").show();

             if(autoplay) {
               $("form:eq(1)").append("<div>Auto Play Enabled. <span id='inx'></span></div>");
             } else {
               $("#submit").show();
             }

             var flag = 1;

             $("#showrules").click(function(e) {
               if(flag++ % 2) {
                 $("#rules").show();
                 $("#showrules").text("Hide Rules");
               } else {
                 $("#rules").hide();
                 $("#showrules").text("Show Rules");
               }
             });

             $("#showrules").show();
             $("#rules").hide().html(rules);
           },
           error: function(err) {
             console.log("doSql:", err);
           }
    });
    return false;
  });

  var ar = {};

  $(".box").on("click", function(e) {
    if(autoplay) return false;

    var id = $("img", this).attr('data-id');
    ar[id] = true;
    $(this).css("backgroundColor", "green");
    return false;
  });

  $("body").on('click', '#submit', function(e) {
    // Submit numbers to the server.
    // get the itemId's

    numbers = '';

    for(var e in ar) {
      numbers += e + ",";
    }
    var data = { page: 'checkwin', siteId: siteId, game:
    game, email: email, numbers: numbers, noauto: true };
    checkwin(data);
    return false;
  });

});
