// Approve Photos
jQuery(document).on("pagebeforeshow", "#approvephotos-page", function(e, data) {
  // Approve is called from the emailphoto.php with ?siteId=siteid in
  // the email sent to the admin
  
  if(location.search.indexOf('siteId') != -1) {
    siteId = location.search.match(/siteId=([^&]*)/)[1];
  }

  // Approve All, Clear All, Disapprove All Buttons
  
  $("#approveallnone").on("click", "button", function(e) {
    var id = $(this).attr("id"), cl, state;

    switch(id) {
      case "approveall":
        cl = ".approveyes";
        state = true;
        break;
      case "approvenone":
        cl = ".approveno";
        state = true;
        break;
      case "approveclear":
        cl = ".approveyes, .approveno";
        state = false;
        break;
      case "approvephotosOK":
        // If there is userId in then update visits in users

        if(userId) {
          sql = "update users set approved=approved+1, approvedtime=now() where id='" + userId + "'";
          
          console.log("userId", userId, sql);
          doSql(sql, function(data) {
            console.log("doSql", data);
          });
        }
        
        // loop through the tdapproveinput <td> and collect the radio
        // button info. The radio button values are 'yes' and 'no'

        var emails = new Array;
        
        $(".tdapproveinput input:checked").each(function(i, v) {
          var sql;
          var vv = $(v);
          var parent = vv.parents("tr");
          
          var val = vv.val(); // yes or no
          // The values are "approved-$id" so we want what is after the -,
          // the image Id.

          var id = v.name.match(/.*-(.*)/)[1];

          // val can only be yes or no because one of the two radios was
          // checked.

          if(val == 'no') {
            // no means 'inactive'
            sql = "update items set status='inactive' where itemId='"+id+"' and siteId='"+siteId+"'";
          } else if(val == 'yes') {
            // yes means 'active' and set the creation time to now to make
            // it a feature.
            var ext = parent.attr("data-ext");
            var email = parent.attr("data-email");

            if(ext != "no") {
              emails.push({ext: ext, email: email});
            }
              
            sql = "update items set status='active', showTime=now() " +
                  "where itemId='"+id+"' and siteId='"+siteId+"'";
          }

          // Send the sql to the Ajax program

          doSql(sql, function(data) {
            console.log("update", data);
            // Get the containing row <tr> and remove it.

            parent.remove();

            $("#approvePostedOK").popup("open", {x: 200, y: 200});
            setTimeout(function() {
              $("#approvePostedOK").popup("close");
            }, 3000);

            // Now see if we have removed all of the rows of the
            // table. If so Go home.

            if($("#approvephotostable tr").length == 0) {
              $.mobile.changePage("cpanel.php?siteId="+siteId);
            }
          });
        });

        $(emails).each(function(i, v) {
          // ext looks like "no" or "<type>,<days before
          // now>,<limit>,<days before now>"
          // for example "rand,10,3,1". That means use rand() for order
          // instead of showTime, where showTime > date-sub(now(),
          // interval 10 day) and showTime < date-sub(now(), interval 1
          // day), that is we search for three images that are more
          // recent than 10 days ago and less recent than 1 day ago.
          
          if(v.ext != 'no') {
            var ar = v.ext.split(","); // ar[0]:type, ar[1]:more-recent, ar[2]:limit, ar[3]:less-recent
            var order = "showTime";
            if(ar[0] == 'rand') {
              order = "rand()";
            }
            sql = "update items set showTime=now() "+
                  " where siteId='"+siteId+
                  "' and creatorName like('%"+v.email+
                  "%') and showTime > date_sub(now(), interval "+ar[1]+
                  " day) and showTime < date_sub(now(), interval "+ar[3]+
                  " day) order by "+order+
                  " limit "+ar[2];
            
            doSql(sql, function(data) {
              console.log("update", data);
            });
          }
        });
        
        console.log("Post Done");
        break;
    }
    console.log("id ", id, " cl", cl, state)
    $(cl).prop("checked", state);
  });

  // Load the photos
  
  $("#approvephotoshere").load(ajaxfile,
                               { page: 'approvephotos', siteId: siteId },
                               function(data) { console.log("approvephotos OK"); });

  // Rotate photo

  $("#approvephotoshere").on('click', 'img', function(e) {
    var x = $(this);
    var imagename = x.prop('src');
    var id = x.prop('id');

    $.ajax({
      url: ajaxfile,
      data: { page: 'rotate', itemId: id, image: imagename },
      type: 'post',
      success: function(data) {
             console.log(data);
             var image = new Image;
             image.width = $("#"+id).attr('width'); // img width from approvephotos in cpanel.ajax.php
             image.src = SITE_DOMAIN + data;

             $(image).load(function() {
               $(this).prop("id", id);
               var y = x.parent();
               y.html(this);
             });
           }
    });
  });

  // Change back to the main page

  $("#homejames").on("click", function() {
    $("#home").remove();
    $.mobile.changePage("cpanel.php?siteId="+siteId);
    return false;
  });
});
