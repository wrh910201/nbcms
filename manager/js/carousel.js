$('a.bootstrap-popover').popover({
    'template':'<div class="popover" role="tooltip" style="min-width: 990px; left: 0px;"><div class="arrow" style="left: 20%;"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
    'title':'',
    'content':function(){
        var object = $(this).children()[0];
        return "<img src=\"" + object.alt + "\" width=\"960px\" height=\"225px\"/>";
        $('.popover').css('min-width', '990px');
    },
    'html':true
    //'placement': 'bottom'
});
$(document).on('click', 'a.bootstrap-popover', function() {
    $('a.bootstrap-popover').not(this).popover('hide');
    $(this).popover('toggle');
    return false;
});



$('body').find('.popovers').each(function(){
    $(this).click(function(e){
        $('.popover').remove();
        e.preventDefault();
        return false;
    });
    $(this).popover({
        trigger : 'click'
    });
});
$('body').click(function(){
    $('.popover').remove();
});