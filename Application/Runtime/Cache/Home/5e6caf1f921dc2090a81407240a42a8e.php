<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Layui</title>
	<meta name="renderer" content="webkit">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<link rel="stylesheet" href="/Public/Home/assets/libs/layui/css/layui.css"  media="all">
	<style>
		h2 {
			padding: 15px;
			border-bottom: 1px solid #d5d0d0;
		}

		.layui-layer-dialog .layui-layer-content {
			padding: 0px;
		}

		.layui-inline {
			vertical-align: inherit;
		}

		.unselect_btn {
			line-height: 2.4;
			border: 1px #c7cacb solid;
			background: #fff;
			padding: 0 15px;
			cursor: pointer;
			margin-right: 15px;
			color: #969899;
			border-radius: 2px;
		}

		.select_btn {
			line-height: 2.4;
			background: #4063AE;
			padding: 0 15px;
			margin-right: 15px;
			cursor: pointer;
			color: #fff;
			border: none;
			border: 1px #4063AE solid;
			border-radius: 2px;
		}

		.condition_title {
			text-align: left;
			padding-right: 10px;
			font-size: 16px;
			line-height: 2.4;
			vertical-align: top;
			width: 80px;
		}

		.selectedFairId {
			background: #EFF1F9;
			padding: 7px 7px 7px 7px;
			margin-right: 5px;
			border-radius: 4px;
		}

		.del {
			margin-left: 5px;
			cursor: pointer;
			color: #459ae9;
			text-decoration: none;
		}

		.del:HOVER {
			margin-left: 5px;
			cursor: pointer;
			color: red;
			text-decoration: none;
		}

		.more {
			border: none;
			vertical-align: top;
			color: #4668B0;
			border-radius: 4px;
			background: #EFF1F9;
			width: 80px;
			height: 30px;
			margin-top: 6px;
			cursor: pointer;
		}

		#search {
			font-size: 17px;
			background: #6888F8;
			line-height: 2.4;
			border: none;
			padding: 0 50px;
			cursor: pointer;
			margin: 0 auto;
			color: #fff;
			border-radius: 4px;
			display: block;
			margin-top: 50px;
		}

		.time_type_div {
			width: 85px;
		}

		.time_type_div .layui-select-title input {
			border: none;
			color: #4668B0;
		}

		.time_year_div {
			width: 75px;
		}

		.time_year_div .layui-select-title input {
			border: none;
			color: #4668B0;
		}

		.time_detail_div {
			width: 85px;
			display: none;
		}

		.time_detail_div .layui-select-title input {
			border: none;
			color: #4668B0;
		}

		.layui-form-select .layui-edge {
			border-top-color: #4668B0;
		}
		.right-aligned-button {
			text-align: right;
		}
	</style>
	<!-- 注意：如果你直接复制所有代码到本地，上述css路径需要改成你本地的 -->
</head>
<body>

