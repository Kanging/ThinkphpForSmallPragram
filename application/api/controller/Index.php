<?php

namespace app\api\controller;

class Index {
	public function index(){
		// $code = $_POST["code"];
//		 $this->return_msg(200, '登录成功');
		 echo "**************";
	}
}


 // $code = $_POST["code"];
 //    $appid = "wx24c3729c7e59af01";
 //    $secret = "4b6a69e487da2198bcf1dde830a700bc";

 //    $api = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$secret}&js_code={$code}&grant_type=authorization_code";


 //     function httpGet($url) {
 //        $curl = curl_init();
 //        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
 //        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
 //        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
 //        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
 //        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
 //        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
 //        curl_setopt($curl, CURLOPT_URL, $url);

 //        $res = curl_exec($curl);
 //        curl_close($curl);

 //        return $res;
 //    }

 //    $str = httpGet($api);

 //    echo $str;

