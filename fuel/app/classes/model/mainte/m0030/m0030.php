<?php
namespace Model\Mainte\M0030;
use \Model\Common\SystemConfig;
use \Model\Common\GenerateList;
use \Model\Common\OpeLog;
use \Model\Mainte\M0030\M0035;
use \Model\Search\S0030;

class M0030 extends \Model {

    public static $db       = 'MAKINO';

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 庸車先マスタレコード取得
     */
    public static function getCarrier($code, $db) {
        
        // 項目
        $stmt = \DB::select(
                array('m.carrier_code', 'carrier_code'),
                array('m.carrier_name', 'carrier_name'),
                array('m.closing_date', 'closing_date'),
                array('m.start_date', 'start_date'),
                );

        // テーブル
        $stmt->from(array('m_carrier', 'm'));
        
        // 庸車先コード
        $stmt->where('m.carrier_code', '=', $code);
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        return $stmt->execute($db)->as_array();
    }

    /**
     * 庸車先マスタ検索件数取得
     */
    public static function getSearchCount($conditions, $db) {
        return S0030::getSearch(true, $conditions, null, null, $db);
    }

    /**
     * 庸車先マスタ検索
     */
    public static function getSearch($conditions, $offset, $limit, $db) {
        return S0030::getSearch(false, $conditions, $offset, $limit, $db);
    }
    
    /**
     * 庸車先データ削除
     */
    public static function delCarrier($carrier_code, $db = null) {
        return M0035::delete_record($carrier_code, $db);
    }
    
    /**
     * エクセル作成処理
     */
    public static function createTsv($conditions, $db) {
        //出力データ取得
        $header = self::getHeader($db);
        $body = self::getBody($conditions, $db);
        
        try {
            \DB::start_transaction($db);

            // 操作ログ出力
            $result = OpeLog::addOpeLog('MI0018', \Config::get('m_MI0018'), '', $db);
            if (!$result) {
                \Log::error(\Config::get('m_CE0007'));
            }
            
            \DB::commit_transaction($db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            \DB::rollback_transaction($db);
            \Log::error($e->getMessage());
        }
        
        //ファイル名設定
        $title = mb_convert_encoding('庸車先マスタ一覧', 'SJIS', 'UTF-8');
        $fileName = $title.'.tsv';

        //HTMLヘッダー
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $fileName);

        //ファイルへの書き込み
        $handle = fopen('php://output', 'w');
        
        mb_convert_variables('SJIS-win', 'UTF-8', $header);
        fputcsv($handle, $header, "\t");
        
        foreach ($body as $row) {
            mb_convert_variables('SJIS-win', 'UTF-8', $row);
            fputcsv($handle, $row, "\t");
        }
        
        fclose($handle);

        exit();
    }
    
    /**
     * TSV用ヘッダー情報取得
     */
    public static function getHeader($db) {
        $result = array();
        $result += array("carrier_code" => "庸車先コード");
        $result += array("carrier_name" => "庸車先名");
//        $result += array("carrier_company_code" => "庸車先会社コード");
//        $result += array("carrier_company_name" => "庸車先会社名");
//        $result += array("carrier_sales_office_code" => "庸車先営業所コード");
        $result += array("carrier_sales_office_name" => "庸車先営業所名");
//        $result += array("carrier_department_code" => "庸車先部署コード");
        $result += array("carrier_department_name" => "庸車先部署名");
        $result += array("closing_date" => "締日");
//        $result += array("criterion_closing_date" => "基準締日");
//        $result += array("company_section" => "会社区分");
        $result += array("official_name" => "正式名称");
//        $result += array("official_name_kana" => "正式名称（カナ）");
        $result += array("postal_code" => "郵便番号");
        $result += array("address" => "住所");
        $result += array("phone_number" => "電話番号");
        $result += array("fax_number" => "FAX番号");
        $result += array("person_in_charge_surname" => "担当者（姓）");
        $result += array("person_in_charge_name" => "担当者（名）");
        
        //課リスト取得
        $division_list = GenerateList::getDivisionList(false, $db);
        
        //担当部署の設定
        foreach ($division_list as $key => $value) {
            $result['division_'.$key] = $value;
        }
        
        return $result;
    }
    
