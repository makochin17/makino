<?php
/**
 * 社員検索画面
 */
use \Model\Init;
use \Model\AccessControl;
use \Model\Excel\Data;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\GenerateList;
use \Model\Common\PagingConfig;
use \Model\Common\OpeLog;
use \Model\Schedule\S0010;

class Controller_Schedule_Fullcalendar_CommitSchedule extends Controller_Rest {

    protected $format = 'xml';

	public function before() {
		parent::before();
        // ログインチェック
        if(!Auth::check()) {
            Response::redirect(\Uri::base(false));
        }

        // 初期設定(共通画面設定)
        $auth_data = AuthConfig::getAuthConfig('all');

	}

    public function action_index() {

        Config::load('message');

        /**
         * 検索項目の取得＆初期設定
         */
        $error_msg          = null;
        // Postデータを設定
        $conditions         = S0010::setForms('set', null, Input::param());
        // Xmlデータ初期化
        $data               = array();
        $data['item']       = array();

        try {
            DB::start_transaction(S0010::$db);

            // 予約スケジュール変更
            $data['item']['return'] = S0010::complete_record($conditions, S0010::$db);

            DB::commit_transaction(S0010::$db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(S0010::$db);
            Log::error($e->getMessage());
            $data['item']['return'] = $e->getMessage();
            // var_dump($e->getMessage());
        }

        return $this->response($data);

    }

}
