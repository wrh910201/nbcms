<?php
/**
 * 公共函数库
 * @author winsen
 * @version 1.0.0
 */

/**
 * 权限检查函数
 * @param int $sys_purview 系统定义的权限
 * @param int $user_purview 用户的权限
 * @return bool 拥有该权限时返回true,否则返回false
 * @author winsen
 */
//function checkPurview($sys_purview, $user_purview)
//{
//    return $sys_purview & $user_purview;
//}

function checkPurview($sys_purview, $user_purview) {
    $user_purview = json_decode($user_purview);
    $has_power = false;
    foreach( $user_purview as $key => $value ) {
        if( in_array($sys_purview, $value) ) {
            $has_power = true;
            break;
        }
    }
    return $has_power;
}

/**
 * 权限合并
 * @param int $user_purview 用户的权限
 * @param mixed $purviewList 需要合并的权限列表
 * @return int 返回合并后的权限
 * @author winsen
 */
function combilePurview($user_purview, $purviewList)
{
    $temp_purview = $user_purview;
    if(is_array($purviewList))
    {
        foreach($purviewList as $purview)
        {
            $temp_purview |= $purview;
        }
    }

    if(is_int($purviewList))
    {
        $temp_purview |= $purviewList;
    }

    return $temp_purview;
}

/**
 * smarty assign函数
 * @param string $var 参数名
 * @param mixed $value 参数值
 * @return void
 * @author winsen
 */
function assign($var, $value)
{
    global $smarty;
    $smarty->assign($var, $value);
}

/**
 * 获取GET的参数封装
 * @param string $var 参数名
 * @return mixed 返回对应的参数,如果参数不存在,则返回null
 * @author winsen
 */
function getGET($var)
{
    if(isset($_GET[$var]))
    {
        return $_GET[$var];
    } else {
        return null;
    }
}

/**
 * 获取POST的参数封装
 * @param string $var 参数名
 * @return mixed 返回对应的参数,如果参数不存在,则返回null
 * @author winsen
 */
function getPOST($var)
{
    if(isset($_POST[$var]))
    {
        return $_POST[$var];
    } else {
        return null;
    }
}

/**
 * 获取REQUEST的参数封装
 * @param string $var 参数名
 * @return mixed 返回对应的参数,如果参数不存在,则返回null
 * @author winsen
 */
function getREQUEST($var)
{
    if(isset($_REQUEST[$var]))
    {
        return $_REQUEST[$var];
    } else {
        return null;
    }
}

/**
 * 验证页面的act或opera值的合法性
 * @param string $needle 合法操作字符串,多个操作用|分隔开
 * @param string $search 待验证的操作
 * @param string $default 若为非法操作,则采用默认值替换
 * @author winsen
 */
function checkAction($needle, $search, $default = '')
{
    if(!$needle || false === strpos($needle, $search))
    {
        return $default;
    } else {
        return $search;
    }
}

/**
 * 数据过滤函数
 * @param mixed $var 需要过滤的变量
 * @param string $type 过滤的数据类型
 * @return mixed 过滤后的数据
 * @author winsen
 */
function filterVar($var, $type)
{
    switch($type)
    {
    //字符串,过滤空串
    case 'string':
        if(!empty($var) && is_string($var))
        {
            return $var;
        } else {
            return null;
        }
        break;
    //整型数,过滤非整型数
    case 'integer':
        if(isset($var) && is_numeric($var))
        {
            return intval($var);
        } else {
            return null;
        }
        break;
    }
}

/**
 * 显示系统信息
 * @param string $msg 系统提示的文本信息
 * @param mixed $links 自动跳转以及其他链接
 * @param int $time 自动跳转计时
 * @return void
 * @author winsen
 */
