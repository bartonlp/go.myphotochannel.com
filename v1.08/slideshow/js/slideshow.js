// JavaScript to Implement the Slide Show
// Use strict during development.
"use strict"
// GLOBAL VARIABLES
// Global variable look like 'g.xxxx' the g object has all of the
// global variables.

var g = {}; // Global Name Space. All global variable are in this space.

(function(x) {
  x.COPYRIGHT = "&copy; 2013 myphotochannel";
  x.VERSION = lastMod; // 1.08
  x.DEBUG = false; // If invokation has ?degug=nnnn. See below after variable declarations
  x.CONTENTPREFIX = "../"; // Where is the content directory from the slideshow.php file's location
  x.unit = unit;
  x.siteCode = siteCode;
  x.siteId = siteId;
  x.ajaxfile = "slideshow.ajax.php"; // The ajax program we are using
  x.StartUp = true; // true until the slideshow() is started
  x.startupId = 0;
  x.timeoutid; // setTimeout
  x.stopShow = false; // getStop()
  x.stopShowSet = false; // getStop()
  x.images = { // The images array holds the images for each category and psudo category
    'feature': new Array, // psudo category
    'photo': new Array,   // images.photo.[images 0-n]. 
    'announce': new Array,
    'brand': new Array,
    'product': new Array,
    'info': new Array,
    'video': new Array,
    'ads': new Array, // ads[0-n customers][0-n rows]. Psudo category
    'adsVid': new Array, // Ads videos. Same format as ads. Psudo category
    'bingo': new Array, // bingo game images
    'lotto': new Array
  };
// inx array has the counters for each category
  x.inx = {
    'counter': 1,   // Counts every show image
    'feature': 0,   // Counter for 'feature' and the other 
    'photo': 0,     // categories
    'announce': 0,
    'brand': 0,
    'product': 0,
    'info': 0,
    'video': 0,
    'ads': new Array,  // adId=n. so inx.ads[i] = 0
    'adsVid': new Array,
    'bingo': 0,
    'lotto': 0
  };
// mpcSkipCntr was in the video images as an added field. Now making it
// its own array.
  x.mpcSkipCntr = new Array;
// like above but for adsVidios  
  x.adsSkipCntr = new Array;
// This array is updated every slowCall time if necessary.
// adsInfo[0-nCust] { dur, trans, effect, inx, cbCnt, custCnt,
// segs[0-5] }. An indexed array.
  x.adsInfo = new Array;
// The items which is an image or a div depending on what 'type' the
// item in the 'items' table was.
// lastItem is the fadding out item.
  x.item;
  x.lastItem;
// ADS STUFF
  x.nCust; // number of customers: inx.ads.length
  x.curCust = 0; // this is custInx % nCust. Used to index into adsInfo
// sites, appinfo, segments and categories comes via the Ajax getInfo
// call.
  x.sites; // sites table info
  x.appInfo; // appinfo: life, aged, callbackTime, progDur, features, fastCallback.
  x.segments; // segments[cat][0..4] each csN
  x.categories; // categories[cat] dur, trans, effect
  x.allowAds = 'no';  // from sites table. Do we allow ADS to be shown
  x.allowVideo = 'no'; // from sites table
  x.playbingo = 'no';
  x.bingoGame = 0; // bingo game number
  x.bingotimeout = null;
  x.bingoFreq = 0;  // bingo during show. Number of show photos between bingo photos.
  x.bingoOver = 'no'; // game over
  x.bingoDrawNumber = 30;
  x.playLotto = 'no';
  x.segIndex = 0; // goes from 0 to 4 (cs1...cs5)
  x.segCount = 0; // This has the segments[cat][<csN value>] which we count down
  x.segCat;       // The segment category to use
  x.featureCnt = 0;
  x.itemList = new Array('announce', 'brand', 'product', 'info', 'video', 'bingo', 'lotto'); // CB categories
  x.itemCtr = 0; // item counter goes from 0-itemList.length
  x.image; // Make this a global so it is always in scope. It gets the 'image' in slideshow.
  x.heightExtra = 30;
  x.widthExtra = 20; // fudge factor for screen
  x.vidHeightExtra = 10;
  x.vidWidthExtra = 20; // fudge factor for video screen
  x.winHeight;
  x.winWidth; // current height and width of screen. resize keeps these current
  x.closeCall = 0; // While closeed how often do we call the server to get appInfo. A counter
  x.youtubeflag = false; // keeps us from doing the PLAYING callback more than the first time.
  x.doAdsVid = false; // when true we start the doAdsVideo() function of doCommercial()
  x.inCB = false;
  x.pusherChannel; // Pusher channel (slideshow)
  x.videoTypes = function() {
    var t = {};
    var v = document.createElement('video');

    t.ogg = v.canPlayType('video/ogg;codecs="theora, vorbis"');
    t.mp4 = v.canPlayType('video/mp4;codecs="avc1.4D401E, mp4a.40.2"');
    t.webm = v.canPlayType('video/webm;codecs="vp8, vorbis"');
    return t;
  }
})(g);

// If invocation has a query with debug=nnnn
// If ture we display the debug information in the upper left corner of
// the screen.

if(location.search.indexOf('debug') != -1) {
  g.DEBUG = true;
}

// PUSHER Start
// Enable pusher logging - don't include this in production

Pusher.log = function(message) {
  if (window.console && window.console.log) {
    window.console.log(message);
  }
};

// Our key '2aa0c68479472ef92d2a'
var pusher = new Pusher('2aa0c68479472ef92d2a');

g.pusherChannel = pusher.subscribe('slideshow');;

pusher.connection.bind("error", function(err) {
  console.log("Pusher Connection Error, Falling back to old fastCall: ", err);
  if(g.DEBUG) {
    $("#debugitems").append("<p id='pusher-err'>Pusher Error, Falback</p>").css("color", "red");
  }
});

pusher.connection.bind("connecting_in", function(d) {
  console.log("connecting_in: ", d);
});

pusher.connection.bind("state_change", function(s) {
  console.log("STATE_CHANGE: ", s);
  if(s.previous == 'disconnected' && s.current == 'connecting') {
    fastCall();
  }
});

pusher.connection.bind("connected", function() {
  console.log("CONNECTED");
  if(g.DEBUG) {
    $("#pusher-err").remove();
  }
});

pusher.connection.bind("unavailable", function() {
  console.log("UNAVAILABLE");
});

pusher.connection.bind("disconnected", function() {
  console.log("DISCONNECTED");
});

g.pusherChannel.bind('pusher:subscription_succeeded', function() {
  console.log("slideshow subscribe OK");
  g.pusherChannel.bind('gameover', function(data) {
    // game, gameover, inx
    console.log("gameover", data);
    if(data.game == g.bingoGame) {
      g.bingoOver = 'yes';
    }    
  });

  g.pusherChannel.bind('fastcall', function(data) {
    console.log("fastcall", data);
    if(data.siteId = g.siteId) {
      fastCall();
    }
  });
});
// PUSHER End

// **********
// Functions

// On Unload
// Do the Ajax when we close the page.
// We want to mark the page as closed in the 'startup' table.

