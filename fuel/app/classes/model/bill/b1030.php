<?php
namespace Model\Bill;
use \Model\Common\SystemConfig;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;
use \Model\Mainte\M0010\M0010;
use \Model\Mainte\M0020\M0020;
use \Model\Mainte\M0030\M0030;
use \Model\Mainte\M0050;
use \Model\Mainte\M0060;

use PhpOffice\PhpSpreadsheet\Spreadsheet;               // スプレッドシート用
use PhpOffice\PhpSpreadsheet\IOFactory;                 // IOFactory
use PhpOffice\PhpSpreadsheet\Reader\Xls as XlsReader;   // 拡張子xlsのExcelファイル読み込み用
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader; // 拡張子xlsxのExcelファイル読み込み用
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter; // 拡張子xlsxのExcelファイル書き込み用
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;        // 日付形式
use PhpOffice\PhpSpreadsheet\Style\Border;              // 罫線用
use PhpOffice\PhpSpreadsheet\Style\Alignment;           // 出力位置指定用
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;       // ワークシート用
use PhpOffice\PhpSpreadsheet\Worksheet\ColumnDimension; // セル幅用
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;           // セル用
use PhpOffice\PhpSpreadsheet\Cell\DataType;             // セルフォーマット用
// use PhpOffice\PhpSpreadsheet\IOFactory as Fact;         // スプレッドシート用ファイル操作クラス

ini_set("memory_limit", "1000M");

class B1030 extends \Model {

    public static $db       = 'MAKINO';

    public static $format_array = array(
                                  'xls'             => 'Excel5'
                                , 'xlsx'            => 'Excel2007'
                                , 'csv'             => 'CSV'
                                , 'tsv'             => 'TSV'
                            );

