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
use \Model\Logistics\L0010;

class Controller_Schedule_Fullcalendar_ChangeDateSchedule extends Controller_Rest {

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
            $data['item']['return'] = S0010::change_datetime_record($conditions, S0010::$db);
            // 入出庫登録
            // 予約IDが存在している場合
            if (!empty($conditions['id'])) {
                $logistics_list = L0010::getForms();
                $list           = array(
                    'schedule_id'               => $conditions['id'],
                );
                if ($item = L0010::getLogisticsBySchedule($list, S0010::$db)) {
                    $logistics_list['logistics_id']             = $item['logistics_id'];
                    $logistics_list['schedule_id']              = $conditions['id'];
                    $logistics_list['delivery_schedule_date']   = $conditions['start_date'];
                    $logistics_list['delivery_schedule_time']   = $conditions['start_time'];
                    $logistics_list['receipt_date']             = $conditions['start_date'];
                    $logistics_list['receipt_time']             = $conditions['start_time'];

                    $error_msg = L0010::update_record($logistics_list, $logistics_create_list, S0010::$db);
                    if (!empty($error_msg)) {
                        throw new Exception($error_msg, 1);
                    }
                }
            }

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
