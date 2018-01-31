<?php


namespace app\api\controller;

use think\Db;
class User extends Common{





	//用户每次进来先进行openid的判断，
	//如果openid不存在数据库说明，用户未注册
	//如果存在的话可正常进入程序
	public function login(){
		
		// echo $id;	
		// echo '0000000';
		parent::_initializa();
		// quest -> only(['openid']));
		$data = $this->params;

		// echo $data;
		//检查opneid是否存在，不存在就去注册，存在则登录成功，返回姓名职称，工号
 		// $this->check_exist($data);
		// echo $data;
		if ( $this->check_exist($data["openid"])) {
				$db_res = Db::view('user','user_openid,user_name,user_num')
   						->view('position','position_name','position_id=user.user_pid')
   						->view('department','department_name','department_id=user.user_departmentid')
    					->where('user_openid', $data["openid"])
    					->find();

				// $db_res = db('user')
    //             ->field('user_id,user_name,user_num,user_powerid,department_name')
    //             ->where('user_openid', $data)
    //             ->find();

                $this->return_msg(200, '登录成功',$db_res);
            }else{
            	$this->return_msg(400, '未注册');
            }
		
	}


	//请假
	public function leave(){
		parent::_initializa();
		$data = $this->params;
        /*********** 写入数据库  ***********/
			// if ( !$this->check_exist($data["activity_openid"]) || !$this->check_exist($data["activity_fristcheck_openid"])) {
			// 		$this->return_msg(400, 'openid异常');
			// }

			 $res = db('activity') ->insertGetId($data);//插入数据返回id
			 if ($res) {
			 		// $db_res = Db::view('user','user_openid,user_name,user_num')
	   		// 				->view('position','position_name','position_id=user.user_pid')
	   		// 				->view('department','department_name','department_id=user.user_departmentid')
	    	// 				->where('user_openid', $data["openid"])
	    	// 				->find();
			        // $db_res = db('activity')
			        // 		->field('activity_id,activity_openid,activity_type,activity_st,activity_et')
			        // 		->where('activity_openid', $data["activity_openid"])
			        // 		->find();	
			 	
				if(!isset($data["activity_secondcheck_openid"])){//插入单条数据，即活动没有第二审核人
					

					$arr = array(
				 		'handle_check_uid' => $data["activity_fristcheck_openid"],
				 		'handle_one_or_two' => 1,
				 		'handle_check_activityid' => $res,
				 	);
			 		$re = db('handle') ->insertGetId($arr);//插入数据返回id
			 	 	if($re){
			           $this->return_msg(200, '新增成功!',$res);
			        } else {
			            $this->return_msg(400, '新增失败!');
			        }   
				}else{//插入多条数据
					// if ( !$this->check_exist($data["activity_secondcheck_openid"])) {
					// 	$this->return_msg(400, 'secondopenid异常');
					// }
					$dataall = [
							    ['handle_check_uid' => $data["activity_fristcheck_openid"], 'handle_check_activityid' => $res, 'handle_one_or_two' => 1],
							    ['handle_check_uid' => $data["activity_secondcheck_openid"], 'handle_check_activityid' => $res,'handle_one_or_two' => 2]
							];
					$reAll = Db::name('handle')->insertAll($dataall);

					if($reAll){
			           $this->return_msg(200, '新增成功!',$reAll);
			        } else {
			            $this->return_msg(400, '新增失败!');
			        } 
				}	    
			}
		
	}

	//获取向我申请的活动
	public function getactivitylist(){

		parent::_initializa();
		$data = $this->params;
		$db_res = Db::view('handle','handle_id,handle_check_uid,handle_check_activityid,handle_one_or_two')//多表查询
   						->view('activity','activity_id,activity_type,activity_st,activity_et','activity_id=handle.handle_check_activityid')
    					->where('handle_check_uid', $data["openid"])
    					->select();
    	 $this->return_msg(200, '查询成功',$db_res);
	}

	//处理向我申请的活动
	public function handleactivity(){
		parent::_initializa();
		$data = $this->params;

		if($data['check_one_or_two']==1){
			$res = db('activity')->where('activity_id',$data['activity_id'])->setField('activity_fristcheck_result',$data['check_result']);//1是拒绝2是同意
				if($res){
					$re = db('handle')->delete($data['handle_id']);
					
				}
			 
			}else{
			$res = db('activity')->where('activity_id',$data['activity_id'])->setField('activity_secondcheck_result',$data['check_result']);//1是拒绝2是同意
			if($res){
					$re = db('handle')->delete($data['handle_id']);//需要删除handle里面的数据
					
				}
		}
		 $this->return_msg(200, '审核处理完成',$re);
		
	}

	//获取我发出的申请
	public function getmyactivitylist(){
		parent::_initializa();
		$data = $this->params;
		$db_res = db('activity')
			->field('activity_id,activity_openid,activity_type,activity_st,activity_et')
			->where('activity_openid', $data["openid"])
			->select();    
		 $this->return_msg(200, '查询成功',$db_res);    		
	}

	// /**
 //     * 用户注册
 //     * @return [json] [api返回的json数据]
 //     */
 //    public function register() {
 //        /*********** 接收参数  ***********/
 //        $data = $this->params;
 //        /*********** 检查验证码  ***********/
 //        $this->check_code($data['user_name'], $data['code']);
 //        /*********** 检测用户名  ***********/
 //         $this->check_exist($data['user_name']);
 //        /*********** 将用户信息写入数据库  ***********/
 //        unset($data['user_name']);
 //        $data['user_rtime'] = time(); // register time
 //        $res                = db('user')->insert($data);
 //        if (!$res) {
 //            $this->retrun_msg(400, '用户注册失败!');
 //        } else {
 //            $this->return_msg(200, '用户注册成功!');
 //        }
 //    }


	public function getcode(){
		// echo json_encode($_GET);
		echo json_encode($_POST);  
		// parent::_initializa();
		// $data = $this->params;
		//  $code = $data["code"];
		 // $code = $_GET["code"];
	    // $appid = "wx24c3729c7e59af01";
	    // $secret = "4b6a69e487da2198bcf1dde830a700bc";

	    // $api = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$secret}&js_code={$code}&grant_type=authorization_code";


	    //  function httpGet($url) {
	    //     $curl = curl_init();
	    //     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    //     curl_setopt($curl, CURLOPT_TIMEOUT, 500);
	    //     // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
	    //     // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
	    //     curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
	    //     curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
	    //     curl_setopt($curl, CURLOPT_URL, $url);

	    //     $res = curl_exec($curl);
	    //     curl_close($curl);

	    //     return $res;
	    // }

	    // $str = httpGet($api);

	    // echo $code;
	}
  

}