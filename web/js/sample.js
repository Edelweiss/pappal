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
    a.attr('title', a.find('div.uploadedImageTooltip').html());

  });
  $('a.uploadedImage').tipTip({keepAlive: true});

});

function sampleDeleteImage(path){
  if(confirm('Are you sure you want to delete this image?\nThis action cannot be undone.')){

    $.post(path, {}, function(data){
      if(data.success){
        $('a.uploadedImage[href*="' + data.data.image + '"]').fadeOut('slow', function(){
          var a = $(this);
          a.next('br').remove();
          a.remove();
        });
      } else {
        alert(data.error);
      }
    }, 'json');
  }
}

function sampleDeleteThumbnail(path){
  if(confirm('Are you sure you want to delete this thumbnail?\nThis action cannot be undone.')){

    $.post(path, {}, function(data){
      if(data.success){
        var thumbnail = data.data.thumbnail;

        $('img.sampleMini[src*="' + thumbnail + '"]').fadeOut('slow', function(){
          $('img.sampleMini[src*="' + thumbnail + '"]').remove();
          $('div[id$="' + thumbnail + '"]').remove();
        });
      } else {
         alert(data.error);
      }
    }, 'json');
  }
}

function sampleRotateThumbnail(path){
  $.post(path, {}, function(data){
    if(data.success){
      var thumbnail = data.data.thumbnail;
      $('img.sampleMini[src*="' + thumbnail + '"]').fadeOut('normal', function(){
          var image = $(this);
          var source = image.attr('src') + (image.attr('src').indexOf('?') > 0 ? '&' : '?') + Math.round(Math.random() * 1000);
          image.attr('src',  source).stop(true,true).fadeIn();
        });

    } else {
       alert(data.error);
    }
  }, 'json');
}