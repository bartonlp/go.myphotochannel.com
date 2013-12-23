// Text To TV

jQuery(document).on("pagebeforeshow", "#texttotv", function(e, data) {
  var textfile = false;

  // Use a file on the client

  $("#messagefile").change(function() {
    textfile = true;
    
    var filelist = $(this).prop('files');
    if(filelist.length === 0) {
      $("#status").html("<h2 style='color: red'>No file selected</h2>");      
      return;
    }

    var reader = new FileReader();

    var file = filelist[0];

    reader.desc = file.name;
    reader.readAsText(file);

    // Reader onload callback

    reader.onload = function (e) {
      var desc = this.desc;
      var data = e.target.result; // this is the data uri
      $("#messagetextentry").val(data);
      $("#saveastext").trigger('click');
    }
    return false;
  });

  // Count character up to 130
  
  $("#texttotvcontent").on("keyup", "#messagetextentry", function(e) {
    var len, left, limit=130;
    var text = $(this).val();

    len = $(this).val().length;

    left = ((limit - len) > -1) ? limit - len : -1;

    $("#remainingchar").text(left);

    // e.which is the character and 8 is backspace
    
    if((left < 0) && (e.which != 8)) {
      $("#remainingchar").html("<span stype='color: red'>No Characters Remaining</span>");
      text = text.replace(/.$/, '');
      $(this).val(text);
      return false; // Don't put the character in the textarea
    }
    return true;
  });

  // Save as text button
  
  $("#saveastext").on('click', function(e) {
    var text = $("#messagetextentry").val();
    
    $("#imagepreview").html("<p>Your Text Will Look Like This:</p><div width='600' style='padding: 20px;border: 3px solid white'>"+text+"</div>");
    $("#sendimagediv").hide();
    $("#sendtextdiv").show();
    
    $("#sendtext").on('click', function(e) {
      var page = "saveTextAnnounce";
      if(textfile) page = "saveTextFileAnnounce";
     
      $.ajax({
        url: ajaxfile,
        data: { page: page, text: text, siteId: siteId },
        type: 'post',
        success: function(data) {
               console.log(data);
               $("#alldone").popup("open", {x: 100, y: 100});
               setTimeout(function() {
                 $.mobile.changePage("cpanel.php?siteId="+siteId);
               }, 5000);
             }
      });
    });
    return false;
  });

  // Save as Image button
  
  $("#saveasimage").on('click', function(e) {
    var dataUri = createImage($("#messagetextentry").val()); // createImage() is in cpanel.js

    $("#imagepreview").html("<p>Your Image Will Look Like This</p><img width='600' src='"+dataUri+"'>");
    $("#sendtextdiv").hide();
    $("#sendimagediv").show();

    $("#sendimage").on('click', function(e) {
      $.ajax({
        url: ajaxfile,
        data: { page: 'saveImageAnnounce', image: dataUri, siteId: siteId },
        type: 'post',
        success: function(data) {
               console.log(data);
               $("#alldone").popup("open", {x: 100, y: 100});
               setTimeout(function() {
                 //$("#alldone").popup("close");
                 $.mobile.changePage("cpanel.php?siteId="+siteId);
               }, 5000);
             }
      });
    });
    return false;
  });
});
