<?php

namespace Home\Controller;

use Think\Controller;

class BaseController extends Controller
{
    // 用户ID
    protected $userId;
    // 用户信息
    protected $userInfo;
    // 模型
    protected $model;
    // 服务
    protected $service;

    /**
     * 初始化
     * @author 牧羊人
     * @since 2021/1/17
     */
    protected function _initialize()
    {
	    // 初始化配置
	    $this->initConfig();
    }

	private function initConfig()
	{
		// 网站全称
		$this->assign("siteName", C('SITE_NAME'));
		// 网站简称
		$this->assign("nickName", C('NICK_NAME'));
		// 版本号
		$this->assign('version', C('VERSION'));

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
     * 后台入口
     * @author 牧羊人
     * @since 2021/1/17
     */
    public function index()
    {
        if (IS_POST) {
            $result = $this->service->getList();
            $this->ajaxReturn($result);
            return;
        }
        // 默认参数
        $param = func_get_args();
        if (!empty($param)) {
            foreach ($param[0] as $key => $val) {
                $this->assign($key, $val);
            }
        }
		$this->assign('total', $this->service->getTotal());
        $this->render();
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

    /**
     * 404错误页面
     * @author 牧羊人
     * @since 2021/1/17
     */
    public function _empty()
    {
        $this->display('Public:404');
    }

    /**
     * 设置状态
     * @return mixed
     * @since 2021/1/18
     * @author 牧羊人
     */
    public function setStatus()
    {
        if (IS_POST) {
            $result = $this->service->setStatus();
            $this->ajaxReturn($result);
            return;
        }
    }

}