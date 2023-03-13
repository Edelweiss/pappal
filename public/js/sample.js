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

function sampleSetMasterThumbnail(path){
  $.post(path, {}, function(data){
    if(data.success){
      var languageCapitalised = data.data.language.charAt(0).toUpperCase() + data.data.language.slice(1);
      var imageId = 'thumbnail' + languageCapitalised
      var images  = $('#' + imageId);

      if(images.length){
        image = images.first();
        var source = image.attr('src') + (image.attr('src').indexOf('?') > 0 ? '&' : '?') + Math.round(Math.random() * 1000);
        image.attr('src',  source).stop(true,true).fadeIn();
      } else {
        var src = (path.indexOf('/pappal.dev') == 0 ? '/pappal.dev' : '' ) + '/thumbnail/' + data.data.asset;
        var language = {
          'grc': 'Griechisch',
          'lat': 'Lateinisch',
          'cop': 'Koptisch',
          'egy': 'Demotisch',
          'ara': 'Griechisch'
        }[data.data.language];
        $('div#thumbnail').html($('div#thumbnail').html() + '<img width="300" alt="' + language + '" title="' + language + '" src="' + src + '" id="' + imageId + '"/>');
      }

    } else {
      alert(data.error);
    }
  }, 'json');
}