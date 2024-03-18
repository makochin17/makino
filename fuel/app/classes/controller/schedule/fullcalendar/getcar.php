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

class Controller_Schedule_Fullcalendar_GetCar extends Controller_Rest {

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
        $car_code           = Input::param('car_code', '');
        $data               = array();
        $data['item']       = array(
            'car_cnt'               => 0,
            'car_id'                => '',
            'car_code'              => '',
            'car_name'              => '',
            'customer_code'         => '',
            'customer_name'         => '',
            'consumer_name'         => '',
            'work_required_time'    => '',
            'return'                => '',
        );

        try {
            // 車両番号で検索
            if ($res = S0010::getCar($car_code, null, S0010::$db)) {
                foreach ($res as $key => $val) {
                    if ($key == 0) {
                        $car_id                 = $val["car_id"];
                        $car_code               = $val["car_code"];
                        $car_name               = $val["car_name"];
                        $customer_code          = $val["customer_code"];
                        $customer_name          = $val["customer_name"];
                        $consumer_name          = $val["consumer_name"];
                        $work_required_time     = $val["work_required_time"];
                    } else {
                        $car_id                 = $car_id.",".$val["car_id"];
                        $car_code               = $car_code.",".$val["car_code"];
                        $car_name               = $car_name.",".$val["car_name"];
                        $customer_code          = $customer_code.",".$val["customer_code"];
                        $customer_name          = $customer_name.",".$val["customer_name"];
                        $consumer_name          = $consumer_name.",".$val["consumer_name"];
                        $work_required_time     = $work_required_time.",".$val["work_required_time"];
                    }
                }

                $data['item'] = array(
                    'car_cnt'               => count($res),
                    'car_id'                => $car_id,
                    'car_code'              => $car_code,
                    'car_name'              => $car_name,
                    'customer_code'         => $customer_code,
                    'customer_name'         => $customer_name,
                    'consumer_name'         => $consumer_name,
                    'work_required_time'    => $work_required_time,
                    'return'                => '',
                );
            } else {
                // 車種名で曖昧検索
                if ($res = S0010::getCar(null, $car_code, S0010::$db)) {
                    foreach ($res as $key => $val) {
                        if ($key == 0) {
                            $car_id                 = $val["car_id"];
                            $car_code               = $val["car_code"];
                            $car_name               = $val["car_name"];
                            $customer_code          = $val["customer_code"];
                            $customer_name          = $val["customer_name"];
                            $consumer_name          = $val["consumer_name"];
                            $work_required_time     = $val["work_required_time"];
                        } else {
                            $car_id                 = $car_id.",".$val["car_id"];
                            $car_code               = $car_code.",".$val["car_code"];
                            $car_name               = $car_name.",".$val["car_name"];
                            $customer_code          = $customer_code.",".$val["customer_code"];
                            $customer_name          = $customer_name.",".$val["customer_name"];
                            $consumer_name          = $consumer_name.",".$val["consumer_name"];
                            $work_required_time     = $work_required_time.",".$val["work_required_time"];
                        }
                    }

                    $data['item'] = array(
                        'car_cnt'               => count($res),
                        'car_id'                => $car_id,
                        'car_code'              => $car_code,
                        'car_name'              => $car_name,
                        'customer_code'         => $customer_code,
                        'customer_name'         => $customer_name,
                        'consumer_name'         => $consumer_name,
                        'work_required_time'    => $work_required_time,
                        'return'                => '',
                    );
                } else {
                    $data['item']['return'] = '該当する車両は見つかりませんでした('.$car_code.')';
                }

            }

        } catch (Exception $e) {
            $data['item']['return'] = 'エラーが発生しました'.$e;
        }

        return $this->response($data);

    }

}