function showSystemMessage($msg, $links, $time = 3)
{
    global $smarty;
    assign('message', nl2br(htmlspecialchars($msg)));
    if(count($links) > 0)
    {
        assign('links', $links);
        assign('link', $links[0]['link']);
    } else {
        $pre = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
        assign('links', array(array('alt'=>'返回上一页', 'link'=> $pre)));
        assign('link', $pre);
    }
    assign('time', $time);
    assign('pageTitle', '系统信息');
    $smarty->display('message.phtml');
    exit;
}

/**
 * $required = false 没有文件上传不报错
 * @param $file
 * @param string $type
 * @param bool $required
 * @return array
 */
function upload_with_choice($file, $type = '', $required = false) {
    $response = upload($file, $type);
    if( $response['error'] ) {
        if( !$required && ( $response['msg'] == '没有文件上传'|| $response['msg'] == '请选择图片。') ) {
            //获得文件扩展名
            $file_name = $file['name'];
            $temp_arr = explode(".", $file_name);
            $file_ext = array_pop($temp_arr);
            $file_ext = trim($file_ext);
            $file_ext = strtolower($file_ext);
            return array('error' => 0, 'msg' => '', 'type' => $file_ext);
        } else {
            return $response;
        }
    }
    return $response;
}

/**
 * 图片缩放
 * @param $filename
 * @param int $max_width
 * @param int $max_height
 * @param $type
 */
function resize_image($filename, $type, $max_width = 55, $max_height = 55) {
    //文件保存目录路径
    $save_path = ROOT_PATH.'upload/';
    //文件保存目录URL
    $save_url = '/upload/';
    //upload下保存图片的目录
    $dir_name = 'image';

    $im = null;
    switch($type) {
        case 'jpg': $im = imagecreatefromjpeg(ROOT_PATH.$filename);break;
        case 'jpeg': $im = imagecreatefromjpeg(ROOT_PATH.$filename);break;
        case 'png': $im = imagecreatefrompng(ROOT_PATH.$filename);break;
        case 'gif': $im = imagecreatefromgif(ROOT_PATH.$filename);break;
        default: $im = imagecreatefromjpeg(ROOT_PATH.$filename);break;
    }
    $pic_width = imagesx($im);
    $pic_height = imagesy($im);

    if(($max_width && $pic_width > $max_width) || ($max_height && $pic_height > $max_height))
    {
        if($max_width && $pic_width>$max_width)
        {
            $widthratio = $max_width/$pic_width;
            $resizewidth_tag = true;
        }

        if($max_height && $pic_height>$max_height)
        {
            $heightratio = $max_height/$pic_height;
            $resizeheight_tag = true;
        }

        if($resizewidth_tag && $resizeheight_tag)
        {
            if($widthratio<$heightratio)
                $ratio = $widthratio;
            else
                $ratio = $heightratio;
        }

        if($resizewidth_tag && !$resizeheight_tag)
            $ratio = $widthratio;
        if($resizeheight_tag && !$resizewidth_tag)
            $ratio = $heightratio;

        $new_width = $pic_width * $ratio;
        $new_height = $pic_height * $ratio;

        if(function_exists("imagecopyresampled"))
        {
            $new_im = imagecreatetruecolor($new_width,$new_height);
            imagecopyresampled($new_im,$im,0,0,0,0,$new_width,$new_height,$pic_width,$pic_height);
        }
        else
        {
            $new_im = imagecreate($new_width,$new_height);
            imagecopyresized($new_im,$im,0,0,0,0,$new_width,$new_height,$pic_width,$pic_height);
        }
        //新文件名
        $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $type;
    }
    else
    {
        //新文件名
        $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $type;
        $new_im = $im;
    }


    //创建文件夹
    if ($dir_name !== '')
    {
        $save_path .= $dir_name . "/";
        $save_url .= $dir_name . "/";
        if (!file_exists($save_path))
        {
            mkdir($save_path);
        }
    }
    $ymd = date("Ymd");
    $save_path .= $ymd . "/";
    $save_url .= $ymd . "/";
    if (!file_exists($save_path))
    {
        mkdir($save_path);
    }

    $file_path = $save_path . $new_file_name;

    switch($type) {
        case 'jpg': imagejpeg($new_im, $file_path);break;
        case 'jpeg': imagejpeg($new_im, $file_path);break;
        case 'png': imagepng($new_im, $file_path);break;
        case 'gif': imagegif($new_im, $file_path);break;
        default: imagejpeg($new_im, $file_path);break;
    }
    return $save_url . $new_file_name;

}


