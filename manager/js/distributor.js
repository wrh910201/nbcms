$(function() {

    $('#distributor-list').dataTable({
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
            targets:[0,5]  //指定的列
        }]
    });


    // 百度地图API功能
    var map = new BMap.Map("baidu-map");
    if (map_init) {
        map.centerAndZoom(new BMap.Point($('input[name=lng]').val(), $('input[name=lat]').val()), 20);
        var marker = new BMap.Marker(new BMap.Point($('input[name=lng]').val(), $('input[name=lat]').val()));
        map.addOverlay(marker);
    } else {
        map.centerAndZoom(new BMap.Point(116.404, 39.915), 11);

    }

    map.addEventListener("click", showInfo);
    map.addControl(new BMap.NavigationControl());
    map.addControl(new BMap.ScaleControl());
    map.addControl(new BMap.OverviewMapControl());
    map.addControl(new BMap.MapTypeControl());
    map.enableScrollWheelZoom();   //启用滚轮放大缩小，默认禁用

    function showInfo(e) {
        $('input[name=lng]').val(e.point.lng);
        $('input[name=lat]').val(e.point.lat);
        $('input[name=lng-show]').val(e.point.lng);
        $('input[name=lat-show]').val(e.point.lat);
        var marker = new BMap.Marker(new BMap.Point(e.point.lng, e.point.lat));
        map.clearOverlays();
        map.addOverlay(marker);
        //alert(e.point.lng + ", " + e.point.lat);
    }


    /**
     * 省市区三级联动效果
     */
    $('#province').change(function () {
        var temp = '<option value="0">请选择</option>>'
        var pid = $(this).val();
        if (pid == 0) {
            $('#city').empty();
            $('#city').append(temp);
            return;
        }
        var child = data_cities[pid];
        if (child) {

            for (var i = 0; i < child.length; i++) {
                temp += '<option value="' + child[i].CityID + '">' + child[i].CityName + '</option>';
            }
            $('#city').empty();
            $('#city').append(temp);
            $('#district').empty();
            $('#district').append('<option value="0">请选择</option>>');
        } else {
            return;
        }
    });

    $('#city').change(function () {
        var temp = '<option value="0">请选择</option>>';
        var pid = $('#province').val();
        var cid = $(this).val();
        if (cid == 0) {
            $('#district').empty();
            $('#district').append(temp);
            return;
        }
        var city = $(this).children(':selected').text();

        locate_city(city);
        var child = data_districts[cid];
        if (child) {

            for (var i = 0; i < child.length; i++) {
                temp += '<option value="' + child[i].DistrictID + '">' + child[i].DistrictName + '</option>';
            }
            $('#district').empty();
            $('#district').append(temp);
        } else {
            return;
        }
    });

    $('#district').change(function () {
        var district = $(this).children(':selected').text();
        locate_city(district);
    });

    $('input[name=address]').blur(function () {
        var address_kw = $(this).val();
        if ($('#search-on').is(':checked')) {
            map.clearOverlays();
            locate_by_address(address_kw);
        }
    });


    //城市、地区改变地图相应定位
    function locate_city(city) {
        if (city != "") {
            map.centerAndZoom(city, 11);      // 用城市名设置地图中心点
        }
    }

    function locate_by_address(address_kw) {
        var local = new BMap.LocalSearch(map, {
            renderOptions: {map: map}
        });
        local.search(address_kw);
    }
});



