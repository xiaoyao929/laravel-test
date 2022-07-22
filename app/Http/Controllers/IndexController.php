<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
    public $apiService = null;

    public function __construct()
    {
        $this->apiService = new APIService();
    }

    public function Index()
    {
        return view('index.index');
    }

    public function BenBoBa(Request $request)
    {
        $token = $request->get("token");
        if (empty($request->get("token"))) {
            $token = "jsc2skp.119a0bcf-a982-4e56-81cb-4e1df71a7023";
        }

        $url = "https://mapi.weimob.com/api3/o2oadapter/gateway/tradeServices/v1.0/booking/queryTimesByService";
        $headers = [
            "x-wx-token" => $token,
            "host" => "mapi.weimob.com",
            "accept" => "application/json",
            "content-type" => "application/json",
            "merchantid" => "100000931087",
            "channel-type" => "miniApp",
            "weimob-pid" => "100000931087",
            "pid" => "100000931087",
            "accept-encoding" => "gzip,compress,br,deflate",
            "user-agent" => "Mozilla/5.0 (iPhone; CPU iPhone OS 13_6_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 MicroMessenger/8.0.6(0x18000628) NetType/WIFI Language/zh_CN",
        ];

        $sites = [
            "1号场地" => [
                "goodsId" => 2388200604087,
                "skuId" => 2050200604087,
            ],
            "2号场地" => [
                "goodsId" => 2383200604087,
                "skuId" => 2048200604087,
            ],
            "3号场地" => [
                "goodsId" => 2384200604087,
                "skuId" => 2051200604087,
            ],
            "4号场地" => [
                "goodsId" => 2386200604087,
                "skuId" => 2052200604087,
            ],
            "5号场地" => [
                "goodsId" => 2385200604087,
                "skuId" => 2049200604087,
            ],
            "6号场地" => [
                "goodsId" => 2387200604087,
                "skuId" => 2053200604087,
            ],
        ];

        $params = [
            'preview' => false,
            'zhanId' => 610439,
            'appid' => 'wx4766ac101706163b',
            'appChannel' => '1',
            'env' => 'production',
            'pid' => '100000931087',
            'data' => [
                'orderSource' => '201',
                'cardCode' => '',
                'storeId' => 2125499087,
                'staffId' => ''
            ]
        ];

        $i = 0;
        while ($i < 10) {
            $timestamp = strtotime(date("Y-m-d")) + $i * 24 * 3600;
            $bookingDate = date("Y-m-d", $timestamp);
            $dateTime = $timestamp * 1000;
            $list[$bookingDate] = [];
            $params['data']['bookingDate'] = $dateTime;
            foreach ($sites as $name => $info) {
                $params['data']['goodsId'] = $info['goodsId'];
                $params['data']['skuId'] = $info['skuId'];
                $options['json'] = $params;
                $options['headers'] = $headers;

                $data = $this->apiService->getResponse("POST", $url, $options);

                if ($data['errcode'] != 0) {
                    if ($data['errcode'] == 1041) {
                        echo "<span style='color: red;'>token 已过期, 请重新获取token.</span>" . "<br>";
                        echo "<span style='color: red;'>本页面可自行使用新的token访问, 在地址栏后拼接token参数即可, 例: xxx/bbb?token=新token</span>";
                    } else {
                        echo $data['errmsg'] . "<br>";
                    }
                    die;
                }
                $list[$bookingDate][$name] = [];
                if (empty($data['data']['bookingCalendarList'])) {
                    continue;
                }
                $calendar = $data['data']['bookingCalendarList'];
                foreach ($calendar as $key => $value) {
                    if ($value['stock'] > 0) {
                        $timeRange = $value['startTime'] . "-" . $value['endTime'];
                        $list[$bookingDate][$name][] = $timeRange;
                    }
                }
            }
            $i++;
        }

        $weekMap = [
            "0" => "日",
            "1" => "一",
            "2" => "二",
            "3" => "三",
            "4" => "四",
            "5" => "五",
            "6" => "六",
        ];

        echo "<span style='color: red;'>奔波霸, 查询时间: &nbsp" . date("Y-m-d H:i:s") . "</span><br>";
        foreach ($list as $date => $value) {
            $week = date("w", strtotime($date));
            echo "<h2>" . $date . "&nbsp周" . $weekMap[$week] . "</h2>";
            foreach ($value as $site => $time) {
                if (empty($time)) {
                    echo "<p>" . $site . ": &nbsp无</p>";
                } else {
                    echo "<p>" . $site . ": &nbsp" . implode(", ", $time) . "</p>";
                }
            }
            echo "----------------------------------------------------------------";
            echo "<br>";
        }
        die;
        return view('index.bbb');
    }


    public function XinJiaYuan()
    {
        $options = [
            "headers" => [
                "host" => "feiyutiyu.cn",
                "accept" => "application/json, text/plain, */*",
                "accept-encoding" => "gzip, deflate, br",
                "user-agent" =>   "Mozilla/5.0 (iPhone; CPU iPhone OS 13_6_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 MicroMessenger/8.0.7(0x1800072d) NetType/WIFI Language/zh_CN",

            ]
        ];
        $loginUrl = 'https://feiyutiyu.cn/Api/Login/Index1?phone=13761718098&password=123321&platform=h5';
        $loginData = $this->apiService->getResponse("post", $loginUrl, $options);
        $token = $loginData['data']['token'];
        $venueSportId = "c2fcaa16fa2341579b23795e28a77014";

        $i = 0;
        $list = [];
        while ($i < 7) {
            $timestamp = strtotime(date("Y-m-d")) + $i * 24 * 3600;
            $bookingDate = date("Y-m-d", $timestamp);
            $list[$bookingDate] = [];
            $url = "https://feiyutiyu.cn/Api/Fields/getFieldState?venueSportId=" . $venueSportId . "&token=" . $token;
            $url .= "&date=" . $bookingDate;
            $data = $this->apiService->getResponse("get", $url, $options);
            if (empty($data['data'])) {
                echo $data['errmsg'];
                die;
            }
            $data = $data['data'];
            foreach ($data as $item) {
                $siteName = $item['Name'];
                $time = [];
                foreach ($item['states'] as $val) {
                    if ($val['state'] == 0) {
                        $time[] = $val['startTime'] . "-" . $val['endTime'];
                    }
                }
                $list[$bookingDate][$siteName]['site_name'] = $siteName;
                $list[$bookingDate][$siteName]['time'] = $time;
            }
            $i++;
        }

        $weekMap = [
            "0" => "日",
            "1" => "一",
            "2" => "二",
            "3" => "三",
            "4" => "四",
            "5" => "五",
            "6" => "六",
        ];
        echo "<span style='color: #ff0000;'>馨家园体育中心, 查询时间: &nbsp" . date("Y-m-d H:i:s") . "</span><br>";
        foreach ($list as $date => $value) {
            $week = date("w", strtotime($date));
            echo "<h2>" . $date . "&nbsp周" . $weekMap[$week] . "</h2>";
            foreach ($value as $site => $time) {
                if (empty($time)) {
                    echo "<p>" . $site . ": &nbsp无</p>";
                } else {
                    echo "<p>" . $site . ": &nbsp" . implode(", ", $time['time']) . "</p>";
                }
            }
            echo "----------------------------------------------------------------";
            echo "<br>";
        }
        die;

        return view('index.xjy');
    }

    public function CunLi()
    {
        // get auth data

        $authOptions = [
            "headers" => [
                "user-agent" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.83 Safari/537.36",
            ]
        ];
        $authUrl = "https://mvp.joyreserve.com/index.php/Wechat/Login/user_login?subnet_name=11ec490cd26eeb00";
        $tokenData = $this->apiService->getResponse("get", $authUrl, $authOptions);
        $authUrl = $tokenData['data']['auth_url'];
        $queryUrl = parse_url($authUrl);
        $loginToken = str_replace("token=", "", $queryUrl['query']);

        $headers = [
            "accept" => "application/json",
            "accept-encoding" => "gzip, deflate, br",
            "content-type" => "application/json",
            "origin" => "https://general-master.joyreserve.com",
            "user-agent" => "Mozilla/5.0 (iPhone; CPU iPhone OS 13_6_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 MicroMessenger/8.0.16(0x18001031) NetType/WIFI Language/zh_CN",
        ];

        // login
        $loginUrl = 'https://mvp.joyreserve.com/index.php/Wechat/Login/login_in';
        $loginHeaders = array_merge($headers, ["token" => $loginToken]);
        $loginParams = [
            "phone" => "13761718098",
            "password" => "Yl111111",
            "sign_list" => []
        ];
        $loginOptions = [
            "json" => $loginParams,
            "headers" => $loginHeaders,
        ];
        $loginData = $this->apiService->getResponse("post", $loginUrl, $loginOptions);
        $token = $loginData['data']['token'];

        if (!empty($_REQUEST['token'])) {
            $token = $_REQUEST['token'];
        }

        // search data
        $url = "https://mvp.joyreserve.com/index.php/Wechat/Index/time_display";
        $sitesHeaders = array_merge($headers, ["token" => $token]);

        $sites = [
            "1号场地" => [
                "id" => 4158,
            ],
            "2号场地" => [
                "id" => 4159,
            ],
            "3号场地" => [
                "id" => 4160,
            ],
            "4号场地" => [
                "id" => 4161,
            ],
            "5号场地" => [
                "id" => 4162,
            ],

        ];


        $bookingDate = date("Y-m-d");
        $list[$bookingDate] = [];
        $params['date'] = $bookingDate;
        foreach ($sites as $name => $info) {
            $params['display_type'] = "private";
            $params['resource_id'] = $info['id'];
            $sitesOptions = [
                "json" => $params,
                "headers" => $sitesHeaders,
            ];
            $data = $this->apiService->getResponse("post", $url, $sitesOptions);
            if ($data['code'] != 200) {
                echo $data['message'] . "<br>";
                die;
            }
            if (empty($data['data'][0]['item'])) {
                continue;
            }
            $calendar = $data['data'][0]['item'];
            foreach ($calendar as $key => $value) {
                foreach ($value['item'] as $k => $v) {
                    if ($v['is_reserve']) {
                        $timeRange = $v['time'];
                        $list[$value['date']][$name][] = $timeRange;
                    }
                }
            }
        }

        $weekMap = [
            "0" => "日",
            "1" => "一",
            "2" => "二",
            "3" => "三",
            "4" => "四",
            "5" => "五",
            "6" => "六",
        ];

        echo "<span style='color: red;'>村里的馆, 查询时间: &nbsp" . date("Y-m-d H:i:s") . "</span><br>";
        foreach ($list as $date => $value) {
            $week = date("w", strtotime($date));
            echo "<h2>" . $date . "&nbsp周" . $weekMap[$week] . "</h2>";
            foreach ($value as $site => $time) {
                if (empty($time)) {
                    echo "<p>" . $site . ": &nbsp无</p>";
                } else {
                    echo "<p>" . $site . ": &nbsp" . implode(", ", $time) . "</p>";
                }
            }
            echo "----------------------------------------------------------------";
            echo "<br>";
        }
        die;
        return view('index.cunli');
    }
}
