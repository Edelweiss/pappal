$(function(){

  // master thumbnail
  $('img.sampleMini').click(function(){
    var image = $(this).attr('src');
    $('#sample').attr('src', image);
    $('#masterThumbnail').val(image.match(/\/([^\/]+\.jpg)(\?\d+)?/)[1]);
  });

  // delete action
  $('#buttonDelete').click(function(){
    if(confirm('Are you sure you want to delete this record?\nThis action cannot be undone.')){
      window.location.href = this.value;
    }
  });

  // tooltips for thumbnails
  $('img.sampleMini').each(function(index){
    var image = $(this);
    image.attr('title', image.next('div.sampleMiniTooltip').html());
  });
  $('img.sampleMini').tipTip({keepAlive: true});

  // tooltips for image
  $('a.uploadedImage').each(function(index){
    var a = $(this);
    a.attr('title', a.next('div.uploadedImageTooltip').html());
  });
  $('a.uploadedImage').tipTip({keepAlive: true});

});