<div class="layui-container">
	<fieldset class="layui-elem-field layui-field-title" style="margin-top: 30px;">
		<legend>基本统计</legend>
	</fieldset>

	<div class="layui-tab">
		<ul class="layui-tab-title">
			<li class="layui-this">总人数</li>
			<li>学校</li>
			<li>年级</li>
		</ul>
		<div class="layui-tab-content">
			<div class="layui-tab-item layui-show">
				总人数：<?php echo $total ?>
			</div>
			<div class="layui-tab-item">
				<table class="layui-hide" id="school"></table>
			</div>
			<div class="layui-tab-item">
				<table class="layui-hide" id="grade"></table>
			</div>
		</div>
	</div>

	<fieldset class="layui-elem-field layui-field-title" style="margin-top: 30px;">
		<legend>历史统计</legend>
	</fieldset>

	<div class="layui-tab">
		<table class="layui-hide" id="history-statis" lay-filter="history-statis-filter"></table>
	</div>

	<form class="layui-form" action="/home/student/result" method="post">
		<fieldset class="layui-elem-field layui-field-title" style="margin-top: 30px;">
			<legend>数据筛选</legend>
		</fieldset>

		<div class="layui-form-item">
			<label class="layui-form-label">是否使用全部数据：</label>
			<div class="layui-input-block">
				<input type="radio"  lay-filter="radio-type" name="showForm" value="yes" title="是" checked>
				<input type="radio" lay-filter="radio-type" name="showForm" value="no" title="否">
			</div>
		</div>

		<div id="formContainer" style="display: none;">
			<div id="complex-query" class="layui-form-item">
				<!-- <label class="layui-form-label">查询条件: </label>-->
				<div id="msg2"></div>
			</div>
			<input type="hidden" name="condition" id="condition">
		</div>

		<fieldset class="layui-elem-field layui-field-title" style="margin-top: 30px;">
			<legend>分组设置</legend>
		</fieldset>

		<div class="layui-form-item">
			<div class="layui-form-item">
				<label class="layui-form-label">分组字段:</label>
				<div class="tags-container" id="selectedTagList"></div>
			</div>
			<div class="layui-btn layui-btn-radius layui-btn-primary layui-btn-xs" id="addTagBtn">
				<i class="layui-icon layui-icon-add-circle"></i>
				<span>编辑分组字段</span>
			</div>
		</div>

		<!-- 隐藏的表单字段，用于提交选中的标签 -->
		<input type="hidden" name="selectedTags" id="hiddenSelectedTags">

		<div class="layui-form-item">
			<label class="layui-form-label">统计字段: </label>
				<div class="layui-input-block">
					<input type="checkbox" class="checkbox-item" name="fields[number]" title="人数" lay-skin="primary">
					<input type="checkbox" class="checkbox-item" name="fields[max_score]" title="最高分" lay-skin="primary">
					<input type="checkbox" class="checkbox-item" name="fields[min_score]" title="最低分" lay-skin="primary">
					<input type="checkbox" class="checkbox-item" name="fields[avg_score]" title="平均分" lay-skin="primary">
				</div>
		</div>

		<div class="layui-form-item">
			<button class="layui-btn layui-btn-fluid" lay-submit lay-filter="demo-submit">
				提交，下一步
			</button>
		</div>

	</form>

	<ul id="dcDemo" style="display:none;">
		<li field="school_name" title="学校" edit="text"></li>
		<li field="grade" title="年级" edit="text"></li>
		<li field="class" title="班级" edit="text"></li>
		<li field="extend_1" title="扩展组1" edit="text"></li>
		<li field="extend_2" title="扩展组2" edit="text"></li>
	</ul>

	<div id="tagSelectLayer" style="display: none;">
		<div class="layui-form" id="tagSelectForm">
			<div class="layui-form-item" id="tagOptions"></div>
			<div class="layui-btn-container right-aligned-button">
				<button class="layui-btn layui-btn-normal" lay-submit lay-filter="submitTags">确认</button>
			</div>
		</div>
	</div>
</div>

<script type="text/html" id="history-statis-bar">
	<div class="layui-clear-space">
		<a class="layui-btn layui-btn-xs layui-btn-danger"  lay-event="del">删除</a>
		<a class="layui-btn layui-btn-xs" lay-event="more"> 查看 </a>
	</div>
</script>

