<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;
use Common\Model\LevelModel;

class LevelController extends AdminBaseController{
    /**
     * 列表
     */
    public function index() {
        $map = $this->_search();
        $model = M('level');
        $list = $model->order('level asc')->select();



        $this->assign('max_level',  end($list)['level']+1);
        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 添加、编辑操作
     */
    public function add() {
        $model = M('level');
        if( IS_POST ) {
            $data = I('post.');

            if($data['id']){
                unset($data['level']);
            }else{

                $data['level']  = intval($data['level']);
                if(!$data['level']){
                    $this->error ('请正确输入等级号');
                }
                $res =   M('level')->where(['level'=>$data['level']])->find();
                if($res){
                    $this->error ('当前等级已存在');
                }
            }
            $data = I('post.');
            if ( $model->add ($data) ) {
                $this->success ('新增成功!');
            } else {
                $this->error ('新增失败!');
            }
        }
    }

    /**
     * 添加、编辑操作
     */
    public function edit() {
        $model = M('level');
        if( IS_POST ) {
            $data = I('post.');
            if ($model->save ($data) !== false) {
                $this->success ('编辑成功!');
            } else {
                $this->error ('编辑失败!');
            }
        }
    }



    public function delete(){
        $model = M('level');
        $data=[];

        if( IS_POST ) {
            $id= I('post.id');
            $info   =    M('level')->where(['id'=>$id])->find();
            if(!$info){
                exit(json_encode($data));
            }

            $user   =    M('member')->where(['level'=>$info['level']])->find();
            if($user){
                exit(json_encode($data=['error'=>1,'msg'=>'已有会员为该等级，无法删除']));
            }
            $task   =    M('task')->where(['level'=>$info['level']])->find();
            if($task){
                exit(json_encode($data=['error'=>1,'msg'=>'已有任务为该等级，无法删除']));
            }

          M('level')->where(['id'=>$id])->delete();
            exit(json_encode($data));
        }
    }








}
