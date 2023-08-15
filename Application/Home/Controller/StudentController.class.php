<?php
namespace Home\Controller;

use Home\model\QqueryLogModel;
use Home\Model\StudentModel;
use Home\Service\StudentService;
use PhpZip\ZipFile;
use Think\Log;

class StudentController extends BaseController {
	const MAPPING = [
		'school_name' => '学校',
		'grade' => '年级',
		'class' => '班级',
		'name' => '姓名',
		'score' => '分数',
		'max_score' => '最高分',
		'min_score' => '最低分',
		'avg_score' => '平均分',
		'number' => '人数',
	];
	/**
	 * @var \XLSXWriter
	 */
	private $writer;

	public function __construct()
	{
		parent::__construct();
		Vendor('XlsxWriter.xlsxwriter#class');
		$this->write = new \XLSXWriter(); //如果提示不存在，就引入一下
	}

	protected function _initialize()
	{
		parent::_initialize(); // TODO: Change the autogenerated stub
		$this->model = new StudentModel();
		$this->service = new StudentService();
		$this->queryLogModel = new QqueryLogModel();
	}

	/**
	 * 把数据下载为文件
	 * @param array $title 标题数据
	 * @param array $data 内容数据
	 * @param string $fileName 文件名（可包含路径）
	 * @param int $type Excel下载类型：1-下载文件；2-浏览器弹框下载
	 * @param string $sheet Excel的工作表
	 */
	public function downloadExcel($title, $data, $fileName = '11.xlsx', $type = 2, $sheet = 'Sheet1')
	{
		// 设置导出的字体微软雅黑
		$styles1 = array( 'font'=>'Arial','font-size'=>10,'font-style'=>'bold', 'fill'=>'#eee', 'halign'=>'center', 'border'=>'left,right,top,bottom');

		if ($type == 2) {
			//设置 header，用于浏览器下载
			header('Content-disposition: attachment; filename="' . \XLSXWriter::sanitize_filename($fileName) . '"');
			header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
			header('Content-Transfer-Encoding: binary');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
		}

		//处理标题数据，都设置为string类型
		$header = [];
		foreach ($title as $value) {
			$header[$value] = 'string'; //把表头的数据全部设置为string类型
		}
		$this->write->writeSheetHeader($sheet, $header);

		//根据标题数据title，按title的字段顺序把数据一条条加到excel中
		foreach ($data as $key => $value) {
			$row = [];
			foreach ($title as $k => $val) {
				$row[] = $value[$k];
			}
			$this->write->writeSheetRow($sheet, $row, $styles1);
		}

		if ($type == 1) { //直接保存文件
			$this->write->writeToFile($fileName);
		} else if ($type == 2) { //浏览器下载文件
			$this->write->writeToStdOut();
			exit(0);
		} else {
			die('文件下载方式错误~');
		}
	}


	public function school() {
		// 查询并分组
		$result = $this->service->group('school_name');
		$this->ajaxReturn(message('success', 'success', $result));
	}

	public function grade() {
		// 查询并分组
		$result = $this->service->group('grade');
		$this->ajaxReturn(message('success', 'success', $result));
	}

	// 前端访问上来的 downloadZip 点击下载压缩包
	public function downloadZip()
	{
		$id = I('get.param'); // 自增id;
		$secret = I('get.secret'); // 自增id;
		$logRes = $this->queryLogModel->where(['id' => $id])->find();
		$sql = $logRes['query_sql'];
		$title = json_decode($logRes['header_mapping'], true);
		$result = $this->model->query($sql);
		$fileName = date('YmdHis') . rand(1000, 9999) . '.xlsx';
		$this->downloadExcel($title, $result, $fileName, 1);
		$zipFileName = 'downloaded_files.zip';
		$fileName = SELF_ROOT_PATH . $fileName;
		// 压缩 $fileName
		if (!empty($secret)) {
			$command = "zip -P $secret $zipFileName -j $fileName";
		} else {
			$command = "zip $zipFileName -j $fileName";
		}
		//Log::record('command: ' . $command, 'debug');
		system($command);

		// 设置响应头的 Content-Type
		header("Content-Type: application/zip");
		// 设置响应头的 Content-Disposition，指示浏览器下载附件
		header("Content-Disposition: attachment; filename=$zipFileName");
		// 设置响应头的 Content-Length，指示文件大小
		header("Content-Length: " . filesize($zipFileName));
		// 读取文件内容并发送给浏览器
		ob_clean();
		flush();
		@readfile($zipFileName);
		unlink($zipFileName);
		unlink($fileName);
		exit;
	}