$(window).bind("unload", function(e) {
  $.ajax({
    url: g.ajaxfile,
    data: {name: 'unload', siteCode: g.siteCode, startupId: g.startupId},
    type: "get",
    async: false
  });
});

// Bingo Update
// Send info to playbingo programs

function bingoupdate(inx) {
  // inx is -1 for the start photo and 0 for the first bingo photo etc.
  
  $.ajax({
    url: g.ajaxfile,
    data: { name: 'bingoupdate', inx: inx, siteCode: g.siteCode,
            game: g.bingoGame, gameover: g.bingoOver},
    type: 'get',
    dataType: 'text',
    success: function(data) {
           console.log("bingoupdate", data);
           if(data == 'gameOver') {
             g.bingoOver = 'yes';
           }
         },
         error: function(err) {
           console.log("bingoupdate error: ", err);
         }
  });
}

// Resize the screen if the browser size changes.

$(window).resize(function(e) {
  g.winHeight = window.innerHeight - g.vidHeightExtra;
  g.winWidth = window.innerWidth - g.vidWidthExtra;

  var csshw = "img, .htmlitem {\n" +
              "height: " +(window.innerHeight - g.heightExtra)+"px;\n"+
              "max-width: " +(window.innerWidth - g.widthExtra)+ "px;\n" +
              "}\n";

  $("#csshw").html(csshw);
  
  if(g.image && $("#YT-"+g.image.mpcId).length) {
    $("#YT-"+g.image.mpcId).attr({width: g.winWidth, height: g.winHeight});
  }
});

// Do slideshow dissolve feature

function dissolve(callback) {
  var r = (g.image.width / g.image.height)|0;
  if((g.winHeight * r) > g.winWidth) {
    var w = (g.winWidth/r)|0;
    var t = ((g.winHeight / 2) - (w/2))|0;
    $(g.image).css({'position': 'absolute', 'left': 10,
                   'height': w, 'width': g.winWidth,
                   'top': t});
  } else {
    var left = (window.innerWidth / 2);
    var w = $("#photoemailaddress").width()/2;
  
    left = parseInt(left - (g.item.width() / 2));
    g.item.css({ position: 'absolute', top: 0, left: left });
  }

  // For the dissolve we do fadeOut at the same time we do fadeIn.

  g.lastItem.fadeOut(fade, function() {
    g.lastItem.remove(); // remove after fade out done.
    g.lastItem = g.item;
  });
  fadeIn(callback);
}

// Do slideshow fade or pop

function fade(callback) {
  var r = (g.image.width / g.image.height)|0;
  if((g.winHeight * r) > g.winWidth) {
    var w = (g.winWidth/r)|0;
    var t = ((g.winHeight / 2) - (w/2))|0;
    $(g.image).css({'position': 'absolute', 'left': 10,
                   'height': w, 'width': g.winWidth,
                   'top': t});
  }

  g.lastItem.fadeOut(g.image.mpcTrans, function() {
    //remove old item object from the div, the one we just faded out.

    g.lastItem.remove(); // remove after fade out done.

    // This item will be lastItem next time around and it will be faded
    // out.

    g.lastItem = g.item;
    fadeIn(callback);
  });
}

// slideshow fade in function

function fadeIn(callback) {
  // Do the fade in and when fade complete do the rest of the show,
  // At the end reset the timeout for the next image.

  g.item.fadeIn(g.image.mpcTrans, function() {
    // inx.counter mod 'callbackTime' signals when we call the
    // server for 'features', 'photo' and all commercial categories.
    // 'fastCall(slowCall)' gets everything so we don't want to ever do
    // just 'fastCall()' which only gets 'feature' and
    // 'announce' at the same time (note 'if elseif').

    if(g.inCB === false) {
      // NOT Commercial break.

      // NOTE: g.inx.photo (g.inx[cat]) is always incremented and mod.
      // Only here do we actually look to see if it is greater than
      // g.images.photo.length. This means that if the Internet
      // connection is down and the fastCall/slowCall fails we will be
      // very persistent about trying to get new data!
      
      if(g.inx.photo > g.images.photo.length) {
        // slowCall now always does a fastCall first (9/1/13)
        slowCall(function() {
          if(typeof callback == 'function') return callback();
        });
      } else if(g.pusherChannel.subscribed !== true &&
                ((g.inx.photo + g.inx.feature) % g.appInfo.fastCallback) == 0) {
        // Pusher not available so do old fastCall
        fastCall(function() {
          if(typeof callback == 'function') return callback();
        });
      } else {
        // Wasn't either fast or slow callback time
        if(typeof callback == 'function') return callback();
      }
    } else {
      // During a commercial break
      if(typeof callback == 'function') return callback();
    }  
  });
}

// Do the effect -- dissolve, fade, or pop

function doEffect(callback) {
  switch(g.image.mpcEffect) {
    case 'dissolve':
      // If a dissolve we have to make the two <img objects positioned
      // absolute within the show div.
      // Center the image.

      dissolve(callback); // note: 'item' and 'lastItem' are globals
      break;
    case 'pop':
    case 'fade':
    default:
      // For fade or pop we do the fadeOut first and only after the
      // first image is completely out do we do the fadeIn.

      fade(callback); // note: 'item' and 'lastItem' are globals
      break;
  }
}

// Stop/Start the slideshow.

function getStop(x) {
  if(x !== null) {
    g.stopShow = x;
    // If we have used Stop/Start button then don't do open/close logic
    g.stopShowSet = true;
  } else {
    var open = g.sites.open, close = g.sites.close;

    if(open && close) {
      // Open and Close have values so we are going to use these to
      // start and stop the slideshow.
      
      open = open.split(":");
      close = close.split(":");

      var o = new Date();
      o.setHours(open[0]);
      o.setMinutes(open[1]);
      o.setSeconds(open[2]);

      var c = new Date();
      c.setHours(close[0]);
      c.setMinutes(close[1]);
      c.setSeconds(close[2]);

      // If the open value is greater than the close value that means
      // the close is in the next day like: we open at 16:00 and close
      // the next day at 03:00 in the morning.
      
      if(o > c) {
        c.setDate(c.getDate() + 1); // add a day
      }

      var d = new Date(); // Current time

      // If the current time is greater than open and less then close
      // then we are open. If not we are closed
      
      if(!(d > o && d < c)) {
        // CLOSED
        if(g.stopShowSet) {
          return g.stopShow;
        }
        
        $("#stopStart").text("Closed");
        $(("#show").children()[0]).remove(); // remove whatever is there.

        // Add the Site Closed message and set it as lastItem
        g.lastItem = $("#show").append("<div class='htmlitem'><h1>Site Closed</h1></div>");
        g.lastItem = $(g.lastItem[0].children[0]);
        return true;
      } else {
        // We are OPEN
        // Now return if we are running or not.
        return g.stopShow;
      }
    } else {
      // This site is not using open/close
      
      return g.stopShow;
    }
  }
}

// This is the heart of the program. This function implements the slide
// show. It is fired via a timer every interval. The interval is
// determined by the items mpcDur property.