/**
 * 文件上传
 */
function upload($file, $type = '')
{
    $ext_arr = array(
    	'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
	    'flash' => array('swf', 'flv'),
    	'media' => array('swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'),
	    'file' => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'),
    );

    $php_path = dirname(__FILE__) . '/';
    $php_url = dirname($_SERVER['PHP_SELF']) . '/';
    //文件保存目录路径
    $save_path = '../upload/';
    //文件保存目录URL
    $save_url = '../upload/';


    //PHP上传失败
    if (!empty($file['error'])) 
    {
        switch($file['error'])
        {
		case '1':
			$error = '超过php.ini允许的大小。';
			break;
		case '2':
			$error = '超过表单允许的大小。';
			break;
		case '3':
			$error = '图片只有部分被上传。';
			break;
		case '4':
			$error = '请选择图片。';
			break;
		case '6':
			$error = '找不到临时目录。';
			break;
		case '7':
			$error = '写文件到硬盘出错。';
			break;
		case '8':
			$error = 'File upload stopped by extension。';
			break;
		case '999':
		default:
			$error = '未知错误。';
    	}

        return array('error'=>1, 'msg'=>$error);
    }

    $save_path = realpath($save_path).'/';

    //有上传文件时
    if (empty($file) === false) 
    {
	    //原文件名
    	$file_name = $file['name'];
	    //服务器上临时文件名
    	$tmp_name = $file['tmp_name'];
	    //文件大小
    	$file_size = $file['size'];
	    //检查文件名
    	if (!$file_name) {
	    	return array('error'=>1,'msg'=>"请选择文件。");
    	}
	    //检查目录
    	if (@is_dir($save_path) === false) {
	    	return array('error'=>1,'msg'=>"上传目录不存在。");
    	}
	    //检查目录写权限
    	if (@is_writable($save_path) === false) {
	    	return array('error'=>1,'msg'=>"上传目录没有写权限。");
    	}
	    //检查是否已上传
    	if (@is_uploaded_file($tmp_name) === false) {
	    	return array('error'=>1,'msg'=>"上传失败。");
    	}
	    //检查文件大小
        if ($file_size > 100000000)
        {
		    return array('error'=>1,'msg'=>"上传文件大小超过限制。");
    	}
	    //检查目录名
    	$dir_name = empty($type) ? 'image' : trim($type);
        if (empty($ext_arr[$dir_name])) 
        {
		    return array('error'=>1,'msg'=>"目录名不正确。");
    	}
	    //获得文件扩展名
    	$temp_arr = explode(".", $file_name);
	    $file_ext = array_pop($temp_arr);
    	$file_ext = trim($file_ext);
	    $file_ext = strtolower($file_ext);
    	//检查扩展名
        if (in_array($file_ext, $ext_arr[$dir_name]) === false)
        {
		    return array('error'=>1,'msg'=>"上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr[$dir_name]) . "格式。");
    	}
	    //创建文件夹
        if ($dir_name !== '') 
        {
		    $save_path .= $dir_name . "/";
    		$save_url .= $dir_name . "/";
	    	if (!file_exists($save_path)) 
            {
			    mkdir($save_path);
    		}
	    }
    	$ymd = date("Ymd");
	    $save_path .= $ymd . "/";
    	$save_url .= $ymd . "/";
	    if (!file_exists($save_path)) 
        {
		    mkdir($save_path);
    	}
	    //新文件名
    	$new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
	    //移动文件
    	$file_path = $save_path . $new_file_name;
        if (move_uploaded_file($tmp_name, $file_path) === false) 
        {
		    return array('error'=>1, 'msg'=>"上传文件失败。");
    	}
	    @chmod($file_path, 0644);
    	$file_url = $save_url . $new_file_name;
        
        return array('error' => 0, 'msg' => str_replace('..', '', $file_url), 'type' => $file_ext);
    }

    return array('error'=>1, 'msg'=>'没有文件上传');
}

function checkAdminLogin()
{
    if(isset($_SESSION['purview']) && isset($_SESSION['account']))
    {
        return true;
    } else {
        showSystemMessage('您尚未登录', array(array('alt'=>'管理后台登录', 'link'=>'index.php')));
        exit;
    }
}

//function createMenus()
//{
//    global $menus;
//    $temp = 0xf;
//    $index = 0;
//    $menu = array();
//
//    while($temp <= $_SESSION['purview'])
//    {
//        if(checkPurview($temp, $_SESSION['purview']))
//        {
//            $menu[] = $menus[$index];
//        }
//        $index++;
//        $temp = $temp<<4;
//    }
//
//    assign('menus', $menu);
//}

function createMenus() {
    global $menus;
    $purview = $_SESSION['purview'];
    $purview = json_decode($purview);
    $menu = array();
    foreach($purview as $key => $value) {
        if( count($value) > 0 ) {
            $menu[] = $menus[$key];
        }
    }
    assign('menus', $menu);
}

/**
 * 生成伪静态化的URL或者原始URL
 */
function buildUrl($script, $params)
{
	global $sysconf;

	switch($script)
	{
		case 'article':
			if($sysconf['isStatic'])
			{
				return 'article-'.intval($params['id']).'.html';
			} else {
				return 'article.php?id='.intval($params['id']);
			}
			break;
		case 'product':
			if($sysconf['isStatic'])
			{
				return 'product-'.intval($params['id']).'.html';
			} else {
				return 'product.php?id='.intval($params['id']);
			}
			break;
		case 'articleCat':
			if($sysconf['isStatic'])
			{
				return 'articleCat-'.intval($params['id']).'-page-'.intval($params['page']).'html';
			} else {
				return 'articleCat.php?id='.intval($params['id']).'&page='.intval($params['page']);
			}
			break;
	}
}

function smarty_modifier_build_url($script, $params)
{
    global $conf;
    switch($script)
    {
    case 'article':
        if($conf['isStatic'])
        {
            return 'article-'.$params.'.html';
        } else {
            return 'article.php?id='.$params;
        }
        break;
    case 'ads':
        if($conf['isStatic'])
        {
            return 'ads-'.$params.'.html';
        } else {
            return 'ads.php?id='.$params;
        }
        break;
	case 'product':
		if($conf['isStatic'])
		{
			return 'product-'.$params.'.html';
		} else {
			return 'product.php?id='.$params;
		}
		break;
	case 'articleCat':
		if($conf['isStatic'])
		{
			return 'articleCat-'.intval($params['id']).'.html';
		} else {
			return 'articleCat.php?id='.intval($params['id']);
		}
		break;
    }
}

function buildUrHere($script, $params)
{
	global $db;
	$urHere = array(array('link'=>'/','title'=>'首页'));
	switch($script)
	{
		case 'article':
			$getArticle = 'select `title`,`articleCatId` from `'.DB_PREFIX.'article` where `id`='.$params['id'];
			$article = $db->fetchRow($getArticle);
			$getArticleCat = 'select `name`,`id` from `'.DB_PREFIX.'articleCat` where `id`='.$article['articleCatId'];
			$articleCat = $db->fetchRow($getArticleCat);
			$urHere[] = array('link'=>buildUrl('articleCat', array('id'=>$articleCat['id'])), 'title'=>$articleCat['name']);
			$urHere[] = array('link'=>'', 'title'=>$article['title']);
			break;
		case 'product':
			break;
		case 'category':
			break;
		case 'articleCat':
			$getArticleCat = 'select `path` from `'.DB_PREFIX.'articleCat` where `id`='.$params['id'];
			$path = $db->fetchRow($getArticleCat);
			$path = $path['path'];
			$path = explode(',', $path);
			foreach($path as $cid)
			{
				$getArticleCat = 'select `id`,`name` from `'.DB_PREFIX.'articleCat` where `id`='.$cid;
				$cat = $db->fetchRow($getArticleCat);
				if($params['id'] != $cid)
				{
					$urHere[] = array('link'=>buildUrl('articleCat', $cat), 'title'=>$cat['name']);
				} else {
					$urHere[] = array('link'=>'', 'title'=>$cat['name']);
				}
			}
			break;
	}
	return $urHere;
}

/**
 * @return string 当前文件名
 * author wrh
 */
function get_active_nav() {
    $url = $_SERVER['PHP_SELF'];
    $filename= substr( $url , strrpos($url , '/')+1 );
    return $filename;
}

/**
 * 后台文件初始化
 * author wrh
 */
function back_base_init() {
    checkAdminLogin();
    $activeNav = get_active_nav();

    createMenus();
    if( $activeNav != 'main.php') {
        $is_main = false;
        //assign('is_main', false);
    } else {
        $is_main = true;
        //assign('is_main', true);
    }
    //var_dump($is_main);exit;
    assign('is_main', $is_main);
    assign('activeNav', $activeNav);
    assign('pageTitle', 'NB_CMS管理后台');
}

/**
 * 接收经销商的相关数据并过滤
 */
function check_distributor_input($get_post_id = false) {
    global $db;

    $id = $get_post_id ? getPOST('id') : '';
    $name = getPOST('name');
    $contact = getPost('contact');
    $phone = getPost('phone');
    $district = getPost('district');
    $address = getPost('address');
    $lat = getPost('lat');
    $lng = getPost('lng');
    $authCode = getPost('auth_code');

    if( $get_post_id ) {
        $id = intval($id);
        if( 0 >= $id ) {
            showSystemMessage('参数错误', array());
            exit;
        } else {
            $id = $db->escape(htmlspecialchars($id));
        }
    }

    if('' == $name)
    {
        showSystemMessage('请填写经销商名称', array());
        exit;
    } else {
        $name = $db->escape(htmlspecialchars($name));
    }

    if('' == $contact)
    {
        showSystemMessage('请填写联系人', array());
        exit;
    } else {
        $contact = $db->escape(htmlspecialchars($contact));
    }

    if('' == $phone)
    {
        showSystemMessage('请填写联系方式', array());
        exit;
    } else {
        $phone = $db->escape(htmlspecialchars($phone));
    }

    if('' == $authCode)
    {
        showSystemMessage('请填写授权码', array());
        exit;
    } else {
        $authCode = $db->escape(htmlspecialchars($authCode));
    }

    if('' == $district)
    {
        showSystemMessage('请选择地区', array());
        exit;
    } else {
        $district = intval($district);
        if($district <= 0) {
            showSystemMessage('参数错误', array());
            exit;
        } else {
            $district = $db->escape(htmlspecialchars($district));
        }
    }

    $data = array(
        'id' => $id,
        'name' => $name,
        'contact' => $contact,
        'phone' => $phone,
        'district' => $district,
        'address' => $address,
        'lat' => $lat,
        'lng' => $lng,
        'authCode' => $authCode,
    );
    return $data;
}

/**
 * 获取地区数据（省市区），并assign到前端
 * @author wrh
 */
function get_area_data() {
    global $db;
    //获取省份
    $getProvinces = 'select ProvinceID, ProvinceName from '.DB_PREFIX.'Province where 1 order by ProvinceID asc';
    $provinces = $db->fetchAll($getProvinces);
    //获取城市
    $getCities = 'select CityID, CityName, ProvinceID from '.DB_PREFIX.'City where 1 order by ProvinceID asc,CityID asc';
    $cities = $db->fetchAll($getCities);
    //获取地区
    $getDistricts = 'select DistrictID, DistrictName, CityID from '.DB_PREFIX.'District where 1 order by CityID asc, DistrictID asc';
    $districts = $db->fetchAll($getDistricts);


    //转换城市的结构
    $target_cities = array();
    $count = count($cities);
    for($i = 0; $i < $count; ) {
        $pid = $cities[$i]['ProvinceID'];
        $temp = array();
        do {
            $temp[] = $cities[$i];
            $i++;
        } while($i < $count && $cities[$i]['ProvinceID'] == $pid);
        $target_cities[$pid] = $temp;
    }
    //转换地区的结构
    $target_districts = array();
    $count = count($districts);
    for($i = 0; $i < $count; ) {
        $pid = $districts[$i]['CityID'];
        $temp = array();
        do {
            $temp[] = $districts[$i];
            $i++;
        } while($i < $count && $districts[$i]['CityID'] == $pid);
        $target_districts[$pid] = $temp;
    }

    assign('provinces', $provinces);
    assign('cities', $target_cities);
    assign('districts', $target_districts);
    assign('json_cities', json_encode($target_cities));
    assign('json_districts', json_encode($target_districts));
}

/**
 * 判断是否跨域
 * @return bool
 * @author wrh
 */
function check_cross_domain() {
    $server_name = $_SERVER['SERVER_NAME'];//当前运行脚本所在服务器主机的名字。
    $sub_from = $_SERVER["HTTP_REFERER"];//链接到当前页面的前一页面的 URL 地址
    $sub_len = strlen($server_name);//统计服务器的名字长度。
    $check_from = substr($sub_from,7,$sub_len);//截取提交到前一页面的url，不包含http:://的部分。

    if( $check_from != $server_name ) {
        return false;
    } else {
        return true;
    }
}


function remove_file($filename) {
    if( file_exists($filename) ) {
        return unlink($filename);
    } else {
        return false;
    }
}

function create_pager($page, $totalPage, $total) {
    $show_page = array();
    if( $page == 1 ) {
        for($i = 1; $i <= $totalPage && $i <= 3; $i++) {
            $show_page[] = $i;
        }
        $go_first = false;  //首页
        $has_prev = false;  //上一页
        $has_many_prev = false; //前页省略号
        $has_next = ($totalPage > 1) ? true : false;    //下一页
        $has_many_next = ($totalPage > 3) ? true : false;   //后页省略号
        $go_last = ($total > 1) ? true : false; //末页
    } elseif( $page == $totalPage ) {   //必然不是第一页
        $i = ($totalPage < 3) ? $page - 1 : $page - 2;
        for( ; $i <= $totalPage; $i++ ) {
            $show_page[] = $i;
        }
        $go_first = true;
        $has_prev = true;
        $has_many_prev = ($totalPage > 3) ? true : false;
        $has_next = false;
        $has_many_next = false;
        $go_last = false;
    } else {
        for($i = $page - 1; $i <= $totalPage && $i <= $page + 1; $i++ ) {
            $show_page[] = $i;
        }
        $go_first = true;
        $has_prev = true;
        $has_many_prev = ($page > 3) ? true : false;
        $has_many_next = ( ($totalPage - $page) > 2 ) ? true : false;
        $has_next = true;
        $go_last = true;
    }
    assign('show_page', $show_page);
    assign('go_first', $go_first);
    assign('has_prev', $has_prev);
    assign('has_many_prev', $has_many_prev);
    assign('has_many_next', $has_many_next);
    assign('has_next', $has_next);
    assign('go_last', $go_last);

    assign('page', $page);
    assign('total', $total);
    assign('totalPage', $totalPage );
}