	public function exportExcel()
	{
		$id = I('get.param'); // 自增id;
		$logRes = $this->queryLogModel->where(['id' => $id])->find();
		$sql = $logRes['query_sql'];
		$title = json_decode($logRes['header_mapping'], true);
		$result = $this->model->query($sql);
		$fileName = date('YmdHis') . '_statis' . '.xlsx';
		$this->downloadExcel($title, $result, $fileName);
		exit();
	}

	// index 历史查询记录回看
	public function resultByID()
	{
		$id = I('get.id'); // 自增id;
		$type = I('get.type');
		$logRes = $this->queryLogModel->where(['id' => $id])->find();

		$result = $this->queryLogModel->query($logRes['query_sql']);
		$tableHeader = json_decode($logRes['table_header'], true);
		$this->display("Public:header");
		$this->assign('queryLogID', $id);
		$this->assign('result', $result);
		$this->assign('tableHeader', $tableHeader);
		$this->assign('type', $type);
		$this->display('result');
		$this->display("Public:footer");
	}

	public function result() {
		$params = I('post.');
		$header = $params['fields'];
		// 组装成 {field:'username', width:100}
		$tableHeader = [];
		foreach ($header as $key => $value) {
			// 拼接成 {field:'id', width:80, sort: true} 的字符串，非json
			$excelHeader[$key] = self::MAPPING[$key];
			$tableHeader[self::MAPPING[$key]] = [
				'slug' => $key,
				'layData' => str_replace('"', "'", json_encode(
					[
						'field' => $key
					], JSON_UNESCAPED_UNICODE
				))
			];
		}

		$result = $this->service->handleData($params)['result'];
		$sql = $this->service->handleData($params)['sql'];

		// 把这次的查询条件存入数据库
		//$tableHeader
		$this->queryLogModel->add([
			'header_mapping' => json_encode($excelHeader, JSON_UNESCAPED_UNICODE),
			'table_header' => json_encode($tableHeader, JSON_UNESCAPED_UNICODE),
			'query_data' => json_encode($result, JSON_UNESCAPED_UNICODE),
			'org_params' => json_encode($params, JSON_UNESCAPED_UNICODE),
			'query_sql' => $sql,
			'status' => 2, // 临时保存，用户实际上并不知道
			'created_at' => date('Y-m-d H:i:s')
		]);
		// 获取新增的id
		$id = $this->queryLogModel->getLastInsID();

		$this->display("Public:header");
		$this->assign('queryLogID', $id);
		$this->assign('result', $result);
		$this->assign('tableHeader', $tableHeader);
		$this->display();
		$this->display("Public:footer");
	}

