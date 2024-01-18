<?php
/**
 * 入庫印刷
 */
use \Model\Init;
use \Model\Excel\Data;
use \Model\AccessControl;
use \Model\Common\AuthConfig;
use \Model\Common\PagingConfig;
use \Model\Common\GenerateList;
use \Model\Logistics\L0020;

class Controller_Logistics_L0020 extends Controller_Rest {

    protected $format = 'xlsx';

    public function action_index() {

        $status             = false;
        $data               = array();
        $logistics_ids      = array();
        $print_status_id    = Input::param('print_status_id', '');

        // IDを配列化
        $logistics_ids = explode(',', $print_status_id);
        if (!is_array($logistics_ids)) {
            $logistics_ids[] = $logistics_ids;
        }

        // 対象の入庫データを取得
        if ($list = L0020::getLogisticsById($logistics_ids, L0020::$db)) {
            // データをテンプレートエクセルに記載
            L0020::setExcelData($list, L0020::$db);
            $status = true;
        }
        return $this->response($status);

    }

}
