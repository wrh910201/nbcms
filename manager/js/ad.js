$(function(){
    //$(".form_datetime").datetimepicker({format: 'yyyy-mm-dd hh:ii', autoclose: true});
    // disabling dates
    var nowTemp = new Date();

    var endDate = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);
    endDate += 3600 * 100;
    $('.dpd1').datepicker({
        format: 'yyyy-mm-dd'
    }).on('changeDate', function(ev){
            console.log('startDate=' + ev.date.valueOf());
            console.log('endDate=' + endDate.valueOf());
            if (ev.date.valueOf() > endDate.valueOf()){
                $('.modal-title').text('温馨提示');
                $('.modal-body').text('起始日期不能大于结束日期');
                $('.modal').modal();
                $('input[name=startTime]').val('');
                //$('.dpd1')[0].val('');
            } else {
                startDate = new Date(ev.date);
                $('input[name=startTime]').val(startDate);
                //$('#startTime').text($('.dpd1').data('date'));
            }
            $('.dpd1').datepicker('hide');
        });
    $('.dpd2').datepicker({
        format: 'yyyy-mm-dd'
    }).on('changeDate', function(ev){
            if (ev.date.valueOf() < startDate.valueOf()){
                $('.modal-title').text('温馨提示');
                $('.modal-body').text('结束日期不能小于起始日期');
                $('.modal').modal();
                $('input[name=endTime]').val('');

            } else {
                endDate = new Date(ev.date);
                $('input[name=endTime]').val(endDate);
                //$('#endTime').text($('.dpd2').data('date'));
            }
            $('.dpd2').datepicker('hide');
        });
});