	// 对前端请求的数据二次清洗
	public function handleData($dataArr)
	{
		// 1. 获取数据
		// 2. condition
		//$condition = json_decode($dataArr['condition'], true);
		$jsonStr = json_decode(urldecode($dataArr['condition']), true)['jsonStr'];
		$conditions = json_decode($jsonStr, true);
		$whereConditions = array();
		$logicalOperator = 'AND'; // 默认逻辑操作符

		foreach ($conditions as $condition) {
			if (isset($condition['logicalOperator'])) {
				$logicalOperator = strtoupper($condition['logicalOperator']);
			}
			$conditionFieldVal = $condition['conditionFieldVal'];
			$conditionOptionVal = $condition['conditionOptionVal'];
			$conditionValue = $condition['conditionValueVal']['value'];
			$whereCondition = array();
			if ($conditionOptionVal == 'equal') {
				$whereCondition["$conditionFieldVal"] = $conditionValue;
			} elseif ($conditionOptionVal == 'unequal') {
				$whereCondition["$conditionFieldVal"] = array('NEQ', $conditionValue);
			}
			$whereConditions[] = $whereCondition;
		}

		// 构建完整的查询条件
		$where = array();
		if (!empty($whereConditions)) {
			$where['_logic'] = $logicalOperator;
			$where['_complex'] = $whereConditions;
		}
		// 3. fields
		$fields = $dataArr['fields'];
		// 4. selectedTags
		$selectedTags = explode(',', $dataArr['selectedTags']);
		// 5. showForm
		$showForm = $dataArr['showForm'];
		// 上面是拿到的条件， 需要组装成sql 查询，并记录值
		//$dataArr['selectedTags'] 是 group 字段
		// 查询的字段是 $fields = $dataArr['fields'];
		$fieldArr = [];
		//var_dump($fields);
		foreach ($fields as $key => $value) {
			if ($value === 'on' && $key === 'max_score') {
				$fieldArr[] = 'MAX(score) as max_score';
			} elseif ($value === 'on' && $key === 'min_score') {
				$fieldArr[] = 'MIN(score) as min_score';
			} elseif ($value === 'on' && $key === 'avg_score') {
				$fieldArr[] = 'AVG(score) as avg_score';
			} elseif ($value === 'on' && $key === 'number') {
				$fieldArr[] = 'COUNT(*) as number';
			} else {
				$fieldArr[] = $key;
			}
		}
		// 组装成 sql 动态的
		if ($showForm === 'no' && !empty($where)) {
			$result = $this->model
				->field(implode(',', $fieldArr))
				->where($where)
				->group($dataArr['selectedTags'])
				->select();
		} else {
			$result = $this->model
				->field(implode(',', $fieldArr))
				->group($dataArr['selectedTags'])
				->select();
		}
		Log::record('sql: ' . $this->model->getLastSql());
		return $result;
	}


	public function search()
	{
		$tableHeader = [];
		//		{
		//			"showForm":"no",
		//    "condition":"{"rowLength":1,"QueryCondition[0].conditionField":"school_name","QueryCondition[0].conditionOption":"equal","QueryCondition[0].conditionValue":"1111","QueryCondition[0].conditionValueLeft":"","QueryCondition[0].conditionValueRight":"","QueryCondition[0].logicalOperator":"and","QueryCondition[0].groupname":"groupname2","QueryCondition[0].subgroupname":"groupname5515024744430082","QueryCondition[0].rowlevel":"0","QueryCondition[0].datatype":null}",
		//    "selectedTags":"grade, class",
		//    "fields":{
		//			"min_score":"on",
		//        "avg_score":"on"
		//    }
		//}
		//按照年级和班级分组，计算平均分和最低分
		$result = $this->model->field('grade, class, MAX(score) as max_score, MIN(score) as min_score')
			->group('grade, class')
			->select();
		return $this->ajaxReturn(message('success', 'success', $result));
	}

	public function saveQueryLog()
	{
		$queryLogID = I('post.queryLogID');
		$queryLogName = I('post.queryLogName');
		if (empty($queryLogName)) {
			// 随机一个名字带上日期
			$queryLogName = '查询日志' . date('Y-m-d H:i:s');
		}
		$this->queryLogModel->where(['id' => $queryLogID])
			->save(['title' => $queryLogName, 'status' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
		return $this->ajaxReturn(message('保存成功', 'success', []));
	}

	public function history()
	{
		// 查询并分组
		$result = $this->queryLogModel->field('id, title, created_at, updated_at')
			->where(['status' => 1])
			->order('created_at desc')
			->select();
		$this->ajaxReturn(message('success', 'success', $result));
	}

	public function historyDel()
	{
		// 接收到路由上的id值 delete 发送过来的
		$id = I('post.id');
		$this->queryLogModel->where(['id' => $id])->save(['status' => 0]);
		$this->ajaxReturn(message('删除成功', 'success', []));
	}
}