function slideshow() {
  if(getStop(null) === true) {
    // If we have used the debug Stop/Start button then don't do the
    // open/close stuff
    
    if(!g.stopShowSet) {
      if(++g.closeCall > 10) { // every 10 minutes check the server
        g.closeCall = 0;
        getInfo();
      }
      setTimeout(slideshow, 60000); // Check again in one minute
    }
    return; // Stop
  }
  // What type of category? Show or commercial etc.
  
  var cat = getCategory(), num, image;
  
  // Array index. Get image.

  for(var n=0; image == null; ++n) {
    // Not sure why this should happen but I saw it once.
    // Instead of images from 0-n the started at 2-n.
    // This logic copes with that but keeps from having an run away
    // loop.
    
    if(g.images[cat].length == 0 || n > 50) {
      console.log("cat: "+cat+ " No images found. Number of "+cat+" images: "+g.images[cat].length);
      // Do a slowCall to get more photos
      slowCall(function() {
        setTimeout(slideshow, 0);
      });
      return;
    }

    // Now get the item. 

    switch(cat) {
      case 'ads':
        // curCust is incremented in doCommercial() at the ads logic
      
        num = g.inx.ads[g.curCust]++ % g.images.ads[g.curCust].length;
        image = g.images.ads[g.curCust][num];
        break;
      case 'adsVid':
        num = g.inx.adsVid[g.curCust]++ % g.images.adsVid[g.curCust].length;
        image = g.images.adsVid[g.curCust][num];
        break;
      case 'video':
        num = g.inx.video++;
        image = g.images.video[num];
        break;
      case 'bingo':
        // bingo game
        // The first photo (0) is the start game photo!
        // So num for the first bingo photo will be 1
        num = g.inx.bingo;

        if(num == 0)
          $("#bingoinfo").show();
        
        // draw must be plus one to cope with the start photo.
        
        if(num >= (g.bingoDrawNumber+1) || g.bingoOver == 'yes') {
          // Game over.
          // Set the timer to start the next game
          if(g.playbingo == 'yes' && g.pusherChannel.subscribed === true) {
            g.bingotimeout = setTimeout(function() { getBingo(); }, g.sites.bingoInterval);
          }
          // setup game over photo which is the last photo of the pool.

          $("#bingoinfo").hide();
          
          image = g.images.bingo[g.images.bingo.length-1];
          g.bingoOver = 'yes';
          g.bingoGame = 0;
        } else {
          image = g.images.bingo[num];
        }

        // This makes the start photo -1 and the first bingo photo 0
        
        bingoupdate(num-1);

        image.mpcDur = 5000;
        image.mpcTrans = 1000;
        image.mpcEffect = 'dissolve';
        image.mpcDesc = 'bingo';
        image.mpcType = 'image';
        image.mpcCat = 'bingo';
        image.mpcTime = new Date;
        
        ++g.inx.bingo;
        if(g.inCB === false) {
          cat = 'photo'; // nasty hack to get the debug info correct!
        }
        break;
      case 'lotto':
        num = 0;
        image = g.images.lotto[0];
        image.mpcDur = 5000;
        image.mpcTrans = 1000;
        image.mpcEffect = 'dissolve';
        image.mpcDesc = 'lotto';
        image.mpcType = 'image';
        image.mpcCat = 'lotto';
        image.mpcId = 'lotto';
        break;
      default:
        // photo, feature, announce, brand, product, info
      
        num = g.inx[cat]++ % g.images[cat].length;
        image = g.images[cat][num];
        break;
    }
  }
  
  if(image.mpcEffect == 'pop') image.mpcTrans = 0;
  
  // Each image object has:
  //   mpcId (itemId from 'items' or 'ads' table
  //   mpcDur (full screen duration in milliseconds)
  //   mpcTrans
  //   mpcEffect
  //   mpcSkip -- ONLY for Video and AdsVideo
  //   mpcDesc (description of image as text),
  //   mpcTime (image showTime as a datetime) and
  //   mpcCat (the category for the image like 'photo', 'announce' etc.
  //   mpcType (the type of the item: 'image', 'text', 'html' etc.
  
  console.log('Image, num, counter, cat', image, num, g.inx.counter, cat);

  if(g.DEBUG == true) {
    var debugMsg = "Site: "+g.siteId+
                   "<br>Version: "+g.VERSION;

    switch(cat) {
      case 'feature':
      case 'photo':
        debugMsg += "<br>inx: "+(g.inx.counter-1)+
                    "<br>"+image.mpcCat+' '+(num+1)+' of ';

        debugMsg += +g.images[cat].length +
                    '<br>show: ' + ((g.inx.counter-1) % g.appInfo.progDur) + ' of ' +
                    g.appInfo.progDur +
                    '<br>'+ image.mpcTime;
                    //'<br>life: ' + g.appInfo.life +
                    //'<br>callback in: ' +
                    //(g.appInfo.callbackTime - (g.inx.counter % g.appInfo.callbackTime)) +
                    //"<br>" + image.src.replace(new RegExp(".*\/"), '') +
                    //"<br>Fade: "+image.mpcTrans+", Dur: "+image.mpcDur+
                    //"<br>Effect: "+image.mpcEffect
                    //", Skip: "+image.mpcSkip+
                    //"<br>W"+image.width+"xH"+image.height+
        break;
      case 'adsVid':
        debugMsg += "<br>ADS Vid Cust: "+g.adsInfo[g.curCust].adId+
                    "<br>Cust-cnt: "+(g.adsInfo[g.curCust].custCnt)+
                    "<br>Dur: "+image.mpcDur+
                    "<br>Desc: "+image.mpcDesc+
                    "<br>Vid: "+image.mpcLoc;
        break;
      case 'ads':
        var segInx = g.adsInfo[g.curCust].segInx % g.adsInfo[g.curCust].segs.length;
        var num = g.adsInfo[g.curCust].segs[segInx].num;

        debugMsg += "<br>ADS Cust: "+g.adsInfo[g.curCust].adId+
                    "<br>Seg: "+segInx+
                    "<br>Num: "+num+
                    "<br>Cust-cnt: "+(g.adsInfo[g.curCust].custCnt);
        break;
      case 'announce':
      case 'brand':
      case 'product':
      case 'info':
      case 'video':
      case 'lotto':
      default:
        // stays on screen until next category
        debugMsg +="<br>Commercial Break<br>" + cat +
                    ", segment " + g.segIndex + ": " + (g.segCount+1);
        break;
    }

    var reg = new RegExp(".*\/"), imagename = image.src.replace(reg, '');
    if(!imagename) {
      imagename = image.mpcLoc.replace(reg, '');
    }
    
    debugMsg += "<br>Type: "+image.mpcType+
                "<br>" + imagename +
                "<br>ID: "+image.mpcId;
    
    $("#info").html(debugMsg);
  }

  // Check to see what type of item this is

  g.image = image; // copy to global
  
  switch(g.image.mpcType) {
    case 'image':
      $("#show").append(g.image); // add new
      break;
    case 'html':
    case 'filehtml': // File html is handled by the getItem() function
      $("#show").append("<div class='htmlitem'>"+g.image.mpcLoc+"</div>");
      break;
    case 'video':
      if(g.allowVideo == 'no') {
        setTimeout(slideshow, 0);
        return;
      }
      
      // Make sure the id is unique as there could be two video tags at
      // some point.
      var ext = (/\.([^.]*?)$/gi).exec(g.image.mpcLoc)[1].toLowerCase();

      switch(ext) {
        case 'ogv':
        case 'ogg':
          ext = "ogg; codecs=theora,vorbis";
          break;
        case 'mp4':
          ext = "mp4; codecs=avc1.42E01E,mp4a.40.2";
          break;
        case 'webm':
          ext = "webm; codecs=vp8,vorbis";
          break;
      }

      if(g.DEBUG) $("#info").append("<br>"+ ext.replace(new RegExp('.*; '), ''));

      var video = $("<video/>");
      //video.width(g.winWidth);
      //video.height(g.winHeight);
      //video.attr({id: "video-"+g.image.mpcId, type: "video/"+ext, poster: "", src: g.image.mpcLoc});


      var v = video[0];

      v.setAttribute("id", "video-"+g.image.mpcId);
      v.setAttribute("type", "video/"+ext);
      v.setAttribute("poster", "");
      v.setAttribute("preload", "true");

      v.width = g.winWidth;
      v.height = g.winHeight;
      v.src = g.image.mpcLoc;

      // HTML5 Video stuff. This is async so we set the timeout after
      // the video starts playing and we know how long it will be.

      video.on('error', function(e) {
        if(this.error == null) {
          // This does not happen with Chrome on Linux!
          // Firefox, phones and tablets like Nexus 7 have this problem
          // We really do not support anything but Chrome!
          console.log("this.error is null", e, g.image.mpcLoc);
          $("#info").append("<br>this.error is null");
          g.lastItem.remove();
          this.play();
          g.timeoutid = setTimeout(slideshow, g.image.mpcDur);
          return;
        }

        if(this.error.code == MediaError.MEDIA_ERR_SRC_NOT_SUPPORTED) { 
          $("#info").append("<br>VID Error: Not Supported");
        } else {
          $("#info").append("<br>VID Error: "+this.error.code);
        }

        console.log("video Error: ", this.error.code);
        // If the video is not supported we want to restart the
        // slideshow and go to the next item.
        
        clearTimeout(g.timeoutid);
        g.timeoutid = setTimeout(slideshow, 0);
        return;
      });

      // This should fire right after the append.

      video.on('loadedmetadata', function(e) {
        var dur = Math.ceil(this.duration * 1000);
        console.log("Video Duration:", dur);
        if(g.DEBUG) {
          $("#info").append("<br>VidDur: "+dur);
        }

        // For type==youtube we don't use the adsInfo[].dur. This is
        // taken care of in getAds() or getItem().
        // If the dur for the item in the ads table is zero then use
        // the actual length of the video. We ignore the default in
        // adsInfo

        if(g.image.mpcDur == null || g.image.mpcDur == 0) {
          g.image.mpcDur = dur;
        }

        var x = $("<div class='videoitem'/>");
        x.append($(this)).hide();
        $("#show").append(x);

        // Get the new appended video item. children()[0] is the
        // lastItem.
        
        g.item = $($("#show").children()[1]); 

        doEffect(function() {
          video[0].play(); // Start the video.
          g.timeoutid = setTimeout(slideshow, (g.image.mpcDur));
        });
      });

      return;
    case 'youtube':
      // Make sure the id is unique as there could be two YT-xx tags
      // at the same time.

      $("#show").append("<div class='youtubeitem' style='display: none'>\n"+
                        "<div id='YT-"+g.image.mpcId+"'></div>\n"+
                        "</div>\n");

      // Instantiate the YouTube object. This causes the <div to turn
      // into an <iframe.

      new YT.Player('YT-'+g.image.mpcId, {
        height: g.winHeight, // winHeight and winWidth are the current widths (globals)
                width: g.winWidth,
                videoId: g.image.mpcLoc, // The video ID on YouTube
                playerVars: { 'controls': 0, 'showinfo': 0, 'modestbranding': 1, 'rel': 0 }, // No controls
                events: {
                  'onReady': function(e) {
                    console.log("onPlayReady", e);
                    g.youtubeflag = true; // we only want to do onPlayStateChange PLAYING once
                    e.target.playVideo(); // fire off onPlayerStateChange()
                  },
                  'onStateChange': function(e) {
                    switch(e.data) {
                      case YT.PlayerState.PLAYING:
                        console.log("PLAYING", e, g.youtubeflag);

                        if(g.youtubeflag == false) {
                          // If we have been here once after playVideo() we don't want to
                          // do this again!
                          return;
                        }

                        g.youtubeflag = false; // No more until new video

                        var dur = Math.ceil(e.target.getDuration()) * 1000;
                        if(g.DEBUG) {
                          $("#info").append("<br>VidDur: "+dur);
                        }

                        console.log("YouTube duration:", dur);
                        // For type==youtube we don't use the adsInfo or the
                        // categories dur. This is taken care of in getAds() or
                        // getItem(). 
                        // If the dur for the item in the ads table is zero then use
                        // the actual length of the video. We ignore the default in
                        // adsInfo or categories.

                        if(g.image.mpcDur == null || g.image.mpcDur == 0) {
                          g.image.mpcDur = dur;
                        }
                        // Once we have the duration set the timeout. This is async
                        // and the fade out and fade in have already happened. The
                        // afterFadeIn() function does NOT set a time out!

                        g.item = $($("#show").children()[1]);
                        doEffect(function() {
                          g.timeoutid = setTimeout(slideshow, (g.image.mpcDur));
                        });
                        break;
                      case YT.PlayerState.PAUSED:
                        console.log("PAUSED: ", e);
                        break;
                      case YT.PlayerState.ENDED:
                        console.log("ENDED: ", e);
                        g.timeoutid = setTimeout(slideshow, 0);
                        break;
                      case YT.PlayerState.BUFFERING:
                        console.log("BUFFERING: ", e);
                        break;
                      case YT.PlayerState.CUED:
                        console.log("CUED: ", e);
                        break;
                      default:
                        console.log("DEFAULT: ", e);
                        break;
                    }
                  },
                      'onError': function(e) {
                    /*
                     * data property of e has the following values.
                     * 2 – The request contains an invalid parameter value.
                     * For example, this error occurs if you specify a
                     * video ID that does not have 11 characters, or if the
                     * video ID contains invalid characters, such as
                     * exclamation points or asterisks.
                     *
                     * 5 – The requested content cannot be played in an
                     * HTML5 player or another error related to the HTML5
                     * player has occurred.
                     *
                     * 100 – The video requested was not found.  This error
                     * occurs when a video has been removed (for any
                     * reason) or has been marked as private.
                     *
                     * 101 – The owner of the requested video does not
                     * allow it to be played in embedded players.  150 –
                     * This error is the same as 101. It's just a 101 error
                     *in disguise!
                     */

                    console.log("YT Error: ", e);
                    // remove the iframe container
                    $("#info").append("<br>YT ERROR: "+e.data);
                    $($("#show").children()[1]).remove()
                        g.timeoutid = setTimeout(slideshow, 0); // start up on next slide.
                  }
                }
      });

      // DON'T DO THE REST OF THE LOGIC!!!
      return; // the 'doEffect()' is done by the async onStateChange
  }
  
  g.item = $($("#show").children()[1]);

  // Determin if this is a dissolve or a fade

  doEffect(function() {
    g.timeoutid = setTimeout(slideshow, g.image.mpcDur);
  });
}

