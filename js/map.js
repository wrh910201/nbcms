$(function(){



    //百度地图API功能
    var map = new BMap.Map("map_l");
    var point = new BMap.Point(113.462472,23.17227);
    map.centerAndZoom(point, 15);
    map.addControl(new BMap.NavigationControl());
    map.addControl(new BMap.ScaleControl());
    map.addControl(new BMap.OverviewMapControl());
    map.addControl(new BMap.MapTypeControl());
    map.enableScrollWheelZoom();   //启用滚轮放大缩小，默认禁用

    /**
     * 省市区三级联动效果
     */
    if( $('#provinceDiv').children(':selected').val() !== 0 ) {
        var init_child = data_cities[$('#provinceDiv').val()];
        if( init_child ) {
            var city_init = '<option value="0">请选择</option>';
            for (var i = 0; i < init_child.length; i++) {
                city_init += '<option value="' + init_child[i].CityID + '">' + init_child[i].CityName + '</option>';
            }
            $('#cityDiv').empty();
            $('#cityDiv').append(city_init);
            $('#countyDiv').empty();
            $('#countyDiv').append('<option value="0">请选择</option>>');
        }
    }


    $('#provinceDiv').change(function() {
        var temp = '<option value="0">请选择</option>'
        var pid = $(this).val();
        if( pid == 0 ) {
            $('#cityDiv').empty();
            $('#cityDiv').append(temp) ;
            return;
        }
        var child = data_cities[pid];
        if( child ) {

            for(var i = 0; i < child.length; i++) {
                temp += '<option value="' + child[i].CityID + '">' + child[i].CityName + '</option>';
            }
            $('#cityDiv').empty();
            $('#cityDiv').append(temp);
            $('#countyDiv').empty();
            $('#countyDiv').append('<option value="0">请选择</option>>') ;
        } else {
            return;
        }
    });

    $('#cityDiv').change(function() {
        var temp = '<option value="0">请选择</option>>';
        var pid = $('#provinceDiv').val();
        var cid = $(this).val();
        if( cid == 0 ) {
            $('#countyDiv').empty();
            $('#countyDiv').append(temp) ;
            return;
        }
        var city = $(this).children(':selected').text();

        //locate_city(city);
        var child = data_districts[cid];
        if( child ) {

            for(var i = 0; i < child.length; i++) {
                temp += '<option value="' + child[i].DistrictID + '">' + child[i].DistrictName + '</option>';
            }
            $('#countyDiv').empty();
            $('#countyDiv').append(temp) ;
        } else {
            return;
        }
    });

    $('#countyDiv').change(function(){
        var did = $(this).val();
        if( did == 0 ) {
            return;
        }
        var district = $(this).children(':selected').text();
        locate_city(district);

        var url = '/ajax.php';
        var data = {'DistrictID':did,'opera':'get_distributors'};

        $.post(url, data, function(response){
            map.clearOverlays();
            $('#address_info_input').empty();
            if( response.error == 0 ) {
                list_distributors(response.data);
            } else {
                alert(response.message);
            }

        }, 'json');
    });

    $('#address_info_input').change(function() {
        var lng = $(this).children(':selected').attr('data-lng');
        var lat = $(this).children(':selected').attr('data-lat');
        var address = $(this).children(':selected').attr('data-address');
        var name = $(this).children(':selected').text();
        var opts = {
            width : 200,     // 信息窗口宽度
            height: 100,     // 信息窗口高度
            title : name // 信息窗口标题
        }
        var infoWindow = new BMap.InfoWindow("地址："+address, opts);  // 创建信息窗口对象
        var point = new BMap.Point(lng, lat);
        map.panTo(point);
        map.openInfoWindow(infoWindow,point); //开启信息窗口

    });


    //列出该地区的所有经销商
    function list_distributors(data) {

        if( data.length == 0 ) {
            alert('该地区暂无经销商');
            return;
        } else {
            temp = '';
            for(var i = 0; i < data.length; i++) {
                temp += '<option value="" data-lat="'+data[i].lat+'" data-lng="'+data[i].lng+'" data-address="'+ data[i].address +'">' + data[i].name+ '</option>';
                var point = new BMap.Point(data[i].lng, data[i].lat);
                addMarker(point, data[i].name, data[i].address);
            }
            $('#address_info_input').append(temp);
        }
    }

    /**
     * 在地图上描点
     * @param point
     * @param name
     * @param address
     */
    function addMarker(point, name, address) {
        var marker = new BMap.Marker(point);  // 创建标注
        var opts = {
            width : 200,     // 信息窗口宽度
            height: 100,     // 信息窗口高度
            title : name // 信息窗口标题
            //enableMessage:true,//设置允许信息窗发送短息
            //message:"亲，我要加盟佳百莉！"
        }
        var infoWindow = new BMap.InfoWindow("地址："+address, opts);  // 创建信息窗口对象

        marker.addEventListener("click", function(){
            map.openInfoWindow(infoWindow,point); //开启信息窗口
            map.panTo(point);
        });
        map.addOverlay(marker);
    }

    //城市、地区改变地图相应定位
    function locate_city(city) {
        if(city != ""){
            map.centerAndZoom(city,12);      // 用城市名设置地图中心点
        }
    }


});
