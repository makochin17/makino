<?php
/**
 * New GlobalWifi TOP画面
 * @author M.Komine
 */

class Controller_404 extends Controller_Template {

	// テンプレート定義
	public $template 	= 'template_error';
	private $head 		= 'head';

	private $type 		= '404';

	private function initViewForge(){
		$head   		= View::forge($this->head);

		$cnf          	= \Config::load('siteinfo', true);

		$head->title	= $cnf['system_title'];

		// テンプレートに定義するCSS・JS
		$ary_jquery_ui_css = array(
			''
		);
		Asset::css($ary_jquery_ui_css, array(), 'jquery_ui_css', false);

		$ary_style_css = array(
			''
		);
		Asset::css($ary_style_css, array(), 'style_css', false);

		$ary_footer_js = array(
			''
		);
		Asset::js($ary_footer_js, array(), 'footer_js', false);
		// テンプレートに渡す定義
		$this->template->head   = $head;
		$this->template->title	= $cnf['system_title'];
	}

	public function before() {
		parent::before();

		$this->initViewForge();
	}

	public function action_index() {
		$this->template->action_name 	= Request::main()->action;
		$this->template->content		= View::forge('404');
	}

	public function action_404() {
		// ページが見つからない
		$this->template->title   = 'ページが見つかりません。';
		$this->template->content = View::forge('404');
	}

}
