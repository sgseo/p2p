<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends HCommonController  {
    public function index(){
		$per = C('DB_PREFIX');
	    $Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
		//网站公告
		$parm['type_id'] = 26;
		$parm['limit'] =4;
		$this->assign("noticeList",getArticleList($parm));
    //网站公告
	
	$a=loan_total_info();
	//var_dump($a);
	
	
	$d['a1']= M('members m')->where($map)->count('m.id');;  //假的
	$d['a2']=$a['ordinary_total'];
	$d['a3']=$a['num_total'];
	$d['a4']=$a['has_also'];
	$d['a5']=$a['arrears'];	
	

	//var_dump($d);
	$this->assign("a1",$d);
    
    //正在进行的贷款
    $searchMap = array();
    $searchMap['b.borrow_status']=array("in",'2,4,6,7');
    //$searchMap['b.is_tuijian']=array("in",'0,1');
	//$searchMap['b.borrow_btype']=1;
    $parm=array();
    $parm['map'] = $searchMap;
    $parm['limit'] =6;
    $parm['orderby']="b.borrow_status ASC,b.id DESC";
    $listBorrow = getBorrowList($parm);
	//var_dump($listBorrow);
    $this->assign("listBorrow",$listBorrow);



	$searchMap = array();
    $searchMap['b.borrow_status']=array("in",'2,4,6,7');
    $searchMap['_string'] = "b.is_tuijian = 1";
	$parm=array();
    $parm['map'] = $searchMap;
    $parm['limit'] =2;
	$parm['_string'] = "b.is_tuijian = 1";
    $parm['orderby']="b.borrow_status ASC,b.id DESC";
    $listBorrow1 = getBorrowList($parm);
    $this->assign("listBorrow1",$listBorrow1);
	
	//测试时间获取函数开始
	//$date = date("Y-m-d",time());
   	//$ret =  get_date($date,'m');
   	//dump($ret);exit;
	//测试时间获取函数结束


	//////////////////////调取数据////////////	

		$money_log = get_money_log(0);
		$borrowOutNum = M( "borrow_info" )->where( "borrow_status in(6,7,8,9)" )->count( "id" );
		$transferBorrowOutNum = M( "transfer_borrow_info" )->count( "id" );
		$mborrowOutNum = $borrowOutNum + $transferBorrowOutNum;
		$shouyi=M('borrow_info')->sum('borrow_interest');
		$this->assign("shouyi",$shouyi);
		$this->assign( "mcount", M( "members" )->count( "id" ) );
		$this->assign( "mborrowOut",$money_log['17']['money']);
		$this->assign( "mborrowOutNum", $mborrowOutNum);

	//////////////////////调取数据////////////	



    //正在进行的贷款
    ///////////////省心宝列表开始  fan 2013-10-21//////////////
    $parm = array();
    $searchMap = array();
    //$searchMap['borrow_status']=2;
	//$searchMap['b.on_off']=1;
	//$searchMap['b.is_jijin']=0;
    //$searchMap['b.is_show'] = array('in','0,1');
	$searchMap['b.borrow_status'] = array('neq','3');
	//$searchMap['b.online_time']=array("lt",time());
    $parm['map'] = $searchMap;
    $parm['limit'] = 3;
    //$parm['orderby'] = "b.is_show desc,b.progress asc";
    $listTBorrow = getTBorrowList($parm);
    $this->assign("listTBorrow",$listTBorrow);
    ///////////////省心宝列表结束  fan 2013-10-21//////////////
    ///////////////省心宝列表开始  fan 2014-06-13//////////////
		/* hycd $parm = array();
		$searchMap = array();
		//$searchMap['borrow_status']=2;
		//$searchMap['is_tuijian']=0;
		$searchMap['on_off']=1;
		$searchMap['is_jijin']=1;
		$searchMap['b.online_time']=array("lt",time()+300);
		$parm['map'] = $searchMap;
		$parm['limit'] = 2;
		$parm['orderby'] = "b.is_show desc,b.borrow_status ASC,b.borrow_duration ASC,b.online_time desc";
		$listFBorrow = getTBorrowList($parm);
		$this->assign("listFBorrow",$listFBorrow);
		$this->assign('Bconfig',$Bconfig);*/
		$searchMap = array();
		$searchMap['b.borrow_status']=array("in",'2,4,6,7');
		$searchMap['b.is_tuijian']=array("in",'0,1');
		//$searchMap['b.borrow_btype']=2;
		$parm=array();
		$parm['map'] = $searchMap;
		$parm['limit'] = 2;
		$parm['orderby']="b.borrow_status ASC,b.id DESC";
		$listFBorrow = getTBorrowList($parm);
		//var_dump($listBorrow);
		$this->assign("listFBorrow",$listFBorrow);
	///////////////省心宝列表结束  fan 2014-06-13///////////////
    $this->display();
    /****************************募集期内标未满,自动流标 新增 2013-03-13****************************/
    //流标返回
    $mapT = array();
    $mapT['collect_time']=array("lt",time());
    $mapT['borrow_status'] = 2;
    $tlist = M("borrow_info")->field("id,borrow_uid,borrow_type,borrow_money,first_verify_time,borrow_interest_rate,borrow_duration,repayment_type,collect_day,collect_time")->where($mapT)->select();
    if(empty($tlist)) exit;
    foreach($tlist as $key=>$vbx){
    $borrow_id=$vbx['id'];
    //流标
    $done = false;
    $borrowInvestor = D('borrow_investor');
    $binfo = M("borrow_info")->field("borrow_type,borrow_money,borrow_uid,borrow_duration,repayment_type")->find($borrow_id);
    $investorList = $borrowInvestor->field('id,investor_uid,investor_capital')->where("borrow_id={$borrow_id}")->select();
    M('investor_detail')->where("borrow_id={$borrow_id}")->delete();
    if($binfo['borrow_type']==1) $limit_credit = memberLimitLog($binfo['borrow_uid'],12,($binfo['borrow_money']),$info="{$binfo['id']}号标流标");//返回额度
    $borrowInvestor->startTrans();
    
    $bstatus = 3;
    $upborrow_info = M('borrow_info')->where("id={$borrow_id}")->setField("borrow_status",$bstatus);
    //处理借款概要
    $buname = M('members')->getFieldById($binfo['borrow_uid'],'user_name');
    //处理借款概要
    if(is_array($investorList)){
    $upsummary_res = M('borrow_investor')->where("borrow_id={$borrow_id}")->setField("status",$type);
    foreach($investorList as $v){
    MTip('chk15',$v['investor_uid']);//sss
    $accountMoney_investor = M("member_money")->field(true)->find($v['investor_uid']);
    $datamoney_x['uid'] = $v['investor_uid'];
    $datamoney_x['type'] = ($type==3)?16:8;
    $datamoney_x['affect_money'] = $v['investor_capital'];
    $datamoney_x['account_money'] = ($accountMoney_investor['account_money'] + $datamoney_x['affect_money']);//投标不成功返回充值资金池
    $datamoney_x['collect_money'] = $accountMoney_investor['money_collect'];
    $datamoney_x['freeze_money'] = $accountMoney_investor['money_freeze'] - $datamoney_x['affect_money'];
    $datamoney_x['back_money'] = $accountMoney_investor['back_money'];
    
    //会员帐户
    $mmoney_x['money_freeze']=$datamoney_x['freeze_money'];
    $mmoney_x['money_collect']=$datamoney_x['collect_money'];
    $mmoney_x['account_money']=$datamoney_x['account_money'];
    $mmoney_x['back_money']=$datamoney_x['back_money'];
    
    //会员帐户
    $_xstr = ($type==3)?"复审未通过":"募集期内标未满,流标";
    $datamoney_x['info'] = "第{$borrow_id}号标".$_xstr."，返回冻结资金";
    $datamoney_x['add_time'] = time();
    $datamoney_x['add_ip'] = get_client_ip();
    $datamoney_x['target_uid'] = $binfo['borrow_uid'];
    $datamoney_x['target_uname'] = $buname;
    $moneynewid_x = M('member_moneylog')->add($datamoney_x);
    if($moneynewid_x) $bxid = M('member_money')->where("uid={$datamoney_x['uid']}")->save($mmoney_x);
    }
    }else{
    $moneynewid_x = true;
    $bxid=true;
    $upsummary_res=true;
    }
    
    if($moneynewid_x && $upsummary_res && $bxid && $upborrow_info){
    $done=true;
    $borrowInvestor->commit();
    }else{
    $borrowInvestor->rollback();
    }
    if(!$done) continue;
    
    
    MTip('chk11',$vbx['borrow_uid'],$borrow_id);
    $verify_info['borrow_id'] = $borrow_id;
    $verify_info['deal_info_2'] = text($_POST['deal_info_2']);
    $verify_info['deal_user_2'] = 0;
    $verify_info['deal_time_2'] = time();
    $verify_info['deal_status_2'] = 3;
    if($vbx['first_verify_time']>0) M('borrow_verify')->save($verify_info);
    else  M('borrow_verify')->add($verify_info);
    
    $vss = M("members")->field("user_phone,user_name")->where("id = {$vbx['borrow_uid']}")->find();
    SMStip("refuse",$vss['user_phone'],array("#USERANEM#","ID"),array($vss['user_name'],$verify_info['borrow_id']));
    //@SMStip("refuse",$vss['user_phone'],array("#USERANEM#","ID"),array($vss['user_name'],$verify_info['borrow_id']));
    //updateBinfo
    $newBinfo=array();
    $newBinfo['id'] = $borrow_id;
    $newBinfo['borrow_status'] = 3;
    $newBinfo['second_verify_time'] = time();
    $x = M("borrow_info")->save($newBinfo);
    }
    /****************************募集期内标未满,自动流标 新增 2013-03-13****************************/
    
    }	
  }
