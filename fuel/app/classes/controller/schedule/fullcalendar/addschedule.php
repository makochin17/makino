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

class Controller_Schedule_Fullcalendar_AddSchedule extends Controller_Rest {

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

    // 登録処理
    private function create_record($conditions, &$item) {

        $error_msg = null;
        try {
            DB::start_transaction(S0010::$db);

            // 予約情報登録
            $error_msg = S0010::create_record($conditions, $item, S0010::$db);
            if (!empty($error_msg)) {
                throw new Exception($error_msg, 1);
            }
            // 入出庫登録
            // 持込みフラグのチェックがない場合のみ
            if (empty($conditions['carry_flg']) || $conditions['carry_flg'] == 'NO') {
                $logistics_list = L0010::getForms();
                $list           = array(
                    'schedule_id'               => $item['schedule_id'],
                    'delivery_schedule_date'    => $conditions['start_date'],
                    'delivery_schedule_time'    => $conditions['start_time'],
                );
                if (!$data = L0010::getLogisticsBySchedule($list, S0010::$db)) {
                    $logistics_list['delivery_schedule_date']   = '';
                    $logistics_list['delivery_schedule_time']   = '';
                    $logistics_list['receipt_date']             = $conditions['start_date'];
                    $logistics_list['receipt_time']             = $conditions['start_time'];
                    $logistics_list['car_id']                   = $item['car_id'];
                    $logistics_list['car_code']                 = $item['car_code'];
                    $logistics_list['car_name']                 = $item['car_name'];
                    $logistics_list['customer_code']            = $item['customer_code'];
                    $logistics_list['customer_name']            = $item['customer_name'];
                    $logistics_list['consumer_name']            = $item['consumer_name'];
                    $logistics_list['schedule_id']              = $item['schedule_id'];

                    // 今回予約した情報を元に入出庫データを作成
                    $error_msg = L0010::create_record($logistics_list, $logistics_create_list, S0010::$db);
                    if (!empty($error_msg)) {
                        throw new Exception($error_msg, 1);
                    }
                    // 今回予約した情報を元に以前の入出庫データを取得して出荷指示日を更新
                    $set_list   = array(
                        'customer_code'     => $item['customer_code'],
                        'car_id'            => $item['car_id'],
                        'car_code'          => $item['car_code'],
                    );
                    if ($result = L0010::getScheduleTypeLogisticsByScheduleData($set_list, S0010::$db)) {
                        foreach ($result as $key => $val) {
                            $set['delivery_schedule_date']   = $conditions['start_date'];
                            $set['delivery_schedule_time']   = $conditions['start_time'];
                            $set['car_id']                   = $item['car_id'];
                            $set['car_code']                 = $item['car_code'];
                            $set['customer_code']            = $item['customer_code'];
                            if ($error_msg = L0010::updLogisticsByCusAndCar('update', $set, S0010::$db)) {
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
            $item['return'] = $e->getMessage();
            // var_dump($e->getMessage());
            return Config::get('m_CE0001');
        }
        return null;
    }

    // 登録処理
    private function update_record($conditions, &$item) {

        $error_msg = null;
        try {
            DB::start_transaction(S0010::$db);

            // 予約情報更新
            $error_msg = S0010::update_record($conditions, $item, S0010::$db);
            if (!empty($error_msg)) {
                throw new Exception($error_msg, 1);
            }
            // 入出庫更新
            // 持込みフラグのチェックがない場合のみ
            if (empty($conditions['carry_flg']) || $conditions['carry_flg'] == 'NO') {
                $logistics_list = L0010::getForms();
                $list           = array(
                    'schedule_id'               => $conditions['id'],
                    'delivery_schedule_date'    => $conditions['start_date'],
                    'delivery_schedule_time'    => $conditions['start_time'],
                );
                if ($data = L0010::getLogisticsBySchedule($list, S0010::$db)) {
                    $logistics_list['logistics_id']             = $data['logistics_id'];
                    $logistics_list['schedule_id']              = $conditions['id'];
                    $logistics_list['delivery_schedule_date']   = $conditions['start_date'];
                    $logistics_list['delivery_schedule_time']   = $conditions['start_time'];
                    $logistics_list['receipt_date']             = $conditions['start_date'];
                    $logistics_list['receipt_time']             = $conditions['start_time'];
                    $logistics_list['car_id']                   = $item['car_id'];
                    $logistics_list['car_code']                 = $item['car_code'];
                    $logistics_list['car_name']                 = $item['car_name'];
                    $logistics_list['customer_code']            = $item['customer_code'];
                    $logistics_list['customer_name']            = $item['customer_name'];
                    $logistics_list['consumer_name']            = $item['consumer_name'];

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
            $item['return'] = $e->getMessage();
            // var_dump($e->getMessage());
            return Config::get('m_CE0001');
        }
        return null;
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
        $data['item']       = array(
            'schedule_id'       => 0,
            'unit_id'           => '',
            'car_id'            => '',
            'car_code'          => '',
            'car_name'          => '',
            'customer_code'     => '',
            'customer_name'     => '',
            'consumer_name'     => '',
            'cancel'            => '',
            'commit'            => '',
            'carry_flg'         => '',
            'back_color'        => '',
            'text_color'        => '',
            'request_class'     => '',
            'request_memo'      => '',
            'memo'              => '',
            'return'            => '',
        );
        if ($conditions['request_class'] == 'undefined') {
            $conditions['request_class'] = 'other';
        }
        // 予約スケジュールIDで検索
        if ($res = S0010::getScheduleById($conditions['id'], S0010::$db)) {
            // 更新
            $this->update_record($conditions, $data['item']);
        } else {
            // 追加
            $this->create_record($conditions, $data['item']);
        }
        return $this->response($data);

    }

}
