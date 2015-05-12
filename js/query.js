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
                str+= '<p>' +  response.data.name + '</p>';
                str+= '<p>' +  response.data.contact + '</p>';
                str+= '<p>' +  response.data.phone + '</p>';
                str+= '<p>' +  response.data.ProvinceName + ' ' +response.data.CityName + ' ' + response.data.DistrictName + '</p>';
                str+= '<p>' +  response.data.address + '</p>';
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