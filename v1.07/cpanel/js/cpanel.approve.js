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

        $(".tdapproveinput input:checked").each(function(i, v) {
          var sql;
          var val = $(v).val(); // yes or no
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
            sql = "update items set status='active', creationTime=now() where itemId='"+id+"' and siteId='"+siteId+"'";
          }

          // Send the sql to the Ajax program

          doSql(sql, function(data) {
            console.log("update", data);
            // Get the containing row <tr> and remove it.

            $(v).parents("tr").remove();

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
        console.log("Post Done");
        break;
    }
    console.log("id ", id, " cl", cl, state)
    $(cl).prop("checked", state);
  });

  // Load the photos
  
  $("#approvephotoshere").load(ajaxfile,
                               { page: 'approvephotos', siteId: siteId },
                               function(data) { console.log(data); });

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
});
