<?php

include 'library/init.inc.php';

wechat_back_base_init();
if(!checkPurview('pur_wechat_manager', $_SESSION['purview']))
{
    showSystemMessage('权限不足', array());
    exit;
}

$action = 'add|edit|list|delete|sync';
$operation = 'add|edit';

$act = checkAction($action, getGET('act'), 'list');
$opera = checkAction($operation, getPOST('opera'));

if( 'add' == $opera ) {
//    set_time_limit(0);
    $type = getPOST('type');
    $name = getPOST('name');

    if( empty($name) ) {
        showSystemMessage('素材名称不能为空', array());
    } else {
        $name = $db->escape(trim($name));
    }

    $notNews = true;
    switch($type) {
        case 'news':
            $notNews = false;
            $multi = getPOST('multi');
            $multi = ( intval($multi) == 0 ) ? 0 : 1;
            if( $multi == 0 ) {         //单图文
                $title = getPOST('title');
                $author = getPOST('author');
                $picUrl = getPOST('picUrl');
                $show_cover_pic = getPOST('show_cover_pic');
                $digest = getPOST('digest');
                $content = getPOST('content');
                $content_source_url = getPOST('content_source_url');

                if( !empty($title) ) {
                    $title = $db->escape(trim($title));
                } else {
                    showSystemMessage('标题不能为空');
                }
                if( !empty($author) ) {
                    $author = $db->escape(trim($author));
                } else {
                    showSystemMessage('作者不能为空');
                }
                if( !empty($picUrl) ) {
                    if( file_exists(realpath('../../'.$picUrl)) ) {
                        $picUrl = $db->escape(trim($picUrl));
                    } else {
                        showSystemMessage('封面图片不存在');
                    }
                } else {
                    showSystemMessage('封面不能为空');
                }

                $show_cover_pic = intval($show_cover_pic) == 0 ? 0 : 1;

                if( !empty($digest) ) {
                    $digest = $db->escape(trim($digest));
                } else {
                    showSystemMessage('摘要不能为空');
                }
                if( !empty($content) ) {
                    $content = $db->escape(trim($content));
                } else {
                    showSystemMessage('内容不能为空');
                }
                if( !empty($content_source_url) ) {
                    $content_source_url = $db->escape(trim($content_source_url));
                } else {
                    showSystemMessage('原文链接不能为空');
                }
                $orderView = 0;
            } else {    //多图文
                $title = getPOST('title');
                $author = getPOST('author');
                $picUrl = getPOST('picUrl');
                $show_cover_pic = getPOST('show_cover_pic');
//                $digest = getPOST('digest');
                $content = getPOST('content');
                $content_source_url = getPOST('content_source_url');

                $count = count($title);
                for($i = 0; $i < $count; $i++) {
                    if( !empty($title[$i]) ) {
                        $title[$i] = $db->escape(trim($title[$i]));
                    } else {
                        showSystemMessage('标题不能为空');
                    }
                    if( !empty($author[$i]) ) {
                        $author[$i] = $db->escape(trim($author[$i]));
                    } else {
                        showSystemMessage('作者不能为空');
                    }
                    if( !empty($picUrl[$i]) ) {
                        if( file_exists(realpath('../../'.$picUrl[$i])) ) {
                            $picUrl[$i] = $db->escape(trim($picUrl[$i]));
                        } else {
                            showSystemMessage('封面图片不存在');
                        }
                    } else {
                        showSystemMessage('封面不能为空');
                    }

                    $show_cover_pic[$i] = intval($show_cover_pic[$i]) == 0 ? 0 : 1;

                    if( !empty($content[$i]) ) {
                        $content[$i] = $db->escape(trim($content[$i]));
                    } else {
                        showSystemMessage('内容不能为空');
                    }
                    if( !empty($content_source_url[$i]) ) {
                        $content_source_url[$i] = $db->escape(trim($content_source_url[$i]));
                    } else {
                        showSystemMessage('原文链接不能为空');
                    }
                    $orderView[$i] = $i;
                }
            }
            break;
        case 'image':
            $value = getPOST('picUrl');
            if( empty($value) ) {
                showSystemMessage('请选择一张图片作为封面');
            } else {
                if( !file_exists(realpath('../../'.$value)) ) {
                    showSystemMessage('图片不存在');
                }
            }
            $key = 'picUrl';
            $getName = 'select name from '.$db_prefix.'material where type=\'image\' and picUrl = \''.$value.'\';';
            $name = $db->fetchOne($getName);
            if( !empty($name) ) {
                showSystemMessage('该图片素材已存在,素材名称为：'.$name.'。永久素材的数量是有上限的，请谨慎新增');
            }
            break;
        case 'voice':
            $uploadResult = upload($_FILES['voiceUrl'], 'media');
            $key = 'voiceUrl';
            break;
        case 'video':
            $video_title = getPOST('video_title');
            $introduction = getPOST('introduction');
            if( empty($video_title) ) {
                showSystemMessage('视频标题不能为空', array());
            } else {
                $video_title = $db->escape(trim($video_title));
            }

            if( empty($introduction) ) {
                showSystemMessage('视频简介不能为空', array());
            } else {
                $introduction = $db->escape(trim($introduction));
            }

            $uploadResult = upload($_FILES['videoUrl'], 'media');
            $key = 'videoUrl';
            break;
    }
    if( $notNews ) {
        if( $type == 'voice' || $type == 'video' ) {
            if ($uploadResult['error'] != 0) {
                showSystemMessage($uploadResult['msg'], array());
            } else {
                $value = $uploadResult['msg'];
            }
        }
        $real_path = realpath('../../'.$value);
        $data = array(
            'type' => $type,
            'media' => $real_path,
        );
        if( $type == 'video') {
            $data['description']['introduction'] = $introduction;
            $data['description']['title'] = $video_title;
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token=';
        $data = wechat_request($url, $data, false, true);
        $mediaId = $data->media_id;
        if( $type == 'video' ) {
            $addMaterial = 'insert into '.$db_prefix.'material (mediaId, name, type, '.$key.', addTime, video_title, introduction) values';
            $addMaterial .= ' (\''.$mediaId.'\',\''.$name.'\', \''.$type.'\', \''.$value.'\', '.time().', \''.$video_title.'\', \''.$introduction.'\')';

        } elseif( $type == 'image' ) {

            $addMaterial = 'insert into '.$db_prefix.'material (mediaId, name, type, ' . $key . ', addTime, down_url) values';
            $addMaterial .= ' (\''.$mediaId.'\',\'' . $name . '\', \'' . $type . '\', \''.$value.'\', ' . time() . ', \''.$data->url.'\')';
        } elseif( $type == 'voice' ) {
            $addMaterial = 'insert into '.$db_prefix.'material (mediaId, name, type, ' . $key . ', addTime) values';
            $addMaterial .= ' (\''.$mediaId.'\',\'' . $name . '\', \'' . $type . '\', \''.$value.'\', ' . time() . ')';
        }
        if( $db->insert($addMaterial) ) {
            $response['msg'] = '添加永久素材成功';
        } else {
            $response['msg'] = '添加永久素材失败';
        }
        showSystemMessage($response['msg']);
    } else {
        if( $multi == 0 ) {
            //判断是否已存在图片素材，若存在，直接引用mediaId作为封面
            $thumb_media_id = 'select mediaId from '.$db_prefix.'material where type=\'image\' and picUrl = \''.$picUrl.'\';';
            $thumb_media_id = $db->fetchOne($thumb_media_id);
            //图片素材不存在，添加素材
            if( empty($thumb_media_id) ) {
                $real_path = realpath('../../' . $picUrl);
                $data = array(
                    'type' => 'thumb',
                    'media' => $real_path,
                );
                $url = 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token=';
                $data = wechat_request($url, $data, false, true);
                $thumb_media_id = $data->media_id;
                $addMaterial = 'insert into '.$db_prefix.'material (mediaId, name, type, picUrl, addTime, down_url) values';
                $addMaterial .= ' (\''.$thumb_media_id.'\',\'' . $name . '封面\', \'image\', \''.$picUrl.'\', ' . time() . ', \''.$data->url.'\')';
                $db->insert($addMaterial);
            }
            $data = array(
                'articles' => array(
                    array(
                        'title' => urlencode($title),
                        'thumb_media_id' => $thumb_media_id,
                        'author' => urlencode($author),
                        'digest' => urlencode($digest),
                        'show_cover_pic' => $show_cover_pic,
                        'content' => urlencode($content),
                        'content_source_url' => $content_source_url
                    )
                )
            );

            $data = json_encode($data);
            $url = 'https://api.weixin.qq.com/cgi-bin/material/add_news?access_token=';
            $data = wechat_request($url, $data, false);
            $mediaId = $data->media_id;
            $addMaterial = 'insert into '.$db_prefix.'material (mediaId, name, type, addTime) values';
            $addMaterial .= ' (\''.$mediaId.'\',\''.$name.'\', \''.$type.'\', '.time().')';
            if( $db->insert($addMaterial) ) {
                $addMaterialNews = 'insert into '.$db_prefix.'materialNews (mediaId, title, thumb_media_id, show_cover_pic, author, digest, content, orderView, content_source_url, picUrl) values ';
                $addMaterialNews .= ' (\''.$mediaId.'\', \''.$title.'\', \''.$thumb_media_id.'\', \''.$show_cover_pic.'\', \''.$author.'\', \''.$digest.'\', \''.$content.'\', \''.$orderView.'\', \''.$content_source_url.'\', \''.$picUrl.'\')';
                $db->insert($addMaterialNews);
                showSystemMessage('添加单图文成功');
            } else {
                showSystemMessage('添加单图文失败');
            }
        } else {
            $articles_data = array();
            $thumb_media_id = array();
            for($i = 0; $i < $count; $i++) {
                $getThumb = 'select mediaId from '.$db_prefix.'material where type=\'image\' and picUrl = \''.$picUrl[$i].'\';';
                $thumb_media_id[$i] = $db->fetchOne($getThumb);
                if( empty($thumb_media_id[$i]) ) {
                    $real_path = realpath('../../' . $picUrl[$i]);
                    $data = array(
                        'type' => 'thumb',
                        'media' => $real_path,
                    );
                    $url = 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token=';
                    $data = wechat_request($url, $data, false, true);
                    $thumb_media_id[$i] = $data->media_id;
                    $addMaterial = 'insert into '.$db_prefix.'material (mediaId, name, type, picUrl, addTime, down_url) values';
                    $addMaterial .= ' (\''.$thumb_media_id[$i].'\',\'' . $name . '封面\', \'image\', \''.$picUrl[$i].'\', ' . time() . ', \''.$data->url.'\')';
                    $db->insert($addMaterial);
                }
                $article_data = array(
                    'title' => urlencode($title[$i]),
                    'thumb_media_id' => $thumb_media_id[$i],
                    'author' => urlencode($author[$i]),
                    'show_cover_pic' => $show_cover_pic[$i],
                    'content' => urlencode($content[$i]),
                    'content_source_url' => $content_source_url[$i]
                );
                $articles_data['articles'][] = $article_data;
            }
//            var_dump($articles_data);exit;
//            echo json_encode($articles_data);exit;
            $url = 'https://api.weixin.qq.com/cgi-bin/material/add_news?access_token=';
            $data = json_encode($articles_data);
            $data = wechat_request($url, $data, false);
            $mediaId = $data->media_id;
//            $mediaId = '123123123123';
            $addMaterial = 'insert into '.$db_prefix.'material (mediaId, name, type, addTime, multi) values';
            $addMaterial .= ' (\''.$mediaId.'\',\''.$name.'\', \''.$type.'\', '.time().', 1)';
//            echo $addMaterial;exit;
            if( $db->insert($addMaterial) ) {
//            if( true ) {
                for($i = 0; $i < $count; $i++) {
                    $addMaterialNews = 'insert into '.$db_prefix.'materialNews (mediaId, title, thumb_media_id, show_cover_pic, author, digest, content, orderView, content_source_url, picUrl) values ';
                    $addMaterialNews .= ' (\''.$mediaId.'\', \''.$title[$i].'\', \''.$thumb_media_id[$i].'\', \''.$show_cover_pic[$i].'\', \''.$author[$i].'\', \'\', \''.$content[$i].'\', \''.$i.'\', \''.$content_source_url[$i].'\', \''.$picUrl[$i].'\')';
                    $db->insert($addMaterialNews);
                }
                showSystemMessage('添加多图文成功');
            } else {
                showSystemMessage('添加多图文失败');
            }
        }

    }

}


if( 'edit' == $opera ) {
    $mediaId = getPOST('id');
    $name = getPOST('name');
    $type = getPOST('type');

    if( empty($mediaId) ) {
        showSystemMessage($lang['warning']['param_error']);
    } else {
        $mediaId = $db->escape(trim($mediaId));
    }
    if( empty($type) ) {
        $type = 'image';
    }

    if( empty($name) ) {
        showSystemMessage('素材名称不能为空');
    } else {
        $name = $db->escape(trim($name));
    }
    if( $type == 'video' ) {
        $video_title = getPOST('video_title');
        if( empty($video_title) ) {
            showSystemMessage('视频标题不能为空');
        } else {
            $video_title = $db->escape(trim($video_title));
        }
        $introduction = getPOST('introduction');
        $introduction = empty($introduction) ? '' : $db->escape(trim($introduction));
    }


    switch($type) {
        case 'news':
            $multi = getPOST('multi');
            $multi = ( intval($multi) == 0 ) ? 0 : 1;
            if( $multi == 0 ) {         //单图文
                $title = getPOST('title');
                $author = getPOST('author');
                $picUrl = getPOST('picUrl');
                $show_cover_pic = getPOST('show_cover_pic');
                $digest = getPOST('digest');
                $content = getPOST('content');
                $content_source_url = getPOST('content_source_url');

                if( !empty($title) ) {
                    $title = $db->escape(trim($title));
                } else {
                    showSystemMessage('标题不能为空');
                }
                if( !empty($author) ) {
                    $author = $db->escape(trim($author));
                } else {
                    showSystemMessage('作者不能为空');
                }
                if( !empty($picUrl) ) {
                    if( file_exists(realpath('../../'.$picUrl)) ) {
                        $picUrl = $db->escape(trim($picUrl));
                    } else {
                        showSystemMessage('封面图片不存在');
                    }
                } else {
                    showSystemMessage('封面不能为空');
                }

                $show_cover_pic = intval($show_cover_pic) == 0 ? 0 : 1;

                if( !empty($digest) ) {
                    $digest = $db->escape(trim($digest));
                } else {
                    showSystemMessage('摘要不能为空');
                }
                if( !empty($content) ) {
                    $content = $db->escape(trim($content));
                } else {
                    showSystemMessage('内容不能为空');
                }
                if( !empty($content_source_url) ) {
                    $content_source_url = $db->escape(trim($content_source_url));
                } else {
                    showSystemMessage('原文链接不能为空');
                }
                $orderView = 0;
            } else {    //多图文
                $title = getPOST('title');
                $author = getPOST('author');
                $picUrl = getPOST('picUrl');
                $show_cover_pic = getPOST('show_cover_pic');
//                $digest = getPOST('digest');
                $content = getPOST('content');
                $content_source_url = getPOST('content_source_url');
                $newsId = getPOST('newsId');

                $count = count($title);
                for($i = 0; $i < $count; $i++) {
                    if( !empty($title[$i]) ) {
                        $title[$i] = $db->escape(trim($title[$i]));
                    } else {
                        showSystemMessage('标题不能为空');
                    }
                    if( !empty($author[$i]) ) {
                        $author[$i] = $db->escape(trim($author[$i]));
                    } else {
                        showSystemMessage('作者不能为空');
                    }
                    if( !empty($picUrl[$i]) ) {
                        if( file_exists(realpath('../../'.$picUrl[$i])) ) {
                            $picUrl[$i] = $db->escape(trim($picUrl[$i]));
                        } else {
                            showSystemMessage('封面图片不存在');
                        }
                    } else {
                        showSystemMessage('封面不能为空');
                    }

                    $show_cover_pic[$i] = intval($show_cover_pic[$i]) == 0 ? 0 : 1;

                    if( !empty($content[$i]) ) {
                        $content[$i] = $db->escape(trim($content[$i]));
                    } else {
                        showSystemMessage('内容不能为空');
                    }
                    if( !empty($content_source_url[$i]) ) {
                        $content_source_url[$i] = $db->escape(trim($content_source_url[$i]));
                    } else {
                        showSystemMessage('原文链接不能为空');
                    }
                }
            }
            break;
        case 'image':;
        case 'voice':
            $updateMaterial = 'update '.$db_prefix.'material set name = \''.$name.'\' where mediaId = \''.$mediaId.'\'';
            if( $db->update($updateMaterial ) ) {
                showSystemMessage('修改素材成功');
            } else {
                showSystemMessage('修改素材失败');
            }
            break;
        case 'video':
            $updateMaterial = 'update '.$db_prefix.'material set name = \''.$name.'\', video_title = \''.$video_title.'\', introduction=\''.$introduction.'\' where mediaId = \''.$mediaId.'\'';
            if( $db->update($updateMaterial ) ) {
                showSystemMessage('修改素材成功');
            } else {
                showSystemMessage('修改素材失败');
            }
            break;
    }
    if( $multi == 0 ) {
        //判断是否已存在图片素材，若存在，直接引用mediaId作为封面
        $thumb_media_id = 'select mediaId from ' . $db_prefix . 'material where type=\'image\' and picUrl = \'' . $picUrl . '\';';
        $thumb_media_id = $db->fetchOne($thumb_media_id);
        //图片素材不存在，添加素材
        if (empty($thumb_media_id)) {
            $real_path = realpath('../../' . $picUrl);
            $data = array(
                'type' => 'thumb',
                'media' => $real_path,
            );
            $url = 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token=';
            $data = wechat_request($url, $data, false, true);
            $thumb_media_id = $data->media_id;
            $addMaterial = 'insert into ' . $db_prefix . 'material (mediaId, name, type, picUrl, addTime, down_url) values';
            $addMaterial .= ' (\'' . $thumb_media_id . '\',\'' . $name . '封面\', \'image\', \'' . $picUrl . '\', ' . time() . ', \'' . $data->url . '\')';
            $db->insert($addMaterial);
        }
        $data = array(
            'media_id' => $mediaId,
            'index' => 0,
            'articles' => array(
                'title' => urlencode($title),
                'thumb_media_id' => $thumb_media_id,
                'author' => urlencode($author),
                'digest' => urlencode($digest),
                'show_cover_pic' => $show_cover_pic,
                'content' => urlencode($content),
                'content_source_url' => $content_source_url
            )
        );

        $data = json_encode($data);


        $url = 'https://api.weixin.qq.com/cgi-bin/material/update_news?access_token=';
        $data = wechat_request($url, $data);

        $updateMaterial = 'update ' . $db_prefix . 'material set name = \''.$name.'\'';
        $updateMaterial .= ' where mediaId = \''.$mediaId.'\'';
        if( $db->update($updateMaterial) ) {
            $updateMaterialNews = 'update .'.$db_prefix.'materialNews set title = \''.$title.'\'';
            $updateMaterialNews .= ',author = \''.$author.'\'';
            $updateMaterialNews .= ',picUrl = \''.$picUrl.'\'';
            $updateMaterialNews .= ',thumb_media_id = \''.$thumb_media_id.'\'';
            $updateMaterialNews .= ',show_cover_pic = '.$show_cover_pic;
            $updateMaterialNews .= ',digest = \''.$digest.'\'';
            $updateMaterialNews .= ',content = \''.$content.'\'';
            $updateMaterialNews .= ',content_source_url = \''.$content_source_url.'\'';
            $updateMaterialNews .= ' where mediaId = \''.$mediaId.'\'';
            $db->update($updateMaterialNews);
            showSystemMessage('更新单图文成功');
        } else {
            showSystemMessage('更新单图文失败');
        }
    } else {
        $articles_data = array();
        $thumb_media_id = array();
        for($i = 0; $i < $count; $i++) {
            $getThumb = 'select mediaId from '.$db_prefix.'material where type=\'image\' and picUrl = \''.$picUrl[$i].'\';';
            $thumb_media_id[$i] = $db->fetchOne($getThumb);
            if( empty($thumb_media_id[$i]) ) {
                $real_path = realpath('../../' . $picUrl[$i]);
                $data = array(
                    'type' => 'thumb',
                    'media' => $real_path,
                );
                $url = 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token=';
                $data = wechat_request($url, $data, false, true);
                $thumb_media_id[$i] = $data->media_id;
                $addMaterial = 'insert into '.$db_prefix.'material (mediaId, name, type, picUrl, addTime, down_url) values';
                $addMaterial .= ' (\''.$thumb_media_id[$i].'\',\'' . $name . '封面\', \'image\', \''.$picUrl[$i].'\', ' . time() . ', \''.$data->url.'\')';
                $db->insert($addMaterial);
            }
            $data = array(
                'media_id' =>  $mediaId,
                'index' => $i,
                'articles' => array(
                    'title' => urlencode($title[$i]),
                    'thumb_media_id' => $thumb_media_id[$i],
                    'author' => urlencode($author[$i]),
                    'digest' => '',
                    'show_cover_pic' => $show_cover_pic[$i],
                    'content' => urlencode($content[$i]),
                    'content_source_url' => $content_source_url[$i]
                )
            );
            $url = 'https://api.weixin.qq.com/cgi-bin/material/update_news?access_token=';
            $data = json_encode($data);
            $data = wechat_request($url, $data);

            if( !empty($newsId[$i]) ) { //更新
                $updateMaterialNews = 'update .'.$db_prefix.'materialNews set title = \''.$title[$i].'\'';
                $updateMaterialNews .= ',author = \''.$author[$i].'\'';
                $updateMaterialNews .= ',picUrl = \''.$picUrl[$i].'\'';
                $updateMaterialNews .= ',thumb_media_id = \''.$thumb_media_id[$i].'\'';
                $updateMaterialNews .= ',show_cover_pic = '.$show_cover_pic[$i];
                $updateMaterialNews .= ',content = \''.$content[$i].'\'';
                $updateMaterialNews .= ',content_source_url = \''.$content_source_url[$i].'\'';
                $updateMaterialNews .= ' where mediaId = \''.$mediaId[$i].'\'';
                $db->update($updateMaterialNews);
            } else {    //增加
                $addMaterialNews = 'insert into '.$db_prefix.'materialNews (mediaId, title, thumb_media_id, show_cover_pic, author, digest, content, orderView, content_source_url, picUrl) values ';
                $addMaterialNews .= ' (\''.$mediaId.'\', \''.$title[$i].'\', \''.$thumb_media_id[$i].'\', \''.$show_cover_pic[$i].'\', \''.$author[$i].'\', \'\', \''.$content[$i].'\', \''.$i.'\', \''.$content_source_url[$i].'\', \''.$picUrl[$i].'\')';
                $db->insert($addMaterialNews);
            }
        }

        $updateMaterial = 'update ' . $db_prefix . 'material set name = \''.$name.'\'';
        $updateMaterial .= ' where mediaId = \''.$mediaId.'\'';
        if( $db->update($updateMaterial) ) {
            showSystemMessage('修改多图文成功');
        } else {
            showSystemMessage('修改多图文失败');
        }
    }

}


if( 'list' == $act ) {
    $type = getGET('type');
    if (empty($type)) {
        $type = 'news';
    }
    $page = getGET('page');
    if (intval($page) <= 0) {
        $page = 1;
    }
    $multi = getGET('multi');
    if( empty($multi) ) {
        $multi = 0;
    }
    $count = 20;
    $offset = ($page - 1) * $count;

    assign('multi', $multi);
    assign('type', $type);

    $getMaterials = 'select mediaId, name, addTime, down_url from ' . $db_prefix . 'material where type = \''.$type.'\' and multi = '.$multi.' limit '.$offset.','.$count;
    $materials = $db->fetchAll($getMaterials);
    if ($materials) {
        assign('total_count', count($materials));
        foreach ($materials as $k => $v) {
            $v['addTime'] = date('Y-m-d H:i:s', $v['addTime']);
            $materials[$v['mediaId']] = $v;
            unset($materials[$k]);
        }
    } else {
        assign('total_count', 0);
    }
    if( $type == 'news' && $multi == 1 ) {
        $getNewsList = 'select n.* from '.$db_prefix.'materialNews as n';
        $getNewsList .= ' left join '.$db_prefix.'material as m on n.mediaId = m.mediaId';
        $getNewsList .= ' where m.type= \'news\' and multi = 1 order by n.mediaId';
        $newsList = $db->fetchAll($getNewsList);
        assign('newsList', $newsList);
    }


    assign('materials', $materials);


}

if( 'add' == $act ) {

    $type = getGET('type');
    if( empty($type) ) {
        $type = 'news';
    }
    switch($type) {
        case 'news':
            $multi = getGET('multi');
            $multi = (intval($multi) == 0 ) ? 0 : 1;
            assign('multi', $multi);
            break;
        case 'image':
            break;
        case 'voice':
            break;
        case 'video':
            break;
    }

    assign('type', $type);
}

if( 'sync' == $act ) {
    $type = getGET('type');
    if (empty($type)) {
        $type = 'image';
    }
    $page = 1;
    $count = 20;
    $offset = ($page - 1) * $count;

    $data = json_encode(array(
        'type' => $type,
        'offset' => $offset,
        'count' => $count,
    ));

    $synced_count = 0;  //已同步的数量
    $total_count = 5000;    //待同步的数量，接口最大限制为5000

    $isFirst = true;
    while( $synced_count < $total_count ) {
        $response_data = sync_material($data);
        $total_count = $response_data->total_count;
        $item_count = $response_data->item_count;
        foreach( $response_data->item as $item ) {
            $getMaterial = 'select mediaId from '.$db_prefix.'material where mediaId = \''.$item->media_id.'\'';
            if( $db->fetchOne($getMaterial) ) {
                $updateMaterial = 'update '.$db_prefix.'material set down_url = \''.$item->url.'\' where mediaId = \''.$item->media_id.'\'';
                $db->update($updateMaterial);
            } else {
                $addMaterial = 'insert into '.$db_prefix.'material (mediaId, name, addTime, down_url, type) values ';
                $addMaterial .= ' (\''.$item->media_id.'\', \'未命名\', '.$item->update_time.', \''.$item->url.'\', \''.$type.'\')';
                $db->insert($addMaterial);
            }
        }
        $synced_count += $item_count;
        $page++;
        $count = 20;
        $offset = ($page - 1) * $count;
        $data = json_encode(array(
            'type' => $type,
            'offset' => $offset,
            'count' => $count,
        ));
    }
    showSystemMessage('同步完成');
}

if( 'delete' == $act ) {
    $mediaId = getGET('id');
    if( empty($mediaId) ) {
        showSystemMessage($lang['warning']['param_error']);
    }
    $mediaId = $db->escape(trim($mediaId));

    $getCount = 'select count(*) from '.$db_prefix.'materialNews where thumb_media_id = \''.$mediaId.'\'';
    $count = $db->fetchOne($getCount);

    if( $count != 0 ) {
        showSystemMessage('该图片素材已被图文素材引用');
    }

    $getType = 'select type from '.$db_prefix.'material where mediaId = \''.$mediaId.'\' limit 1';
    $type = $db->fetchOne($getType);
    if( empty($type) ) {
        showSystemMessage('素材不存在');
    }
    $data = json_encode(array(
        'media_id' => $mediaId
    ));
    $url = 'https://api.weixin.qq.com/cgi-bin/material/del_material?access_token=';
    $data = wechat_request($url, $data, true);

    $deleteMaterial = 'delete from '.$db_prefix.'material where mediaId = \''.$mediaId.'\' limit 1';
    if( $db->delete($deleteMaterial) ) {
        if( $type == 'news' ) {
            $deleteMaterialNews = 'delete from '.$db_prefix.'materialNews where mediaId = \''.$mediaId.'\'';
            $db->delete($deleteMaterialNews);
        }
        showSystemMessage('删除素材成功');
    } else {
        showSystemMessage('删除素材失败');
    }
}

if( 'edit' == $act ) {
    $mediaId = getGET('id');
    if( empty($mediaId) ) {
        showSystemMessage($lang['warning']['param_error']);
    }
    $type = getGET('type');
    if( empty($type) ) {
        $type = 'image';
    }
    $mediaId = $db->escape(trim($mediaId));
    if( $type != 'news' ) {
        $getMaterial = 'select mediaId, type, name, video_title, introduction from ' . $db_prefix . 'material where mediaId = \'' . $mediaId . '\' limit 1';
        $material = $db->fetchRow($getMaterial);
        if (empty($material) || $type != $material['type']) {
            showSystemMessage('素材不存在');
        }

    } else {
        $getMaterial = 'select mediaId, type, name, multi from ' . $db_prefix . 'material where mediaId = \'' . $mediaId . '\' limit 1';
        $material = $db->fetchRow($getMaterial);
        if (empty($material) || $type != $material['type']) {
            showSystemMessage('素材不存在');
        }
        $getMaterialNews = 'select * from '.$db_prefix.'materialNews where mediaId = \''.$mediaId.'\'';
        if( $material['multi'] == 0 ) {
            $materialNews = $db->fetchRow($getMaterialNews);
        } else {
            $materialNews = $db->fetchAll($getMaterialNews);
        }
        assign('materialNews', $materialNews);
    }
    assign('material', $material);
    assign('type', $type);

}


assign('act', $act);
$smarty->display('material.phtml');