// Initial start up of slideshow.

function startUp() {
  console.log("StartUP");
  // slowCall() first does getInfo() and then gets photo, brand,
  // product and info

  $.ajax({
    url: g.ajaxfile,
    data: { name: 'startup', siteCode: g.siteCode, unit: g.unit, version: g.VERSION },
    dataType: 'json',
    type: 'get',
    success: function(data) {
           console.log("startup", data);
           g.startupId = data.id;
           slowCall(function() {
             // We have done everything. Photo was the last of the
             // getItems in slowCall. Now set g.StartUp to false and
             // start the slideshow.
             g.StartUp = false;
             return slideshow();
           });
         },
         error: function(err) {
           console.log("startup error:", err);
         }
  });
  
  console.log("End StartUp");
}

// Call Server for 'feature' and 'announce' categories

function fastCall(callback) {
  console.log('fastCall');

  if(g.DEBUG)
    $("#info").append("<br>fastCall");

  getInfo(function() {
    var num = 0;

    for(var i=0; i < 5; ++i) {
      num += parseInt(g.segments.announce[i]);
    }

    getItem({ category: 'announce', type: 'chron',
                num: num });
  
    getItem({ category: 'feature', type: 'rand',
                num: g.appInfo.features });

    if(typeof callback == 'function') return callback();
  });
}