    /**
     * TSV用データ取得
     */
    public static function getBody($conditions, $db) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);
        
        // 取得データ
        $stmt = \DB::select(
                array('mc.carrier_code', 'carrier_code'),
                array('mc.carrier_name', 'carrier_name'),
//                array('mc.carrier_company_code', 'carrier_company_code'),
//                array('mcc.company_name', 'company_name'),
//                array('mc.carrier_sales_office_code', 'carrier_sales_office_code'),
                array('mcs.sales_office_name', 'sales_office_name'),
//                array('mc.carrier_department_code', 'carrier_department_code'),
                array('mcd.department_name', 'department_name'),
                array('mc.closing_date', 'closing_date'),
                array('mc.closing_date_1', 'closing_date_1'),
                array('mc.closing_date_2', 'closing_date_2'),
                array('mc.closing_date_3', 'closing_date_3'),
//                array(\DB::expr('CASE WHEN mc.criterion_closing_date = 1 THEN "積日" ELSE "降日" END'), 'criterion_closing_date'),
//                array(\DB::expr('CASE WHEN mc.company_section = 1 THEN "自社" ELSE "他社" END'), 'company_section'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.official_name),"'.$encrypt_key.'")'), 'official_name'),
//                array(\DB::expr('AES_DECRYPT(UNHEX(mc.official_name_kana),"'.$encrypt_key.'")'), 'official_name_kana'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.postal_code),"'.$encrypt_key.'")'), 'postal_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.address),"'.$encrypt_key.'")'), 'address'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.phone_number),"'.$encrypt_key.'")'), 'phone_number'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.fax_number),"'.$encrypt_key.'")'), 'fax_number'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.person_in_charge_surname),"'.$encrypt_key.'")'), 'person_in_charge_surname'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.person_in_charge_name),"'.$encrypt_key.'")'), 'person_in_charge_name'),
                array('mc.department_in_charge', 'department_in_charge'),
                );

        // テーブル
        $stmt->from(array('m_carrier', 'mc'))
            ->join(array('m_carrier_company', 'mcc'), 'left outer')
                ->on('mc.carrier_company_code', '=', 'mcc.carrier_company_code')
                ->on('mcc.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('mcc.end_date', '>', '\''.date("Y-m-d").'\'')
            ->join(array('m_carrier_sales_office', 'mcs'), 'left outer')
                ->on('mc.carrier_sales_office_code', '=', 'mcs.carrier_sales_office_code')
                ->on('mcs.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('mcs.end_date', '>', '\''.date("Y-m-d").'\'')
            ->join(array('m_carrier_department', 'mcd'), 'left outer')
                ->on('mc.carrier_department_code', '=', 'mcd.carrier_department_code')
                ->on('mcd.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('mcd.end_date', '>', '\''.date("Y-m-d").'\'');
        
        // 庸車先コード
        if (trim($conditions['carrier_code']) != '') {
            $stmt->where('mc.carrier_code', '=', $conditions['carrier_code']);
        }
        // 会社区分
        if (trim($conditions['company_section']) != '' && trim($conditions['company_section']) != '0') {
            $stmt->where('mc.company_section', '=', $conditions['company_section']);
        }
        // 会社名
        if (trim($conditions['company_name']) != '') {
            $stmt->where('mcc.company_name', 'LIKE', \DB::expr("'%".$conditions['company_name']."%'"));
        }
        // 営業所名
        if (trim($conditions['sales_office_name']) != '') {
            $stmt->where('mcs.sales_office_name', 'LIKE', \DB::expr("'%".$conditions['sales_office_name']."%'"));
        }
        // 部署名
        if (trim($conditions['department_name']) != '') {
            $stmt->where('mcd.department_name', 'LIKE', \DB::expr("'%".$conditions['department_name']."%'"));
        }
        // 締日
        if (trim($conditions['closing_date']) != '' && trim($conditions['closing_date']) != '0') {
            $stmt->and_where_open();
            $stmt->where('mc.closing_date', '=', $conditions['closing_date']);
            $stmt->or_where('mc.closing_date_1', '=', $conditions['closing_date']);
            $stmt->or_where('mc.closing_date_2', '=', $conditions['closing_date']);
            $stmt->or_where('mc.closing_date_3', '=', $conditions['closing_date']);
            $stmt->and_where_close();
        }
        // 正式名称
        if (trim($conditions['official_name']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(mc.official_name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['official_name']."%'"));
        }
        // 正式名称（カナ）
        if (trim($conditions['official_name_kana']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(mc.official_name_kana),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['official_name_kana']."%'"));
        }
                
        // 適用開始日
        $stmt->where('mc.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('mc.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        $carrier_list =  $stmt->order_by('mc.carrier_code', 'ASC')->execute($db)->as_array();
        
        //課リスト取得
        $division_list = GenerateList::getDivisionList(false, $db);
        
        // 締日リスト
        $closing_date_list = GenerateList::getClosingDateList(false);
        
        $result = array();
        
        //担当部署の値再セット
        foreach ($carrier_list as $carrier) {
            $record = array(
                'carrier_code' => $carrier['carrier_code'],
                'carrier_name' => $carrier['carrier_name'],
                'sales_office_name' => $carrier['sales_office_name'],
                'department_name' => $carrier['department_name'],
                'closing_date' => $carrier['closing_date'],
                'official_name' => $carrier['official_name'],
                'postal_code' => $carrier['postal_code'],
                'address' => $carrier['address'],
                'phone_number' => $carrier['phone_number'],
                'fax_number' => $carrier['fax_number'],
                'person_in_charge_surname' => $carrier['person_in_charge_surname'],
                'person_in_charge_name' => $carrier['person_in_charge_name']
            );
            
            //担当部署の雛形設定
            foreach ($division_list as $key => $value) {
                $record['division_'.$key] = '×';
            }
            
            //担当部署の設定
            if (!empty($carrier['department_in_charge'])) {
                $department_in_charge_list = explode(",", $carrier['department_in_charge']);
                
                foreach ($department_in_charge_list as $division_code) {
                    $record['division_'.$division_code] = '〇';
                }
            }
            
            //締日成形
            $closing_date = "";
            switch ($carrier['closing_date']){
                case "51": //月2回
                    $closing_date = $carrier['closing_date_1']."、".$closing_date_list[$carrier['closing_date_2']];
                    break;
                case "52": //月3回
                    $closing_date = $carrier['closing_date_1']."、".$carrier['closing_date_2']."、".$closing_date_list[$carrier['closing_date_3']];
                    break;
                case "50": //都度
                default: //月1回
                    $closing_date = $closing_date_list[$carrier['closing_date']];
                    break;
            }
            
            $record['closing_date'] = $closing_date;
            
            $result[$carrier['carrier_code']] = $record;
        }

        return $result;

    }
    
    /**
     * 担当部署の項目名設定
     */
    public static function setDepartmentInChargeColumn($division_list, $conditions, $department_in_charge = null) {
        //項目追加
        foreach ($division_list as $key => $value) {
            $conditions += array('department_in_charge'.$key => '');
        }
        
        //値セット
        if (!empty($department_in_charge)){
            $department_in_charge_list = explode(",", $department_in_charge);
            foreach ($department_in_charge_list as $division_code) {
                $conditions['department_in_charge'.$division_code] = 1;
            }
        }
        
        return $conditions;
    }
    
    /**
     * 担当部署の連結値取得
     */
    public static function getDepartmentInCharge($division_list, $conditions) {
        $result = '';
        $cnt = 0;
        foreach ($division_list as $key => $value) {
            if ($conditions['department_in_charge'.$key] == 1) {
                if ($cnt > 0) {$result = $result.',';}
                $result = $result.$key;
                $cnt++;
            }
        }
        return $result;
    }
}