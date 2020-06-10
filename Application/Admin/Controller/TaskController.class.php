<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;
use Common\Model\LevelModel;

class TaskController extends AdminBaseController{

    /**
     * 列表
     */
    public function index() {

        //会员等级
        $level_list = LevelModel::get_member_level();
        // unset($level_list[3]);//不分钻石会员
        $this->assign ( 'level_list', $level_list );

        $map = $this->_search();
        $type = I('get.type');
        if(!$type){
            $type=0;
        }
        $level = I('get.level');
        $start_date = I('get.start_date')?strtotime(I('get.start_date')):0;
        $end_date = I('get.end_date')?strtotime(I('get.end_date')):0;
        $map['type'] = $type;
        if( $level!='' ) $map['level'] = $level;


        if( $start_date!='' )   $map['create_time'] =['gt',$start_date];
        if( $end_date!='' )  $map['_string'] = " create_time < {$end_date} ";


        $on_show_total = M('task')->where(['no_show'=>1])->count();  //待审核

        //列表数据
        $list = $this->_list ('task', $map ,'',' end_time asc ,no_show asc ');

        $task_type = C('TASK_TYPE');
        foreach( $list as &$_list ) {
            $_list['level_name'] = $level_list[$_list['level']]['name'];
            $_list['type_name'] = $task_type[$_list['type']];
        }
        $this->assign('list',$list);
        $this->assign('time',time());
        $this->assign('on_show_total',$on_show_total);
        $this->assign('get',$_GET);



        $this->display();
    }

    /**
     * 添加、编辑操作
     */
    public function handle() {
        $model = M ('task');

        if( IS_POST ) {
            $data = I('post.');
            foreach($data as $key=>$val){
                if(is_array($val)){
                    $data[$key] = implode(',', $val);
                }
            }
            $data['timing_date']= strtotime($data['timing_date'].$data['timing_hour'].":00:00");
            unset($data['timing_hour']);
            $id = $data[$model->getPk ()];
            if( isset($data['content']) ) $data['content'] = I('content','',false);
            $data['end_time'] = strtotime($data['end_time']." 23:59:59");
            if( !($id > 0) ) {
                $time = time();
                $data['create_time'] = $time;
                $data['update_time'] = $time;
                if ( $model->add ($data) ) {
                    $this->success ('新增成功!', U('index'));
                } else {
                    $this->error ('新增失败!');
                }
            } else {
                if( !is_numeric($data['update_time']) ) {
                    $data['update_time'] = strtotime($data['update_time']);
                } else {
                    $data['update_time'] = time();
                }

                $copy = intval(I('post.copy'));
                if( $copy == 1 ) {
                    $time = time();
                    $data['create_time'] = $time;
                    $data['update_time'] = $time;
                    unset($data['id']);
                    if ( $model->add ($data) ) {
                        $this->success ('复制成功!', U('index'));
                    } else {
                        $this->error ('复制失败!');
                    }
                } else {
                    if ($model->save ($data) !== false) {

                        M('task_apply')->where(['task_id' => $id, 'is_end' => 0])->save(['end_time' => $data['end_time']]);

                        $this->success ('编辑成功!', U('index'));
                    } else {
                        $this->error ('编辑失败!');
                    }
                }
            }
        } else {
            $data = I('get.');
            $id = intval($data[$model->getPk()]);
            $timing_hour='';
            if( $id > 0) {
                $info = $model->find ( $id );


                //如果类型是自助发  和 描述是空就用
                if($info['type']==2&&!$info['content']){
                   $tushi = unserialize($info['tushi']);
                    foreach ($tushi as $li){
                        $info['content'].=$li;
                    }
                    $info['content'].='<br/>';
                    $step_info = unserialize($info['step_info']);
                    if(count($step_info['step_desc'])){
                        foreach ($step_info['step_desc'] as $key=>$li) {
                            $info['content'].='步骤:<br/>';
                            $step_num=$key+1;
                            $info['content'].=$step_num.'、'.$li;
                        }
                    }


                    if(count($step_info['step_img'])){

                        foreach ($step_info['step_img'] as $key=>$li) {
                            $info['content'].='<br/>如图:<br/>';
                            $step_num=$key+1;
                            $info['content'].=$step_num.'、' . '<img src="' .$li. '" width="300px"/>';
                        }
                    }



                }


                $timing_hour=date('H',$info['timing_date']);
                $this->assign ( 'info', $info );
            }

            $timing_hour=$timing_hour?$timing_hour:00;

            //会员等级
            $level = LevelModel::get_member_level();
//            unset($level[3]);//不分钻石会员
            $this->assign ( 'level', $level );
            $this->assign ( 'timing_hour', $timing_hour );
            $this->assign ( 'now_date', time() );
            $this->display ();
        }
    }


    /**
     * 删除
     */
    public function delete() {
        $model = M ('task');

        $ids = I('ids', '');
        if (is_array($ids)) {
            $ids = implode(',', $ids);
            $map['id'] = ['in', $ids];
        } else {
            $data = I('get.');
            $pk = $model->getPk();
            $id = intval($data[$pk]);
            $map[$pk] = array ('eq', $id );
        }

        $result = $model->where($map)->delete();
        if( $result ) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * xiao5    2019年7月10日17:35:03  批量删除
     */
    public function batchDel()
    {
        $ids = I('ids');
        var_dump($ids);
    }

    function task_on_top(){
        if( IS_POST ) {
            $id=intval(I('id'));
            $val=intval(I('val'));
            $data=[
                'id'=>$id,
                'task_on_top'=>$val,
            ];
            if( M('task')->save($data)){
                $this->success('success');
            }else{
                $this->error('操作失败');
            }


        }


    }

}