// Communicate with the server
// Slower call for 'photo', 'brand', 'product' and 'info' categories

function slowCall(callback) {
  console.log('slowCall');
  if(g.DEBUG)
    $("#info").append("<br>slowCall");

  // Slow call always does a fastCall first and getBingo if we are
  // playing bingo.
  
  fastCall(function() {
    // For the categories in the segments table get the sum of the
    // segments for each category

    var num = { brand: 0, product: 0, info: 0, video: 0 };
    var c = ['brand', 'product', 'info', 'video'];
    var s = ['cs1','cs2','cs3','cs4','cs5'];
  
    for(var a in c) {
      if(typeof g.segments[c[a]] == 'undefined') {
        continue;
      }
    
      for(var i=0; i < 5; ++i) {
        num[c[a]] += parseInt(g.segments[c[a]][i]);
      }
    }

    getAds();
  
    getItem({ category: 'brand', type: 'rand',  // get these in chronological order: most recent first
            num: num.brand}, function() {
      getItem({ category: 'product', type: 'rand',
              num: num.product}, function() {
        getItem({ category: 'info', type: 'rand',
                num: num.info}, function() {
          getItem({ category: 'video', type: 'chron',
                  num: num.video}, function() {
            // photo MUST be the last one to run! Startup depends on it!
            getItem({ category: 'photo', type: 'rand',
                    num: g.appInfo.callbackTime, startupid: g.startupId }, function() {
              if(typeof callback == 'function') {
                return callback();
              }
            });
          });
        });
      });
    });
  });
}

// Get bingo

function getBingo() {
  if(g.DEBUG)
    $("#info").append("<br>getBingo");

  g.bingotimeout = null; // clear the setTimeout for call to getBingo
  
  $.ajax({
    url: g.ajaxfile,
    data: { name: 'getBingo', siteCode: g.siteCode, unit: g.unit },
    dataType: 'json',
    type: 'get',
    success: function(data) {
           console.log("getBingo", data);
           // data[0]: bingoGame , data[1]: array of [0]:itemId,
           // [1]:location
           g.bingoGame = data[0];
           $("#bingoinfo").html("Game #"+g.bingoGame);
           g.bingoFreq = data[1];
           g.bingoDrawNumber = data[2];
           
           g.images.bingo = new Array;
           
           for(var i=0; i<data[3].length; ++i) {
             var image = new Image;
             image.mpcId = data[3][i][0];
             image.mpcInx = i;
             if(i == data[3].length-1) {
               image.src = data[3][i][1];
             } else if(i != 0) {
               image.src = "bingoimage.php?loc="+
                           data[3][i][1] +"&game="+g.bingoGame+"&text=Photo No. "+i;
             } else {
               image.src = data[3][i][1];
             }
             
             $(image).load(function() {
               $(this).hide();
               g.images.bingo[this.mpcInx] = this;
             });

             $(image).error(function(err) {
               console.log(err);
             });
           }
           g.bingoOver = 'no';
           g.inx.bingo = 0;
         },
         error: function(err) {
           console.log("getBingo error:", err);
         }
  });
}

// Get the ADS and the adsInfo and adsSegments
// ONLY ads videos and videos use the mpcSkip and mpcSkipCntr!!!

