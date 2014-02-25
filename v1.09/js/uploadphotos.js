// Works with uploadphotos.php

var ajaxfile = "uploadphotos.php";
var fileInx = 0;

function send(filelist) {
  var reader = new FileReader();

  if(fileInx >= filelist.length) {
    // We are done.
    $("#status").html("<h2>Upload Done</h2>");
    return;
  }
  
  var file = filelist[fileInx++];
  
  reader.desc = file.name;
  reader.readAsDataURL(file);

  // Reader onload callback

  reader.onload = function(e) {
    var desc = this.desc;
    var url = e.target.result; // this is the data uri
    var image = new Image;
    image.src = url;

    // wait for the image to load.

    $(image).load(function() {
      var wi = w = image.width;
      var hi = h = image.height;

      var c = document.createElement('canvas');
      if((wi * hi) > 500000) {
        w = 600/(hi/wi);
        h = w*hi/wi;
      }

      c.width = w; // make canvas big enough
      c.height = h;

      var ctx = c.getContext("2d");
      ctx.drawImage(image, 0, 0, w, h); // origine 0x0 with width and height as scaled
      var dataUri = c.toDataURL(); // get the base64 URI
      $("#uploadPreview").attr('src', dataUri);
      $("#filename").text(desc);
      
      // Send the dataUri to the server to process

      $.ajax({
        url: ajaxfile,
        data: { page: 'upload', data: dataUri, desc: desc, siteId: siteId },
        type: 'post',
        success: function(data) {
               console.log(data);
               return send(filelist);
             },
             error: function(err) {
               console.log(err.responseText);
               return;
             }
      });
    });
  }
}

jQuery(document).ready(function($) {
  // When new images are added to the <input type='file'...

  var timeout;

  $("#uploadImage").change(function() {
    var filelist = $(this).prop('files');
    if(filelist.length === 0) {
      $("#status").html("<h2 style='color: red'>No file selected</h2>");      
      return;
    }
    $("#status").html("<h2>Starting Upload Please Wait</h2>");
    send(filelist);
  });
});
