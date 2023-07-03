<?php
namespace Model\Search;
use \Model\Common\SystemConfig;

class S0050 extends \Model {

    public static $db       = 'ONISHI';
    public static $count    = 0;

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 車両マスタレコード検索
     */
    public static function getSearch($is_count, $conditions, $offset, $limit, $db) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 件数取得
        if ($is_count) {

            $stmt = \DB::select(\DB::expr('COUNT(mcar.car_code) AS count'));

        // データ取得
        } else {

            $stmt = \DB::select(
                    array('mcar.car_code', 'car_code'),
                    array('mcarmodel.car_model_name', 'car_model_name'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mcar.car_name),"'.$encrypt_key.'")'), 'car_name'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mcar.car_number),"'.$encrypt_key.'")'), 'car_number'),
                    );
        }

        // テーブル
        $stmt->from(array('m_car', 'mcar'))
            ->join(array('m_car_model', 'mcarmodel'), 'left outer')
                ->on('mcar.car_model_code', '=', 'mcarmodel.car_model_code')
                ->on('mcarmodel.start_date', '<=', '\''.date("Y-m-d").'\'')
                ->on('mcarmodel.end_date', '>', '\''.date("Y-m-d").'\'');
        
        // 車両コード
        if (trim($conditions['car_code']) != '') {
            $stmt->where('mcar.car_code', '=', $conditions['car_code']);
        }
        // 車種
        if (trim($conditions['car_model']) != '' && trim($conditions['car_model']) != '000') {
            $stmt->where('mcar.car_model_code', '=', $conditions['car_model']);
        }
        // 車両名
        if (trim($conditions['car_name']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(mcar.car_name),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['car_name']."%'"));
        }
        // 車両番号
        if (trim($conditions['car_number']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(mcar.car_number),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['car_number']."%'"));
        }
        // 適用開始日
        $stmt->where('mcar.start_date', '<=', date("Y-m-d"));
        // 適用終了日
        $stmt->where('mcar.end_date', '>', date("Y-m-d"));
        
        // 検索実行
        if ($is_count) {
            // 件数取得
            $res = $stmt->execute($db)->as_array();
            return $res[0]['count'];

        } else {
            // データ取得
            return $stmt->order_by('mcar.car_code', 'ASC')
            ->limit($limit)
            ->offset($offset)
            ->execute($db)
            ->as_array();
        }

    }

}