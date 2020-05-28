<?php
namespace Common\Controller;
use Common\Controller\BaseController;
use Common\Model\OauthUserModel;
use Common\Model\SystemConfigModel;

/**
 * Home基类控制器
 */
class HomeBaseController extends BaseController{

	/**
	 * 初始化方法
	 */
	public function _initialize(){
		parent::_initialize();
        //是否为推荐
        $smid = I('get.smid');
        if( !empty($smid) ) {
            session('smid', $smid);
        }

/*        if( $this->is_login() ) {
            $this->assign('is_login', 1);
            $this->assign('member', session('member'));
        } else {
            $this->assign('is_login', 0);
            $this->redirect('Public/login');
        }*/

        if( $this->is_login()){
          if(! $this->get_member_status()) {
              unset($_SESSION['member']);
              unset($_SESSION['member_client_info']);
              session_destroy();
              $this->redirect('Index/index');
          }

        }
        $system_config = SystemConfigModel::get();
//        var_dump($system_config);die;
        $this->assign('sys_config', $system_config);

	}

    /**
     * 浏览记录
     * @param $id
     * @param title
     */
    public function view_history($id,$title)
    {
        $keyword = $id . "|" .$title;
        $_history = array();
        if( empty($_SESSION['view_history']) || $_SESSION['view_history'] == '' || !isset($_SESSION['view_history']) ) {
            $_history[] = $keyword;
        } else {
            array_unshift($_SESSION['view_history'],$keyword);
            $_history = $_SESSION['view_history'];
        }
        $_history = array_unique($_history);
        if( count($_history) > 10 ) {
            #只保留10个记录
            $temp = array_chunk($_history,10,false);
            $history = $temp[0];
        } else {
            $history = $_history;
        }
        $_SESSION['view_history'] = $history;
    }

    /**
     * 获取浏览记录
     * @return array
     */
    public function get_view_history()
    {
        $data = $_SESSION['view_history'];
        $ret_data = array();
        if( !empty($data) ) {
            foreach( $data as $key=>$_data ) {
                $d = explode('|', $_data);
                $ret_data[$key]['id'] = $d[0];
                $ret_data[$key]['title'] = $d[1];
            }
        }
        return $ret_data;
    }


    /**
     * 分页列表
     * @param mixed|string $modelName 模型名称
     * @param array $map 查询条件
     * @param string $fields 查询字段
     * @param string $sortBy 排序
     * @param int $pageRows 默认分页条数
     * @return mixed
     */
    public function _list($modelName = CONTROLLER_NAME, $map = array(), $fields ='', $sortBy = '', $pageRows = 20) {
        $model = M($modelName);
        if( $sortBy == '' ) $sortBy = $model->getPk().' desc';
        $count = $model->where($map)->count();
        $page = sp_get_page_m($count,$pageRows);//分页
        $firstRow = $page->firstRow;
        $listRows = $page->listRows;
        if( isset($fields) || $fields != '' ){
            $list = $model->field($fields)->where($map)->order($sortBy)->limit("$firstRow , $listRows")->select();
        } else {
            $list = $model->where($map)->order($sortBy)->limit("$firstRow , $listRows")->select();
        }
        $this->assign("Page", $page->show());
        $this->assign("list", $list);
        $this->assign('count', $count);
        return $list; //返回数据方便数据重组
    }

    //处理用户过期任务
    public function handle_deteriortion_task(){

        $map['member_id'] = $this->get_member_id();
        if (!$map['member_id']){
            return true;
        }

        $map['status'] = ['eq','0'];
        $map['end_time'] = ['lt',time()];
        $model      = M('task_apply');
        $list      = $model->where($map)->select();
        if(count($list)>0){
            foreach ($list as $li){
                $model      = M('task_apply');
                $data=[];
                $data['id']=$li['id'];
                $data['status']='-3';
                $model->save($data);
            }
        }


    }

    //获取今日领取任务总数
    function today_task_total(){
        if(!$this->get_member_id()){
            return 0;
        }
        $today_time = strtotime(date("Ymd"));
        $where = " create_time > $today_time and member_id=".($this->get_member_id())."  and status <> -2 ";
        $task_limit_count = M('task_apply')->where( $where )->count();
        return $task_limit_count;
    }

}

