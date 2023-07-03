<?php
namespace Model\Search;
use \Model\Common\SystemConfig;

class S0070 extends \Model {

    public static $db       = 'ONISHI';
    public static $count    = 0;

    //=========================================================================//
    //==============================   対象検索   ==============================//
    //=========================================================================//
    /**
     * 通知データレコード検索
     */
    public static function getSearch($is_count, $conditions, $offset, $limit, $db) {
        
        $encrypt_key = SystemConfig::getSystemConfig('encrypt_key',$db);

        // 件数取得
        if ($is_count) {

            $stmt = \DB::select(\DB::expr('COUNT(tn.notice_number) AS count'));

        // データ取得
        } else {

            $stmt = \DB::select(
                    array('tn.notice_number', 'notice_number'),
                    array('md.division_name', 'division_name'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(mp.position_name),"'.$encrypt_key.'")'), 'position_name'),
                    array('tn.notice_date', 'notice_date'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(tn.notice_title),"'.$encrypt_key.'")'), 'notice_title'),
                    array(\DB::expr('AES_DECRYPT(UNHEX(tn.notice_message),"'.$encrypt_key.'")'), 'notice_message'),
                    array('tn.notice_start', 'notice_start'),
                    array('tn.notice_end', 'notice_end')
                    );
        }

        // テーブル
        $stmt->from(array('t_notice', 'tn'))
            ->join(array('m_division', 'md'), 'left outer')
                ->on('tn.division_code', '=', 'md.division_code')
            ->join(array('m_position', 'mp'), 'left outer')
                ->on('tn.position_code', '=', 'mp.position_code');

        // 通知番号
        if (trim($conditions['notice_number']) != '') {
            $stmt->where('tn.notice_number', '=', $conditions['notice_number']);
        }
        // 課コード
        if (trim($conditions['division']) != '' && trim($conditions['division']) != '000') {
            $stmt->and_where_open()
                ->where('tn.division_code', '=', $conditions['division'])
                ->or_where('tn.division_code', 'IS', \DB::expr("NULL"))
                ->and_where_close();
        }
        // 役職コード
        if (trim($conditions['position']) != '' && trim($conditions['position']) != '00') {
            $stmt->and_where_open()
                ->where('tn.position_code', '=', $conditions['position'])
                ->or_where('tn.position_code', 'IS', \DB::expr("NULL"))
                ->and_where_close();
        }
        // 通知日付
        if (trim($conditions['notice_date']) != '') {
            $stmt->where('tn.notice_date', '=', $conditions['notice_date']);
        }
        // 通知タイトル
        if (trim($conditions['notice_title']) != '') {
            $stmt->where(\DB::expr('AES_DECRYPT(UNHEX(tn.notice_title),"'.$encrypt_key.'")'), 'LIKE', \DB::expr("'%".$conditions['notice_title']."%'"));
        }
        // 通知開始日
        if (trim($conditions['notice_start']) != '') {
            $stmt->and_where_open()
                ->where('tn.notice_start', '>=', $conditions['notice_start'])
                ->or_where('tn.notice_end', '>=', $conditions['notice_start'])
                ->and_where_close();
        }
        // 通知終了日
        if (trim($conditions['notice_end']) != '') {
            $stmt->and_where_open()
                ->where('tn.notice_start', '<=', $conditions['notice_end'])
                ->or_where('tn.notice_end', '<=', $conditions['notice_end'])
                ->and_where_close();
        }
        
        // 検索実行
        if ($is_count) {
            // 件数取得
            $res = $stmt->execute($db)->as_array();
            return $res[0]['count'];

        } else {
            // データ取得
            return $stmt->order_by('tn.notice_number', 'ASC')
            ->limit($limit)
            ->offset($offset)
            ->execute($db)
            ->as_array();
        }

    }

}