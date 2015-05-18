$(function(){
    var ajaxing = false;

    $('input[type="button"]').click(function(){
        value = $('#search-texthybrid-search').val();
        value = value.trim();
        if( value == '' ) {
            $('.bs-example-modal-sm .modal-title').text('系统消息');
            $('.bs-example-modal-sm .modal-body').text('请输入授权编码或者手机号码!!');
            $('.bs-example-modal-sm').modal();
            return;
        }
        if( ajaxing ) {
            return;
        }
        var url = 'ajax.php';
        var data = {'data':value, 'opera':'get_distributor'};
        $.post(url, data, function(response){
            ajaxing = true;
            if( response.error == 0 ) {
                str = '';
                str+= response.data;
                $('.bs-example-modal-static .modal-title').text('经销商信息');
                $('.bs-example-modal-static .modal-body').empty();
                $('.bs-example-modal-static .modal-body').append(str);
                $('.bs-example-modal-static').modal();
            } else {
                $('.bs-example-modal-sm .modal-title').text('系统消息');
                $('.bs-example-modal-sm .modal-body').text(response.message);
                $('.bs-example-modal-sm').modal();
            }

            ajaxing = false;
        }, 'json');

    });
});
