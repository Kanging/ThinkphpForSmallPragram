<?php

namespace app\index\controller;
use think\Request;
use think\Controller;
use think\Db;
use think\Validate;

class Common extends Controller{
	protected $request;//处理参数

	protected $params;

    protected $rules = array(//验证参数的合法性
	 	'User' => array(
            'login'           => array(//利用openid可完成登录
                'openid' => 'require',
            ),
//            'register'        => array(//利用姓名和工号可完成注册
//                'user_name' => 'require',
//                'user_num'  => 'require',
//            ),
            'leave'        => array(//申请加班
                'activity_openid' => 'require',//申请人id
                'activity_type'  => 'require',//申请类型，1还是2请假还是出差
                'activity_st'  => 'require',//开始时间
                'activity_et'  => 'require',//结束时间
                'activity_reson'  => 'require',
                'activity_fristcheck_openid' => 'require',
                'activity_time' => 'require|number',//活动时长
            ),
            'getactivitylist'   => array(//获取向我申请的活动，根据个人openid获取
                'openid' => 'require',
            ),
             'handleactivity'   => array(//处理一个活动
                'openid' => 'require',
                'check_one_or_two' => 'require',
                'activity_id' => 'require',
                'handle_id' => 'require',//handid
                'check_result' => 'require',//处理结果1是同意，2是拒绝
            ),
             'getmyactivitylist'   => array(//获取我的申请记录，根据个人openid获取
                'activity_openid' => 'require',
            ),
             'getcode'   => array(
                // 'code' => 'require',
            ),
             'search'   => array(//搜索
                 'key' => 'require',
            ),
            'getsinglemyactivity'   => array(//获取单个活动
                 'activity_id' => 'require',
//                 'activity_secondcheck_openid'=>'require',
            ),
            'regiter'   => array(//注册
                'name' => 'require',
                'num' => 'require',
                'openid' => 'require',
//                 'activity_secondcheck_openid'=>'require',
            ),
        ),
	 );

	protected function _initializa(){
		parent::_initialize();
		$this -> request = Request::instance();
		$this->check_params($this->request->param(true));
	}

	/**
     * 验证参数 参数过滤
     * @param  [array] $arr [除time和token外的所有参数]
     * @return [return]      [合格的参数数组]
     */
    public function check_params($arr) {
        /*********** 获取参数的验证规则  ***********/
        $rule = $this->rules[$this->request->controller()][$this->request->action()];
        /*********** 验证参数并返回错误  ***********/
        $this->validater = new Validate($rule);
        if (!$this->validater->check($arr)) {
            $this->return_msg(400, $this->validater->getError());
        }
        /*********** 如果正常,通过验证  ***********/
         $this->params =  $arr;
    }

	public function check_Openid($arr){
		if(!isset($arr['openid'])){
			$this -> return_msg(400,'openid不正确');
		}
		 $this->paramsOpenid = $arr['openid'];
	}

	public function return_msg($code,$msg = '',$data = []){
		$return_data['code'] = $code;
		$return_data['msg'] = $msg;
		$return_data['data'] = $data;

		echo json_encode($return_data);die;
	}


	public function check_exist($value) {
        
        $openid_res = db('user')->where('user_openid', $value)->find();
        return $openid_res;
        
        
    }
}