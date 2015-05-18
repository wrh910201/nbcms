$(function(){
    $('a.bootstrap-popover').popover({'title':'','content':function(){
        var object = $(this).children()[0];
        return "<img src=\"" + object.alt + "\"/>";
    },'html':true});
    $(document).on('click', 'a.bootstrap-popover', function() {
        $('a.bootstrap-popover').not(this).popover('hide');
        $(this).popover('toggle');
        return false;
    });

    $('input[name=isAutoPublish]').change(function(){
        if( $(this).val() == '1' ) {
            $('input[name="publishTime"]').removeAttr('disabled');
        } else {
            $('input[name="publishTime"]').attr('disabled', 'disabled');
        }
    });

    $(".form_datetime").datetimepicker({format: 'yyyy-mm-dd hh:ii', autoclose: true});
});