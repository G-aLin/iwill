function reSize(){
  var viewW = $(window).width();
  $('html').css('font-size', viewW / 750 * 100 + 'px');
}
reSize();
window.onresize = function(){
  reSize();
}
