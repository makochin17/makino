<?php
namespace Model\Mainte\M0020;
use \Model\Common\SystemConfig;
use \Model\Common\GenerateList;
use \Model\Common\OpeLog;
use \Model\Mainte\M0020\M0025;
use \Model\Search\S0020;

class M0020 extends \Model {

    public static $db       = 'ONISHI';

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 得意先マスタレコード取得
     */
    public static function getClient($code, $db) {
        
        // 項目
        $stmt = \DB::select(
                array('m.client_code', 'client_code'),
                array('m.client_name', 'client_name'),
                array('m.closing_date', 'closing_date'),
                array('m.start_date', 'start_date'),
                );

        // テーブル
        $stmt->from(array('m_client', 'm'));
        
        // 得意先コード
        $stmt->where('m.client_code', '=', $code);
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        return $stmt->execute($db)->as_array();
    }
    
    /**
     * 得意先マスタ検索件数取得
     */
    public static function getSearchCount($conditions, $db) {
        return S0020::getSearch(true, $conditions, null, null, $db);
    }

    /**
     * 得意先マスタ検索
     */
    public static function getSearch($conditions, $offset, $limit, $db) {
        return S0020::getSearch(false, $conditions, $offset, $limit, $db);
    }
    
    /**
     * 得意先データ削除
     */
    public static function delClient($client_code, $db = null) {
        return M0025::delete_record($client_code, $db);
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
            $result = OpeLog::addOpeLog('MI0017', \Config::get('m_MI0017'), '', $db);
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
        $title = mb_convert_encoding('得意先マスタ一覧', 'SJIS', 'UTF-8');
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
        $result += array("client_code" => "得意先コード");
        $result += array("client_name" => "得意先名");
//        $result += array("client_company_code" => "得意先会社コード");
//        $result += array("client_company_name" => "得意先会社名");
//        $result += array("client_sales_office_code" => "得意先営業所コード");
        $result += array("client_sales_office_name" => "得意先営業所名");
//        $result += array("client_department_code" => "得意先部署コード");
        $result += array("client_department_name" => "得意先部署名");
        $result += array("closing_date" => "締日");
//        $result += array("criterion_closing_date" => "基準締日");
        $result += array("official_name" => "正式名称");
//        $result += array("official_name_kana" => "正式名称（カナ）");
        $result += array("postal_code" => "郵便番号");
        $result += array("address" => "住所");
        $result += array("phone_number" => "電話番号");
        $result += array("fax_number" => "FAX番号");
//        $result += array("person_in_charge_surname" => "担当者（姓）");
//        $result += array("person_in_charge_name" => "担当者（名）");
        
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
                array('mc.client_code', 'client_code'),
                array('mc.client_name', 'client_name'),
//                array('mc.client_company_code', 'client_company_code'),
//                array('mcc.company_name', 'company_name'),
//                array('mc.client_sales_office_code', 'client_sales_office_code'),
                array('mcs.sales_office_name', 'sales_office_name'),
//                array('mc.client_department_code', 'client_department_code'),
                array('mcd.department_name', 'department_name'),
                array('mc.closing_date', 'closing_date'),
                array('mc.closing_date_1', 'closing_date_1'),
                array('mc.closing_date_2', 'closing_date_2'),
                array('mc.closing_date_3', 'closing_date_3'),
//                array(\DB::expr('CASE WHEN mc.criterion_closing_date = 1 THEN "積日" ELSE "降日" END'), 'criterion_closing_date'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.official_name),"'.$encrypt_key.'")'), 'official_name'),
//                array(\DB::expr('AES_DECRYPT(UNHEX(mc.official_name_kana),"'.$encrypt_key.'")'), 'official_name_kana'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.postal_code),"'.$encrypt_key.'")'), 'postal_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.address),"'.$encrypt_key.'")'), 'address'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.phone_number),"'.$encrypt_key.'")'), 'phone_number'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.fax_number),"'.$encrypt_key.'")'), 'fax_number'),
//                array(\DB::expr('AES_DECRYPT(UNHEX(mc.person_in_charge_surname),"'.$encrypt_key.'")'), 'person_in_charge_surname'),
//                array(\DB::expr('AES_DECRYPT(UNHEX(mc.person_in_charge_name),"'.$encrypt_key.'")'), 'person_in_charge_name'),
                array('mc.department_in_charge', 'department_in_charge'),
                );

        // テーブル
        $stmt->from(array('m_client', 'mc'))
            ->join(array('m_client_company', 'mcc'), 'left outer')
                ->on('mc.client_company_code', '=', 'mcc.client_company_code')
                ->on('mcc.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('mcc.end_date', '>', '\''.date("Y-m-d").'\'')
            ->join(array('m_client_sales_office', 'mcs'), 'left outer')
                ->on('mc.client_sales_office_code', '=', 'mcs.client_sales_office_code')
                ->on('mcs.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('mcs.end_date', '>', '\''.date("Y-m-d").'\'')
            ->join(array('m_client_department', 'mcd'), 'left outer')
                ->on('mc.client_department_code', '=', 'mcd.client_department_code')
                ->on('mcd.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('mcd.end_date', '>', '\''.date("Y-m-d").'\'');
        
        // 得意先コード
        if (trim($conditions['client_code']) != '') {
            $stmt->where('mc.client_code', '=', $conditions['client_code']);
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
        $client_list = $stmt->order_by('mc.client_code', 'ASC')->execute($db)->as_array();
        
        //課リスト取得
        $division_list = GenerateList::getDivisionList(false, $db);
        
        // 締日リスト
        $closing_date_list = GenerateList::getClosingDateList(false);
        
        $result = array();
        
        //担当部署の値再セット
        foreach ($client_list as $client) {
            $record = array(
                'client_code' => $client['client_code'],
                'client_name' => $client['client_name'],
                'sales_office_name' => $client['sales_office_name'],
                'department_name' => $client['department_name'],
                'closing_date' => $client['closing_date'],
                'official_name' => $client['official_name'],
                'postal_code' => $client['postal_code'],
                'address' => $client['address'],
                'phone_number' => $client['phone_number'],
                'fax_number' => $client['fax_number']
            );
            
            //担当部署の雛形設定
            foreach ($division_list as $key => $value) {
                $record['division_'.$key] = '×';
            }
            
            //担当部署の設定
            if (!empty($client['department_in_charge'])) {
                $department_in_charge_list = explode(",", $client['department_in_charge']);
                
                foreach ($department_in_charge_list as $division_code) {
                    $record['division_'.$division_code] = '〇';
                }
            }
            
            //締日成形
            $closing_date = "";
            switch ($client['closing_date']){
                case "51": //月2回
                    $closing_date = $client['closing_date_1']."、".$closing_date_list[$client['closing_date_2']];
                    break;
                case "52": //月3回
                    $closing_date = $client['closing_date_1']."、".$client['closing_date_2']."、".$closing_date_list[$client['closing_date_3']];
                    break;
                case "50": //都度
                default: //月1回
                    $closing_date = $closing_date_list[$client['closing_date']];
                    break;
            }
            
            $record['closing_date'] = $closing_date;
            
            $result[$client['client_code']] = $record;
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