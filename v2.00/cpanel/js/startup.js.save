// Start up script. At this point jQuery is loaded.

var siteId, userId, superuser,
nocache = "nocache=" + (new Date()).getTime(),
CONTENTPREFIX="/kunden/homepages/45/d454707514/htdocs/",
SITE_DOMAIN="http://go.myphotochannel.com/";


if(location.search.indexOf('superuser') != -1) {
  superuser = location.search.match(/superuser=([^&]*)/)[1];
}

if(location.search.indexOf('debug') != -1) {
  superuser = location.search.match(/debug=([^&]*)/)[1];
  var sql = "select id, concat(fname, ' ', lname) as name from superuser where password = '"+superuser+"'";
  $.ajax({
    url: "cpanel.ajax.php",
    data: { page: 'doSql', sql: sql },
    dataType: 'json',
    type: 'post',
    success: function(data) {    
           if(data.num == 1) {
             console.log("superuser: ", data.rows[0].name);
             document.location.href = "getsite.html?superuser="+superuser+'&'+nocache;
           }
         },
         error: function(err) {
           console.log("ERROR: ", err);
         }
  });
}

if(location.search.indexOf('siteId') != -1) {
  siteId = location.search.match(/siteId=([^&]*)/)[1];
} else {
  if(document.cookie.indexOf('SiteId') != -1) {
    siteId = document.cookie.match(/SiteId=([^;]*)/)[1];
  }
}

if(location.search.indexOf('userId') != -1) {
  userId = location.search.match(/userId=([^&]*)/)[1];
}

$("link").after('<link rel="stylesheet" href="css/cpanel.photoadmin.css?'+nocache+'">');

$("#startupscript").after("<script src='js/cpanel.js?"+nocache+"'></script>",
                          "<script src='js/cpanel.tv.js?"+nocache+"'></script>",
                          "<script src='js/cpanel.account.js?"+nocache+"'></script>",
                          "<script src='js/cpanel.approve.js?"+nocache+"'></script>",
                          "<script src='js/cpanel.channel.js?"+nocache+"'></script>",
                          "<script src='js/cpanel.commercial.js?"+nocache+"'></script>",
                          "<script src='js/cpanel.display.js?"+nocache+"'></script>",
                          "<script src='js/cpanel.expunge.js?"+nocache+"'></script>",
                          "<script src='js/cpanel.newuser.js?"+nocache+"'></script> ",
                          "<script src='js/cpanel.segment.js?"+nocache+"'></script>",
                          "<script src='js/cpanel.photoadmin.js?"+nocache+"'></script>"
                         );

// For each page on 'pageinit' fixup links etc.

// ----------------------
// CPANEL HOME

jQuery(document).on("pagebeforeshow", "#home", function(e, data) {
  nocache = "nocache="+(new Date()).getTime();
  
  if(siteId) {
    // The first time the ul will not be there so add this info
    if(!$("#homemainmenu ul[data-role='listview']").length) {
      $("#homemainmenu").prepend('<ul data-role="listview" data-inset="true">'+
                              '<li style="display: none" id="approvephotos">'+
                              '<a href="cpanel.approve.html?siteId='+siteId+'&'+nocache+'">'+
                              'Approve Photos (<span id="numtoapprove"></span>)</a></li>'+
                              '<li style="display: none" id="deletephotos">'+
                              '<a href="cpanel.expunge.html?siteId='+siteId+'&'+nocache+'">'+
                              'Remove Photos Marked Deleted (<span id="numtodelete"></span>)</a></li>'+
                              '<li><a href="cpanel.tv.html?siteId='+siteId+'&'+nocache+'">'+
                              'Text to Channel</a></li>'+
                              '<li><a href="cpanel.photoadmin.html?siteId='+siteId+'&'+nocache+'">'+
                              'Manage Content</a></li>'+
                              '<li><a href="cpanel.channel.html?siteId='+siteId+'&'+nocache+'">'+
                              'Channel Settings</a></li>'+
                              '<li><a href="cpanel.account.html?siteId='+siteId+'&'+nocache+'">'+
                              'Account Maintenance</a></li>'+
                              '</ul>');

      $("#home").trigger('create');
    }
  } else {
    $("body").html("<h1>NO SITE ID</h1>");
  }
});

// ----------------------
// TEXTTTOTV

jQuery(document).on("pagebeforeshow", "#texttotv", function(e, data) {
  nocache = "nocache="+(new Date()).getTime();
  
  $("div[data-role='header'] a[href='cpanel.html']").attr('href',
    'cpanel.html?siteId='+siteId);
});
