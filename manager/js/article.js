$('a.bootstrap-popover').popover({'title':'','content':function(){
    var object = $(this).children()[0];
    return "<img src=\"" + object.alt + "\"/>";
},'html':true});
$(document).on('click', 'a.bootstrap-popover', function() {
    $('a.bootstrap-popover').not(this).popover('hide');
    $(this).popover('toggle');
    return false;
});