    public static $format_array_sp = array(
                                  'xls'             => 'Xls'
                                , 'xlsx'            => 'Xlsx'
                                , 'csv'             => 'Csv'
                            );

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 配車データ（共配便）検索 & 配車レコード検索件数取得
     */
    public static function getSearch($type = 'search', $conditions, $offset, $limit, $mode, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        switch ($type) {
            case 'select':
            case 'count':
                $stmt = \DB::select(array('t.dispatch_number', 'dispatch_number'));
                break;
            case 'search':
            default:
                $stmt = \DB::select(
                            array('t.dispatch_number', 'dispatch_number'),
                            array('t.division_code', 'division_code'),
                            array(\DB::expr('(SELECT division_name FROM m_division WHERE division_code = t.division_code)'), 'division_name'),
                            array('t.delivery_code', 'delivery_code'),
                            array('t.dispatch_code', 'dispatch_code'),
                            array('t.area_code', 'area_code'),
                            array('t.course', 'course'),
                            array('t.delivery_date', 'delivery_date'),
                            array('t.pickup_date', 'pickup_date'),
                            array(\DB::expr('AES_DECRYPT(UNHEX(t.delivery_place),"'.$encrypt_key.'")'), 'delivery_place'),
                            array(\DB::expr('AES_DECRYPT(UNHEX(t.pickup_place),"'.$encrypt_key.'")'), 'pickup_place'),
                            array('t.client_code', 'client_code'),
                            array(\DB::expr('(SELECT client_name FROM m_client WHERE client_code = t.client_code AND start_date <= t.update_datetime AND end_date > t.update_datetime)'), 'client_name'),
                            array('t.carrier_code', 'carrier_code'),
                            array(\DB::expr('(SELECT carrier_name FROM m_carrier WHERE carrier_code = t.carrier_code AND start_date <= t.update_datetime AND end_date > t.update_datetime)'), 'carrier_name'),
                            array('t.product_name', 'product_name'),
                            array('t.maker_name', 'maker_name'),
                            array('t.volume', 'volume'),
                            array('t.unit_code', 'unit_code'),
                            array('t.car_model_code', 'car_model_code'),
                            array(\DB::expr('(SELECT car_model_name FROM m_car_model WHERE car_model_code = t.car_model_code AND start_date <= t.update_datetime AND end_date > t.update_datetime)'), 'car_model_name'),
                            array('t.car_code', 'car_code'),
                            array('t.member_code', 'member_code'),
                            array(\DB::expr('AES_DECRYPT(UNHEX(t.driver_name),"'.$encrypt_key.'")'), 'driver_name'),
                            array('t.requester', 'requester'),
                            array('t.inquiry_no', 'inquiry_no'),
                            array('t.onsite_flag', 'onsite_flag'),
                            array(\DB::expr('AES_DECRYPT(UNHEX(t.delivery_address),"'.$encrypt_key.'")'), 'delivery_address'),
                            array('t.remarks', 'remarks1'),
                            array('t.remarks2', 'remarks2'),
                            array('t.remarks3', 'remarks3'),
                            array('t.carrier_payment', 'carrier_payment'),
                            array('t.sales_status', 'sales_status')
                        );
                break;
        }

        // テーブル
        $stmt->from(array('t_dispatch_share', 't'));
        // 得意先
        if (!empty($conditions['client_name'])) {
            $stmt->join(array('m_client', 'mcl'), 'INNER')
                ->on('t.client_code', '=', 'mcl.client_code')
                ->on('mcl.start_date', '<=', 't.update_datetime')
                ->on('mcl.end_date', '>', 't.update_datetime');
        }
        // 傭車先
        if (!empty($conditions['carrier_name'])) {
            $stmt->join(array('m_carrier', 'mca'), 'INNER')
                ->on('t.carrier_code', '=', 'mca.carrier_code')
                ->on('mca.start_date', '<=', 't.update_datetime')
                ->on('mca.end_date', '>', 't.update_datetime');
        }
        // 車種コード
        if (!empty($conditions['car_model_code']) && trim($conditions['car_model_code']) != '000') {
            $stmt->join(array('m_car_model', 'mcm'), 'INNER')
                ->on('t.car_model_code', '=', 'mcm.car_model_code')
                ->on('mcm.start_date', '<=', 't.update_datetime')
                ->on('mcm.end_date', '>', 't.update_datetime')
                ->on('t.car_model_code', '=', \DB::expr("'".$conditions['car_model_code']."'"));
        }
        // 車番
        if (!empty($conditions['car_code']) && trim($conditions['car_code']) != '') {
            $stmt->join(array('m_car', 'mc'), 'INNER')
                ->on('t.car_code', '=', 'mc.car_code')
                ->on('mc.start_date', '<=', 't.update_datetime')
                ->on('mc.end_date', '>', 't.update_datetime')
                ->on('mc.car_code', '=', \DB::expr("'".$conditions['car_code']."'"));
        }
        // 運転手
        if (!empty($conditions['driver_name'])) {
            $stmt->join(array('m_member', 'mm'), 'INNER')
                ->on('t.member_code', '=', 'mm.member_code')
                ->on('mm.start_date', '<=', 't.update_datetime')
                ->on('mm.end_date', '>', 't.update_datetime')
                ->on(\DB::expr('AES_DECRYPT(UNHEX(t.driver_name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['driver_name']."%'"));
        }

        // 配車番号
        if (!empty($conditions['dispatch_number'])) {
            $stmt->where(\DB::expr('CAST(t.dispatch_number AS SIGNED)'), '=', $conditions['dispatch_number']);
        }
        // 課コード
        if (!empty($conditions['division']) && trim($conditions['division']) != '000') {
            $stmt->where('t.division_code', '=', $conditions['division']);
        }
        // 売上ステータス
        if (!empty($conditions['sales_status']) && trim($conditions['sales_status']) != '0') {
            $stmt->where('t.sales_status', '=', $conditions['sales_status']);
        }
        // 配送区分
        if (!empty($conditions['delivery_code']) && trim($conditions['delivery_code']) != '0') {
            $stmt->where('t.delivery_code', '=', $conditions['delivery_code']);
        }
        // 配車区分
        if (!empty($conditions['dispatch_code']) && trim($conditions['dispatch_code']) != '0') {
            $stmt->where('t.dispatch_code', '=', $conditions['dispatch_code']);
        }
        // 地区
        if (!empty($conditions['area_code']) && trim($conditions['area_code']) != '0') {
            $stmt->where('t.area_code', '=', $conditions['area_code']);
        }
        // コース
        if (!empty($conditions['course'])) {
            $stmt->where('t.course', '=', $conditions['course']);
        }
        // 納品日
        if (!empty($conditions['delivery_date_from']) && trim($conditions['delivery_date_to']) != '') {
            $date_from = \Date::forge(strtotime(trim($conditions['delivery_date_from'])))->format('mysql_date');
            $date_to = \Date::forge(strtotime(trim($conditions['delivery_date_to'])))->format('mysql_date');
            $stmt->where('t.delivery_date', 'between', array($date_from, $date_to));
        } else {
            if (!empty($conditions['delivery_date_from'])) {
                $date = \Date::forge(strtotime(trim($conditions['delivery_date_from'])))->format('mysql_date');
                $stmt->where('t.delivery_date', '>=', $date);
            }
            if (!empty($conditions['delivery_date_to'])) {
                $date = \Date::forge(strtotime(trim($conditions['delivery_date_to'])))->format('mysql_date');
                $stmt->where('t.delivery_date', '<=', $date);
            }
        }
        // 引取日
        if (!empty($conditions['pickup_date_from']) && trim($conditions['pickup_date_to']) != '') {
            $date_from = \Date::forge(strtotime(trim($conditions['pickup_date_from'])))->format('mysql_date');
            $date_to = \Date::forge(strtotime(trim($conditions['pickup_date_to'])))->format('mysql_date');
            $stmt->where('t.pickup_date', 'between', array($date_from, $date_to));
        } else {
            if (!empty($conditions['pickup_date_from'])) {
                $date = \Date::forge(strtotime(trim($conditions['pickup_date_from'])))->format('mysql_date');
                $stmt->where('t.pickup_date', '>=', $date);
            }
            if (!empty($conditions['pickup_date_to'])) {
                $date = \Date::forge(strtotime(trim($conditions['pickup_date_to'])))->format('mysql_date');
                $stmt->where('t.pickup_date', '<=', $date);
            }
        }
        // 納品先
        if (!empty($conditions['delivery_place']) && trim($conditions['delivery_place']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(t.delivery_place),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['delivery_place']."%'"));
        }
        // 引取先
        if (!empty($conditions['pickup_place']) && trim($conditions['pickup_place']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(t.pickup_place),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['pickup_place']."%'"));
        }
        // 得意先
        if (!empty($conditions['client_code'])) {
            $stmt->where('t.client_code', '=', $conditions['client_code']);
        }
        // 庸車先
        if (!empty($conditions['carrier_code'])) {
            $stmt->where('t.carrier_code', '=', $conditions['carrier_code']);
        }
        // 商品
        if (!empty($conditions['product_name'])) {
            $stmt->where('t.product_name', 'LIKE', \DB::expr("'%".$conditions['product_name']."%'"));
        }
        // 車種コード
        if (!empty($conditions['car_model_code']) && trim($conditions['car_model_code']) != '000') {
            $stmt->where('t.car_model_code', '=', $conditions['car_model_code']);
        }
        // 車両コード
        if (!empty($conditions['car_code'])) {
            $stmt->where('t.car_code', '=', $conditions['car_code']);
        }
        // 運転手
        if (!empty($conditions['driver_name'])) {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(t.driver_name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['driver_name']."%'"));
        }
        // 登録者
        if (!empty($conditions['create_user'])) {
            $stmt->where('t.create_user', '=', $conditions['create_user']);
        }
        // 作成日時
        if ($mode == 2) {
            $stmt->where('t.create_datetime', 'between', array(date("Y/m/d").' 00:00:00', date("Y/m/d").' 23:59:59'));
        }

