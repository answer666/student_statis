<?php if (!defined('THINK_PATH')) exit();?>
<!--
本「综合演示」包含：自定义头部工具栏、获取表格数据、表格重载、自定义模板、单双行显示、单元格编辑、自定义底部分页栏、表格相关事件与操作、与其他组件的结合等相对常用的功能，以便快速掌握 table 组件的使用。
-->
<div style="padding: 16px;">
	<input type="hidden" name="queryLogID"  value="<?php echo ($queryLogID); ?>">
	<table lay-filter="result-show" id="result-show">
		<thead>
			<tr>
				<?php if(is_array($tableHeader)): foreach($tableHeader as $key=>$vo): ?><th lay-data=<?php echo ($vo["layData"]); ?>><?php echo ($key); ?></th><?php endforeach; endif; ?>
			</tr>
		</thead>
		<tbody>
			<?php if(is_array($result)): foreach($result as $key=>$vo): ?><tr>
					<?php if(is_array($tableHeader)): foreach($tableHeader as $key=>$vo1): ?><td><?php echo ($vo[$vo1['slug']]); ?></td><?php endforeach; endif; ?>
				</tr><?php endforeach; endif; ?>
		</tbody>
	</table>
</div>
<script type="text/html" id="toolbarDemo">
	<div class="layui-btn-container">
		<button class="layui-btn layui-btn-sm" lay-event="getCheckData">下载ZIP</button>
		<button class="layui-btn layui-btn-sm" lay-event="getExcel">Excel导出</button>
		<?php if($type !== 'history'):?>
			<button class="layui-btn layui-btn-sm" lay-event="getData">保存查询记录</button>
		<?php endif;?>
		<button class="layui-btn layui-btn-sm" lay-event="getBack">返回</button>
	</div>
</script>
<script type="text/html" id="barDemo">
	<div class="layui-clear-space">
		<a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
		<a class="layui-btn layui-btn-xs" lay-event="more">
			更多
			<i class="layui-icon layui-icon-down"></i>
		</a>
	</div>
</script>
<script>
	layui.use(['table', 'dropdown'], function(){
		var table = layui.table;
		// var dropdown = layui.dropdown;
		// res = '<?php echo $result?>';
		// console.log(res);

		//转换静态表格
		table.init('result-show', {
			height: 315 //设置高度
			,limit: 10 //注意：请务必确保 limit 参数（默认：10）是与你服务端限定的数据条数一致
			,page: true
			//支持所有基础参数
			,defaultToolbar:[] //去除默认工具栏
			,toolbar: '#toolbarDemo'
			,height: 'full-35' // 最大高度减去其他容器已占有的高度差
			,css: [ // 重设当前表格样式
				'.layui-table-tool-temp{padding-right: 145px;}'
			].join('')
		});
		// table.r


		// 工具栏事件
		table.on('toolbar(result-show)', function(obj){
			var id = obj.config.id;
			var checkStatus = table.checkStatus(id);
			var othis = lay(this);
			var $ = layui.$;
			switch(obj.event){
				case 'getCheckData':
					layer.prompt({
						formType: 0,
						value: '',
						title: '请输入压缩密码',
						area: ['50%', '50%'], //自定义文本域宽高
						yes: function(index, layero){
							// 校验密码 把密码做个强校验 必须是6到10位的纯字母数字 不能有别的符号 确保安全
							var reg = /^[a-zA-Z0-9]{6,10}$/;
							if (!reg.test(layero.find(".layui-layer-input").val())) {
								layer.msg('密码格式不正确：必须是6到10位的纯字母数字');
								return false;
							}

							// layer.close(index);
							// layer.msg('压缩密码：'+layero.find(".layui-layer-input").val());
							var params = {
								// 传递的参数，可以根据需求修改
								// 取 input 的值, 而且要注意不要被转义
								param: $('input[name="queryLogID"]').val(),
								secret: layero.find(".layui-layer-input").val()
							};
							window.location.href = 'downloadZip?' + $.param(params);  // 发起导出请求
							layer.close(index);
						}
					});
					break;
				case 'getExcel':
					var params = {
						// 传递的参数，可以根据需求修改
						// 取 input 的值, 而且要注意不要被转义
						param: $('input[name="queryLogID"]').val(),
					};
					window.location.href = 'export_excel?' + $.param(params);  // 发起导出请求
					break;
				case 'getData':
					layer.prompt({
						formType: 0,
						// 默认为当前时间
						value: '',
						title: '请输入查询名称(如不填写，会生成默认名称)',
						area: ['50%', '50%'], //自定义文本域宽高
						yes: function(index, layero){
							$.ajax({
								url: '/home/student/saveQueryLog',
								type: 'post',
								data: {
									'queryLogID': $('input[name="queryLogID"]').val(),
									'queryLogName': layero.find(".layui-layer-input").val()
								},
								dataType: 'json',
								success: function(res){
									layer.msg(res.msg);
									layer.close(index);
								},
								error: function(res){
									console.log(res);
								}
							});
						}
					});
					break;
				case 'getBack':
					// 返回上一页
					window.history.back(-1);
					break;
			};
		});
	});
</script>