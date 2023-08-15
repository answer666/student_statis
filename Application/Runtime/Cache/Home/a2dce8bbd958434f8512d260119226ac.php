<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <title><?php echo ($siteName); ?></title>
  <link href="/Public/Home/assets/images/favicon.ico" rel="icon">
  <link rel="stylesheet" href="/Public/Home/assets/libs/layui/css/layui.css"/>
  <link rel="stylesheet" href="/Public/Home/assets/module/admin.css"/>
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
  <script type="text/javascript" src="/Public/Home/assets/libs/layui/layui.js"></script>
  <script type="text/javascript" src="/Public/Home/assets/js/common.js?v=318"></script>
  <script type="text/javascript">
    var C = '<?php echo ($app); ?>';
    var A = '<?php echo ($act); ?>';
    var mUrl = "/";
    var cUrl = "/" + C;
    var aUrl = cUrl+"/"+A;
  </script>
</head>

<body>