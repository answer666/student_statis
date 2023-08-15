
(function (){
	var xplTreeUtil = {
			//list列表对象转换为tree对象
			create:create,
	};
	//约定优于配置。设置默认配置选项
    var defaultConfig={
    		idName:"id",
    		parIdName:"parId",
    		showName:'name',
    		sortName:"",//默认不用排序
    		levelNum:"levelNum",//层级数属性名称。
    		children:"children",//子元素列表
    		openChildren:false, //默认隐藏子元素
    		container:null,
    		downArrow : "&#xe625;",//向下箭头图标，表示展开
    		rightArrow : "&#xe623;",//向右箭头，表示收缩
    		levelWidth: 25,//层级缩进宽度，单位xp
    		checked:'checked', //是否选中item
    		checkedIdList:[], //选中item的id数组
    		disabledName:"disabled", //该选项是否可勾选/取消勾选
    		defaultDisabledValue: false, //所有选项是否可勾选/取消勾选，默认选项框可以勾选/取消勾选
    		descrName:"descr",//节点描述属性名称
    		defaultDescrWidth: 30, //所有选项是否可勾选/取消勾选，默认选项框可以勾选/取消勾选
    		checkedItemAList:"checkedItemAList",//xplTreeB树元素itemB被选中，根据abList找出关联的itemA元素列表
    		eleIdName:"_eleId",//元素渲染生成树对应的DOM元素id属性名称。
    		parChecked: false,//子元素选中，父元素是否关联选中。true 关联选择，false 不关联
    		subChecked: false,//true：父元素选中，所有子元素，孙子元素都全选,父元素取消选中，所有子元素，孙子元素都取消选中。false 不关联
    		onlyShowChecked: false,//仅显示选中项。只有parChecked=true时，该配置才有效
    };
    function create(list, config){
    	var _config = {};
    	$.extend(true, _config ,defaultConfig, config);
    	var instance = new xplTree(list, _config);
    	config.container && instance.render(config.container, config.checkedIdList);
    	return instance;
    }
    function xplTree(list, config){
    	var res = listToTrees(list, config);
    	this.list = list;//原始数据列表
    	this.config = config;//树配置
    	this.cacheMap = res.cacheMap;//key=item.id，value=item  便于根据元素id快速查找元素
    	this.treeObjs = res.treeObjs;//数据转换为树结构，即元素有children子元素列表属性
    	//xplTreeBList 绑定的xplTreeB 数组。因为xplTreeA 可能不只绑定一个树，如绑定菜单树，角色树
    	this.xplTreeBList = [];
    }
    /***
     * 绑定关联的树，并渲染绑定的树. xplTreeA与xplTreeB绑定。xplTreeB元素是否选中，依赖于xplTreeA树元素是否选中。
     * 举例：xplTreeA 是角色树，xplTreeB是权限树。xplTreeA勾选一个角色， 根据角色与权限关系abList 表，自动勾选xplTreeB中的权限选项
     * config 是定义abList中 itemA的id与itemB的id属性名称，以及xplTreeB树的容器。
     * 如：{container:"#apiTreeDiv",roleIdName:"roleId",menuIdName:"apiURL"}
     */
    xplTree.prototype.bindTree = function(xplTreeB, abList, config){
    	var xplTreeA = this;
    	//缓存绑定关系
    	var relation = {xplTreeB: xplTreeB, abList:abList, config:config };
    	//xplTreeBList 绑定的xplTreeB 数组。因为xplTreeA 可能不只绑定一个树，如绑定菜单树，角色树
    	xplTreeA.xplTreeBList.push(relation);
    	xplTreeB.xplTreeA = xplTreeA;
    	//checkedItemBIds xplTreeB 树初始选中元素id列表
    	var checkedItemBIds = bindTree(xplTreeA, xplTreeB, abList, config);
    	//渲染xplTreeB树，并显示
    	xplTreeB.render(config.container, checkedItemBIds);
    }

    /***
     * 设置选择所有元素
     */
    xplTree.prototype.checkedAll = function(){
    	var instance = this;
    	var sourceData = instance.list;
    	var config = instance.config;
    	var parChecked = config.parChecked;
    	var subChecked = config.subChecked;
    	config.parChecked = false;
    	config.subChecked = false;
    	//先将所有元素的checked设置为false
    	for(var i=0, len = sourceData.length;i<len; i++){
    		var item = sourceData[i];
    		if(item[config.checked] == false){
    			$("#" + item[config.eleIdName]).children(".layui-form-checkbox").click();
    		}
    	}
    	config.parChecked = parChecked;
    	config.subChecked = subChecked;
    }
    /***
     * 设置取消选择所有元素
     */
    xplTree.prototype.uncheckedAll = function(){
    	var instance = this;
    	var sourceData = instance.list;
    	var config = instance.config;
    	var parChecked = config.parChecked;
    	var subChecked = config.subChecked;
    	config.parChecked = false;
    	config.subChecked = false;
    	//先将所有元素的checked设置为true
    	for(var i=0, len = sourceData.length;i<len; i++){
    		var item = sourceData[i];
    		if(item[config.checked] == true){
    			$("#" + item[config.eleIdName]).children(".layui-form-checkbox").click();
    		}
    	}
    	config.parChecked = parChecked;
    	config.subChecked = subChecked;
    }
    /***
     * 重置为初始选择元素
     */
    xplTree.prototype.initChecked = function(){
    	var instance = this;
    	var cacheMap = instance.cacheMap;
    	instance.uncheckedAll();
    	var sourceData = instance.list;
    	var config = instance.config;
    	var checkedIdList = config.checkedIdList;
    	var parChecked = config.parChecked;
    	var subChecked = config.subChecked;
    	config.parChecked = false;
    	config.subChecked = false;
    	//先将所有元素的checked设置为false
    	for(var i=0, len = checkedIdList.length;i<len; i++){
    		var checkedId = checkedIdList[i];
    		var item = cacheMap[checkedId];
    		$("#" + item[config.eleIdName]).children(".layui-form-checkbox").click();
    	}
    	config.parChecked = parChecked;
    	config.subChecked = subChecked;
    }
    /****
	 * 根据item的属性值，重新渲染其选中状态，以及后面的描述信息
	 * @param xplTree 树对象
	 * @param item 需要更新的元素
	 * @returns
	 */
    xplTree.prototype.updateCheckedAndDescr = function(item){
    	var instance = this;
		var config = instance.config;
		var checked = item[config.checked];
		var itemDiv = $("#" + item[config.eleIdName]);
		var itemContentDiv = itemDiv.children(".xpl-tree-item-content");
		//要设置为勾选状态，但目前未勾选，则addClass
		if(checked && !itemContentDiv.hasClass("layui-form-checked")){
			itemContentDiv.addClass("layui-form-checked");
		}
		//要设置为未勾选状态，但目前勾选，则removeClass
		else if(!checked && itemContentDiv.hasClass("layui-form-checked")){
			itemContentDiv.removeClass("layui-form-checked");
		}
		//更新描述
		var itemDescrDiv =  itemContentDiv.find(".item-descr");
		var descr = instance.getDescr(item);
		itemDescrDiv.html(descr);
		itemDescrDiv.attr("title", descr);
//		if(descr){
//			instance.updateDescrWidth(item);
//		}
	}
    /***
     * 获取选中的元素列表
     */
    xplTree.prototype.getDescr = function(item){
    	var instance = this;
    	var config = instance.config;
    	var descr = item[config.descrName] || "";
    	//checkedItemAList:"checkedItemAList",//xplTreeB树元素itemB被选中，根据abList找出关联的itemA元素列表
    	var checkedItemAList = item[config.checkedItemAList];
    	if(checkedItemAList && checkedItemAList.length > 0){
    		descr = "";
    		var xplTreeA = instance.xplTreeA;
    		var configA = xplTreeA.config;
    		for(var i=0, len = checkedItemAList.length;i<len; i++){
        		var itemA = checkedItemAList[i];
        		descr += "," + itemA[configA.showName];
        	}
    		descr = descr.substr(1);
    	}
    	return descr;
    }
    /***
     * 设置元素初始选项
     */
    xplTree.prototype.setCheckeds = function(checkedIdList){
    	var instance = this;
    	var sourceData = instance.list;
    	var config = instance.config;
    	var cacheMap = instance.cacheMap;
    	config.checkedIdList = parseIdList(checkedIdList);
    	//先将所有元素的checked设置为false
    	for(var i=0, len = sourceData.length;i<len; i++){
    		var item = sourceData[i];
    		item[config.checked] = false;
    	}
    	//设置初始已选元素
    	for(var i=0, len = config.checkedIdList.length;i<len; i++){
    		var checkedId = config.checkedIdList[i];
    		if(cacheMap[checkedId]){
    			cacheMap[checkedId][config.checked] = true;
    		}
    	}
    }
    /***
     * 获取选中的id列表
     */
    xplTree.prototype.getCheckedIdList = function(){
    	var instance = this;
    	var sourceData = instance.list;
    	var config = instance.config;
    	var checkedIdList = [];
    	//先将所有元素的checked设置为false
    	for(var i=0, len = sourceData.length;i<len; i++){
    		var item = sourceData[i];
    		if(item[config.checked]){
    			checkedIdList.push(item[config.idName]);
    		}
    	}
    	return checkedIdList;
    }
    /***
     * 与初始选中对比，获取新增的选择元素id列表
     */
    xplTree.prototype.getAddCheckedIdList = function(){
    	var instance = this;
    	var sourceData = instance.list;
    	var config = instance.config;
    	var initcheckedIdList = config.checkedIdList;
    	var checkedIdList = instance.getCheckedIdList();
    	var addCheckedIdList = [];
    	//遍历checkedIdList，获取新增的选项列表
    	for(var i=0, len = checkedIdList.length;i<len; i++){
    		var checkedId = checkedIdList[i];
    		if(initcheckedIdList.indexOf(checkedId) == -1){
    			//不包含该元素
    			addCheckedIdList.push(checkedId);
    		}
    	}
    	return addCheckedIdList;
    }
    /***
     * 与初始选中对比，获取删除的选择元素id列表
     */
    xplTree.prototype.getDelCheckedIdList = function(){
    	var instance = this;
    	var sourceData = instance.list;
    	var config = instance.config;
    	var initcheckedIdList = config.checkedIdList;
    	var checkedIdList = instance.getCheckedIdList();
    	var delCheckedIdList = [];
    	//遍历checkedIdList，获取新增的选项列表
    	for(var i=0, len = initcheckedIdList.length;i<len; i++){
    		var checkedId = initcheckedIdList[i];
    		if(checkedIdList.indexOf(checkedId) == -1){
    			//不包含该元素
    			delCheckedIdList.push(checkedId);
    		}
    	}
    	return delCheckedIdList;
    }
    /***
     * 根据元素id列表，获取对应元素列表
     */
    xplTree.prototype.getItemList = function(itemIdList){
    	var instance = this;
    	var cacheMap = instance.cacheMap;
    	var config = instance.config;
    	var itemList = [];
    	//先将所有元素的checked设置为false
    	for(var i=0, len = itemIdList.length;i<len; i++){
    		var itemId = itemIdList[i];
    		itemList.push(cacheMap[itemId]);
    	}
    	return itemList;
    }
    /***
     * 渲染树
     */
    xplTree.prototype.render = function(container, checkedIdList){
    	var instance = this;
    	var cacheMap = instance.cacheMap
    	var config = instance.config
    	checkedIdList && instance.setCheckeds(checkedIdList);
    	config.container = container;
    	var html = instance.renderItem(instance.treeObjs);
    	$(container).html(html);
    	if(config.onlyShowChecked){
    		instance.onlyShowCheckedItems(config.onlyShowChecked);
    	}
    	instance.updateDescrWidth(instance.treeObjs);
    	//树的展开与收缩
    	$(container).on('click', '.tree-down-arrow,.tree-right-arrow', function(e){
    		var iconJq = $(this);
    		var itemDiv = iconJq.parent();
    		var childrenDiv = itemDiv.children(".xpl-tree-item-children");
    		childrenDiv.toggle();
    		if(iconJq.hasClass("tree-down-arrow")){
    			//收缩，即隐藏子元素
				iconJq.removeClass("tree-down-arrow");
				iconJq.addClass("tree-right-arrow");
    			iconJq.html(config.rightArrow);
    		}else{
    			//扩展，即显示子元素
    			iconJq.removeClass("tree-right-arrow");
				iconJq.addClass("tree-down-arrow");
    			iconJq.html(config.downArrow);
    			//判断是否需要更新描述宽度
    			if(!childrenDiv.attr("updateDescrWidth")){
    				var item = cacheMap[itemDiv.children(".xpl-tree-item-content").attr("xpl-tree-item-id")];
    				var itemList = item[config.children] || [];
    				instance.updateDescrWidth(itemList);
    				//只更新一次，下次不则不用更新了。
    				childrenDiv.attr("updateDescrWidth","updateDescrWidth");
    			}

    		}
    	});
    	//选中与取消节点
    	$(container).on('click', '.layui-form-checkbox', function(e){
    		var divJq = $(this);
    		if(divJq.hasClass("layui-disabled")){
    			return ;
    		}
    		var item = instance.cacheMap[divJq.attr("xpl-tree-item-id")];
    		if(divJq.hasClass("layui-form-checked")){
    			divJq.removeClass("layui-form-checked");
    			item[instance.config.checked] = false;
    		}else{
    			divJq.addClass("layui-form-checked");
    			item[instance.config.checked] = true;
    		}
    		//更新关联树显示
    		var xplTreeBList = instance.xplTreeBList;
    		for(var i=0,len=xplTreeBList.length;i<len;i++){
    			var relation = xplTreeBList[i];
    			updateRelationTree(instance, item, relation);
    		}
    		//判断是否需要关联勾选parChecked: false,//子元素选中，父元素是否关联选中。true 关联选择，false 不关联
    		//subChecked: false,//true：父元素选中，所有子元素，孙子元素都全选,父元素取消选中，所有子元素，孙子元素都取消选中。false 不关联
    		var parChecked = config.parChecked;
    		//parChecked=true,并且子元素选中
    		if(parChecked == true && item[instance.config.checked] == true){
    			var parItem = cacheMap[item[config.parIdName]];
    			//存在父元素，并且父元素未选中
    			if(parItem && parItem[instance.config.checked] == false){
    				$("#" + parItem[config.eleIdName]).children(".layui-form-checkbox").click();
    			}
    		}
        	var subChecked = config.subChecked;
        	if(subChecked == true){
        		var children = item[config.children];
        		if(children){
        			for(var i=0,len=children.length;i<len;i++){
        				var subItem = children[i];
        				//子元素全选
                		if(item[instance.config.checked] == true && subItem[instance.config.checked] == false){
                			$("#" + subItem[config.eleIdName]).children(".layui-form-checkbox").click();
                		}
                		//子元素全部取消选中
                		else if(item[instance.config.checked] == false && subItem[instance.config.checked] == true){
                			$("#" + subItem[config.eleIdName]).children(".layui-form-checkbox").click();
                		}
        			}
        		}
        	}
    	});
    }

    /***
     * 获取显示描述信息宽度,每个item只初始化一次，并且只有对应item元素显示时才能初始化
     */
    xplTree.prototype.updateDescrWidth = function(item){
    	var instance = this;
    	var config = instance.config;
    	if(item instanceof Array){
    		var itemList = item;
    		//初始化描述宽度
        	for(var i=0,len=itemList.length;i<len;i++){
        		var item = itemList[i];
        		instance.updateDescrWidth(item);
        	}
        	return ;
    	}
    	var xplTreeA = instance.xplTreeA;
    	var itemDiv = $("#" + item[config.eleIdName]);
    	var widht = itemDiv.width();
    	var contentDiv = itemDiv.children(".xpl-tree-item-content");
    	var spanDivs = itemDiv.children(".xpl-tree-item-content").children("span");
    	//先减去图标
    	widht = widht - 16 - 24;
    	//最后减去showName对应的span的padding-right
    	widht = widht - 15 - 15;
    	//实际情况还是多了20多像素，优化
    	widht = widht - 50;
    	//最后减去showName对应的span宽度
    	if(spanDivs.length > 1){
    		widht = widht - spanDivs.eq(0).width();
    		spanDivs.eq(1).width(widht);
    	}
    }
	/****
	 * 递归生成树界面
	 * @param item
	 * @param config
	 * @param isLastNode 是否是兄弟节点中最后一个节点
	 * @returns
	 */
    xplTree.prototype.renderItem =function (item, isLastNode){
		var instance = this;
    	var config = instance.config
		item = item || [];
		var str = '';
		if (item instanceof Array) {
			for(var i=0;i<item.length;i++){
				var isLastNodeSub = i == item.length - 1 ? true : false;
				str += instance.renderItem(item[i], isLastNodeSub);
			}
		}else{
			var children = item[config.children];
			var hasChild = children && children.length>0 ? 1 : 0;
			var checkedClass = item[config.checked] ? "layui-form-checked" : "";
			checkedClass += item[config.disabledName] ? " layui-disabled" : " ";
			var openChildrenClass = config.openChildren ? "tree-down-arrow" : "tree-right-arrow";
			openChildrenClass = hasChild ? openChildrenClass : "";
			var nodePreStr = isLastNode ? "└" : "├";

			var descrStyleStr = "color:red;white-space: nowrap;display: inline-block;overflow: hidden; text-overflow: ellipsis;";
			descrStyleStr += "width: 0px;";
			str += '<div class="xpl-tree-item" id="'+item[config.eleIdName]+'">';
			str += '	<i class="layui-icon '+openChildrenClass+' " style="cursor:pointer;display:inline-block;width: 16px;">';
			str += 		 (hasChild ? (config.openChildren ? config.downArrow : config.rightArrow ) : "&nbsp;");
			str += '	</i>';
			if(item[config.levelNum] > 1){
				str += '		<span>'+nodePreStr+'─ </span>';
			}
			str += '	<div class="xpl-tree-item-content layui-form-checkbox '+checkedClass+'" lay-skin="primary" xpl-tree-item-id="'+item[config.idName]+'">';
			str += '		<span>' + item[config.showName] + '</span>';
			str += '		<span class="item-descr" style="'+descrStyleStr+'" title="'+instance.getDescr(item)+'">'+ instance.getDescr(item) +'</span>';
			str += '		<i class="layui-icon layui-icon-ok"></i>';
			str += '	</div>';
			str += '	<div class="xpl-tree-item-children" style="'+(config.openChildren ? "" : "display:none;" ) +'padding-left: '+ (item[config.levelNum] == 1 ? 5 : 5+config.levelWidth) +'px;">';
			str += '	' + instance.renderItem(children, false);
			str += '	</div>';
			str += '</div>';
		}
		return str;
	}

    /****
	 * 仅显示已选元素（隐藏未选元素）parChecked为true才能正常使用
	 * flag true 仅显示已选元素,false 显示所有元素
	 */
    xplTree.prototype.onlyShowCheckedItems =function(flag){
    	var instance = this;
    	var config = instance.config;
    	config.onlyShowChecked = flag;
    	var sourceData = instance.list;
    	for(var i=0,len=sourceData.length;i<len;i++){
    		var item = sourceData[i];
    		var itemDiv = $("#"+item[config.eleIdName]);
    		var checked = item[config.checked];
    		if(config.onlyShowChecked){
    			if(checked){
        			itemDiv.show();
        		}else{
        			itemDiv.hide();
        		}
    		}else{
    			itemDiv.show();
    		}
    	}
    }

	//list列表对象转换为tree对象
	function listToTrees(sourceData, config){
		//treeObjs：排序后的结果集
		var treeObjs = [];
		var cacheMap = {};
    	//list 数组转为Map对象，方便快速查找
    	for(var i=0, len = sourceData.length;i<len; i++){
    		var item = sourceData[i];
    		cacheMap[item[config.idName]] = item;
    		item[config.checked] = false;
    		item[config.disabledName] = config.defaultDisabledValue;
    		item[config.descrName] = item[config.descrName] || "";
    		item[config.eleIdName] = "xpl-tree-item-" + (Math.random()+"").substr(2);
    	}
    	//主键id属性名称
    	var idName = config.idName;
    	//父id属性名称
    	var parIdName = config.parIdName;
    	//排序属性名称
    	var sortName = config.sortName;
    	//遍历sourceData，关联设置父子关系。
    	for(var i=0, len = sourceData.length;i<len; i++){
    		var item = sourceData[i];
    		var parItem = cacheMap[item[config.parIdName]];
    		//parItem 不存在，则表示item是第一层元素，不用处理
    		if(parItem){
    			//存在，则判断期是否已关联子元素。
    			if(parItem[config.children]){
    				//直接添加子元素，并修改子元素的层级数
    				parItem[config.children].push(item);
    			}else{
    				//创建子元素数组，并修改父元素层级数和子元素的层级数
    				parItem[config.children] = [item];
    			}
    		}
    	}
    	//再次遍历sourceData，设置元素层级数。
    	var firstLevelItems = [];//第一层元素列表
    	for(var i=0, len = sourceData.length;i<len; i++){
    		var item = sourceData[i];
    		var parItem = cacheMap[item[config.parIdName]];
    		var levelNum = 1;
    		//获取层级数。
    		while(parItem){
    			levelNum++;
    			parItem = cacheMap[parItem[config.parIdName]];
    		}
    		item[config.levelNum] = levelNum;
    		if(levelNum == 1){
    			firstLevelItems.push(item);
    		}
    	}
    	if(sortName){
    		//递归排序。
        	var compareFun = function(itemA, itemB){
        		var sortA = itemA[sortName] || 0;
        		var sortB = itemB[sortName] || 0;
        		if(sortA < sortB){
        			return -1;
        		}else if(sortA > sortB){
        			return 1;
        		}
        		return 0;
            }
        	sort(firstLevelItems, config.children, compareFun);
    	}

//    	//排序完成后组装到treeObjs
//    	for(var i=0, len = firstLevelItems.length;i<len; i++){
//    		var item = firstLevelItems[i];
//    		addItem(treeObjs, item, config.children);
//    	}
		return {treeObjs:firstLevelItems, cacheMap:cacheMap};
	}
	/***
     * 递归添加元素。先添加父元素，然后再添加子元素。
     * @param cacheData
     * @param item
     * @returns
     */
    function addItem(cacheData, item, childrenName){
    	cacheData.push(item);
    	var children = item[childrenName];
    	if(children){
    		for(var i=0, len = children.length;i<len; i++){
        		var item = children[i];
        		addItem(cacheData, item, childrenName);
        	}
    	}
    }
    /***
     * 进行递归排序
     * @param items 待排序的数组
     * @param childrenName 子元素数组属性名称。如：children_避免冲突
     * @param compareFun 排序函数
     * @returns
     */
    function sort(items, childrenName, compareFun){
    	items.sort(compareFun);
    	for(var i=0, len = items.length;i<len; i++){
    		var item = items[i];
    		var children = item[childrenName];
        	if(children){
        		sort(children, childrenName, compareFun);
        	}
    	}
    }
    /***
     * 将逗号字符串转为数组格式，如果传入的是数组格式，则直接返回该数组。如果传入为空，则返回长度为0的数组[]。
     * @param idList
     * @returns
     */
    function parseIdList(idList){
    	if(idList){
    		if(typeof idList == "string"){
        		return idList.split(",");
        	}else{
        		return idList;
        	}
    	}else{
    		return [];
    	}
    }
    /****
     * 绑定关联的树，并渲染绑定的树. xplTreeA与xplTreeB绑定。xplTreeB元素是否选中，依赖于xplTreeA树元素是否选中。
     * 举例：xplTreeA 是角色树，xplTreeB是菜单树。xplTreeA勾选一个角色， 根据角色与权限关系aBList 表，自动勾选xplTreeB中的权限选项

     * @param xplTreeA 如：roleXplTree 角色树对象
     * @param xplTreeB 如：menuXplTree 菜单树对象
     * @param abList 如：roleMenuList 角色与菜单的关系列表
     * @param config 是定义aBList中 itemA的id与itemB的id属性名称，以及xplTreeB树的容器。
     * 	如：{container:"#menuTreeDiv",roleIdName:"roleId",menuIdName:"menuId"}
     * @returns xplTreeB 树初始选中元素id数组
     *  初始化xplTreeB树中itemB的checkedItemAList属性。
     *  config新增abMap属性。便于根据itemId开始获取itemBList
     */
    function bindTree(roleXplTree, menuXplTree, roleMenuList, config){
    	var roleIdList = roleXplTree.getCheckedIdList();
    	config = config || {roleIdName:"roleId",menuIdName:"menuId"};
    	var checkedMenuIds = new Set();
    	var roleMenuMap = {};
    	for(var i=0,len=roleMenuList.length;i<len;i++){
    		var item = roleMenuList[i];
    		//角色菜单Map
    		if(roleMenuMap[item[config.roleIdName]]){
    			roleMenuMap[item[config.roleIdName]].push(item[config.menuIdName]);
    		}else{
    			roleMenuMap[item[config.roleIdName]] = [item[config.menuIdName]];
    		}
    	}
    	//roleMenuMap根据角色id获取菜单id列表
//    	config.roleMenuMap = roleMenuMap;
    	config.abMap = roleMenuMap;
    	var roleCacheMap = roleXplTree.cacheMap;
    	var roleConfig = roleXplTree.config;
    	var menuCacheMap = menuXplTree.cacheMap;
    	var menuConfig = menuXplTree.config;
    	for(var i=0,len1=roleIdList.length;i<len1;i++){
    		var roleId = roleIdList[i];
    		var menuList = roleMenuMap[roleId];
    		var roleItem = roleCacheMap[roleId];
    		if(menuList && roleItem){
    			for(var j=0,len2=menuList.length;j<len2;j++){
    	    		var menuId = menuList[j];
    	    		checkedMenuIds.add(menuId);
    	    		var menuItem = menuCacheMap[menuId];
    	    		if(menuItem){
    	    			if(menuItem[menuConfig.checkedItemAList]){
        	    			menuItem[menuConfig.checkedItemAList].push(roleItem);
        	    		}else{
        	    			menuItem[menuConfig.checkedItemAList]=[roleItem];
        	    		}
    	    		}
    	    	}
    		}
    	}
    	return  Array.from(checkedMenuIds);
    }
    /***
     * itemA 选中状态发生了改变，更新xplTreeB树中对应元素显示（选中状态与后面描述）
     * @param xplTreeA
     * @param itemA xplTreeA树中触发选中或取消选中的元素
     * @param relation  {xplTreeB : xplTreeB, abList : abList, abMap : abMap, config : config }
     * 		abMap : key=itemAId,value=[itemBId1,itemBId2,...]
     * @returns
     */
    function updateRelationTree(xplTreeA, itemA, relation){
    	var configA = xplTreeA.config;
    	var itemAId = itemA[configA.idName];
    	var checked = itemA[configA.checked];
    	var xplTreeB = relation.xplTreeB;
    	var configB = xplTreeB.config;
    	var itemBIdList = relation.config.abMap[itemAId];
    	if(itemBIdList){
    		for(var i=0,len=itemBIdList.length;i<len;i++){
        		var itemBId = itemBIdList[i];
        		var itemB = xplTreeB.cacheMap[itemBId];
        		itemB[configB.checkedItemAList] = itemB[configB.checkedItemAList] || [];
        		var checkedItemAList = itemB[configB.checkedItemAList];
        		//修改itemB checkedItemAList字段，以及修改itemB是否勾选状态
        		//根据checked的值分2种情况。
        		//第一种情况：checked=true。 即勾选了itemA，则在checkedItemAList中添加itemA。然后判断对应的itemB是否勾选，如果未勾选，则设置为勾选。
        		//第二种情况：checked=false。 即取消勾选itemA，则在checkedItemAList中移除itemA。然后判断checkedItemAList.length是否为0，为0则设置为未勾选。
        		if(checked){
        			checkedItemAList.push(itemA);
        			itemB[configB.checked] = true;
        		}else{
        			removeArrItem(checkedItemAList, itemA);
        			if(checkedItemAList.length == 0){
        				itemB[configB.checked] = false;
        			}
        		}
        		xplTreeB.updateCheckedAndDescr(itemB);
        	}
    		xplTreeB.onlyShowCheckedItems(configB.onlyShowChecked);
    	}
    }
    /**
     * 移除数组中指定元素
     * @param arr
     * @param item
     * @returns 是否移除成功。true 移除成功，false 移除失败
     */
    function removeArrItem(arr,item){
    	var index = arr.indexOf(item);
    	if (index > -1) {
    		arr.splice(index, 1);
    		return true;
    	}
    	return false;
    }
    window.xplTreeUtil = xplTreeUtil;
//    exports("listToTrees",listToTrees);
})()

