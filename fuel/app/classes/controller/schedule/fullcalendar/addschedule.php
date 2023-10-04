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
    private function create_record($conditions) {

        $error_msg = null;
        try {
            DB::start_transaction(C0011::$db);

            $error_msg = C0011::create_record($conditions, C0011::$db);
            if (!is_null($error_msg)) {
                return $error_msg;
            }
            DB::commit_transaction(C0011::$db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(C0011::$db);
            Log::error($e->getMessage());
            // var_dump($e->getMessage());
            return Config::get('m_CE0001');
        }
        echo "<script type='text/javascript'>alert('".Config::get('m_CAR004')."');</script>";
        return null;
    }

    // 登録処理
    private function create_record($conditions) {

        $error_msg = null;
        try {
            DB::start_transaction(C0011::$db);

            $error_msg = C0011::create_record($conditions, C0011::$db);
            if (!is_null($error_msg)) {
                return $error_msg;
            }
            DB::commit_transaction(C0011::$db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            DB::rollback_transaction(C0011::$db);
            Log::error($e->getMessage());
            // var_dump($e->getMessage());
            return Config::get('m_CE0001');
        }
        echo "<script type='text/javascript'>alert('".Config::get('m_CAR004')."');</script>";
        return null;
    }

    public function action_index() {

        Config::load('message');

        /**
         * 検索項目の取得＆初期設定
         */
        $error_msg          = null;
        // Postデータを設定
        $conditions         = C0011::setForms('set', null, Input::param());
        // Xmlデータ初期化
        $data               = array();
        $data['item']       = array(
            'schedule_id'       => 0,
            'car_id'            => '',
            'car_code'          => '',
            'car_name'          => '',
            'customer_code'     => '',
            'customer_name'     => '',
            'consumer_name'     => '',
            'cancel'            => '',
            'commit'            => '',
            'back_color'        => '',
            'fore_color'        => '',
            'return'            => '',
        );

        try {
            // 車両番号で検索
            if ($res = S0010::getScheduleById($conditions['id'], S0010::$db)) {
                // 更新
                if (!$schedule_id = S0010::update_record($conditions, S0010::$db)) {
                    throw new Exception("[スケジュールの更新に失敗しました]", 1);
                }
            } else {
                // 追加
                if (!$schedule_id = S0010::create_record($conditions, S0010::$db)) {
                    throw new Exception("[スケジュールの登録に失敗しました]", 1);
                }
            }

            $data['item']       = array(
                'schedule_id'       => $conditions['id'],
                'car_id'            => '',
                'car_code'          => '',
                'car_name'          => '',
                'customer_code'     => '',
                'customer_name'     => '',
                'consumer_name'     => '',
                'cancel'            => '',
                'commit'            => '',
                'back_color'        => '',
                'fore_color'        => '',
                'return'            => '',
            );
        } catch (Exception $e) {
            $data['item']['return'] = 'エラーが発生しました'.$e;
        }

<rss>
    <item>
        <new_seq><?php echo $seq; ?></new_seq>
        <menu_nm1><?php echo $menu_nm1; ?></menu_nm1>
        <menu_nm2><?php echo $menu_nm2; ?></menu_nm2>
        <menu_nm3><?php echo $menu_nm3; ?></menu_nm3>
        <unauthorized><?php echo $unauthorized; ?></unauthorized>
        <tel_wait><?php echo $tel_wait; ?></tel_wait>
        <tel_wait1><?php echo $tel_wait1; ?></tel_wait1>
        <tel_wait2><?php echo $tel_wait2; ?></tel_wait2>
        <tel_wait3><?php echo $tel_wait3; ?></tel_wait3>
        <cancel><?php echo $cancel; ?></cancel>
        <commit><?php echo $commit; ?></commit>
        <back_color><?php echo $back_color; ?></back_color>
        <fore_color><?php echo $fore_color; ?></fore_color>
        <return><?php echo $return; ?></return>
    </item>
</rss>

        return $this->response($data);

    }

}
