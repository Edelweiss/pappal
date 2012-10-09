slider = new Array();
sliderOptions = {
  bigWidth:     300,
  bigHeight:    300,
  bigHeight2:   332,
  smallWidth:   150,
  smallHeight:  150,
  smallHeight2: 332,
  fontBig:      16,
  fontSmall:    12
};
actual = 1;

function equilibrate() {
  var from = Math.max(actual - 2, 0);
  var to = Math.min(actual + 4, slider.length);

  //console.log('slider: ' + slider);
  //console.log('from:' + from + ' actual: ' + actual + ' to: ' +  to);

	$('#slider ul li').slice(from, to).each(function(i) {
		i = i + from;
		var d = Math.abs(actual - i);
		var a = $(this);
		var img = a.children('a').children('img');
		var title = a.children('.imtitle');
		switch(d){
			case 0:
			  //console.log('i:' + i + ' d: ' + d + ' case: 0');
			  lazyloadImage(img);
				//img.animate({width: sliderOptions.bigWidth, height: sliderOptions.bigHeight});
				img.css({width: sliderOptions.bigWidth, height: sliderOptions.bigHeight});
				//a.animate({width: sliderOptions.bigWidth, height: sliderOptions.bigHeight2}, function () { title.css({'font-size': sliderOptions.fontBig+'px'}); });
				a.css({width: sliderOptions.bigWidth, height: sliderOptions.bigHeight2}, function () { title.css({'font-size': sliderOptions.fontBig+'px'}); });
				showtext(a.attr('id').substr(6));
			break;
			case 1:
			  //console.log('i:' + i + ' d: ' + d + ' case: 1');
				title.css({'font-size': sliderOptions.fontSmall+'px'});
				if (slider[i] == 1) {
					slider[i] = 0;
					a.css({display: 'inline', width: '0px', height: '0px'});
					img.css({width: '0px', height: '0px'});
					title.css({opacity: '0'});
				}
				lazyloadImage(img);
				//img.animate({width: sliderOptions.smallWidth, height: sliderOptions.smallHeight}, function () { title.fadeTo(500, 1); });
				img.css({width: sliderOptions.smallWidth, height: sliderOptions.smallHeight});
				a.animate({width: sliderOptions.smallWidth, height: sliderOptions.smallHeight2}, function () { title.fadeTo(500, 1); });
				//a.css({width: sliderOptions.smallWidth, height: sliderOptions.smallHeight2}, function () { title.fadeTo(500, 1); });
			break;
			default:
			  //console.log('i:' + i + ' d: ' + d + ' case: default');
				slider[i] = 1;
				a.hide('fast');
			break;
		}
	});
	
	$('span#number').html(actual);

}
function actual_change(diff){
	var newActual = actual + diff;
	if(newActual > 0 && newActual < slider.length) {
		actual = newActual;
		return true;
	}
	return false;
};

function showtext(id) {
  var text = $('#text' + id);
  text.prev().hide();
  text.next().hide();
  //text.slideDown('slow');
  text.show();
}

function lazyloadImage(image){
  if(image.attr('data-original') != image.attr('src')){
    image.attr('src', image.attr('data-original'));
  }
}

$(document).ready(function(){

	$('#slider ul').prepend('<li><a><img src="../../images/eo/vide.gif" alt="" /></a></li>');
	var max = $('#slider ul li').size();
	for(var i = 0; i < max; i++){
	  if(i < 3){
	    slider[i] = 0;
	  } else {
	    slider[i] = 1;
	  }
	}

  $('#slider ul li:nth-child(1)').each(function(i){
    var a = $(this);                           // li element
    var img = a.children('a').children('img'); // img element (placeholder needs to be replaced by actual image data)
    var title = a.children('.imtitle');        // a element containing the title
    lazyloadImage(img);
    img.css({width: sliderOptions.smallWidth+'px', height: sliderOptions.smallHeight+'px'});
    a.css({display: 'list-item', width: sliderOptions.smallWidth+'px', height: sliderOptions.smallHeight2+'px'});
  });

	$('#slider ul li:nth-child(2)').each(function(i){
    var a = $(this);                           // li element
    var img = a.children('a').children('img'); // img element (placeholder needs to be replaced by actual image data)
    var title = a.children('.imtitle');        // a element containing the title
	  lazyloadImage(img);
    title.css({'font-size': sliderOptions.fontBig+'px'});
    img.css({width: sliderOptions.bigWidth+'px', height: sliderOptions.bigHeight+'px'});
    a.css({display: 'list-item', width: sliderOptions.bigWidth, height: sliderOptions.bigHeight2+'px'});
    showtext(a.attr('id').substr(6));
	});

	$('#slider ul li:nth-child(3)').each(function(i){
    var a = $(this);                           // li element
    var img = a.children('a').children('img'); // img element (placeholder needs to be replaced by actual image data)
    var title = a.children('.imtitle');        // a element containing the title
    lazyloadImage(img);
    img.css({width: sliderOptions.smallWidth+'px', height: sliderOptions.smallHeight+'px'});
    a.css({display: 'list-item', width: sliderOptions.smallWidth+'px', height: sliderOptions.smallHeight2+'px'});
  });

	$('#butleft').click(function(){
		if(actual_change(-1)){
		  equilibrate();
		}
	});

	$('#butright').click(function(){
		if(actual_change(1)){
		  equilibrate();
		}
	});

});