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

class Controller_Schedule_Fullcalendar_CancelSchedule extends Controller_Rest {

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
            $data['item']['return'] = S0010::delete_record($conditions, S0010::$db);
            // 予約IDが存在している場合
            if (!empty($conditions['id'])) {
                $logistics_list = L0010::getForms();
                $list           = array(
                    'schedule_id'               => $conditions['id'],
                );
                if ($data = L0010::getLogisticsBySchedule($list, S0010::$db)) {
                    $logistics_list['logistics_id'] = $data['logistics_id'];

                    // 対象の入出庫IDのデータを削除
                    $error_msg = L0010::delete_record($logistics_list, S0010::$db);
                    if (!empty($error_msg)) {
                        throw new Exception($error_msg, 1);
                    }

                    // 今回キャンセルした情報を元に以前の入出庫データを取得して出荷指示日を更新
                    $set_list   = array(
                        'customer_code'     => $data['customer_code'],
                        'car_id'            => $data['car_id'],
                        'car_code'          => $data['car_code'],
                    );
                    if ($result = L0010::getScheduleTypeLogisticsByScheduleData($set_list, S0010::$db)) {
                        foreach ($result as $key => $val) {
                            $set['car_id']                   = $data['car_id'];
                            $set['car_code']                 = $data['car_code'];
                            $set['customer_code']            = $data['customer_code'];
                            if ($error_msg = L0010::updLogisticsByCusAndCar('delete', $set, S0010::$db)) {
                                throw new Exception($error_msg, 1);
                            }
                        }
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
