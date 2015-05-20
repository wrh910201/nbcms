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


    $('#article-list').dataTable({
        language: {
            "sProcessing": "处理中...",
            "sLengthMenu": "显示 _MENU_ 项结果",
            "sZeroRecords": "没有匹配结果",
            "sInfo": "显示第 _START_ 至 _END_ 项结果，共 _TOTAL_ 项",
            "sInfoEmpty": "显示第 0 至 0 项结果，共 0 项",
            "sInfoFiltered": "(由 _MAX_ 项结果过滤)",
            "sInfoPostFix": "",
            "sSearch": "搜索:",
            "sUrl": "",
            "sEmptyTable": "表中数据为空",
            "sLoadingRecords": "载入中...",
            "sInfoThousands": ",",
            "oPaginate": {
                "sFirst": "首页",
                "sPrevious": "上页",
                "sNext": "下页",
                "sLast": "末页"
            },
            "oAria": {
                "sSortAscending": ": 以升序排列此列",
                "sSortDescending": ": 以降序排列此列"
            }
        },
        columnDefs:[{
            orderable:false,//禁用排序
            targets:[0,6]  //指定的列
        }]
    });

});