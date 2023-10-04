  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, user-scalable=yes, maximum-scale=1.0, minimum-scale=1.0">
  <meta name="description" content="">
  <meta name="keywords" content="">
  <meta name="auther" content="ProjectGroup" />

  <title><?php echo (!empty($title)) ? $title:'タイヤハウスまきの予約管理システム'; ?></title>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

  <!-- <link rel="stylesheet" href="./css/main.css" type="text/css" media="screen"> -->
  <!--[if lte IE 8]><script src="./js/ie/html5shiv.js"></script><![endif]-->
  <!--[if lte IE 9]><link rel="stylesheet" href="./css/ie9.css" /><![endif]-->
  <!--[if lte IE 8]><link rel="stylesheet" href="./css/ie8.css" /><![endif]-->

<?php
echo html_tag('link', array(
						'rel' => 'shortcut icon',
						'type' => 'image/png',
						'href' => Asset::get_file('icon/favicon.ico', 'img'),
						)
			  );
echo Html::meta('robots', 'noindex');

// echo Asset::js('common/jquery.min.js');
// echo Asset::js('common/skel.min.js');
// echo Asset::js('common/util.js');
// echo Asset::js('common/main.js');

// echo Asset::css('fullcalendar5/lib/main.css', array('rel' => 'stylesheet'));
// echo Asset::js('fullcalendar5/lib/main.js');

echo Asset::css('schedule/main.css', array('rel' => 'stylesheet'));
echo Asset::css('common/jquery-ui.css', array('rel' => 'stylesheet'));
// echo Asset::css('schedule/jqModal.css', array('rel' => 'stylesheet'));
echo Asset::css('common/jqModal.css', array('rel' => 'stylesheet'));

echo Asset::css('fullcalendar/core/main.css', array('rel' => 'stylesheet'));
echo Asset::css('fullcalendar/daygrid/main.css', array('rel' => 'stylesheet'));
echo Asset::css('fullcalendar/timegrid/main.css', array('rel' => 'stylesheet'));

echo Asset::js('common/jquery.min.js');
echo Asset::js('common/skel.min.js');
echo Asset::js('common/util.js');
echo Asset::js('common/main.js');

// echo Asset::js('schedule/jquery.min.js');
// echo Asset::js('schedule/skel.min.js');
// echo Asset::js('schedule/util.js');
// echo Asset::js('schedule/main.js');

echo Asset::js('fullcalendar/core/main.js');
echo Asset::js('fullcalendar/interaction/main.js');
echo Asset::js('fullcalendar/daygrid/main.js');
echo Asset::js('fullcalendar/timegrid/main.js');
echo Asset::js('fullcalendar/resource-common/main.js');
echo Asset::js('fullcalendar/resource-daygrid/main.js');
echo Asset::js('fullcalendar/resource-timegrid/main.js');
echo Asset::js('fullcalendar/core/locales/ja.js');
echo Asset::js('schedule/jquery.min.popup.js');
echo Asset::js('schedule/jqModal.js');
// echo Asset::js('fullcalendar/jquery.min.popup.js');
// echo Asset::js('fullcalendar/jqModal.js');


echo Asset::render('style_css');
echo Asset::render('header_js');

?>
<script src="https://ajaxzip3.github.io/ajaxzip3.js" charset="UTF-8"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.7.1/css/lightbox.css" rel="stylesheet">
<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.7.1/js/lightbox.min.js" type="text/javascript"></script>

