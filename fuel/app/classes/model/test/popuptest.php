<?php
namespace Model\Test\Popuptest;
use \Model\Init;
//use \Model\Table\mRoster;

class Popuptest extends \Model {

    public static $db       = 'MAKINO';
    public static $count    = 0;

    //=========================================================================//
    //=======================   共               通   =========================//
    //=========================================================================//
    /**
     * 委員会の取得
     */
    public static function getCommittee($db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        // バージョンマスタ
        $res        = array();
        $res['']    = '-';
        $data       = \DB::select()->from('m_car_model')->where('car_model_code', '000')->execute($db)->as_array();

        if (!empty($data)) {
            foreach($data as $key => $val){
                $res[$val['car_model_code']] = $val['car_model_name'];
            }
        }
        return $res;
    }

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 検索実行
     */
    public static function getSearch($action, $is_count, $conditions, $offset, $limit, $db) {

        // 件数取得
        if ($is_count) {

            $stmt = \DB::select(\DB::expr('COUNT(m.id) AS count'));

        // データ取得
        } else {

            $stmt = \DB::select(
                    array('m.id', 'id'),
                    array('m.name', 'name'),
                    array('m.committee_id', 'committee_id')
                    );
        }

        // 基礎条件１
        $stmt->from(array('m_roster', 'm'))
        ->where('m.del_flg', 'NO');
        // コード
        if (trim($conditions['from_code']) != '' && trim($conditions['to_code']) != '') {
            $stmt->where('m.id', 'between', array($conditions['from_code'], $conditions['to_code']));
        } else {
            if (trim($conditions['from_code']) != '') {
                $stmt->where('m.id', '>=', $conditions['from_code']);
            }
            if (trim($conditions['to_code']) != '') {
                $stmt->where('m.id', '<=', $conditions['to_code']);
            }
        }
        // 名称
        if (trim($conditions['name']) != '') {
            $stmt->where('m.name', 'LIKE', \DB::expr("'%".$conditions['name']."%'"));
        }
        // 検索実行
        if ($is_count) {
            // 件数取得
            $res = $stmt->execute($db)->as_array();
            return $res[0]['count'];

        } else {
            if ($action == 'export') {
                // データ取得
                return $stmt->order_by('m.id', 'ASC')
                ->execute($db)
                ->as_array();
            } else {
                // データ取得
                return $stmt->order_by('m.id', 'ASC')
                ->limit($limit)
                ->offset($offset)
                ->execute($db)
                ->as_array();
            }
        }

    }

    //=========================================================================//
    //=============   登　　録　・　削　　除　・　更    新  =======================//
    //=========================================================================//
    /**
     * 対象ユーザーの取得
     */
    public static function getMroster($id, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        return mRoster::getMasterById($id, $db);
    }

    /**
     * 対象ユーザーの削除（論理削除）
     */
    public static function delRoster($id, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        $set = array('del_flg' => 'YES');
        if (!mRoster::update($set, $id, $db)) {
            return false;
        }
        return true;
    }

    /**
     * 対象ユーザーの更新
     */
    public static function updRoster($set, $id, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (!mRoster::update($set, $id, $db)) {
            return false;
        }
        return true;
    }

    /**
     * 対象ユーザーの登録
     */
    public static function addRoster($set, $db = null) {

        if (is_null($db)) {
            $db = self::$db;
        }

        if (!mRoster::set($set, $db)) {
            return false;
        }
        return true;
    }

}