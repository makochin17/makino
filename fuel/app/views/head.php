  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, user-scalable=yes, maximum-scale=1.0, minimum-scale=1.0">
  <meta name="description" content="">
  <meta name="keywords" content="">
  <meta name="auther" content="ProjectGroup" />

  <title><?php echo (!empty($title)) ? $title:'大西運輸基幹業務システム'; ?></title>

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
echo Asset::js('common/jquery.min.js');
echo Asset::js('common/skel.min.js');
echo Asset::js('common/util.js');
echo Asset::js('common/main.js');
//echo Asset::js('common/jquery-1.8.3.js');
// echo Asset::js('sidemenu/jquery.slider.js');
// echo Asset::js('sidemenu/sidemenu.js');
// jquery.ui.core.css, jquery.ui.datepicker.css, jquery.ui.theme.cssのCSS読み込み設定
echo Asset::render('jquery_ui_css');
echo Asset::css('common/style.css');
/* style_list.css, style_wide.css, style_shipmentcnt.css
/  style_inout.css, style_ship_return.css, style_mini.css
/  pickinglist/style_pickinglist.css, style_modal.css, fault/style.css
/  chart/jquery.jqplot.css, summary/itemcnt.css
/  のCSS読み込み設定
*/
echo Asset::render('style_css');
echo Asset::render('header_js');

?>
<script src="https://ajaxzip3.github.io/ajaxzip3.js" charset="UTF-8"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.7.1/css/lightbox.css" rel="stylesheet">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.7.1/js/lightbox.min.js" type="text/javascript"></script>