<?php
if (!function_exists('message')) {

	/**
	 * 消息数组函数
	 * @param string $msg 提示语
	 * @param bool $success 是否成功
	 * @param array $data 结果数据
	 * @param int $code 错误码
	 * @return array 返回消息对象
	 * @author 牧羊人
	 * @date 2020/7/1
	 */
	function message($msg = "操作成功", $success = true, $data = array(), $code = 0)
	{
		$result = array('msg' => $msg, 'data' => $data, 'success' => $success);
		if ($success) {
			// 成功统一返回0
			$result['code'] = 0;
		} else {
			// 失败状态(可配置常用状态码)
			$result['code'] = $code ? $code : -1;
		}
		return $result;
	}
}