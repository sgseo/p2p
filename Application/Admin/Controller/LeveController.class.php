<?php
// 全局设置
namespace Admin\Controller;
use Think\Controller;
class LeveController extends ACommonController
{
    public function index()
    {
		$leveconfig = FS("Webconfig/leveconfig");

		$this->assign('leve',$leveconfig);
        $this->display();
    }

    public function save()
    {
		alogs("Leve",0,1,'执行了信用积分等级数据编辑操作！');//管理员操作日志
		FS("leveconfig",$_POST['leve'],"Webconfig/");
		$this->success("操作成功",__URL__."/index/");
    }

    public function invest()
    {
        $leveconfig = FS("Webconfig/leveinvestconfig");

        $this->assign('leve',$leveconfig);
        $this->display();
    }

    public function investsave()
    {
		alogs("Leve",0,2,'执行了投资积分等级数据编辑操作！');//管理员操作日志
        FS("leveinvestconfig",$_POST['leve'],"Webconfig/");
        $this->success("操作成功",__URL__."/invest/");
    }
}
?>