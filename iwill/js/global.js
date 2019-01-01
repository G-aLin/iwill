;$(function () {
  
  $(document).scroll(function () {
    var scrollTop = Math.max(document.documentElement.scrollTop, document.body.scrollTop);
    if (scrollTop > 500) {
      if(!$('.header-abs').hasClass('fixed') && !$('.header-abs').hasClass('finish')){
        $('.header-abs').addClass('fixed').animate({'top': 0}, 200, function (){
          $(this).addClass('finish').removeAttr('style')
        })
      }
    } else {
      if($('.header-abs').hasClass('fixed') && $('.header-abs').hasClass('finish') && !$('.header-abs').is(':animated')) {
        $('.header-abs').animate({'top': '-81px'}, 200, function () {
          $(this).removeClass('fixed finish').removeAttr('style')
        })
      }
    }
  })

  // 头部导航栏
  $('.nav li').hover(function(){
    if ($(this).children('.nav-list').length) {
      $(this).children('.nav-list').stop(true).slideDown(200, function () {
        $(this).removeAttr('style').css('display', 'block')
      })
    }
  }, function () {
    if ($(this).children('.nav-list').length) {
      $(this).children('.nav-list').stop(true).slideUp(200, function () {
        $(this).removeAttr('style').css('display', 'none')
      })
    }
  })
})

function ShowSubMenu(_this) {
  _this.find('i').toggleClass('bg-3').toggleClass('bg-4')
  _this.siblings('.subnav').stop(true).slideToggle(200)
}