function getAds() {
  // First get the adsInfo data
  
  $.ajax({
    url: g.ajaxfile,
    data: { name: 'getAdsInfo', siteCode: g.siteCode },
    dataType: 'json',
    type: 'get',
    success: function(data) {
           console.log(data);

           // At start up the adsInfo length is zero
           
           var startup = (g.adsInfo.length == 0) ? true : false;

           // When we first start up init the segInx, custCnt and
           // skipCnt.
           
           if(startup) {
             for(var i=0; i < data.length; ++i) {
               g.adsInfo[i] = data[i];
               g.adsInfo[i].segInx = 0;
               // Get the first entry from the segs array for num and
               // freq and put them in the customer and skip counters.
               // The ads logic in doCommercial() will step through the
               // rest of the segs array as each segment is finished.
               g.adsInfo[i].custCnt = null;
               if(g.adsInfo[i].segs != null) {
                 g.adsInfo[i].skipCnt = g.adsInfo[i].segs[0].freq;
               }
             }
           } else {
             for(var i=0; i<data.length; ++i) {
               g.adsInfo[i].adId = data[i].adId;
               g.adsInfo[i].dur = data[i].dur;
               g.adsInfo[i].trans = data[i].trans;
               g.adsInfo[i].effect = data[i].effect;
               if(g.adsInfo[i].segs != null) {
                 g.adsInfo[i].segs = data[i].segs;
               }
             }
           }

           // Now get the ADS

           $.ajax({
             url: g.ajaxfile,
             data: { name: 'getAds', siteCode: g.siteCode },
             dataType: 'json',
             type: 'get',
             success: function(data) {
                    console.log(data);

                    g.images.ads = new Array;
                    g.images.adsVid = new Array;
                    
                    g.nCust = data.nCust; // global, total number of ads customers

                    // zero out index and fix the custCnt

                    for(var i=0; i < g.nCust; ++i) {
                      g.inx.ads[i] = 0;
                    }

                    var adsList = data.adsList;
                    // adsList looks like:
                    // adsList[adId].num, adsList[adId].rows
                    // num is the number of items in rows
                    // rows {itemId, adId, time, desc, dur, type, loc}

                    $.each(adsList, function(adId, v) {
                      var num = v.num;
                      var rows = v.rows;   // rows of images

                      g.images.ads[adId] = new Array;
                      g.images.adsVid[adId] = new Array;
                      
                      if(num) {
                        var ad=0, vid=0;

                        $.each(rows, function(inx, row) {
                          var image = new Image,
                          dur, trans, effect;
                          
                          for(var i=0; i < g.adsInfo.length; ++i) {
                            var v = g.adsInfo[i];
                            if(v.adId == row.adId) {
                              dur = v.dur;
                              trans = v.trans;
                              effect = v.effect;
                              break;
                            }
                          }
                          
                          image.mpcTrans = (row.trans != 0) ? row.trans : trans;
                          image.mpcEffect = (row.effect != 'none') ? row.effect : effect;

                          image.mpcId = "ad" + row.itemId; // to diferentiate between ads and items
                          image.mpcTime = row.time;       // creation time as datetime string
                          image.mpcDesc = row.desc;       // text description of image
                          image.mpcType = row.type;

                          image.mpcDur = (row.dur && row.dur != 0) ? row.dur : dur;
                          image.mpcCat = 'ads';
                          
                          image.mpcAdId = row.adId;
                          image.mpcNum = num; // total number of rows

                          switch(image.mpcType) {
                            case 'image':
                              image.mpcInx = ad++; // image index in rows
                              image.src = g.CONTENTPREFIX + row.loc; // set the <img src="..."> to start network loading
                              break;
                            case 'html':
                              image.mpcLoc = row.loc;
                              g.images.ads[adId][ad] = image;
                              image.mpcInx = ad++; // image index in rows
                              break;
                            case 'filehtml':
                              $.get(g.CONTENTPREFIX + row.loc, function(data) {
                                image.mpcLoc = data;
                                g.images.ads[adId][ad] = image;
                                image.mpcInx = ad++; // image index in rows
                              });
                              break;
                            case 'video':
                              // Override mpcCat and mpcDur
                              image.mpcCat = 'adsVid';
                              image.mpcDur = row.dur; // Always use row.dur not dur

                              image.mpcSkip = row.skip; // ONLY for vidio!!!

                              // ONLY videos
                              // We only initialize mpcSkipCntr at startup after
                              // startup it is handled by the doCommercial/doVideo
                              // logic

                              if(startup) {
                                if(typeof g.adsSkipCntr[adId] == 'undefined') {
                                  g.adsSkipCntr[adId] = new Array;
                                }
                                g.adsSkipCntr[adId][vid] = image.mpcSkip; // Init the first time.
                              } 

                              if(row.loc.search(/^http:/i) !== -1) {
                                // Has full absolute path.
                                image.mpcLoc = row.loc;
                              } else {
                                // is local in adcontent so add the
                                // prefix to get us to the doc root.
                                image.mpcLoc = g.CONTENTPREFIX + row.loc;
                              }
                              g.images.adsVid[adId][vid] = image;
                              image.mpcInx = vid++; // image index in rows
                              break;
                            case 'youtube':
                              // Override mpcCat and mpcDur
                              image.mpcCat = 'adsVid';
                              image.mpcDur = row.dur; // Always use row.dur not dur

                              image.mpcSkip = row.skip; // ONLY for vidio!!!

                              // ONLY videos
                              // We only initialize mpcSkipCntr at startup after
                              // startup it is handled by the doCommercial/doVideo
                              // logic

                              if(startup) {
                                if(typeof g.adsSkipCntr[adId] == 'undefined') {
                                  g.adsSkipCntr[adId] = new Array;
                                }
                                g.adsSkipCntr[adId][vid] = image.mpcSkip; // Init the first time.
                              }
                              
                              image.mpcLoc = row.loc;
                              g.images.adsVid[adId][vid] = image;
                              image.mpcInx = vid++; // image index in rows
                              break;
                            default:
                              console.log("Error not valid type");
                              break;
                          }

                          // This only happens if there was an
                          // image.src set! It does not happend for
                          // types 'html', 'filehtml', 'video' or
                          // 'youtube'
                          
                          $(image).load(function() {
                            $(this).hide();
                            g.images.ads[adId][this.mpcInx] = this;
                          });

                          $(image).error(function(err) {
                            console.log(err);
                          });
                        });
                      }
                    });

                  }, error: function(err) {
                    console.log("getAds error: ", err);
                  }
           });
         }, error: function(err) {
           console.log("getAdsInfo error: ", err);
         }
  });
}

// Get a categories photos from server
// obj has: category, type, num

function getItem(obj, callback) {
  // category: photo|food|announce|...
  // type: random, chron, etc.
  // num: number of items. if zero then all

  $.ajax({
    url: g.ajaxfile,
    data: {
           name: 'getItem', type: obj.type, num: obj.num,
           category: obj.category,
           siteCode: g.siteCode,
           startupid: g.startupId
    },
    dataType: 'json',
    success: function(data) {
           if(data.error != 'OK') {
             if(g.DEBUG)
               $("#info").html("ERROR: " + data.error);
             
             console.log("ERROR: "+ data.error);
           }
    
           var rows = data.rows; // rows of images
           var cat = data.cat;   // local cat
           var num = data.num;   // number of items

           if(num) {
             g.images[cat] = new Array;
             g.inx[cat] = 0; // start of new array 7/32/2013

             $.each(rows, function(i, v) {
               var image = new Image;

               // Save some extra information with the image object for use by
               // the slideshow().

               image.mpcId = v.itemId;
               image.mpcTime = v.time;       // showTime as datetime string
               image.mpcDesc = v.desc;       // text description of image
               image.mpcType = v.type;       // image, text, html ...

               image.mpcTrans = (v.trans != 0) ? v.trans : g.categories[cat].trans;
               image.mpcEffect = (v.effect != 'none') ? v.effect : g.categories[cat].effect;

               // Use the row v.dur if it is pressent else use
               // categories[cat].dur

               if(v.dur && v.dur != 0) {
                 image.mpcDur = v.dur;
               } else {
                 image.mpcDur = g.categories[cat].dur;
               }

               image.mpcCat = cat;              // the images category like 'photo'

               switch(image.mpcType) {
                 case 'image':
                   image.mpcNum = num;
                   image.mpcInx = i;  

                   image.src = g.CONTENTPREFIX + v.loc; // set the <img src="..."> to start network loading
                                  // These callbacks only happen for images not for 'html',
                   // 'filehtml', 'video' or 'youtube' types.

                   // Once the image.src is set the image starts loading from the
                   // network. Once it is completely loaded and ready to render we
                   // hide it, increment the category's index, put the image into
                   // the 'images' array for the category, reset the category's
                   // index to zero if all of the images have loaded and restart
                   // the slide show if 'feature' or 'photo' finished loading.

                   $(image).load(function() {
                     $(this).hide();

                     g.images[this.mpcCat][this.mpcInx] = this;

                     if(this.mpcInx >= this.mpcNum-1) {
                       if(typeof callback == 'function') return callback();
                     }
                   });

                   $(image).error(function(err) {
                     console.log(err);
                   });

                   return;
                   
                 case 'html':
                   image.mpcLoc = v.loc;
                   g.images[cat][i] = image;
                   break;
                 case 'filehtml':
                   $.get(g.CONTENTPREFIX + v.loc, function(data) {
                     image.mpcLoc = data;
                     g.images[cat][i] = image;
                   });
                   break;
                 case 'video':
                   image.mpcDur = v.dur; // Override mpcDur with the row v.dur
                   
                   image.mpcSkip = v.skip; // ONLY for vidio!!!

                   if(g.StartUp === true) {
                     g.mpcSkipCntr[i] = image.mpcSkip; // Init the first time.
                   }

                   if(v.loc.search(/^http:/i) !== -1) {
                     // Has full absolute path.
                     image.mpcLoc = v.loc;
                   } else {
                     // is local in adcontent so add the
                     // prefix to get us to the doc root.
                     image.mpcLoc = g.CONTENTPREFIX + v.loc;
                   }
                   g.images[cat][i] = image;
                   break;
                 case 'youtube':
                   image.mpcDur = v.dur; // Override the mpcDur with the row v.dur

                   image.mpcSkip = v.skip; // ONLY for vidio!!!

                   if(g.StartUp === true) {
                     g.mpcSkipCntr[i] = image.mpcSkip; // Init the first time.
                   } 

                   image.mpcLoc = v.loc;
                   g.images[cat][i] = image;
                   break;
                 default:
                   console.log("Error not valid type");
                   break;
               }

               if(i >= num-1) {
                 if(typeof callback == 'function') return callback();
               }
             });
           } else {
             // $rows was empty

             if(cat == 'photo') {
               console.log("PHOTO rows empty");
             } else {
               // For all categories except 'photo' we clear the images array
               // for the category. Photos should really always have photos so
               // if we get a zero number that is really an error and we want
               // to keep the old photos and run from our cached data until we
               // get another bunch of photos.
        
               g.images[cat] = new Array;
             }
      
             console.log("Rows for catagory empty:", cat);
             if(typeof callback == 'function') return callback();
           }
         },
        error:function(err) {
           if(g.DEBUG)
             $("#info").append(err.responseText);
           
           console.log('getItem error:', err);
           if(typeof callback == 'function') return callback();
         }
  });
}

