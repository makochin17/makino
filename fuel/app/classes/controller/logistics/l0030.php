<?php
/**
 * 出庫指示書印刷
 */
use \Model\Init;
use \Model\Excel\Data;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\PagingConfig;
use \Model\Common\GenerateList;
use \Model\Logistics\L0030;

class Controller_Logistics_L0030 extends Controller_Rest {

    protected $format = 'xlsx';

    public function action_index() {

        $status             = false;
        $data               = array();
        $logistics_ids      = array();
        $select_id          = Input::param('select_id', '');

        // IDを配列化
        $logistics_ids = explode(',', $select_id);
        if (!is_array($logistics_ids)) {
            $logistics_ids[] = $logistics_ids;
        }

        // 対象の入庫データを取得
        if ($list = L0030::getLogisticsById($logistics_ids, L0030::$db)) {
            // データをテンプレートエクセルに記載
            L0030::setExcelData($list, L0030::$db);
            $status = true;
        }
        return $this->response($status);

    }

}
