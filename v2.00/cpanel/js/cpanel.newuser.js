jQuery(document).on("pagebeforeshow", "#newuser", function(e, data) {
  // For the new Admin page.

  $("#newadminsubmit").click(function(e) {
    var fname, lname, password, email, sql;
    fname = $("#fname").val();
    lname = $("#lname").val();
    password = $("#password").val();
    email = $("#email").val();
    smsphone = $("#smsphone").val();
    smsprovider = $("#smsprovider").val();
    
    sql = "insert into users (siteId, fname, lname, password, email, "+
          "notifyPhone, notifyCarrier, status, visittime) " +
          "values('"+siteId+"', '"+fname+"', '"+lname+
          "', '"+password+"', '"+email+"', '"+smsphone+
          "', '"+smsprovider+"', 'admin', now())";

    console.log(sql);
    doSql(sql, function(data) {
      $.mobile.changePage("cpanel.php?siteId="+siteId);
    });
  });

  // Change back to the main page

  $("#homejames").on("click", function() {
    $("#home").remove();
    $.mobile.changePage("cpanel.php?siteId="+siteId);
    return false;
  });
});