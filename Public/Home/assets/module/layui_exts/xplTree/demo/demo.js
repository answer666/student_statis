layui.config({
	base: '/static/layui-extend/',
}).extend({
});
layui.use(['table','form'], function(){
	initLayuiParam(layui);
	var userId = #(user.userId);
	$(".l-scroll").height($(document).height() - 35 - 10 - 22 - 48);
	var roleXplTree = null;
	//角色树
	var p1 = new Promise((resolve, reject) => {
		  $.post('/#(appName)/admin/user/getAllRoleList',{  },function(res){
			  if(res.code == 0){
				  roleXplTree = xplTreeUtil.create(res.data,{
						idName:"roleId",
						parIdName:"parRoleId",
						children:"list",
						openChildren:true,
						showName:"roleName",
						sortName:"sort",
//						parChecked: true,//子元素选中，父元素是否关联选中。true 关联选择，false 不关联
//			    		subChecked: true,//true：父元素选中，所有子元素，孙子元素都全选,父元素取消选中，所有子元素，孙子元素都取消选中。false 不关联
				  });
				  resolve(roleXplTree);
		      }else{
		    	  reject(res.msg);
		      }
		  });
	});
	//菜单树
	var p2 = new Promise((resolve, reject) => {
		  $.post('/#(appName)/admin/user/getAllMenuList',{  },function(res){
			  if(res.code == 0){
				  var xplTree = xplTreeUtil.create(res.data,{
						idName:"menuId",
						parIdName:"parentId",
						children:"list",
						openChildren:false,
						showName:"menuName",
						sortName:"sort",
						defaultDisabledValue: true,
						defaultDescrWidth:200,
//						checkedIdList:result[1]
				  });
				  resolve(xplTree);
		      }else{
		    	  reject(res.msg);
		      }
		  });
	});
	//权限树
	var p3 = new Promise((resolve, reject) => {
		  $.post('/#(appName)/admin/user/getAllApiList',{  },function(res){
			  if(res.code == 0){
				  var xplTree = xplTreeUtil.create(res.data,{
						idName:"apiURL",
						parIdName:"parApiURL",
						children:"list",
						openChildren:false,
						showName:"apiName",
						sortName:"level",
						defaultDisabledValue: true,
						defaultDescrWidth:200,
//						checkedIdList:result[1]
				  });
				  resolve(xplTree);
		      }else{
		    	  reject(res.msg);
		      }
		  });
	});

	var p4 = new Promise((resolve, reject) => {
		$.post('/#(appName)/admin/user/getUserRoleIdList',{userId: userId },function(res){
			  if(res.code == 0){
				  resolve(res.data);
		      }else{
		    	  reject(res.msg);
		      }
		  });
	});

	var p5 = new Promise((resolve, reject) => {
		  $.post('/#(appName)/admin/user/getAllRoleMenuList',{  },function(res){
			  if(res.code == 0){
				  resolve(res.data);
		      }else{
		    	  reject(res.msg);
		      }
		  });
	});
	var p6 = new Promise((resolve, reject) => {
		  $.post('/#(appName)/admin/user/getAllRoleApiList',{  },function(res){
			  if(res.code == 0){
				  resolve(res.data);
		      }else{
		    	  reject(res.msg);
		      }
		  });
	});
	Promise.all([p1, p2, p3, p4, p5, p6]).then((result) => {
		window.result = result;
//		console.log(result)               //[p1结果, p2结果]
		result[0].render("#roleTreeDiv", result[3]);
		//渲染菜单树
		result[0].bindTree(result[1], result[4],{container:"#menuTreeDiv",roleIdName:"roleId",menuIdName:"menuId"});
		result[0].bindTree(result[2], result[5],{container:"#apiTreeDiv",roleIdName:"roleId",menuIdName:"apiURL"});

		$(".optRadio").on("click",".layui-form-radio",function(){
			var eleRadio = $(this).prev();
			if(eleRadio.val() == "qbxz"){
//				layer.alert("全部选择");
				result[0].checkedAll();
			}else if(eleRadio.val() == "qbqx"){
//				layer.alert("全部取消");
				result[0].uncheckedAll();
			}else if(eleRadio.val() == "csxz"){
//				layer.alert("初始取消");
				result[0].initChecked();
			}
		});

		$(".optCheckbox").on("click",".layui-form-checkbox",function(){
			var eleCheckbox = $(this).prev();
			var idx = eleCheckbox.attr("idx");
			var checked = eleCheckbox.attr("checked") ? false : true;
			if(checked){
				eleCheckbox.attr("checked","checked");
			}else{
				eleCheckbox.removeAttr("checked");
			}
			if(eleCheckbox.val() == "jxsxz"){
				result[idx].onlyShowCheckedItems(checked);
			}
		});


	}).catch((error) => {
	  console.log(error);
	  layer.alert(error);
	});
	/***
	 * 提交表单
	 */
	window.commitFrm = function(closeFun){
		var idList = roleXplTree.getCheckedIdList();
		idList = idList.join(",");
		$.post("/#(appName)/admin/user/updateUserRole",{"idList":idList,"userId":userId},function(res){
			  if(res.code == "0"){
		      	var idx = layer.alert("更新成功！",function(){
		      		closeFun && closeFun(true);
		      		layer.close(idx);
				});
		      }else{
		    	  layer.msg(data.msg == "" ? "更新失败！" : data.msg);
		      }
		});
	}
	//提交
	$("#tj").on("click",function(){
		var closeFun = null;
		if(window.parent && window.parent.closeFun){
			closeFun = window.parent.closeFun;
		}
		commitFrm(closeFun);
	});
	//取消
	$("#qx").on("click",function(){
		var closeFun = null;
		if(window.parent && window.parent.closeFun){
			closeFun = window.parent.closeFun;
			closeFun(false);
		}else{
			roleXplTree.initChecked();
		}
	});

});




