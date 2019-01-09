;$(function () {
  $('body').on('click', 'a[href*=#]', function() {
    if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
      var $target = $(this.hash);
      $target = $target.length && $target || $('[name=' + this.hash.slice(1) + ']');
      if ($target.length) {
        var scrollTop=document.body.scrollTop
        var targetOffset = $target.offset().top;
        if( scrollTop != targetOffset ){
          $('html,body').animate({
            scrollTop: targetOffset
          },1000);
        }
        return false;
      }
    }
  });
})