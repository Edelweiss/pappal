$(document).ready(function(){

  $('div.memoItem').draggable({
      start: function(event, ui){
        $(this).find('img').each(function(){
          $(this).tipTip('hide');
        });
      }
  });

  $('div.memoItem').mouseenter(function(){
    $(this).find('.remove').show();
  });

  $('div.memoItem').mouseleave(function(){
    $(this).find('.remove').hide();
  });

  $('div.memoItem .remove').click(function(){
    var item = $(this).parent();
    var id = $(this).attr('data');
    var url = window.location.href + '/remove/' +  id;
    $.getJSON(url, {}, function(data, status, jqXHR){
      if(status == 'success' && data.success == true){
        item.remove();
      }
    });
  });

  $('img[title]').tipTip({keepAlive: false});

});