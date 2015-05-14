$(function(){
    $('.purview').click(function(){
        var temp = $(this).val();
        if( $(this).is(':checked') ) {
            $('.' + temp).attr('checked', 'checked');
        } else {
            $('.' + temp).removeAttr('checked');
        }
    });

    $('.sub-purview').click(function(){
        var temp = $(this).attr('data-parent');
        if( $(this).is(':checked') ) {
            $('#' + temp).attr('checked', 'checked');
        } else {
            var still_checked = false;
            $('.' + temp).each(function(){
                if( $(this).is(':checked') ) {
                    still_checked = true;
                }
            });
            if( !still_checked ) {
                $('#' + temp).removeAttr('checked');
            }
        }
    });

    $('#all').click(function(){
        if( $(this).is(':checked') ) {
            $('input').attr('checked', 'checked');
        } else {
            $('input').removeAttr('checked');
        }
    });
});

