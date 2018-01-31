<?php


namespace app\index\controller;

use think\Db;
class User extends Common{

    public function index()
    {
        echo "这是测试返回";
    }

	//启动先进行openid的判断，
	//如果openid不存在数据库，说明用户未注册
	//如果存在的话可正常进入程序
	public function login(){
		parent::_initializa();
		$data = $this->params;
		//检查opneid是否存在，不存在就去注册，存在则登录成功，返回姓名职称，工号
		if ( $this->check_exist($data["openid"])) {
				$db_res = Db::view('user','user_openid,user_name,user_num')
   						->view('position','position_name','position_id=user.user_pid')
   						->view('department','department_name','department_id=user.user_departmentid')
    					->where('user_openid', $data["openid"])
    					->find();
                $this->return_msg(200, '登录成功',$db_res);
            }else{
            	$this->return_msg(400, '未注册');
            }
		
	}

	//请假
	public function leave(){
		parent::_initializa();
		$data = $this->params;
        $res = db('activity') ->insertGetId($data);//插入数据返回id
        if ($res) {
            if($data["activity_secondcheck_openid"]==""||!isset($data["activity_secondcheck_openid"])){//插入单条数据，即活动没有第二审核人
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
            }else{//插入多条数据,有两个审核人存在的情况
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
   						->view('activity','activity_id,activity_type,activity_st,activity_et,activity_openid,activity_secondcheck_openid','activity_id=handle.handle_check_activityid')
                        ->view('user','user_name,user_openid','activity_openid=user.user_openid')
    					->where('handle_check_uid', $data["openid"])
    					->select();
    	 $this->return_msg(200, '查询成功',$db_res);
	}

	//处理向我申请的活动
	public function handleactivity(){
		parent::_initializa();
		$data = $this->params;

        $db_r = db('activity')
            ->field('activity_secondcheck_openid,activity_fristcheck_openid')
            ->where('activity_id', $data["activity_id"])
            ->find();
        if($data['openid']!=$db_r['activity_secondcheck_openid']&&$data['openid']!=$db_r['activity_fristcheck_openid']){
            $this->return_msg(400, '你无权审核该申请');
        }

        if($data['check_result']!=2){
            if($data['check_one_or_two']==1){//第一个审核人的审核结果
                $res = db('activity')->where('activity_id',$data['activity_id'])->setField('activity_fristcheck_result',$data['check_result']);//1是拒绝2是同意
            }else{//第二个审核人的结果
                $res = db('activity')->where('activity_id',$data['activity_id'])->setField('activity_secondcheck_result',$data['check_result']);//1是拒绝2是同意
            }
            if($res){
                db('handle')->delete($data['handle_id']);//需要删除handle里面的数据
            }
            //判断整个活动是否已经完成
            $db_handleAll = db('handle')
                ->where('handle_check_activityid', $data["activity_id"])
                ->count();
            if($db_handleAll==0){//已经完成
                $ress = db('activity')->where('activity_id',$data['activity_id'])->setField('activity_allresult',2);//
                $this->return_msg(200, "此申请已经通过",$db_handleAll);
            }

            $this->return_msg(200, "处理完成",$db_handleAll);
        }else{//如果有一个审核人不同意，则整个申请都为不通过
//            $re = db('handle')->delete($data['activity_id']);//这里删除要根据activity_id删除一个或者多个记录
            db('handle')->where('handle_check_activityid',$data['activity_id'])->delete();
            $ress = db('activity')->where('activity_id',$data['activity_id'])->setField('activity_allresult',3);//
            $this->return_msg(200, "此条审核不通过");
        }

	}

	//获取我发出的申请
	public function getmyactivitylist(){
		parent::_initializa();
		$data = $this->params;
		$db_res = db('activity')
			->field('activity_id,activity_openid,activity_type,activity_st,activity_et,activity_secondcheck_openid')
			->where('activity_openid', $data["activity_openid"])
            ->order('activity_id desc')
			->select();    
		 $this->return_msg(200, '查询成功',$db_res);    		
	}
	//获取单个我发出的申请
    public function getsinglemyactivity(){
        parent::_initializa();
        $data = $this->params;
        $db_res = Db::view('activity','activity_id,activity_openid,activity_type,activity_st,activity_et,activity_address,activity_reson,activity_fristcheck_openid,activity_secondcheck_openid,activity_fristcheck_result,activity_secondcheck_result,activity_time,activity_allresult')//多表查询
        ->view('user','user_name','activity_fristcheck_openid=user.user_openid')
            ->where('activity_id', $data["activity_id"])
            ->find();
        $db_res2 = db('user')
            ->field('user_name')
            ->where('user_openid', $data["activity_secondcheck_openid"])->find();
        $db_res3 = db('user')
            ->field('user_name')
            ->where('user_openid', $db_res["activity_openid"])->find();
        $db_res['secondname'] = $db_res2;
        $db_res['name'] = $db_res3;
        $this->return_msg(200, '查询成功',$db_res);
    }

	public function search(){
        parent::_initializa();
        $data = $this->params;
        $where["user_name"] = ['like',"%".$data["key"]."%"];
        $map['user_num'] = ['like',"%".$data["key"]."%"];
        $db_res = Db::view('user','user_name,user_openid,user_pid,user_num')//多表查询
        ->view('position','position_id,position_name','user_pid=position.position_id')
            ->where($where)
            ->whereOr($map)
            ->select();
        $this->return_msg(200, '查询成功',$db_res);
    }

    /**
     * 注册
     */
    public function regiter(){
        parent::_initializa();
        $data = $this->params;
        $db_res = Db::view('user','user_id,user_name,user_num')
            ->where('user_num', $data["num"])
            ->find();
        if($db_res["user_name"]!=$data["name"]){
            $this->return_msg(400, '工号姓名不一致，注册失败');
        }else{
            $res = db('user')->where('user_id',$db_res["user_id"])->setField('user_openid',$data["openid"]);
            if($res){
                $db_res = Db::view('user','user_openid,user_name,user_num')
                    ->view('position','position_name','position_id=user.user_pid')
                    ->view('department','department_name','department_id=user.user_departmentid')
                    ->where('user_openid', $data["openid"])
                    ->find();

                $this->return_msg(200, '注册成功',$db_res);
            } else {
                $this->return_msg(400, '已注册',$res);
            }
        }
    }

}