<?php
class Weather extends WechatResponse
{
    protected $responseType = 'news';
    protected $content;
    protected $response;

    public function __construct($fromUserName, $toUserName, $content)
    {
        parent::__construct($fromUserName, $toUserName);
        $this->content = $content;
    }

    public function run()
    {
        $content = str_replace('：', ':', $this->content);
        $content = explode(':', $content);
        $location = $content[1];
        $ak = 'dSu1p6ttGK9jEHdnH0pVGacK';
        $sk = 'YSujWxdnPb9ZIulL0aXU5Yd44ILFUHWW';
        $url = 'http://api.map.baidu.com/telematics/v3/weather?ak=%s&location=%s&output=json&sn=%s';
        $uri = '/telematics/v3/weather';
        $output = 'json';

        $querystring_arrays = array(
            'ak' => $ak,
            'location' => $location,
            'output' => $output
        );

        $sn = $this->caculateAKSN($ak, $sk, $uri, $querystring_arrays);
        $target = sprintf($url, $ak, urlencode($location), $sn);

        $data = get($target);
        $data = json_decode($data);

        if($data->status == 'success')
        {
            $results = $data->results[0];
            $weather = $results->weather_data;

            $this->response = array();
            foreach($weather as $key=>$w)
            {
                $item = array();
                if($key == 0)
                {
                    $item['title'] = $results->currentCity;
                    $now_h = date('H');
                    if($now_h > 18)
                    {
                        $item['picUrl'] = $w->nightPictureUrl;
                    } else {
                        $item['picUrl'] = $w->dayPictureUrl;
                    }
                    $item['title'] .= $w->date.",";
                    $item['title'] .= $w->temperature.",".$w->weather.",".$w->wind."\n";
                    $item['description'] = '';
                    $item['url'] = '';
                } else {
                    $item['title'] = $w->date.",".$w->weather.",".$w->temperature.",".$w->wind;
                    $item['description'] = '';
                    $item['url'] = '';
                    $item['picUrl'] = $w->dayPictureUrl;
                }
                $this->response[] = $item;
            }
        } else {
            $this->responseType = 'text';
            $this->response = $data->error.':没有找到该地址信息,请按照以下格式输入: '."\n".'天气:城市名,'."\n".'如:天气:广州';
        }
    }

    public function caculateAKSN($ak, $sk, $url, $querystring_arrays, $method = 'GET')
    {  
        if ($method === 'POST')
        {  
            ksort($querystring_arrays);  
        }  
        $querystring = http_build_query($querystring_arrays);
        return md5(urlencode($url.'?'.$querystring.$sk));  
    }

    public function __toString()
    {
        $responseObj = null;
        if($this->responseType == 'text')
        {
            $responseObj = new TextResponse($this->fromUserName, $this->toUserName, $this->response);
        } else {
            $responseObj = new NewsResponse($this->fromUserName, $this->toUserName, $this->response, false);
        }
        return $responseObj->__toString();
    }
}
