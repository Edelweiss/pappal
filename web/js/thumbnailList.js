$(document).ready(function(){
  $('img.lazy').lazyload();
  $('img[title]').tipTip({keepAlive: true});
  $('span.memo').click(function(){
    var memoButton = $(this);
    var id = memoButton.attr('data');
    var action = memoButton.hasClass('memoActive') ? 'remove' : 'add';
    var urlAdd = window.location.href.substring(0, window.location.href.lastIndexOf('sample/list')) + 'memo/' + action + '/' +  id;
    $.getJSON(urlAdd, {}, function(data, status, jqXHR){
      if(status == 'success' && data.success == true){
        memoButton.toggleClass('memoActive');
      }
    });
  });
});