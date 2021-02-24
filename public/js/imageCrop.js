$(function(){
  $('#crop').Jcrop({aspectRatio: 1, onSelect: saveCoordinates});
});

function saveCoordinates(c){
  $('#imageX').val(c.x);
  $('#imageY').val(c.y);
  $('#imageX2').val(c.x2);
  $('#imageY2').val(c.y2);
  $('#imageW').val(c.w);
  $('#imageH').val(c.h);
}
