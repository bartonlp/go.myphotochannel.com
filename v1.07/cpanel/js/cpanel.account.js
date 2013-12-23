// Account

jQuery(document).on("pagebeforeshow", "#account", function(e, data) {
  var userStatus;
  var users = {};
  var selectId;

  // Make sure the user is the owner. Only the owner has access to this
  // panel.

  if(!superuser) { // superuser has full rights all the time
    if(!userId) {
      // Ask for the user to login
      $.mobile.changePage("cpanel.login.php?siteId="+siteId);
      return false;
    } else {
      doSql("select status from users where id='"+userId+"' and siteId='"+siteId+"'", function(data) {
        userStatus = data.rows[0].status;
        if(userStatus != "member") {
          var exdate = new Date();
          // This should invalide the userId cookie
          exdate.setDate(exdate.getDate() - 365); // subtract one years worth of days, 365.
          var c_value = escape(userId) + "; expires=" + exdate.toUTCString();
          document.cookie = "userId" + "=" + c_value;

          $("#notowner").popup("open", {x: 100, y: 100});
          setTimeout(function() {
            userId = '';
            $.mobile.changePage("cpanel.php?siteId="+siteId);
          }, 5000);
          return;
        }
        $.mobile.changePage("cpanel.account.php?siteId="+siteId);
      });
    }
  }

  // Initialize the 'Select User' <select> options

  doSql("select * from users where siteId='"+siteId+"'", function(data) {
    var opt = '';
    $.each(data.rows, function(i, v) {
      users[v.id] = {
        id: v.id,
        name: v.fname+" "+v.lname,
        password: v.password,
        email: v.email,
        smsphone: v.notifyPhone,
        smsprovider: v.notifyCarrier,
        status: v.status,
        emailNotify: v.emailNotify,
        textNotify: v.textNotify
      };

      opt += "<option value='"+v.id+"'>"+v.fname+" "+v.lname+"</option>";
    });

    // Set initial values for the first in the select
    
    $("#userselect").html(opt);
    $("#userselect").selectmenu("refresh");
    $("#userselect").trigger("change");
  });

  
  // Get the name of the user we are going to work on from 'userselect'
  // <select> options when they change
  
  $("#userselect").change(function(e) {
    selectId = $(this).val();

    // The 'users' object was initialized above in the doSql
    
    $(".username").html(" for <i>'"+users[selectId].name+"'</i>");

    // Set current user status

    $("#newpassword").val('');
    $("#confirm").val('');
    
    $("#changestatusgroup input[value='"+users[selectId].status+"']").attr("checked", "true");
    $("#changestatusgroup input[type='radio']").checkboxradio("refresh");

    $("#changeemailnotify input[value='"+users[selectId].emailNotify+"']").attr("checked","true");
    $("#changeemailnotify input[type='radio']").checkboxradio("refresh");

    $("#changetextnotify input[value='"+users[selectId].textNotify+"']").attr("checked","true");
    $("#changetextnotify input[type='radio']").checkboxradio("refresh");

    $("#emailaddress").val(users[selectId].email);
    $("#smsphone").val(users[selectId].smsphone);
    $("#smsprovider").val(users[selectId].smsprovider);
    $("#smsprovider").selectmenu("refresh");
  });

  // Submit the page

  $("#accountOK").click(function(e) {
    if($("#newpassword").val() != '') {
      if($("#newpassword").val() == $("#confirm").val()) {
        var password = $("#newpassword").val();
      } else {
        alert("passwords do not match");
        return false;
      }
    }
    var email = $("#emailaddress").val(),
    smsphone = $("#smsphone").val(),
    smsprovider = $("#smsprovider").val(),
    status = $("#changestatusgroup input:checked").val(),
    emailNotify = $("#changeemailnotify input:checked").val(),
    textNotify = $("#changetextnotify input:checked").val();
    var sql = "update users set email='"+email+"', notifyphone='"+smsphone+"', notifyCarrier='" +
              smsprovider+"', status='"+status+"', emailNotify='"+emailNotify+"', textNotify='" +
              textNotify+"'";
    if(typeof password != 'undefined') {
      sql += ", password='"+password+"'";
    }
    sql += " where id='"+$("#userselect").val()+"'";
    console.log("sql",sql);
    doSql(sql, function(data) {
      console.log("accountOK", data);
    });
    return false;
  });
  
});

// This is the LOGIN page for account

jQuery(document).on("pagebeforeshow", "#login", function(e, data) {
  // This is from cpanel.login.php

  $("#loginsubmit").click(function(e) {
    var emailaddress, password, userStatus;
    emailaddress = $("#loginemailaddress").val();
    password = $("#loginpassword").val();

    var sql = "select * from users where siteId='"+siteId+"' and email='"+emailaddress+
              "' and password='"+password+"'";

    doSql(sql, function(data) {
      console.log("DATA:", data);
      if(data.num) {
        // set cookie
        // load main page
        var userId = data.rows[0].id;
        var siteId = data.rows[0].siteId;

        var exdate = new Date();
        exdate.setDate(exdate.getDate() + 365); // add one years worth of days, 365.
        var c_value = escape(userId) + "; expires=" + exdate.toUTCString();
        document.cookie = "userId" + "=" + c_value;
        var c_value = escape(siteId) + "; expires=" + exdate.toUTCString();
        document.cookie = "SiteId" + "=" + c_value;

        document.location.href = "cpanel.account.php?siteId="+siteId;
      } else {
        // Display error
        $("#error").html("<h2>Incorect Email address and Password. "+
                         "These values are case sensitive.</h2>");

        $("#error").css({backgroundColor: 'red', color: 'white' });

        setTimeout(function() {
          $("#error").css({backgroundColor: 'white', color: 'black' });
        }, 10000);
      }
      return false;
    });
  });
});

