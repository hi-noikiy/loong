$(function () {
    //顶部导航切换
    $('.admin-menu li').click(function () {
        $(this).parent().find('li').removeClass('active');
        $(this).addClass('active');
        $('.sub-nav').hide();
        var sub_nav = $('.sub_' + $(this).data('param'));
        sub_nav.show();
        sub_nav.find('.item').removeClass('current');
        sub_nav.find('.item').first().addClass('current');
        sub_nav.find('.item').find('.sub-item').hide();
        sub_nav.find('.item').first().children('.sub-item').show();
        sub_nav.find('.item').first().children('.sub-item ul li').removeClass('curr');
        sub_nav.find('.item').first().children('.sub-item ul li').first().addClass('curr');
    });

    //左边导航切换
    $('.item .title').click(function () {
        $(this).parent().parent().children('.item').removeClass('current');
        $(this).parent().addClass('current');
        $(this).parent().parent().children('.item').children('.sub-item').hide();
        $(this).parent().children('.sub-item').show();
        $(this).parent().children('.sub-item').css('top', 50 - $(this).offset().top);
        $(this).parent().children('.sub-item').find('li').removeClass('curr');
        $(this).parent().children('.sub-item').find('li').first().addClass('curr')
    });

    //左边子导航切换
    $('.sub-item li').click(function () {
        $(this).parent().find('li').removeClass('curr');
        $(this).addClass('curr');
    });
<<<<<<< HEAD

    $('.tabs li').click(function () {
        $(this).parent().find('li').removeClass('curr');
        $(this).addClass('curr');
        var i = 0;
        $(this).parent().find('li').each(function (k,v) {
            if($(v).hasClass('curr')) {
                i = k;
            }
        })
        $('.switch-info').hide();
        $('.switch-info').each(function (k,v) {
            if (k == i){
                $(v).show();
            }
        })
    });

    region();

});

function region() {
    $('.shop_country')
}
=======
})
>>>>>>> 3446ec3a04598a6da640fcdbe28208fa77238de4