        $stmt->where('t.delete_flag', '=', '0');
        // 売上ステータス
        $stmt->where('t.sales_status', '=', 1);

        // 検索実行
        switch ($type) {
            case 'count':
                $tmp = $stmt->compile($db);
                $cnt = \DB::select(array(\DB::expr('COUNT(dispatch_number)'), 'count'))
                ->from(array($stmt, 'a'));
                $cnt = $cnt->execute($db)->current();
                return $cnt['count'];
                break;
            case 'select':
            case 'export':
                return $stmt->order_by('t.delivery_date', 'DESC')->order_by('t.pickup_date', 'DESC')->order_by('t.dispatch_number', 'DESC')
                    ->execute($db)
                    ->as_array();
                break;
            case 'search':
            default:
                return $stmt->order_by('t.delivery_date', 'DESC')->order_by('t.pickup_date', 'DESC')->order_by('t.dispatch_number', 'DESC')
                    ->limit($limit)
                    ->offset($offset)
                    ->execute($db)
                    ->as_array();
                break;
        }
    }

    /**
     * 得意先の検索
     */
    public static function getSearchClient($code, $db) {
        return M0020::getClient($code, $db);
    }

    /**
     * 庸車先の検索
     */
    public static function getSearchCarrier($code, $db) {
        return M0030::getCarrier($code, $db);
    }

    /**
     * 商品の検索
     */
    public static function getSearchProduct($code, $db) {
        return M0060::getProduct($code, $db);
    }

    /**
     * 車両の検索
     */
    public static function getSearchCar($code, $db) {
        return M0050::getCar($code, $db);
    }

    /**
     * 社員の検索
     */
    public static function getSearchMember($code, $db) {
        return M0010::getMember($code, $db);
    }

    /**
     * 配車データの取得（存在チェック用）
     */
    public static function getDispatchShare($dispatch_number, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        // 項目
        $stmt = \DB::select(array('t.dispatch_number', 'dispatch_number'));

        // テーブル
        $stmt->from(array('t_dispatch_share', 't'));

        //削除フラグ
        $stmt->where('t.delete_flag', '=', '0');
        // 配車No
        $stmt->where('t.dispatch_number', '=', $dispatch_number);

        // 検索実行
        return $stmt->execute($db)->as_array();
    }

    /**
     * 配車、分載データ削除
     */
    public static function deleteRecord($dispatch_number, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }

        // レコード削除
        $result = self::delDispatchShare($dispatch_number, $db);
        if (!$result) {
            \Log::error(\Config::get('m_DE0012')."[dispatch_number:".$dispatch_number."]");
            return \Config::get('m_DE0012');
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('DI0023', AuthConfig::getAuthConfig('user_name').\Config::get('m_DI0023'), '配車削除', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }

        return null;
    }

    /**
     * 配車データ削除
     */
    public static function delDispatchShare($dispatch_number, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($dispatch_number)) {
            return false;
        }

        // 項目セット
        $set = array('delete_flag' => 1);

        // テーブル
        $stmt = \DB::update('t_dispatch_share')->set(array_merge($set, self::getEtcData(false)));

        // 配車コード
        $stmt->where('dispatch_number', '=', $dispatch_number);
        // 更新実行
        $result = $stmt->execute($db);
        if($result > 0) {
            return true;
        }
        return false;
    }

    /**
     * 配車データ更新（売上ステータス）
     */
    public static function updateRecord($upd_list, $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($upd_list)) {
            return \Config::get('m_CW0010');
        }

        //売上ステータス更新ループ
        foreach ($upd_list as $record) {

            $dispatch_number = $record['dispatch_number'];
            $sales_status = $record['sales_status'];

            // レコード存在チェック
            if (!$result = self::getDispatchShare($dispatch_number, $db)) {
                return \Config::get('m_DW0001');
            }

            // レコード更新
            $result = self::updSalesStatus($dispatch_number, $sales_status, $db);
            if (!$result) {
                \Log::error(\Config::get('m_DE0005')."[dispatch_number:".$dispatch_number."]");
                return \Config::get('m_DE0005');
            }
        }

        // 操作ログ出力
        $result = OpeLog::addOpeLog('DI0010', \Config::get('m_DI0010'), '配車更新（売上ステータス）', $db);
        if (!$result) {
            \Log::error(\Config::get('m_CE0007'));
            return \Config::get('m_CE0007');
        }

        return null;
    }

    /**
     * 売上ステータス更新
     */
    public static function updSalesStatus($dispatch_number, $sales_status, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (empty($dispatch_number)) {
            return false;
        }

        // 項目セット
        $set = array('sales_status' => $sales_status);

        // テーブル
        $stmt = \DB::update('t_dispatch_charter')->set(array_merge($set, self::getEtcData(false)));

        // 配車コード
        $stmt->where('dispatch_number', '=', $dispatch_number);
        // 更新実行
        $result = $stmt->execute($db);
        if($result > 0) {
            return true;
        }
        return false;
    }

    /**
     * 付加データ
     */
    public static function getEtcData($is_insert=false) {

        switch ($is_insert) {
        case true:  // 新規登録
            $data = array(
                'create_datetime'   => \Date::forge()->format('mysql'),
                'create_user'       => AuthConfig::getAuthConfig('user_name'),
                'update_datetime'   => \Date::forge()->format('mysql'),
                'update_user'       => AuthConfig::getAuthConfig('user_name')
            );
            break;
        case false: // 更新
        default:    // 更新
            $data = array(
                'update_datetime'   => \Date::forge()->format('mysql'),
                'update_user'       => AuthConfig::getAuthConfig('user_name')
            );
            break;
        }
        return $data;
    }

    //=========================================================================//
    //=============================   Excel出力   ==============================//
    //=========================================================================//
    /**
     * 配車データ（共配便）取得
     */
    public static function dlExcelFile($kind = null, $dispatch_numbers, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        // テンプレート読み込み
        $reader         = new XlsxReader();
        $tpl_dir        = DOCROOT.'assets/template/';
        $name           = '請求情報(共配便)一括登録_雛形';
        $spreadsheet    = $reader->load($tpl_dir.$name.'.xlsx');

        switch ($kind) {
            case '1':       // チェックした出力
            case '2':       // 検索した出力
                try {
                    \DB::start_transaction(self::$db);
                    // Excel書き込み
                    $spreadsheet = self::sp_create_boder($spreadsheet, 'xlsx', '請求情報', $dispatch_numbers, $db);

                    \DB::commit_transaction(self::$db);
                } catch (Exception $e) {
                    // トランザクションクエリをロールバックする
                    \DB::rollback_transaction(self::$db);
                    \Log::error($e->getMessage());
                    return $e->getMessage();
                }
                break;
        }

        // クッキーを設定
        setcookie('downloaded', 'yes');

        // Excelデータの作成
        $fileName = '請求雛形(共配便).xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        header('Cache-Control: max-age=0');
        ob_end_clean();

        $writer = new XlsxWriter($spreadsheet);
        $writer->save('php://output');
        exit;


        // Excelデータの作成
        // $format_name    = isset(self::$format_array[$version]) ? self::$format_array[$version] : self::$format_array['xlsx'];
        // $writer         = IOFactory::createWriter($excel, $format_name);
        // $writer         = IOFactory::createWriter($spreadsheet, $format_name);
        // $name           = tempnam('', 'excel_');
        // $writer->save($name);
        // $content        = file_get_contents($name);
        // @unlink($name);

        // return $content;

    }

    /**
     * エクセル作成処理
     */
    public static function sp_create_boder($spreadsheet, $version='xlsx', $title='', $dispatch_numbers=array(), $db = null) {

        \Config::load('message');

        if (is_null($db)) {
            $db = self::$db;
        }

        $data   = array();
        /**
         * Excel作成
         */
        $sheet  = $spreadsheet->getActiveSheet();
        // シートタイトル
        $sheet->setTitle($title);

        $i      = 0;
        $result_list = self::getDispatchShareExcelSum($dispatch_numbers, $db);
        foreach($result_list as $result) {
            $list_no = $i;
            $data[$list_no]['dispatch_number']                = $result['dispatch_number'];
            $data[$list_no]['division_code']                  = sprintf('%03d', $result['division_code']);
            $data[$list_no]['area_code']                      = sprintf('%03d', $result['area_code']);
            $data[$list_no]['delivery_code']                  = sprintf('%02d', $result['delivery_code']);
            switch ($result['delivery_code']) {
                case '1':       // 納品日
                    $data[$list_no]['destination_date']       = $result['delivery_date'];
                    $data[$list_no]['destination']            = $result['delivery_place'];
                    break;
                case '2':       // 引取日
                    $data[$list_no]['destination_date']       = $result['pickup_date'];
                    $data[$list_no]['destination']            = $result['pickup_place'];
                    break;
                case '3':       // 納品日or引取日
                default:
                    if (!empty($result['delivery_date'])) {
                        $data[$list_no]['destination_date']   = $result['delivery_date'];
                        $data[$list_no]['destination']        = $result['delivery_place'];
                    } elseif (!empty($result['pickup_date'])) {
                        $data[$list_no]['destination_date']   = $result['pickup_date'];
                        $data[$list_no]['destination']        = $result['pickup_place'];
                    }
                    break;
            }
            $data[$list_no]['client_code']                    = sprintf('%05d', $result['client_code']);
            $data[$list_no]['carrier_code']                   = sprintf('%05d', $result['carrier_code']);
            $data[$list_no]['product_name']                   = $result['product_name'];
            $data[$list_no]['place']                          = 0;
            $data[$list_no]['unit_price']                     = 0.00;
            $data[$list_no]['volume']                         = floatval($result['volume']);
            $data[$list_no]['unit_code']                      = sprintf('%02d', $result['unit_code']);
            $data[$list_no]['rounding_code']                  = '01';
            $data[$list_no]['car_model_code']                 = sprintf('%03d', $result['car_model_code']);
            $data[$list_no]['car_code']                       = sprintf('%04d', $result['car_code']);
            $data[$list_no]['member_code']                    = sprintf('%05d', $result['member_code']);
            $data[$list_no]['driver_name']                    = $result['driver_name'];
            $data[$list_no]['onsite_flag']                    = $result['onsite_flag'];
            $data[$list_no]['requester']                      = $result['requester'];
            $data[$list_no]['inquiry_no']                     = $result['inquiry_no'];
            $data[$list_no]['delivery_address']               = $result['delivery_address'];
            $data[$list_no]['remarks1']                       = $result['remarks1'];
            $data[$list_no]['remarks2']                       = $result['remarks2'];
            $data[$list_no]['remarks3']                       = $result['remarks3'];
            $i++;
        }
        
        
//        foreach($dispatch_numbers as $key => $dispatch_number) {
//            if ($result = self::getDispatchShareExcel($dispatch_number, $db)) {
//                $list_no = $i;
//                $data[$list_no]['dispatch_number']                = sprintf('%010d', $result['dispatch_number']);
//                $data[$list_no]['division_code']                  = sprintf('%03d', $result['division_code']);
//                $data[$list_no]['area_code']                      = sprintf('%03d', $result['area_code']);
//                $data[$list_no]['delivery_code']                  = sprintf('%02d', $result['delivery_code']);
//                switch ($result['delivery_code']) {
//                    case '1':       // 納品日
//                        $data[$list_no]['destination_date']       = $result['delivery_date'];
//                        $data[$list_no]['destination']            = $result['delivery_place'];
//                        break;
//                    case '2':       // 引取日
//                        $data[$list_no]['destination_date']       = $result['pickup_date'];
//                        $data[$list_no]['destination']            = $result['pickup_place'];
//                        break;
//                    case '3':       // 納品日or引取日
//                    default:
//                        if (!empty($result['delivery_date'])) {
//                            $data[$list_no]['destination_date']   = $result['delivery_date'];
//                            $data[$list_no]['destination']        = $result['delivery_place'];
//                        } elseif (!empty($result['pickup_date'])) {
//                            $data[$list_no]['destination_date']   = $result['pickup_date'];
//                            $data[$list_no]['destination']        = $result['pickup_place'];
//                        }
//                        break;
//                }
//                $data[$list_no]['client_code']                    = sprintf('%05d', $result['client_code']);
//                $data[$list_no]['carrier_code']                   = sprintf('%05d', $result['carrier_code']);
//                $data[$list_no]['product_name']                   = $result['product_name'];
//                $data[$list_no]['place']                          = 0;
//                $data[$list_no]['unit_price']                     = 0.00;
//                $data[$list_no]['volume']                         = floatval($result['volume']);
//                $data[$list_no]['unit_code']                      = sprintf('%02d', $result['unit_code']);
//                $data[$list_no]['rounding_code']                  = '01';
//                $data[$list_no]['car_model_code']                 = sprintf('%03d', $result['car_model_code']);
//                $data[$list_no]['car_code']                       = sprintf('%04d', $result['car_code']);
//                $data[$list_no]['member_code']                    = sprintf('%05d', $result['member_code']);
//                $data[$list_no]['driver_name']                    = $result['driver_name'];
//                $data[$list_no]['onsite_flag']                    = 0;
//                $data[$list_no]['requester']                      = $result['requester'];
//                $data[$list_no]['inquiry_no']                     = $result['inquiry_no'];
//                $data[$list_no]['delivery_address']               = $result['delivery_address'];
//                $data[$list_no]['remarks']                        = $result['remarks'];
//                $i++;
//            }
//        }

        // データをセット
        if (is_array($data) && !empty($data)) {

            // 行の繰り返し
            $row_no = 2; // 行番号は2から
            $col_no = 1; // カラム番号は1から
            foreach ($data as $key => $val1) {

                if (is_array($val1) && !empty($val1)) {

                    // 列の繰り返し
                    $col_no = 1; // カラム番号は1から
                    if ($key == 0) { $header_col_no = 0; }
                    foreach ($val1 as $key_name => $val2) {

                        // セルの幅設定を自動にする
                        $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col_no))->setWidth(15);
                        if (is_null($val2) || trim($val2) == '') {
                            // セルに書き込み
                            $sheet->setCellValueByColumnAndRow($col_no, $row_no, '');
                        } else {
                            // セルに書き込み
                            $sheet->setCellValueExplicitByColumnAndRow($col_no, $row_no, $val2, DataType::TYPE_STRING);
                            $sheet->getStyle($sheet->getCellByColumnAndRow($col_no, $row_no)->getCoordinate())->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                        }
                        // セルに罫線を引く
                        if ($header_col_no > $col_no) {
                            $sheet->getStyle($sheet->getCellByColumnAndRow($col_no, $row_no)->getCoordinate())->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                            // // セル（上）に罫線を引く
                            // $sheet->getStyleByColumnAndRow($col_no, $row_no)->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
                            // // セル（下）に罫線を引く
                            // $sheet->getStyleByColumnAndRow($col_no, $row_no)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
                            // // セル（左）に罫線を引く
                            // $sheet->getStyleByColumnAndRow($col_no, $row_no)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THICK);
                            // // セル（右）に罫線を引く
                            // $sheet->getStyleByColumnAndRow($col_no, $row_no)->getBorders()->getRight()->setBorderStyle(Border::BORDER_THICK);
                        }
                        if ($key == 0) {
                            $sheet->getStyle($sheet->getCellByColumnAndRow($col_no, $row_no)->getCoordinate())->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                        }

                        $col_no++;
                        if ($key == 0) { $header_col_no++; }
                    }
                    // 処理結果表示
                    if ($key > 0) {
                        //$sheet->getStyle($sheet->getCellByColumnAndRow($col_no-1, $row_no)->getCoordinate())->getAlignment()->setWrapText(true);
                        //$sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col_no-1))->setAutoSize(true);
                    }

                }

                $row_no++;

            }

            // セルのスタイルを文字列とする
            $target_col_no = Coordinate::stringFromColumnIndex($col_no);
            $target_row_no = $row_no - 1;
            $sheet->getStyle('A1:'.$target_col_no.$target_row_no)->getNumberFormat()->setFormatCode('@');

        } else {
            throw new Exception(\Config::get('m_BW0018'), 1);
        }

        // ob_end_clean(); //バッファ消去
        // // ファイル書込み
        // $format_name    = isset(self::$format_array_sp[$version]) ? self::$format_array_sp[$version] : self::$format_array_sp['xlsx'];
        // // $writer         = Fact::createWriter($spreadsheet, $format_name);
        // $writer         = new XlsxWriter($spreadsheet);
        // $writer->save('php://output');
        // return;

        // そのままリターン
        return $spreadsheet;

    }

    /**
     * レコード取得
     */
    public static function getDispatchShareExcel($code, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        $stmt = \DB::select(
                array('t.dispatch_number', 'dispatch_number'),
                array('t.division_code', 'division_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(md.division_name),"'.$encrypt_key.'")'), 'division'),
                array('t.delivery_code', 'delivery_code'),
                array('t.dispatch_code', 'dispatch_code'),
                array('t.area_code', 'area_code'),
                array('t.course', 'course'),
                array('t.delivery_date', 'delivery_date'),
                array('t.pickup_date', 'pickup_date'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.delivery_place),"'.$encrypt_key.'")'), 'delivery_place'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.pickup_place),"'.$encrypt_key.'")'), 'pickup_place'),
                array('t.client_code', 'client_code'),
                array('mcl.client_name', 'client_name'),
                array('t.carrier_code', 'carrier_code'),
                array('mca.carrier_name', 'carrier_name'),
                array('t.product_name', 'product_name'),
                array('t.maker_name', 'maker_name'),
                array('t.volume', 'volume'),
                array('t.unit_code', 'unit_code'),
                array('t.car_model_code', 'car_model_code'),
                array('mcm.car_model_name', 'car_model_name'),
                array('t.car_code', 'car_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.car_number),"'.$encrypt_key.'")'), 'car_number'),
                array('t.member_code', 'member_code'),
                // array(\DB::expr('CASE WHEN t.member_code IS NULL THEN AES_DECRYPT(UNHEX(t.driver_name),"'.$encrypt_key.'") ELSE AES_DECRYPT(UNHEX(mm.driver_name),"'.$encrypt_key.'") END'), 'driver_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.driver_name),"'.$encrypt_key.'")'), 'driver_name'),
                array('t.requester', 'requester'),
                array('t.inquiry_no', 'inquiry_no'),
                array('t.onsite_flag', 'onsite_flag'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.delivery_address),"'.$encrypt_key.'")'), 'delivery_address'),
                array('t.remarks', 'remarks1'),
                array('t.remarks2', 'remarks2'),
                array('t.remarks3', 'remarks3'),
                array('t.carrier_payment', 'carrier_payment'),
                array('t.sales_status', 'sales_status')
                );

        // テーブル
        $stmt->from(array('t_dispatch_share', 't'))
            ->join(array('m_client', 'mcl'), 'left outer')
                ->on('t.client_code', '=', 'mcl.client_code')
                ->on('mcl.start_date', '<=', 't.update_datetime')
                ->on('mcl.end_date', '>', 't.update_datetime')
            ->join(array('m_carrier', 'mca'), 'left outer')
                ->on('t.carrier_code', '=', 'mca.carrier_code')
                ->on('mca.start_date', '<=', 't.update_datetime')
                ->on('mca.end_date', '>', 't.update_datetime')
            ->join(array('m_division', 'md'), 'left outer')
                ->on('t.division_code', '=', 'md.division_code')
            ->join(array('m_car_model', 'mcm'), 'left outer')
                ->on('t.car_model_code', '=', 'mcm.car_model_code')
                ->on('mcm.start_date', '<=', 't.update_datetime')
                ->on('mcm.end_date', '>', 't.update_datetime')
            ->join(array('m_car', 'mc'), 'left outer')
                ->on('t.car_code', '=', 'mc.car_code')
                ->on('mc.start_date', '<=', 't.update_datetime')
                ->on('mc.end_date', '>', 't.update_datetime')
            ->join(array('m_member', 'mm'), 'left outer')
                ->on('t.member_code', '=', 'mm.member_code')
                ->on('mm.start_date', '<=', 't.update_datetime')
                ->on('mm.end_date', '>', 't.update_datetime');

        // 配車コード
        $stmt->where('t.dispatch_number', '=', $code);
        // 削除フラグ
        $stmt->where('t.delete_flag', '=', 0);

        // 検索実行
        return $stmt->execute($db)->current();
    }
    
    /**
     * レコード取得
     */
    public static function getDispatchShareExcelSum($code_list, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 項目
        $stmt = \DB::select(
                array(\DB::expr('GROUP_CONCAT(LPAD(t.dispatch_number, 10, \'0\'))'), 'dispatch_number'),
                array('t.division_code', 'division_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(md.division_name),"'.$encrypt_key.'")'), 'division'),
                array('t.delivery_code', 'delivery_code'),
                array('t.dispatch_code', 'dispatch_code'),
                array('t.area_code', 'area_code'),
                array('t.course', 'course'),
                array('t.delivery_date', 'delivery_date'),
                array('t.pickup_date', 'pickup_date'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.delivery_place),"'.$encrypt_key.'")'), 'delivery_place'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.pickup_place),"'.$encrypt_key.'")'), 'pickup_place'),
                array('t.client_code', 'client_code'),
                array('mcl.client_name', 'client_name'),
                array('t.carrier_code', 'carrier_code'),
                array('mca.carrier_name', 'carrier_name'),
                array('t.product_name', 'product_name'),
                array('t.maker_name', 'maker_name'),
                array(\DB::expr('SUM(t.volume)'), 'volume'),
                array('t.unit_code', 'unit_code'),
                array('t.car_model_code', 'car_model_code'),
                array('mcm.car_model_name', 'car_model_name'),
                array('t.car_code', 'car_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.car_number),"'.$encrypt_key.'")'), 'car_number'),
                array('t.member_code', 'member_code'),
                // array(\DB::expr('CASE WHEN t.member_code IS NULL THEN AES_DECRYPT(UNHEX(t.driver_name),"'.$encrypt_key.'") ELSE AES_DECRYPT(UNHEX(mm.driver_name),"'.$encrypt_key.'") END'), 'driver_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.driver_name),"'.$encrypt_key.'")'), 'driver_name'),
                array('t.requester', 'requester'),
                array('t.inquiry_no', 'inquiry_no'),
                array('t.onsite_flag', 'onsite_flag'),
                array(\DB::expr('AES_DECRYPT(UNHEX(t.delivery_address),"'.$encrypt_key.'")'), 'delivery_address'),
                array('t.remarks', 'remarks1'),
                array('t.remarks2', 'remarks2'),
                array('t.remarks3', 'remarks3'),
                array('t.carrier_payment', 'carrier_payment'),
                array('t.sales_status', 'sales_status')
                );

        // テーブル
        $stmt->from(array('t_dispatch_share', 't'))
            ->join(array('m_client', 'mcl'), 'left outer')
                ->on('t.client_code', '=', 'mcl.client_code')
                ->on('mcl.start_date', '<=', 't.update_datetime')
                ->on('mcl.end_date', '>', 't.update_datetime')
            ->join(array('m_carrier', 'mca'), 'left outer')
                ->on('t.carrier_code', '=', 'mca.carrier_code')
                ->on('mca.start_date', '<=', 't.update_datetime')
                ->on('mca.end_date', '>', 't.update_datetime')
            ->join(array('m_division', 'md'), 'left outer')
                ->on('t.division_code', '=', 'md.division_code')
            ->join(array('m_car_model', 'mcm'), 'left outer')
                ->on('t.car_model_code', '=', 'mcm.car_model_code')
                ->on('mcm.start_date', '<=', 't.update_datetime')
                ->on('mcm.end_date', '>', 't.update_datetime')
            ->join(array('m_car', 'mc'), 'left outer')
                ->on('t.car_code', '=', 'mc.car_code')
                ->on('mc.start_date', '<=', 't.update_datetime')
                ->on('mc.end_date', '>', 't.update_datetime')
            ->join(array('m_member', 'mm'), 'left outer')
                ->on('t.member_code', '=', 'mm.member_code')
                ->on('mm.start_date', '<=', 't.update_datetime')
                ->on('mm.end_date', '>', 't.update_datetime');

        // 配車コード
        $stmt->where('t.dispatch_number', 'IN', $code_list);
        // 削除フラグ
        $stmt->where('t.delete_flag', '=', 0);
        // 売上ステータス
        $stmt->where('t.sales_status', '=', 1);
        
        // グループ化
        $stmt->group_by('t.division_code')->group_by('t.dispatch_code')->group_by('t.area_code')->group_by('t.delivery_date')
            ->group_by('t.client_code')->group_by(\DB::expr('AES_DECRYPT(UNHEX(t.delivery_place),"'.$encrypt_key.'")'));

        // 検索実行
        return $stmt->execute($db)->as_array();
    }

}