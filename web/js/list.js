$(document).ready(function(){
  $('img.lazy').lazyload();
  $('img[title]').tipTip({keepAlive: true});

  $('span.memo').click(function(){
    var memoButton = $(this);
    var href = memoButton.parent().children().filter('a:nth-child(1)').attr('href');
    var id = href.substring(href.lastIndexOf('show/') + 5);
    var action = memoButton.hasClass('memoActive') ? 'remove' : 'add';

    var urlAdd = window.location.href.substring(0, window.location.href.lastIndexOf('sample/list')) + 'memo/' + action + '/' +  id;
    $.getJSON(urlAdd, {}, function(data, status, jqXHR){
      if(status == 'success' && data.success == true){
        memoButton.toggleClass('memoActive');
        console.log('Thumbnail no. ' +  data.data.id + ' has been ' + (action == 'add' ? 'added to ': 'removed from') + ' shopping cart.');
      }
    });
    console.log(urlAdd);
  });

});