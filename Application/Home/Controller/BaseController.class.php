<?php

namespace Home\Controller;

use Think\Controller;

class BaseController extends Controller
{
    // 模型
    protected $model;
    // 服务
    protected $service;

    /**
     * 初始化
     */
    protected function _initialize()
    {
	    // 初始化配置
	    $this->initConfig();
    }

	private function initConfig()
	{
		//系统分页参数
		define('PERPAGE', isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 0);
		define('PAGE', isset($_REQUEST['page']) ? $_REQUEST['page'] : 0);

		// 数据表前缀
		define('DB_PREFIX', C('DB_PREFIX'));
		// 数据库名
		define('DB_NAME', C('DB_NAME'));

		//系统应用参数
		define('APP', CONTROLLER_NAME);
		define('ACT', ACTION_NAME);
		$this->assign('module', __MODULE__);
		$this->assign('app', APP);
		$this->assign('act', ACT);
	}


    /**
     * 渲染模板
     * @param string $tpl 模板路径
     * @param array $param 参数
     * @author 牧羊人
     * @since 2021/1/17
     */
    public function render($tpl = "", $param = array())
    {
        if (empty($tpl)) {
            $tpl = ACT;
        } elseif (strpos($tpl, ".html") > 0) {
            $tpl = rtrim($tpl, ".html");
        }
        // 参数绑定
        if (!empty($param)) {
            foreach ($param as $name => $val) {
                $this->assign($name, $val);
            }
        }

        // 渲染头部
        $this->display("Public:header");
        // 渲染主体
        if (strpos($tpl, "/") === false) {
            $tpl = ltrim($tpl, "/");
        }
        $this->display(APP . ":{$tpl}");
        // 渲染底部
        $this->display("Public:footer");
    }
}