// Ajax getInfo. Initial ajax call to start everything

function getInfo(callback) {
  $.ajax({
    url: g.ajaxfile,
    data: {name: 'getInfo', siteCode: g.siteCode },
    dataType: 'json',
    success: function(data) {
           console.log("getInfo done", data);
           if(data.error != 'OK') {
             alert("ERROR: "+data.error);
             location.href = "http://go.myphotochannel.com";
           }                  
           g.allowAds = data.appInfo.allowAds;
           g.allowVideo = data.appInfo.allowVideo;
           g.playbingo = data.appInfo.playbingo;
           if(g.playbingo == 'yes' && g.bingotimeout === null && g.pusherChannel.subscribed === true) {
             getBingo();
           }

           g.playLotto = data.appInfo.playLotto;
           if(g.playLotto == 'yes') {
             var image = new Image;
             var r = Math.floor((Math.random()*10000)+1);
             image.src = g.CONTENTPREFIX+"content/lottowinner"+data.sites.siteId+".png?x="+r;

             $(image).load(function() {
               $(this).hide();

               g.images.lotto[0] = this;
             });
           }
           
           g.sites = data.sites;
           g.appInfo = data.appInfo; 
           g.segments = data.segments;
           g.categories = data.categories;

           $("#photoemailaddress").html("Email your photos to "+g.sites.emailUsername);
           
           if(typeof callback == 'function') {
             callback();
           }
         }, error: function(err) {
           console.log("getInfo error:", err);
           if(typeof callback == 'function') {
             callback();
           }
         }
  });
}

// Determin what category of image to show
// Every progDur interval we check the non-show categories.
// We loop through the available non-show categories and use the
// 'segments' table to determin what to show during the commercial
// break.
// returns category

function getCategory() {
  // inx.counter only increments after all the 'segments' commercials
  // are shown.

  var cat;
  
  if((g.inx.counter % g.appInfo.progDur) != 0) {
    // get the show category, either 'photo' or 'feature'
    
    ++g.inx.counter; // during the show we increment this counter but NOT during commercial breaks.
    cat = doShow();
  } else {
    // Time for commercials. This may still return a show if there are
    // not commercails to do. We do not increment inx.counter. It is
    // only incremented at the bottom after we have looked at all the
    // categories in the itemList.

    cat =  doCommercial()
  }
    
  return cat;
}

// Look to see if we can find a commercial break category
// look for a commercial item. If none then just do more photos/features
// Every commercial break we move to a new segments.csN
// returns category

function doCommercial() {
  var cat;

  g.inCB = true;
  
  // If segCount is non zero then we have found a category that has a
  // non zero csN value. Now we take images from the category until
  // we exaust segCount.

  if(g.segCount < 1) {
    // 'announce', 'brand', 'product', 'info', 'video' Done
    // itemCtr starts at zero every CB
    
    while(g.itemCtr < g.itemList.length) {
      cat = g.itemList[g.itemCtr]; // itemList is an array of categories ('announce','brand'...)
      // If cat is video then we use the skip instead of segments.
      if(cat == 'video') {
        var ret = doVideos();
        if(ret) return 'video';

        ++g.itemCtr;
        continue;
      }
      
      if(cat == 'bingo') {
        ++g.itemCtr;
        if(g.bingoGame == 0) {
          // between games
          continue;
        }
        return 'bingo';
      }

      if(cat == 'lotto') {
        ++g.itemCtr;
        if(g.playLotto == 'no') {
          continue;
        }
        return 'lotto';
      }
          
      ++g.itemCtr;
      
      // If there is no segments inforation for a category or no images
      // for that category skip forward to next category
      
      if(g.segments[cat] && g.images[cat].length != 0) {
        // There are images for this category
        // Do we show any of this category during this segment?

        if((g.segCount < 1) && (g.segments[cat].length != 0) && (g.segments[cat][g.segIndex] != 0)) {
          // First time for this category/segment
          
          g.segCount = (g.segments[cat][g.segIndex] > g.images[cat].length) ?
                       g.images[cat].length -1 : g.segments[cat][g.segIndex] -1; // we are returning this one so -1 to start
          
          g.segCat = cat;

          // segCount is now not zero so we will not come here again
          // until it is zero again.
          // Found a category with images and we should show it

          return cat; // first commercial category
        }
      }
    }

    // Done with the customers commercials now check if we are doing ADS
    
    if(g.allowAds == 'yes') {
      // Process customer
      // curCust starts at zero for each CB

      if(g.doAdsVid === false) {
        do {
          // is there an adsInfo for this customer index? If not skip
          // to next. 
          if(g.adsInfo[g.curCust].segs == null) continue;
          
          var segInx = g.adsInfo[g.curCust].segInx % g.adsInfo[g.curCust].segs.length;
          var num = g.adsInfo[g.curCust].segs[segInx].num;

          // We want to make sure the count is not greater than the
          // number of images.

          if(num > g.images.ads[g.curCust].length) {
            num = g.images.ads[g.curCust].length;
          }

          if(g.adsInfo[g.curCust].custCnt == null) {
            g.adsInfo[g.curCust].custCnt = num;
          }

          // If there are NO ads just decrement skipCnt to zero and  move to the next seg
          // Normaly if there num is zero freq should also be zero but
          // who knows.

          if(num == 0) {
            if(g.adsInfo[g.curCust].skipCnt < 1) {
              // The new segInx
              g.adsInfo[g.curCust].segInx = segInx = ++g.adsInfo[g.curCust].segInx %
                g.adsInfo[g.curCust].segs.length;

              g.adsInfo[g.curCust].custCnt = g.adsInfo[g.curCust].segs[segInx].num;
              if(g.adsInfo[g.curCust].custCnt > g.images.ads[g.curCust].length) {
                g.adsInfo[g.curCust].custCnt = g.images.ads[g.curCust].length;
              }

              g.adsInfo[g.curCust].skipCnt = g.adsInfo[g.curCust].segs[segInx].freq;
            } else {
              --g.adsInfo[g.curCust].skipCnt;
            }
            continue;
          }

          if(g.adsInfo[g.curCust].skipCnt < 1) {
            // Have we done all the ads for this customer?

            if(g.adsInfo[g.curCust].custCnt-- < 1) {
              // Yes we are done with this customer
              // increment this customers segInx

              g.adsInfo[g.curCust].segInx = ++g.adsInfo[g.curCust].segInx %
                                            g.adsInfo[g.curCust].segs.length;

              g.adsInfo[g.curCust].custCnt = g.adsInfo[g.curCust].segs[segInx].num;
              if(g.adsInfo[g.curCust].custCnt > g.images.ads[g.curCust].length) {
                g.adsInfo[g.curCust].custCnt = g.images.ads[g.curCust].length;
              }
              g.adsInfo[g.curCust].skipCnt = g.adsInfo[g.curCust].segs[segInx].freq;
              continue;
            }

            return 'ads';
          } else {
            --g.adsInfo[g.curCust].skipCnt;
          }
        } while(++g.curCust < g.nCust);
        
        g.doAdsVid = true;
        g.curCust = 0;
        for(var i=0; i < g.nCust; ++i) {
          g.inx.adsVid[i] = 0;
        }
        var ret = doAdsVideos();
        if(ret) return 'adsVid';
        
      } else {
        // ads vids
        var ret = doAdsVideos();
        if(ret) return 'adsVid';
      }

      // We have looped through all of the customers
      
      g.curCust = 0;
    }

    g.doAdsVid = false;
    g.featureCnt = 0; // reset feature count to zero at the start of each show
    g.itemCtr = 0;    // itemCtr back to start of list ready for next commercial break
    g.inx.video = 0;
    ++g.inx.counter;  // this is stalled while we do commercials. Start back with the show

    // The segIndex is indexed after every commercial break and is
    // circular going from 0 to NUM_SEGMENTS and then resetting to zero.

    g.segIndex = (++g.segIndex % Object.keys(g.segments).length);
    g.inCB = false;
    return doShow();
  } else {
    // We are working on a segment because segCount is not zero
    // We know there are images for this category because we check that
    // above.

    --g.segCount;
    cat = g.segCat; // the segments category we are working on.
    return cat;
  }
}

