(function(d){d.fn.tipTip=function(g){var k={activation:"hover",keepAlive:false,maxWidth:"200px",edgeOffset:0,defaultPosition:"bottom",delay:400,fadeIn:200,fadeOut:200,attribute:"title",content:false,enter:function(){},afterEnter:function(){},exit:function(){},afterExit:function(){},cssClass:"",detectTextDir:true};if(d("#tiptip_holder").length<=0){var i=d("<div>",{id:"tiptip_arrow_inner"}),j=d("<div>",{id:"tiptip_arrow"}).append(i),h=d("<div>",{id:"tiptip_content"}),f=d("<div>",{id:"tiptip_holder"}).append(j).append(h);d("body").append(f)}else{var f=d("#tiptip_holder"),h=d("#tiptip_content"),j=d("#tiptip_arrow")}return this.each(function(){var q=d(this),p=q.data("tipTip"),n=p&&p.options||d.extend({},k,g),m={holder:f,content:h,arrow:j,options:n};if(p){switch(g){case"show":s();break;case"hide":r();break;case"destroy":q.unbind(".tipTip").removeData("tipTip");break;case"position":l()}}else{var o=false;q.data("tipTip",{options:n});if(n.activation=="hover"){q.bind("mouseenter.tipTip",function(){s()}).bind("mouseleave.tipTip",function(){if(!n.keepAlive){r()}else{f.one("mouseleave.tipTip",function(){r()})}})}else{if(n.activation=="focus"){q.bind("focus.tipTip",function(){s()}).bind("blur.tipTip",function(){r()})}else{if(n.activation=="click"){q.bind("click.tipTip",function(t){t.preventDefault();s();return false}).bind("mouseleave.tipTip",function(){if(!n.keepAlive){r()}else{f.one("mouseleave.tipTip",function(){r()})}})}else{if(n.activation=="manual"){}}}}}function s(){if(n.enter.call(q,m)===false){return}var t;if(n.content){t=d.isFunction(n.content)?n.content.call(q,m):n.content}else{t=n.content=q.attr(n.attribute);q.removeAttr(n.attribute)}if(!t){return}h.html(t);f.hide().removeAttr("class").css({"max-width":n.maxWidth});if(n.cssClass){f.addClass(n.cssClass)}l();if(o){clearTimeout(o)}o=setTimeout(function(){f.stop(true,true).fadeIn(n.fadeIn)},n.delay);d(window).bind("resize.tipTip scroll.tipTip",l);q.addClass("tiptip_visible");n.afterEnter.call(q,m)}function r(){if(n.exit.call(q,m)===false){return}if(o){clearTimeout(o)}f.fadeOut(n.fadeOut);d(window).unbind("resize.tipTip scroll.tipTip");q.removeClass("tiptip_visible");n.afterExit.call(q,m)}function l(){var N=q.offset(),u=N.top,G=N.left,Q=q.outerWidth(),v=q.outerHeight(),y,J,L=f.outerWidth(),D=f.outerHeight(),I,P={top:"tip_top",bottom:"tip_bottom",left:"tip_left",right:"tip_right"},F,K,x=12,R=12,B=d(window),C=B.scrollTop(),E=B.scrollLeft(),O=B.width(),H=B.height(),z=n.detectTextDir&&e(h.text());function M(){I=P.top;y=u-D-n.edgeOffset-(R/2);J=G+((Q-L)/2)}function w(){I=P.bottom;y=u+v+n.edgeOffset+(R/2);J=G+((Q-L)/2)}function t(){I=P.left;y=u+((v-D)/2);J=G-L-n.edgeOffset-(x/2)}function A(){I=P.right;y=u+((v-D)/2);J=G+Q+n.edgeOffset+(x/2)}if(n.defaultPosition=="bottom"){w()}else{if(n.defaultPosition=="top"){M()}else{if(n.defaultPosition=="left"&&!z){t()}else{if(n.defaultPosition=="left"&&z){A()}else{if(n.defaultPosition=="right"&&!z){A()}else{if(n.defaultPosition=="right"&&z){t()}else{w()}}}}}}if(I==P.left&&!z&&J<E){A()}else{if(I==P.left&&z&&J-L<E){A()}else{if(I==P.right&&!z&&J>E+O){t()}else{if(I==P.right&&z&&J+L>E+O){t()}else{if(I==P.top&&y<C){w()}else{if(I==P.bottom&&y>C+H){M()}}}}}}if(I==P.left||I==P.right){if(y+D>H+C){y=u+v>H+C?u+v-D:H+C-D}else{if(y<C){y=u<C?u:C}}}if(I==P.top||I==P.bottom){if(J+L>O+E){J=G+Q>O+E?G+Q-L:O+E-L}else{if(J<E){J=G<E?G:E}}}f.css({left:Math.round(J),top:Math.round(y)}).removeClass(P.top).removeClass(P.bottom).removeClass(P.left).removeClass(P.right).addClass(I);if(I==P.top){F=D;K=G-J+((Q-x)/2)}else{if(I==P.bottom){F=-R;K=G-J+((Q-x)/2)}else{if(I==P.left){F=u-y+((v-R)/2);K=L}else{if(I==P.right){F=u-y+((v-R)/2);K=-x}}}}j.css({left:Math.round(K),top:Math.round(F)})}})};var a="A-Za-z\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u02B8\u0300-\u0590\u0800-\u1FFF\u2C00-\uFB1C\uFDFE-\uFE6F\uFEFD-\uFFFF",c="\u0591-\u07FF\uFB1D-\uFDFD\uFE70-\uFEFC",b=new RegExp("^[^"+a+"]*["+c+"]");function e(f){return b.test(f)}})(jQuery);