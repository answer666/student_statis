<?php

namespace Home\Service;

class BaseService
{
    // 模型
    protected $model;

    /**
     * 获取数据列表
     * @return array
     */
    public function getList()
    {
        // 初始化变量
        $map = array();
        $sort = 'id desc';
        $isSql = 0;

        // 获取参数
        $argList = func_get_args();
        if (!empty($argList)) {
            // 查询条件
            $map = (isset($argList[0]) && !empty($argList[0])) ? $argList[0] : array();
            // 排序
            $sort = (isset($argList[1]) && !empty($argList[1])) ? $argList[1] : 'id desc';
            // 是否打印SQL
            $isSql = isset($argList[2]) ? isset($argList[2]) : 0;
        }

        // 常规查询条件
        $param = I('post.', '', 'trim');
        if ($param) {
            // 筛选名称
            if (isset($param['name']) && $param['name']) {
                $map['name'] = array("like", "%{$param['name']}%");
            }
            // 筛选标题
            if (isset($param['title']) && $param['title']) {
                $map['title'] = array("like", "%{$param['title']}%");
            }
            // 筛选类型
            if (isset($param['type']) && $param['type']) {
                $map['type'] = $param['type'];
            }
            // 筛选状态
            if (isset($param['status']) && $param['status']) {
                $map['status'] = $param['status'];
            }
        }

        // 查询数据
        $result = $this->model->where($map)->order($sort)->page(PAGE, PERPAGE)->getField("id", true);

        // 打印SQL
        if ($isSql) {
            echo $this->model->_sql();
        }

        $list = array();
        if (is_array($result)) {
            foreach ($result as $val) {
                $info = $this->model->getInfo($val);
                $list[] = $info;
            }
        }

        //获取数据总数
        $count = $this->model->where($map)->count('id');

        //返回结果
        $result = array(
            "msg" => '操作成功',
            "code" => 0,
            "data" => $list,
            "count" => $count,
        );
        return $result;
    }

    /**
     * 添加或编辑
     * @return array
     */
    public function edit()
    {
        // 获取参数
        $argList = func_get_args();
        // 查询条件
        $data = isset($argList[0]) ? $argList[0] : array();
        // 是否打印SQL
        $is_sql = isset($argList[1]) ? $argList[1] : false;
        // 未传值时默认获取值
        if (empty($data)) {
            $data = I('post.', '', 'trim');
        }
        $error = '';
        $result = $this->model->edit($data, $error, $is_sql);
        if ($result) {
            return message();
        }
        return message($error, false);
    }

    /**
     * 设置状态
     * @return array
     */
    public function setStatus()
    {
        // 参数
        $data = I('post.', '', 'trim');
        // 记录ID
        if (!$data['id']) {
            return message('记录ID不能为空', false);
        }
        // 记录状态
        if (!$data['status']) {
            return message('记录状态不能为空', false);
        }
        $error = '';
        $result = $this->model->edit($data, $error);
        if (!$result) {
            return message($error, false);
        }
        return message();
    }

}