// Do CB Videos category
// returns true or false

function doVideos() {
  if(g.allowVideo == 'no') return false;
  // Look through all of the customers images.adsVid records

  while(g.inx.video < g.images.video.length) {
    if(g.mpcSkipCntr[g.inx.video] == 0) {
      // reset skip counter
      g.mpcSkipCntr[g.inx.video] = g.images.video[g.inx.video].mpcSkip;
      return true;
    } else {
      g.mpcSkipCntr[g.inx.video]--;
      ++g.inx.video;
    }
  }
  
  return false;
}

// Do Ads Videos
// returns true or false

function doAdsVideos() {
  if(g.allowVideo == 'no') return false;
  // Look through all of the customers images.adsVid records

  do {
    if(g.images.adsVid.length && g.images.adsVid[g.curCust].length) {
      // This customer has a video
      // inx.adsVid[curCust] was inited to zero above.

      var custAds = g.adsSkipCntr[g.curCust],
      cur = g.images.adsVid[g.curCust];

      for(var index = g.inx.adsVid[g.curCust]; index < custAds.length;
          index = ++g.inx.adsVid[g.curCust]) {
        
        if(custAds[index] == 0) {
          // reset skip counter
          custAds[index]=  cur[index].mpcSkip;

          return true;
        } else {
          --custAds[index];
        }
      }
    }
  } while(++g.curCust < g.nCust);
  return false;
}

// Do the SHOW
// returns 'photo' or 'feature'

function doShow() {
  // count down the number of features we show per show (defaults
  // to 20 if we have them which is the same as the 'progDuration'
  // 'featureCnt' is reset to zero at the start of each show, see
  // getCategory().
  var cat;

  if(g.pusherChannel.subscribed === true &&
     g.playbingo && g.bingoGame && g.images.bingo.length && (g.inx.counter % g.bingoFreq) == 0) {
    return 'bingo';
  }
  
  if(g.images.feature.length && (g.featureCnt < g.images.feature.length)) {
    // We have features. 'featureCnt' is zero at start of a show. We
    // check if it is less than the maximum number of features allowed
    // during a show and then increment it.
    
    if(g.featureCnt++ < g.appInfo.features) {
      // We have features and we have not reached the max number. For
      // example say we have two features, and the max is 6. We would
      // show those two feature photos six times and then start showing
      // photos. 
      cat = 'feature';
    } else {
      cat = 'photo';
    }
  } else {
    // No features so get photos
    cat = 'photo';
  }

  return cat;
}

// **********************************************************
// Wait till the DOM is built before starting the slide show.

jQuery(document).ready(function($) {
  // Set the initial window height and max width.

  g.winHeight = window.innerHeight - g.vidHeightExtra;
  g.winWidth = window.innerWidth - g.vidWidthExtra;

  var csshw = "<style id='csshw'>\n"+
              "img, .htmlitem {\n"+
              "height: "+(window.innerHeight - g.heightExtra)+"px;\n" +
              "max-width: "+(window.innerWidth - g.widthExtra)+ "px;\n"+
              "}\n" +
              "</style>";
  //, .youtubeitem iframe, .videoitem video
  $("head").append(csshw);
  
  $("head").append("<style>\n"+
                   "#debugitems { position: absolute; top: 10px; }\n"+
                   "</style>"
                  );

  // Add a start/stop button and some info for debug

  if(g.DEBUG) {
    $("body").append("<div id='debugitems'>");
    $("#debugitems").append("<button id='stopStart'>Stop</button>");
    $("#debugitems").append("<p id='info' />");

    var toggle = 1;

    $("#stopStart").click(function() {
      if(toggle++ % 2) {
        // stop it
        getStop(true);
        $(this).text("Start");
      } else {
        // start it
        $(this).text("Stop");
        getStop(false);
        slideshow();
      }
    });
  } 

  $("body").append("<div class='copyright'>");
  //$(".copyright").append("<p id='cinfo'/>");

  $(".copyright").append(g.COPYRIGHT + " " +g.VERSION);
  console.log(g.COPYRIGHT + " "+g.VERSION);

  $("body").append("<div id='bingoinfo'>");
  
  // On initial load put up loading spinner image
  
  var y = ((window.innerHeight - 100) / 2) +"px";
  var x = ((window.innerWidth - 100) / 2) + "px";
  console.log(x, y);

  $("#show").append("<img src='" +
                    "/images/loading.gif' style='position: fixed; top: " +
                     y + "; left: " +  x + "; width: 100px; height: 100px; border: none;'>");

  // Init lastItem so we can fade the spinner out.
  
  g.lastItem = $("#show :nth-child(1)");

  console.log("video types: ", g.videoTypes());
  startUp();
});
