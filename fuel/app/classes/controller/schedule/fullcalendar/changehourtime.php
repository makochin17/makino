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

class Controller_Schedule_Fullcalendar_ChangeHourTime extends Controller_Rest {

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
        $car_code           = Input::param('code', '');
        $date               = Input::param('date', '');
        $hour               = Input::param('hour', '');
        $time               = Input::param('time', '');
        $start_date         = date('Y-m-d H:i:s', strtotime($date.' '.$hour.':'.$time.':00'));
        $d                  = new \DateTime($start_date);
        $data               = array();
        $data['item']       = array(
            'end_hour'              => '',
            'end_time'              => '',
            'return'                => '',
        );

        try {
            // 車両番号で検索
            if ($car = S0010::getCarByCode($car_code, S0010::$db)) {
                $end_date           = $d->modify('+'.$car['work_required_time'].' minute')->format('Y-m-d H:i:s');
                $end_hour           = date('H', strtotime($end_date));
                $end_time           = date('i', strtotime($end_date));
                $data['item'] = array(
                    'end_hour'              => $end_hour,
                    'end_time'              => $end_time,
                    'return'                => '',
                );
            } else {
                $data['item']['return'] = '該当する車両は見つかりませんでした('.$car_code.')';
            }

        } catch (Exception $e) {
            $data['item']['return'] = 'エラーが発生しました'.$e;
        }

        return $this->response($data);

    }

}
