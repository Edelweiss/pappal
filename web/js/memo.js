$(document).ready(function(){

  $('div.memoItem').draggable({
      start: function(event, ui){
        $(this).find('img').each(function(){
          $(this).tipTip('hide');
        });
      }
  });

  $('img[title]').tipTip({keepAlive: false});
  
/*  $('div.memoItem > a > img').each(function(){
    
    $(this).load(function(){
      $(this).mlens( {
        "imgSrc": $(this).attr('src'),
        "lensSize": 120,
        "lensShape": "circle"
      });
    });
    
  });*/

});