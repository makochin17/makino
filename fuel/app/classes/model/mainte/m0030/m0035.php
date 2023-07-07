<?php
namespace Model\Mainte\M0030;
use \Model\Mainte\M0030\M0034;
use \Model\Common\SystemConfig;
use \Model\Common\GenerateList;
use \Model\Common\AuthConfig;
use \Model\Common\OpeLog;
use \Date;
use \Log;
use \Config;

class M0035 extends \Model {

    public static $db       = 'MAKINO';
    
    /**
     * 庸車先マスタレコード取得
     */
    public static function getCarrier($code, $db) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',self::$db);
        
        // 項目
        $stmt = \DB::select(
                array('mc.carrier_code', 'carrier_code'),
                array('mc.carrier_company_code', 'carrier_company_code'),
                array('mcc.company_name', 'carrier_company_name'),
                array('mc.carrier_sales_office_code', 'carrier_sales_office_code'),
                array('mcs.sales_office_name', 'carrier_sales_office_name'),
                array('mc.carrier_department_code', 'carrier_department_code'),
                array('mcd.department_name', 'carrier_department_name'),
                array('mc.closing_date', 'closing_date'),
                array('mc.company_section', 'company_section'),
                array('mc.criterion_closing_date', 'criterion_closing_date'),
                array('mc.closing_date_1', 'closing_date_1'),
                array('mc.closing_date_2', 'closing_date_2'),
                array('mc.closing_date_3', 'closing_date_3'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.official_name),"'.$encrypt_key.'")'), 'official_name'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.official_name_kana),"'.$encrypt_key.'")'), 'official_name_kana'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.postal_code),"'.$encrypt_key.'")'), 'postal_code'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.address),"'.$encrypt_key.'")'), 'address'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.address2),"'.$encrypt_key.'")'), 'address2'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.phone_number),"'.$encrypt_key.'")'), 'phone_number'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.fax_number),"'.$encrypt_key.'")'), 'fax_number'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.person_in_charge_surname),"'.$encrypt_key.'")'), 'person_in_charge_surname'),
                array(\DB::expr('AES_DECRYPT(UNHEX(mc.person_in_charge_name),"'.$encrypt_key.'")'), 'person_in_charge_name'),
                array('mc.department_in_charge', 'department_in_charge'),
                array('mc.start_date', 'start_date')
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
        $stmt->where('mc.carrier_code', '=', $code);
        // 適用開始日
        $stmt->where('mc.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('mc.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        return $stmt->execute($db)->as_array();
    }
    
    /**
     * 庸車先会社マスタレコード取得
     */
    public static function getCarrierCompany($code, $db) {
        // 項目
        $stmt = \DB::select(
                array('m.carrier_company_code', 'carrier_company_code'),
                array('m.start_date', 'start_date'),
                );

        // テーブル
        $stmt->from(array('m_carrier_company', 'm'));
        
        // 庸車先会社コード
        $stmt->where('m.carrier_company_code', '=', $code);
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        return $stmt->execute($db)->as_array();
    }
    
    /**
     * 庸車先営業所マスタレコード取得
     */
    public static function getCarrierSalesOffice($code, $db) {
        // 項目
        $stmt = \DB::select(
                array('m.carrier_sales_office_code', 'carrier_sales_office_code'),
                array('m.carrier_company_code', 'carrier_company_code'),
                array('m.start_date', 'start_date'),
                );

        // テーブル
        $stmt->from(array('m_carrier_sales_office', 'm'));
        
        // 庸車先会社コード
        $stmt->where('m.carrier_sales_office_code', '=', $code);
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        return $stmt->execute($db)->as_array();
    }
    
    /**
     * 庸車先部署マスタレコード取得
     */
    public static function getCarrierDepartment($code, $db) {
        // 項目
        $stmt = \DB::select(
                array('m.carrier_department_code', 'carrier_department_code'),
                array('m.carrier_sales_office_code', 'carrier_sales_office_code'),
                array('m.start_date', 'start_date'),
                );

        // テーブル
        $stmt->from(array('m_carrier_department', 'm'));
        
        // 庸車先会社コード
        $stmt->where('m.carrier_department_code', '=', $code);
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        return $stmt->execute($db)->as_array();
    }
    
    /**
     * 庸車先マスタレコード存在チェック（庸車先会社コード指定）
     */
    public static function existsCarrierCompany($code, $db) {
        
        // 項目
        $stmt = \DB::select(
                array('m.carrier_code', 'carrier_code'),
                array('m.start_date', 'start_date'),
                );

        // テーブル
        $stmt->from(array('m_carrier', 'm'));
        
        // 庸車先会社コード
        $stmt->where('m.carrier_company_code', '=', $code);
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        return $stmt->execute($db)->as_array();
    }
    
    /**
     * 庸車先マスタレコード存在チェック（庸車先営業所コード指定）
     */
    public static function existsCarrierSalesOffice($code, $db) {
        
        // 項目
        $stmt = \DB::select(
                array('m.carrier_code', 'carrier_code'),
                array('m.start_date', 'start_date'),
                );

        // テーブル
        $stmt->from(array('m_carrier', 'm'));
        
        // 庸車先営業所コード
        $stmt->where('m.carrier_sales_office_code', '=', $code);
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        return $stmt->execute($db)->as_array();
    }
    
    /**
     * 庸車先マスタレコード存在チェック（庸車先部署コード指定）
     */
    public static function existsCarrierDepartment($code, $db) {
        
        // 項目
        $stmt = \DB::select(
                array('m.carrier_code', 'carrier_code'),
                array('m.start_date', 'start_date'),
                );

        // テーブル
        $stmt->from(array('m_carrier', 'm'));
        
        // 庸車先部署コード
        $stmt->where('m.carrier_department_code', '=', $code);
        // 適用開始日
        $stmt->where('m.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('m.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        return $stmt->execute($db)->as_array();
    }
    
    /**
     * 庸車先削除
     */
    public static function delete_record($carrier_code, $db) {
        
        //庸車先マスタ情報取得
        $result = self::getCarrier($carrier_code, $db);
        if (is_countable($result)){
            if (count($result) == 0) {
                return Config::get('m_MW0004');
            }
        } else {
            return Config::get('m_MW0004');
        }
        $carrier_data = $result[0];
        
        //庸車先マスタ削除
        $result = self::delCarrier($carrier_code, $db);
        if (!$result) {
            Log::error(str_replace('XXXXX','庸車先',Config::get('m_ME0008'))."[".$carrier_code."]");
            return str_replace('XXXXX','庸車先',Config::get('m_ME0008'));
        }
        
        //庸車先会社レコードが未使用なら削除
        $result = self::delCarrierCompany($carrier_data['carrier_company_code'], $db);
        if (!$result) {
            Log::error(str_replace('XXXXX','庸車先会社',Config::get('m_ME0008'))."[".$carrier_data['carrier_company_code']."]");
            return str_replace('XXXXX','庸車先会社',Config::get('m_ME0008'));
        }
        
        if (!empty($carrier_data['carrier_sales_office_code'])) {
            //庸車先営業所レコードが未使用なら削除
            $result = self::delCarrierSalesOffice($carrier_data['carrier_sales_office_code'], $db);
            if (!$result) {
                Log::error(str_replace('XXXXX','庸車先営業所',Config::get('m_ME0008'))."[".$carrier_data['carrier_sales_office_code']."]");
                return str_replace('XXXXX','庸車先営業所',Config::get('m_ME0008'));
            }
        }
        
        if (!empty($carrier_data['carrier_department_code'])) {
            //庸車先部署レコードが未使用なら削除
            $result = self::delCarrierDepartment($carrier_data['carrier_department_code'], $db);
            if (!$result) {
                Log::error(str_replace('XXXXX','庸車先部署',Config::get('m_ME0008'))."[".$carrier_data['carrier_department_code']."]");
                return str_replace('XXXXX','庸車先部署',Config::get('m_ME0008'));
            }
        }
        
        // 操作ログ出力
        $result = OpeLog::addOpeLog('MI0007', Config::get('m_MI0007'), '庸車先マスタ', $db);
        if (!$result) {
            Log::error(Config::get('m_CE0007'));
            return Config::get('m_CE0007');
        }
        
        return null;
    }

    /**
     * 庸車先更新
     */
    public static function update_record($conditions, $db) {
        
        //庸車先マスタ情報取得
        $result = self::getCarrier($conditions['carrier_code'], $db);
        if (is_countable($result)){
            if (count($result) == 0) {
                return Config::get('m_MW0004');
            }
        } else {
            return Config::get('m_MW0004');
        }
        $carrier_data = $result[0];
        
        ////////////////////////////////////////////
        //庸車先マスタ更新
        
        // 取得レコードの「適用開始日」がシステム日付より過去日か
        if (strtotime($carrier_data['start_date']) < strtotime(Date::forge()->format('mysql_date'))) {
            // レコード削除（論理）
            $result = self::delCarrier($carrier_data['carrier_code'], $db);
            if (!$result) {
                Log::error(str_replace('XXXXX','庸車先',Config::get('m_ME0008'))."[".$carrier_data['carrier_code']."]");
                return str_replace('XXXXX','庸車先',Config::get('m_ME0008'));
            }
            
            // 庸車先マスタ登録情報作成
            $carrier_sales_office_code = $conditions['carrier_sales_office_code'];
            if (empty($carrier_sales_office_code) || $conditions['sales_office_radio'] == 3)$carrier_sales_office_code = \DB::expr('null');
            
            $carrier_department_code = $conditions['carrier_department_code'];
            if (empty($carrier_department_code) || $conditions['department_radio'] == 3)$carrier_department_code = \DB::expr('null');
                
            //担当部署の登録用情報取得
            $department_in_charge = M0030::getDepartmentInCharge(GenerateList::getDivisionList(false, $db), $conditions);
            
            $data = array(
                'carrier_code'				=> $conditions['carrier_code'],
                'carrier_company_code'		=> $conditions['carrier_company_code'],
                'carrier_sales_office_code'	=> $carrier_sales_office_code,
                'carrier_department_code'	=> $carrier_department_code,
                'carrier_name_company'		=> $conditions['company_name'],
                'carrier_name_sales_office'	=> $conditions['company_name'].$conditions['sales_office_name'],
                'carrier_name'				=> $conditions['company_name'].$conditions['sales_office_name'].$conditions['department_name'],
                'closing_date'				=> $conditions['closing_date'],
                'company_section'			=> $conditions['company_section'],
                'criterion_closing_date'	=> $conditions['criterion_closing_date'],
                'closing_date_1'			=> $conditions['closing_date_1'],
                'closing_date_2'			=> $conditions['closing_date_2'],
                'closing_date_3'			=> $conditions['closing_date_3'],
                'official_name'				=> $conditions['official_name'],
                'official_name_kana'		=> $conditions['official_name_kana'],
                'postal_code'				=> $conditions['postal_code'],
                'address'					=> $conditions['address'],
                'address2'					=> $conditions['address2'],
                'phone_number'				=> $conditions['phone_number'],
                'fax_number'				=> $conditions['fax_number'],
                'person_in_charge_surname'	=> $conditions['person_in_charge_surname'],
                'person_in_charge_name'		=> $conditions['person_in_charge_name'],
                'department_in_charge'		=> $department_in_charge,
                );

            //庸車先マスタ登録
            $result = M0034::addCarrier($data, $db);
            if (!$result) {
                Log::error(str_replace('XXXXX','庸車先',Config::get('m_ME0006'))."[".print_r($conditions,true)."]");
                return str_replace('XXXXX','庸車先',Config::get('m_ME0006'));
            }
        } else {
            //担当部署の登録用情報取得
            $conditions['department_in_charge'] = M0030::getDepartmentInCharge(GenerateList::getDivisionList(false, $db), $conditions);
            
            //　レコード更新
            $result = self::updCarrier($conditions, $db);
            if (!$result) {
                Log::error(str_replace('XXXXX','庸車先',Config::get('m_ME0007'))."[".print_r($conditions,true)."]");
                return str_replace('XXXXX','庸車先',Config::get('m_ME0007'));
            }
        }
        
        $name_change_list = array('carrier_company_code' => '', 'carrier_sales_office_code' => '', 'carrier_department_code' => '');
        
        ////////////////////////////////////////////
        //庸車先会社マスタ更新
        if ($conditions['company_radio'] == 2) {
            //名称変更の場合
            $result = self::getCarrierCompany($conditions['carrier_company_code'], $db);
            $carrier_company_data = $result[0];
            // 取得レコードの「適用開始日」がシステム日付より過去日か
            if (strtotime($carrier_company_data['start_date']) < strtotime(Date::forge()->format('mysql_date'))) {
                // レコード削除（論理）
                $result = self::delCarrierCompany($conditions['carrier_company_code'], $db, 2);
                if (!$result) {
                    Log::error(str_replace('XXXXX','庸車先会社',Config::get('m_ME0008'))."[".$carrier_data['carrier_company_code']."]");
                    return str_replace('XXXXX','庸車先会社',Config::get('m_ME0008'));
                }
                // レコード登録
                $result = self::addCarrierCompany($conditions['carrier_company_code'], $conditions['company_name'], $db);
                if (!$result) {
                    Log::error(str_replace('XXXXX','庸車先会社',Config::get('m_ME0006'))."[".print_r($conditions,true)."]");
                    return str_replace('XXXXX','庸車先会社',Config::get('m_ME0006'));
                }
            } else {
                // レコード更新
                $result = self::updCarrierCompany($conditions['carrier_company_code'], $conditions['company_name'], $db);
                if (!$result) {
                    Log::error(str_replace('XXXXX','庸車先会社',Config::get('m_ME0007'))."[".print_r($conditions,true)."]");
                    return str_replace('XXXXX','庸車先会社',Config::get('m_ME0007'));
                }
            }
            $name_change_list['carrier_company_code'] = $conditions['carrier_company_code'];
        }
        
        ////////////////////////////////////////////
        //庸車先営業所マスタ更新
        if ($conditions['sales_office_radio'] == 2) {
            //名称変更の場合
            $result = self::getCarrierSalesOffice($conditions['carrier_sales_office_code'], $db);
            $carrier_sales_office_data = $result[0];
            // 取得レコードの「適用開始日」がシステム日付より過去日か
            if (strtotime($carrier_sales_office_data['start_date']) < strtotime(Date::forge()->format('mysql_date'))) {
                // レコード削除（論理）
                $result = self::delCarrierSalesOffice($conditions['carrier_sales_office_code'], $db, 2);
                if (!$result) {
                    Log::error(str_replace('XXXXX','庸車先営業所',Config::get('m_ME0008'))."[".$carrier_data['carrier_sales_office_code']."]");
                    return str_replace('XXXXX','庸車先営業所',Config::get('m_ME0008'));
                }
                // レコード登録
                $result = self::addCarrierSalesOffice($conditions['carrier_sales_office_code'], $carrier_sales_office_data['carrier_company_code'], $conditions['sales_office_name'], $db);
                if (!$result) {
                    Log::error(str_replace('XXXXX','庸車先営業所',Config::get('m_ME0006'))."[".print_r($conditions,true)."]");
                    return str_replace('XXXXX','庸車先営業所',Config::get('m_ME0006'));
                }
            } else {
                // レコード更新
                $result = self::updCarrierSalesOffice($conditions['carrier_sales_office_code'], $conditions['sales_office_name'], $db);
                if (!$result) {
                    Log::error(str_replace('XXXXX','庸車先営業所',Config::get('m_ME0007'))."[".print_r($conditions,true)."]");
                    return str_replace('XXXXX','庸車先営業所',Config::get('m_ME0007'));
                }
            }
            $name_change_list['carrier_sales_office_code'] = $conditions['carrier_sales_office_code'];
        } elseif ($conditions['sales_office_radio'] == 3) {
            //庸車先営業所レコードが未使用なら削除
            $result = self::delCarrierSalesOffice($conditions['carrier_sales_office_code'], $db);
            if (!$result) {
                Log::error(str_replace('XXXXX','庸車先営業所',Config::get('m_ME0008'))."[".$conditions['carrier_sales_office_code']."]");
                return str_replace('XXXXX','庸車先営業所',Config::get('m_ME0008'));
            }
        }
        
        ////////////////////////////////////////////
        //庸車先部署マスタ更新
        if ($conditions['department_radio'] == 2) {
            //名称変更の場合
            $result = self::getCarrierDepartment($conditions['carrier_department_code'], $db);
            $carrier_department_data = $result[0];
            // 取得レコードの「適用開始日」がシステム日付より過去日か
            if (strtotime($carrier_department_data['start_date']) < strtotime(Date::forge()->format('mysql_date'))) {
                // レコード削除（論理）
                $result = self::delCarrierDepartment($conditions['carrier_department_code'], $db, 2);
                if (!$result) {
                    Log::error(str_replace('XXXXX','庸車先部署',Config::get('m_ME0008'))."[".$carrier_data['carrier_department_code']."]");
                    return str_replace('XXXXX','庸車先部署',Config::get('m_ME0008'));
                }
                // レコード登録
                $result = self::addCarrierDepartment($conditions['carrier_department_code'], $carrier_department_data['carrier_sales_office_code'], $conditions['department_name'], $db);
                if (!$result) {
                    Log::error(str_replace('XXXXX','庸車先部署',Config::get('m_ME0006'))."[".print_r($conditions,true)."]");
                    return str_replace('XXXXX','庸車先部署',Config::get('m_ME0006'));
                }
            } else {
                // レコード更新
                $result = self::updCarrierDepartment($conditions['carrier_department_code'], $conditions['department_name'], $db);
                if (!$result) {
                    Log::error(str_replace('XXXXX','庸車先部署',Config::get('m_ME0007'))."[".print_r($conditions,true)."]");
                    return str_replace('XXXXX','庸車先部署',Config::get('m_ME0007'));
                }
            }
            $name_change_list['carrier_department_code'] = $conditions['carrier_department_code'];
        } elseif ($conditions['department_radio'] == 3) {
            //庸車先部署レコードが未使用なら削除
            $result = self::delCarrierDepartment($conditions['carrier_department_code'], $db);
            if (!$result) {
                Log::error(str_replace('XXXXX','庸車先部署',Config::get('m_ME0008'))."[".$conditions['carrier_department_code']."]");
                return str_replace('XXXXX','庸車先部署',Config::get('m_ME0008'));
            }
        }
        
        ////////////////////////////////////////////
        //庸車先名称最新化
        $carrier_company_code = $name_change_list['carrier_company_code'];
        $carrier_sales_office_code = $name_change_list['carrier_sales_office_code'];
        $carrier_department_code = $name_change_list['carrier_department_code'];
        
        //名称変更があるかチェック
        if (!empty($carrier_company_code) || !empty($carrier_sales_office_code) || !empty($carrier_department_code)) {
            $carrier_list = array();
        
            //変更された会社を持つ庸車先取得
            if (!empty($carrier_company_code)) {
                $result = self::existsCarrierCompany($carrier_company_code, $db);
                foreach ($result as $carrier) {
                    $carrier_list[] = $carrier['carrier_code'];
                }
            }

            //変更された営業所を持つ庸車先取得
            if (!empty($carrier_sales_office_code)) {
                $result = self::existsCarrierSalesOffice($carrier_sales_office_code, $db);
                foreach ($result as $carrier) {
                    $carrier_list[] = $carrier['carrier_code'];
                }
            }

            //変更された部署を持つ庸車先取得
            if (!empty($carrier_department_code)) {
                $result = self::existsCarrierDepartment($carrier_department_code, $db);
                foreach ($result as $carrier) {
                    $carrier_list[] = $carrier['carrier_code'];
                }
            }
            
            //庸車先名称更新
            $result = self::updCarrierName($carrier_list, $conditions['carrier_code'], $db);
            if (!$result) {
                Log::error(str_replace('XXXXX','庸車先',Config::get('m_ME0007'))."[".print_r($carrier_list,true)."]");
                return str_replace('XXXXX','庸車先',Config::get('m_ME0007'));
            }
            
            //営業所もしくは部署に削除があるかチェック
        } elseif ($conditions['sales_office_radio'] == 3 || $conditions['department_radio'] == 3) {
            //庸車先名称更新
            $result = self::updCarrierName(array($conditions['carrier_code']), $conditions['carrier_code'], $db);
            if (!$result) {
                Log::error(str_replace('XXXXX','庸車先',Config::get('m_ME0007'))."[".print_r($conditions,true)."]");
                return str_replace('XXXXX','庸車先',Config::get('m_ME0007'));
            }
        }
        
        // 操作ログ出力
        $result = OpeLog::addOpeLog('MI0006', Config::get('m_MI0006'), '庸車先マスタ', $db);
        if (!$result) {
            Log::error(Config::get('m_CE0007'));
            return Config::get('m_CE0007');
        }
        
        return null;
    }
    
    /**
     * 庸車先マスタ更新
     */
    public static function updCarrier($items, $db) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);
        
        $carrier_sales_office_code = $items['carrier_sales_office_code'];
        if (empty($carrier_sales_office_code) || $items['sales_office_radio'] == 3)$carrier_sales_office_code = \DB::expr('null');
        
        $carrier_department_code = $items['carrier_department_code'];
        if (empty($carrier_department_code) || $items['department_radio'] == 3)$carrier_department_code = \DB::expr('null');
        
        // テーブル
        $stmt = \DB::update('m_carrier');
        
        // 項目セット
        $set = array(
            'carrier_code'				=> $items['carrier_code'],
            'carrier_company_code'		=> $items['carrier_company_code'],
            'carrier_sales_office_code'	=> $carrier_sales_office_code,
            'carrier_department_code'	=> $carrier_department_code,
            'carrier_name_company'		=> $items['company_name'],
            'carrier_name_sales_office'	=> $items['company_name'].$items['sales_office_name'],
            'carrier_name'				=> $items['company_name'].$items['sales_office_name'].$items['department_name'],
            'closing_date'				=> $items['closing_date'],
            'company_section'			=> $items['company_section'],
            'criterion_closing_date'	=> $items['criterion_closing_date'],
            'closing_date_1'			=> !empty($items['closing_date_1']) ? $items['closing_date_1'] : null,
            'closing_date_2'			=> !empty($items['closing_date_2']) ? $items['closing_date_2'] : null,
            'closing_date_3'			=> !empty($items['closing_date_3']) ? $items['closing_date_3'] : null,
            'official_name'				=> \DB::expr('HEX(AES_ENCRYPT("'.$items['official_name'].'","'.$encrypt_key.'"))'),
            'official_name_kana'		=> \DB::expr('HEX(AES_ENCRYPT("'.$items['official_name_kana'].'","'.$encrypt_key.'"))'),
            'postal_code'				=> \DB::expr('HEX(AES_ENCRYPT("'.$items['postal_code'].'","'.$encrypt_key.'"))'),
            'address'					=> \DB::expr('HEX(AES_ENCRYPT("'.$items['address'].'","'.$encrypt_key.'"))'),
            'address2'					=> \DB::expr('HEX(AES_ENCRYPT("'.$items['address2'].'","'.$encrypt_key.'"))'),
            'phone_number'				=> \DB::expr('HEX(AES_ENCRYPT("'.$items['phone_number'].'","'.$encrypt_key.'"))'),
            'fax_number'				=> \DB::expr('HEX(AES_ENCRYPT("'.$items['fax_number'].'","'.$encrypt_key.'"))'),
            'person_in_charge_surname'	=> \DB::expr('HEX(AES_ENCRYPT("'.$items['person_in_charge_surname'].'","'.$encrypt_key.'"))'),
            'person_in_charge_name'		=> \DB::expr('HEX(AES_ENCRYPT("'.$items['person_in_charge_name'].'","'.$encrypt_key.'"))'),
            'department_in_charge'	    => $items['department_in_charge'],
            'start_date'                => Date::forge()->format('mysql_date'),
            'end_date'                  => Date::create_from_string("9999-12-31" , "mysql_date")->format('mysql_date')
            );
        $stmt->set(array_merge($set, self::getEtcData(false)));
        
        // 車両コード
        $stmt->where('carrier_code', '=', $items['carrier_code']);
        // 適用開始日
        $stmt->where('start_date', '<=', Date::forge()->format('mysql_date'));
        // 適用終了日
        $stmt->where('end_date', '>', Date::forge()->format('mysql_date'));
        
        // 更新実行
        $result = $stmt->execute($db);
        
        if($result > 0) {
            return true;
        }
        return false;
    }
    
    /**
     * 庸車先マスタ削除
     */
    public static function delCarrier($code, $db) {
        
        // テーブル
        $stmt = \DB::update('m_carrier');
        
        // 項目セット
        $set = array(
            'end_date' => Date::forge()->format('mysql_date')
            );
        $stmt->set(array_merge($set, self::getEtcData(false)));
        
        // 庸車先コード
        $stmt->where('carrier_code', '=', $code);
        // 適用開始日
        $stmt->where('start_date', '<=', Date::forge()->format('mysql_date'));
        // 適用終了日
        $stmt->where('end_date', '>', Date::forge()->format('mysql_date'));
        
        // 更新実行
        $result = $stmt->execute($db);
        
        if($result > 0) {
            return true;
        }
        return false;
    }
    
    /**
     * 会社マスタ登録
     */
    public static function addCarrierCompany($carrier_company_code, $company_name, $db) {
        
        // 項目セット
        $set = array(
            'carrier_company_code' => $carrier_company_code,
            'company_name' => $company_name,
            'start_date' => Date::forge()->format('mysql_date'),
            'end_date' => Date::create_from_string("9999-12-31" , "mysql_date")->format('mysql_date')
            );
        $set = array_merge($set, self::getEtcData(true));
        
        // 登録実行
        $stmt = \DB::insert('m_carrier_company')->set($set);
        $result = $stmt->execute($db);
        
        if($result[1] > 0) {
            return true;
        }
        return false;
    }
    
    /**
     * 会社マスタ更新
     */
    public static function updCarrierCompany($carrier_company_code, $company_name, $db) {
        
        // テーブル
        $stmt = \DB::update('m_carrier_company');
        
        // 項目セット
        $set = array('company_name' => $company_name);
        $stmt->set(array_merge($set, self::getEtcData(false)));
        
        // 庸車先会社コード
        $stmt->where('carrier_company_code', '=', $carrier_company_code);
        // 適用開始日
        $stmt->where('start_date', '<=', Date::forge()->format('mysql_date'));
        // 適用終了日
        $stmt->where('end_date', '>', Date::forge()->format('mysql_date'));
        
        // 更新実行
        $result = $stmt->execute($db);
        
        if($result > 0) {
            return true;
        }
        return false;
    }
    
    /**
     * 会社マスタ削除
     */
    public static function delCarrierCompany($code, $db, $mode = 1) {
        $flag = false;
        if ($mode == 1) {
            //庸車先会社コードを持つ庸車先マスタ存在チェック
            $result = self::existsCarrierCompany($code, $db);
            if (is_countable($result)){
                if (count($result) == 0) {
                    $flag = true;
                }
            }
        } else {
            $flag = true;
        }
        
        if ($flag) {
            // テーブル
            $stmt = \DB::update('m_carrier_company');

            // 項目セット
            $set = array(
                'end_date' => Date::forge()->format('mysql_date')
                );
            $stmt->set(array_merge($set, self::getEtcData(false)));

            // 庸車先会社コード
            $stmt->where('carrier_company_code', '=', $code);
            // 適用開始日
            $stmt->where('start_date', '<=', Date::forge()->format('mysql_date'));
            // 適用終了日
            $stmt->where('end_date', '>', Date::forge()->format('mysql_date'));

            // 更新実行
            $result = $stmt->execute($db);

            if($result == 0) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * 営業所マスタ登録
     */
    public static function addCarrierSalesOffice($carrier_sales_office_code, $carrier_company_code, $sales_office_name, $db) {
        
        // 項目セット
        $set = array(
            'carrier_sales_office_code' => $carrier_sales_office_code,
            'carrier_company_code' => $carrier_company_code,
            'sales_office_name' => $sales_office_name,
            'start_date' => Date::forge()->format('mysql_date'),
            'end_date' => Date::create_from_string("9999-12-31" , "mysql_date")->format('mysql_date')
            );
        $set = array_merge($set, self::getEtcData(true));
        
        // 登録実行
        $stmt = \DB::insert('m_carrier_sales_office')->set($set);
        $result = $stmt->execute($db);
        
        if($result[1] > 0) {
            return true;
        }
        return false;
    }
    
    /**
     * 営業所マスタ更新
     */
    public static function updCarrierSalesOffice($carrier_sales_office_code, $sales_office_name, $db) {
        
        // テーブル
        $stmt = \DB::update('m_carrier_sales_office');
        
        // 項目セット
        $set = array('sales_office_name' => $sales_office_name);
        $stmt->set(array_merge($set, self::getEtcData(false)));
        
        // 庸車先営業所コード
        $stmt->where('carrier_sales_office_code', '=', $carrier_sales_office_code);
        // 適用開始日
        $stmt->where('start_date', '<=', Date::forge()->format('mysql_date'));
        // 適用終了日
        $stmt->where('end_date', '>', Date::forge()->format('mysql_date'));
        
        // 更新実行
        $result = $stmt->execute($db);
        
        if($result > 0) {
            return true;
        }
        return false;
    }
    
    /**
     * 営業所マスタ削除
     */
    public static function delCarrierSalesOffice($code, $db, $mode = 1) {
        $flag = false;
        if ($mode == 1) {
            //庸車先営業所コードを持つ庸車先マスタ存在チェック
            $result = self::existsCarrierSalesOffice($code, $db);
            if (is_countable($result)){
                if (count($result) == 0) {
                    $flag = true;
                }
            }
        } else { 
            $flag = true;
        }
        
        if ($flag) {
            // テーブル
            $stmt = \DB::update('m_carrier_sales_office');

            // 項目セット
            $set = array(
                'end_date' => Date::forge()->format('mysql_date')
                );
            $stmt->set(array_merge($set, self::getEtcData(false)));

            // 庸車先営業所コード
            $stmt->where('carrier_sales_office_code', '=', $code);
            // 適用開始日
            $stmt->where('start_date', '<=', Date::forge()->format('mysql_date'));
            // 適用終了日
            $stmt->where('end_date', '>', Date::forge()->format('mysql_date'));

            // 更新実行
            $result = $stmt->execute($db);

            if($result == 0) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * 部署マスタ登録
     */
    public static function addCarrierDepartment($carrier_department_code, $carrier_sales_office_code, $department_name, $db) {
        
        // 項目セット
        $set = array(
            'carrier_department_code' => $carrier_department_code,
            'carrier_sales_office_code' => $carrier_sales_office_code,
            'department_name' => $department_name,
            'start_date' => Date::forge()->format('mysql_date'),
            'end_date' => Date::create_from_string("9999-12-31" , "mysql_date")->format('mysql_date')
            );
        $set = array_merge($set, self::getEtcData(true));
        
        // 登録実行
        $stmt = \DB::insert('m_carrier_department')->set($set);
        $result = $stmt->execute($db);
        
        if($result[1] > 0) {
            return true;
        }
        return false;
    }
    
    /**
     * 部署マスタ更新
     */
    public static function updCarrierDepartment($carrier_department_code, $department_name, $db) {
        
        // テーブル
        $stmt = \DB::update('m_carrier_department');
        
        // 項目セット
        $set = array('department_name' => $department_name);
        $stmt->set(array_merge($set, self::getEtcData(false)));
        
        // 庸車先部署コード
        $stmt->where('carrier_department_code', '=', $carrier_department_code);
        // 適用開始日
        $stmt->where('start_date', '<=', Date::forge()->format('mysql_date'));
        // 適用終了日
        $stmt->where('end_date', '>', Date::forge()->format('mysql_date'));
        
        // 更新実行
        $result = $stmt->execute($db);
        
        if($result > 0) {
            return true;
        }
        return false;
    }
    
    /**
     * 部署マスタ削除
     */
    public static function delCarrierDepartment($code, $db, $mode = 1) {
        $flag = false;
        if ($mode == 1) {
            //庸車先部署コードを持つ庸車先マスタ存在チェック
            $result = self::existsCarrierDepartment($code, $db);
            if (is_countable($result)){
                if (count($result) == 0) {
                    $flag = true;
                }
            }
        } else {
            $flag = true;
        }
        
        if ($flag) {
            // テーブル
            $stmt = \DB::update('m_carrier_department');

            // 項目セット
            $set = array(
                'end_date' => Date::forge()->format('mysql_date')
                );
            $stmt->set(array_merge($set, self::getEtcData(false)));

            // 庸車先部署コード
            $stmt->where('carrier_department_code', '=', $code);
            // 適用開始日
            $stmt->where('start_date', '<=', Date::forge()->format('mysql_date'));
            // 適用終了日
            $stmt->where('end_date', '>', Date::forge()->format('mysql_date'));

            // 更新実行
            $result = $stmt->execute($db);

            if($result == 0) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * 庸車先名称最新化
     */
    public static function updCarrierName($input_list, $no_check_code, $db) {
        //重複値削除
        $carrier_list = array_unique($input_list);
        
        foreach ($carrier_list as $carrier_code) {
            //庸車先マスタ情報取得
            $result = self::getCarrier($carrier_code, $db);
            $carrier_data = $result[0];
            
            // テーブル
            $stmt = \DB::update('m_carrier');

            // 項目セット
            $set = array(
                'carrier_name_company' => $carrier_data['carrier_company_name'],
                'carrier_name_sales_office' => $carrier_data['carrier_company_name'].$carrier_data['carrier_sales_office_name'],
                'carrier_name' => $carrier_data['carrier_company_name'].$carrier_data['carrier_sales_office_name'].$carrier_data['carrier_department_name']
                );
            $stmt->set(array_merge($set, self::getEtcData(false)));

            // 庸車先コード
            $stmt->where('carrier_code', '=', $carrier_code);
            // 適用開始日
            $stmt->where('start_date', '<=', Date::forge()->format('mysql_date'));
            // 適用終了日
            $stmt->where('end_date', '>', Date::forge()->format('mysql_date'));

            // 更新実行
            $result = $stmt->execute($db);

            if($result == 0 && $no_check_code != $carrier_code) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * 付加データ
     */
    public static function getEtcData($is_insert) {
        
        $user_master_id   = AuthConfig::getAuthConfig('user_id');
        switch ($is_insert) {
        case true:  // 新規登録
            $data = array(
                'create_datetime'   => Date::forge()->format('mysql'),
                'create_user'       => $user_master_id,
                'update_datetime'   => Date::forge()->format('mysql'),
                'update_user'       => $user_master_id
            );
            break;
        case false: // 更新
        default:    // 更新
            $data = array(
                'update_datetime'   => Date::forge()->format('mysql'),
                'update_user'       => $user_master_id
            );
            break;
        }
        return $data;
    }
}