<script>
	// 提交下一步的时候检查是否选择了分组字段，
	layui.use(['form'], function () {
		var form = layui.form;
		form.on('submit(demo-submit)', function (data) {
			var selectedTags = $('#hiddenSelectedTags').val();
			if (selectedTags === '') {
				layer.msg('请选择分组字段');
				return false;
			}
			// 至少选择一个统计字段
			var checkedCount = $("input.checkbox-item:checked").length;
			if (checkedCount === 0) {
				layer.msg('请至少选择一个统计字段');
				return false;
			}
		});
	});

	// 控制查询条件是否展示
	layui.use(['form'], function () {
		var form = layui.form;
		$ = layui.$;
		form.on('radio(radio-type)', function (data) {
			var formContainer = document.getElementById('formContainer');
			if (data.value === 'yes') {
				formContainer.style.display = 'none';
			} else {
				formContainer.style.display = 'block';
			}
		});

		// radio-type 默认选中是
		// $("input[name='radio-type'][value='yes']").attr("checked", true);
		//如果你的 HTML 是动态生成的，自动渲染就会失效
		//因此你需要在相应的地方，执行下述方法来进行渲染
		form.render();
	});

	layui.use('table', function(){
		var table = layui.table;
		//第一个实例
		table.render({
			elem: '#school'
			// ,height: 312
			,url: "/home/student/school" //数据接口
			,page: true //开启分页
			,cols: [[ //表头
				{field: 'school_name', title: '学校名称',  fixed: 'left'}
				,{field: 'count', title: '学生数量',  sort: true}
			]]
		});
	});

	layui.use('table', function(){
		var table = layui.table;
		//第一个实例
		table.render({
			elem: '#grade'
			// ,height: 312
			,url: "/home/student/grade" //数据接口
			,limit: 10
			,page: true //开启分页
			,cols: [[ //表头
				{field: 'grade', title: '年级',  fixed: 'left'}
				,{field: 'count', title: '学生数量',  sort: true}
			]]
		});
	});

	// // history-statis
	layui.use('table', function(){
		var table = layui.table;
		//第一个实例
		table.render({
			elem: '#history-statis'
			// ,height: 312
			,type: 'get'
			,url: "/home/student/history" //数据接口
			,limit: 10 //注意：请务必确保 limit 参数（默认：10）是与你服务端限定的数据条数一致
			,page: true //开启分页
			,cols: [[ //表头
				{field: 'id', title: 'ID',  fixed: 'left', sort: true}
				,{field: 'title', title: '查询名称'}
				,{field: 'updated_at', title: '保存时间',  sort: true}
				,{field: 'action', title: '操作', toolbar: '#history-statis-bar'}
			]]
		});

		// 提交事件
		table.on('tool(history-statis-filter)', function(obj){
			console.log(obj)
			let id = obj.data.id;
			if(obj.event === 'del'){
				layer.confirm(' 确定删除行[id: '+ id +'] 么', function(index){
					obj.del(); // 删除对应行（tr）的DOM结构
					layer.close(index);
					// 向服务端发送删除指令
					$.ajax({
						url: '/home/student/historyDel',
						type: 'POST',
						data: {id: id},
						success: function (res) {
							layer.msg(res.msg);
						}
					});
				});
			} else if (obj.event === 'more') {
				window.location.href = '/home/student/resultByID?id=' + id + '&type=history';
			}
		});

	});

	layui.use(['index', 'table','layer', 'dynamicCondition', 'form'], function() {
		//兼容低版本ie
		var device = layui.device();
		var ie = parseInt(device.ie);
		$ = layui.$;
		console.log($)
		dynamicCondition = layui.dynamicCondition;
		var form = layui.form
				, table = layui.table;

		const hiddenSelectedTags = document.getElementById("hiddenSelectedTags");

		dynamicCondition.create({
			elem:"#dcDemo" //通过容器选择器传入，也可以$("#dcDemo"),或者document.getElementById("dcDemo")
			// ,tableId:"listTable" //静态页面不好演示table数据表格更新
			,type:"complex"  //type:"simple"/"complex"
			,conditionTextId:"#msg2"
			,popupMsgText:""
			//当有多个动态条件查询实例时，定义instanceName属性可以通过dynamicCondition.getInstance(instanceName)获取对应的实例
			,instanceName:  "complexInstance"
			,queryCallBack:function(requestData){
				//  encodeURIComponent 是为了解决中文乱码问题
				$("#result2").html(encodeURIComponent(JSON.stringify(requestData)));
				$("#condition").val(encodeURIComponent(JSON.stringify(requestData)));
			}
		});

		// 分组设置
		// const tags = ["学校", "年级", "班级", "扩展组1", "扩展组2"];
		const tags = [{"key":"school_name", "value":"学校"}, {"key":"grade", "value":"年级"}, {"key":"class", "value":"班级"}, {"key":"extend_1", "value":"扩展组1"}, {"key":"extend_2", "value":"扩展组2"}];
		const tagOptions = document.getElementById("tagOptions");
		tags.forEach(tag => {
			const checkboxDiv = document.createElement("div");
			checkboxDiv.classList.add("layui-form-item");
			checkboxDiv.setAttribute("style", "margin: 8px;")

			const checkbox = document.createElement("input");
			checkbox.type = "checkbox";
			checkbox.name = "selectedTags";
			checkbox.title = tag.value;
			checkbox.value = tag.key;
			checkbox.setAttribute("lay-skin", "primary")
			checkboxDiv.appendChild(checkbox);
			tagOptions.appendChild(checkboxDiv);
		});

		document.getElementById("addTagBtn").addEventListener("click", function() {
			layer.open({
				type: 1,
				title: "选择标签",
				content: $("#tagSelectLayer"),
				area: ['50%', 'auto'],
			});
		});

		form.on('submit(submitTags)', function(data) {
			const selectedTags = [];
			const selectedVal = [];
			$("input[name='selectedTags']:checked").each(function() {
				selectedTags.push($(this).attr("title"));
				selectedVal.push($(this).val());
			});

			const selectedTagList = document.getElementById("selectedTagList");
			selectedTagList.innerHTML = ""; // 清空已选项列表

			selectedTags.forEach(tag => {
				const div = document.createElement("div");
				div.classList.add("layui-btn", "layui-btn-primary", "layui-btn-xs");
				div.textContent = tag;
				selectedTagList.appendChild(div);
			});
			// 更新隐藏字段的值
			hiddenSelectedTags.value = selectedVal.join(", ");
			layer.closeAll();
			return false;
		});

		// 触发一次初始渲染
		form.render();
	});



	/**复杂查询*/
	function complexQuery(){
		dynamicCondition.getInstance("complexInstance").open();
	}
</script>

</body>
</html>