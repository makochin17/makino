<?php
namespace Model\Summary;
use \Model\Common\GenerateList;
use \Model\Common\OpeLog;
use \Model\Summary\T0030;

ini_set("memory_limit", "1000M");

class T0031 extends \Model {

    public static $db       = 'ONISHI';
    
    /**
     * エクセル作成処理（日単位）
     */
    public static function createTsvDay() {
        $conditions = T0030::getConditions();
        $header = array('carrier_name' => '', 'column' => '');
        $body = array();
        
        //日見出し出力
        $col_num_list = array();
        $start = $conditions['start_date'];
        $end = $conditions['end_date'];
        $col_num = 2;
        for ($i = $start; $i <= $end; $i = date('Y-m-d', strtotime($i . '+1 day'))) {
            $header += array($col_num => date('Y年m月d日', strtotime($i)));
            $col_num_list += array($i => $col_num);
            $col_num++;
        }
        
        //売上区分リスト取得
        $sales_category_list = GenerateList::getSalesCategoryList(false, self::$db);
        
        //売上区分見出し出力
        foreach ($sales_category_list as $key => $value) {
            $header += array($col_num => $value);
            $col_num_list += array($key => $col_num);
            $col_num++;
        }
        
        //行テンプレート作成
        $body_tmp_base = array('carrier_name' => '', 'column' => '');
        foreach ($col_num_list as $key => $val) {
            $body_tmp_base += array($val => 0);
        }
        $body_tmp1 = $body_tmp_base;
        $body_tmp1['column'] = "請求売上";
        $body_tmp2 = $body_tmp_base;
        $body_tmp2['column'] = "庸車費用";
        $body_tmp3 = $body_tmp_base;
        $body_tmp3['column'] = "差益";
        $body_tmp4 = $body_tmp_base;
        $body_tmp4['column'] = "差益率";
        
        if ($conditions['summary_category'] == 1) {
            //チャーター便
            
            //配車集計データ取得
            $dispatch_list = T0030::getDispatchList($conditions);
            //売上補正集計データ取得
            $sales_correction_list = T0030::getSalesCorrectionList($conditions);

            //庸車先リスト取得
            $carrier_list = T0030::createCarrierList($dispatch_list, $sales_correction_list);

            //庸車先見出し出力
            $row_num_list = array();
            $row_num = 0;
            foreach ($carrier_list as $carrier) {
                $body_tmp1['carrier_name'] = $carrier['carrier_name'];
                $body_tmp2['carrier_name'] = $carrier['carrier_name'];
                $body_tmp3['carrier_name'] = $carrier['carrier_name'];
                $body_tmp4['carrier_name'] = $carrier['carrier_name'];

                $body[] = $body_tmp1;
                $body[] = $body_tmp2;
                $body[] = $body_tmp3;
                $body[] = $body_tmp4;

                $row_num_list += array($carrier['carrier_name']=>$row_num);
                $row_num += 4;
            }

            //配車集計データ出力
            foreach ($dispatch_list as $dispatch) {
                $col_num = $col_num_list[$dispatch['stack_date']];
                $row_num = $row_num_list[$dispatch['carrier_name']];

                //差益
                $margin = $dispatch['claim_sales'] - $dispatch['carrier_payment'];

                //差益率
                $margin_rate = 0;
                if ($dispatch['carrier_payment'] > 0) {
                    $margin_rate = round($margin / $dispatch['carrier_payment'] * 100, 1);
                }

                $body[$row_num][$col_num] = $dispatch['claim_sales'];
                $body[$row_num + 1][$col_num] = $dispatch['carrier_payment'];
                $body[$row_num + 2][$col_num] = $margin;
                $body[$row_num + 3][$col_num] = $margin_rate;

            }
        } elseif ($conditions['summary_category'] == 2) {
            //共配便
            
            //配車集計データ取得
            $dispatch_list = T0030::getDispatchShareList($conditions);
            //売上補正集計データ取得
            $sales_correction_list = T0030::getSalesCorrectionList($conditions);

            //庸車先リスト取得
            $carrier_list = T0030::createCarrierList($dispatch_list, $sales_correction_list);

            //庸車先見出し出力
            $row_num_list = array();
            $row_num = 0;
            foreach ($carrier_list as $carrier) {
                $body_tmp1['carrier_name'] = $carrier['carrier_name'];
                $body_tmp2['carrier_name'] = $carrier['carrier_name'];
                $body_tmp3['carrier_name'] = $carrier['carrier_name'];
                $body_tmp4['carrier_name'] = $carrier['carrier_name'];

                $body[] = $body_tmp1;
                $body[] = $body_tmp2;
                $body[] = $body_tmp3;
                $body[] = $body_tmp4;

                $row_num_list += array($carrier['carrier_name']=>$row_num);
                $row_num += 4;
            }

            //配車集計データ出力
            foreach ($dispatch_list as $dispatch) {
                $col_num = $col_num_list[$dispatch['destination_date']];
                $row_num = $row_num_list[$dispatch['carrier_name']];

                //差益
                $margin = $dispatch['claim_sales'] - $dispatch['carrier_payment'];

                //差益率
                $margin_rate = 0;
                if ($dispatch['carrier_payment'] > 0) {
                    $margin_rate = round($margin / $dispatch['carrier_payment'] * 100, 1);
                }

                $body[$row_num][$col_num] = $dispatch['claim_sales'];
                $body[$row_num + 1][$col_num] = $dispatch['carrier_payment'];
                $body[$row_num + 2][$col_num] = $margin;
                $body[$row_num + 3][$col_num] = $margin_rate;
            }
        }
        
        //売上補正集計データ出力
        foreach ($sales_correction_list as $sales_correction) {
            $col_num = $col_num_list[$sales_correction['sales_category_code']];
            $row_num = $row_num_list[$sales_correction['carrier_name']];
            
            //差益
            $margin = $sales_correction['sales'] - $sales_correction['carrier_cost'];
            
            //差益率
            $margin_rate = 0;
            if ($sales_correction['carrier_cost'] > 0) {
                $margin_rate = round($margin / $sales_correction['carrier_cost'] * 100, 1);
            }
            
            $body[$row_num][$col_num] = $sales_correction['sales'];
            $body[$row_num + 1][$col_num] = $sales_correction['carrier_cost'];
            $body[$row_num + 2][$col_num] = $margin;
            $body[$row_num + 3][$col_num] = $margin_rate;
            
        }
        
        try {
            \DB::start_transaction(self::$db);

            // 操作ログ出力
            $result = OpeLog::addOpeLog('TI0007', \Config::get('m_TI0007'), '', self::$db);
            if (!$result) {
                \Log::error(\Config::get('m_CE0007'));
            }
            
            \DB::commit_transaction(self::$db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            \DB::rollback_transaction(self::$db);
            \Log::error($e->getMessage());
        }
        
        //ファイル名設定
        $title = mb_convert_encoding(T0030::getExcelName(), 'SJIS', 'UTF-8');
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
     * エクセル作成処理（月単位）
     */
    public static function createTsvMonth() {
        $conditions = T0030::getConditions();
        $header = array('carrier_name' => '', 'category' => '', 'column' => '');
        $body = array();
        
        //日見出し出力
        $col_num_list = array();
        $start = date('Y-m', strtotime($conditions['start_date']));
        $end = date('Y-m', strtotime($conditions['end_date']));
        $col_num = 2;
        for ($i = $start; $i <= $end; $i = date('Y-m', strtotime($i . '+1 month'))) {
            $header += array($col_num => date('Y年m月', strtotime($i.'-01')));
            $col_num_list += array($i => $col_num);
            $col_num++;
        }
        
        //行テンプレート作成
        $body_tmp_base = array('carrier_name' => '', 'category' => '', 'column' => '');
        foreach ($col_num_list as $key => $val) {
            $body_tmp_base += array($val => 0);
        }
        $body_tmp_base1 = $body_tmp_base;
        $body_tmp_base2 = $body_tmp_base;
        $body_tmp_base3 = $body_tmp_base;
        $body_tmp_base4 = $body_tmp_base;
        $body_tmp_index = 0;
        
        if ($conditions['summary_category'] == 1) {
            $body_tmp_base1['column'] = "請求売上";
            $body_tmp_base2['column'] = "庸車費用";
            $body_tmp_base3['column'] = "差益";
            $body_tmp_base4['column'] = "差益率";

            $body_tmp_base1['category'] = "チャーター便";
            $body_tmp_base2['category'] = "チャーター便";
            $body_tmp_base3['category'] = "チャーター便";
            $body_tmp_base4['category'] = "チャーター便";

            $body_tmps = array();
            $body_tmps[] = $body_tmp_base1;
            $body_tmps[] = $body_tmp_base2;
            $body_tmps[] = $body_tmp_base3;
            $body_tmps[] = $body_tmp_base4;

            $body_tmp_list = array("チャーター便" => $body_tmp_index);
        } elseif ($conditions['summary_category'] == 2) {
            $body_tmp_base1['column'] = "請求売上";
            $body_tmp_base2['column'] = "庸車費用";
            $body_tmp_base3['column'] = "差益";
            $body_tmp_base4['column'] = "差益率";

            $body_tmp_base1['category'] = "共配便";
            $body_tmp_base2['category'] = "共配便";
            $body_tmp_base3['category'] = "共配便";
            $body_tmp_base4['category'] = "共配便";

            $body_tmps = array();
            $body_tmps[] = $body_tmp_base1;
            $body_tmps[] = $body_tmp_base2;
            $body_tmps[] = $body_tmp_base3;
            $body_tmps[] = $body_tmp_base4;

            $body_tmp_list = array("共配便" => $body_tmp_index);
        }
        
        //売上区分リスト取得
        $sales_category_list = GenerateList::getSalesCategoryList(false, self::$db);
        
        //売上区分見出し出力
        foreach ($sales_category_list as $key => $value) {
            $body_tmp_base1['category'] = $value;
            $body_tmp_base2['category'] = $value;
            $body_tmp_base3['category'] = $value;
            $body_tmp_base4['category'] = $value;

            $body_tmps[] = $body_tmp_base1;
            $body_tmps[] = $body_tmp_base2;
            $body_tmps[] = $body_tmp_base3;
            $body_tmps[] = $body_tmp_base4;
            
            $body_tmp_index += 4;
            $body_tmp_list += array($key => $body_tmp_index);
        }
        
        if ($conditions['summary_category'] == 1) {
            //チャーター便
            
            //配車集計データ取得
            $dispatch_list = T0030::getDispatchList($conditions);
            //売上補正集計データ取得
            $sales_correction_list = T0030::getSalesCorrectionList($conditions);

            //庸車先リスト取得
            $carrier_list = T0030::createCarrierList($dispatch_list, $sales_correction_list);

            //庸車先見出し出力
            $row_num_list = array();
            $row_num = 0;
            foreach ($carrier_list as $carrier) {
                $row_num_list += array($carrier['carrier_name']=>$row_num);

                foreach ($body_tmps as $body_tmp) {
                    $body_tmp['carrier_name'] = $carrier['carrier_name'];
                    $body[] = $body_tmp;

                    $row_num++;
                }
            }

            //配車集計データ出力
            foreach ($dispatch_list as $dispatch) {
                $col_num = $col_num_list[$dispatch['stack_date']];
                $row_num = $row_num_list[$dispatch['carrier_name']];

                //差益
                $margin = $dispatch['claim_sales'] - $dispatch['carrier_payment'];

                //差益率
                $margin_rate = 0;
                if ($dispatch['carrier_payment'] > 0) {
                    $margin_rate = round($margin / $dispatch['carrier_payment'] * 100, 1);
                }

                $body[$row_num][$col_num] = $dispatch['claim_sales'];
                $body[$row_num + 1][$col_num] = $dispatch['carrier_payment'];
                $body[$row_num + 2][$col_num] = $margin;
                $body[$row_num + 3][$col_num] = $margin_rate;

            }
        } elseif ($conditions['summary_category'] == 2) {
            //共配便
            
            //配車集計データ取得
            $dispatch_list = T0030::getDispatchShareList($conditions);
            //売上補正集計データ取得
            $sales_correction_list = T0030::getSalesCorrectionList($conditions);

            //庸車先リスト取得
            $carrier_list = T0030::createCarrierList($dispatch_list, $sales_correction_list);

            //庸車先見出し出力
            $row_num_list = array();
            $row_num = 0;
            foreach ($carrier_list as $carrier) {
                $row_num_list += array($carrier['carrier_name']=>$row_num);

                foreach ($body_tmps as $body_tmp) {
                    $body_tmp['carrier_name'] = $carrier['carrier_name'];
                    $body[] = $body_tmp;

                    $row_num++;
                }
            }

            //配車集計データ出力
            foreach ($dispatch_list as $dispatch) {
                $col_num = $col_num_list[$dispatch['destination_date']];
                $row_num = $row_num_list[$dispatch['carrier_name']];

                //差益
                $margin = $dispatch['claim_sales'] - $dispatch['carrier_payment'];

                //差益率
                $margin_rate = 0;
                if ($dispatch['carrier_payment'] > 0) {
                    $margin_rate = round($margin / $dispatch['carrier_payment'] * 100, 1);
                }

                $body[$row_num][$col_num] = $dispatch['claim_sales'];
                $body[$row_num + 1][$col_num] = $dispatch['carrier_payment'];
                $body[$row_num + 2][$col_num] = $margin;
                $body[$row_num + 3][$col_num] = $margin_rate;

            }
        }
        
        //売上補正集計データ出力
        foreach ($sales_correction_list as $sales_correction) {
            $col_num = $col_num_list[$sales_correction['sales_date']];
            $shift_num = $body_tmp_list[$sales_correction['sales_category_code']];
            $row_num = (int)$row_num_list[$sales_correction['carrier_name']] + (int)$shift_num;
            
            //差益
            $margin = $sales_correction['sales'] - $sales_correction['carrier_cost'];
            
            //差益率
            $margin_rate = 0;
            if ($sales_correction['carrier_cost'] > 0) {
                $margin_rate = round($margin / $sales_correction['carrier_cost'] * 100, 1);
            }
            
            $body[$row_num][$col_num] = $sales_correction['sales'];
            $body[$row_num + 1][$col_num] = $sales_correction['carrier_cost'];
            $body[$row_num + 2][$col_num] = $margin;
            $body[$row_num + 3][$col_num] = $margin_rate;
            
        }

        try {
            \DB::start_transaction(self::$db);

            // 操作ログ出力
            $result = OpeLog::addOpeLog('TI0008', \Config::get('m_TI0008'), '', self::$db);
            if (!$result) {
                \Log::error(\Config::get('m_CE0007'));
            }
            
            \DB::commit_transaction(self::$db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            \DB::rollback_transaction(self::$db);
            \Log::error($e->getMessage());
        }
        
        //ファイル名設定
        $title = mb_convert_encoding(T0030::getExcelName(), 'SJIS', 'UTF-8');
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
     * エクセル作成処理（年単位）
     */
    public static function createTsvYear() {
        $conditions = T0030::getConditions();
        $header = array('carrier_name' => '', 'category' => '', 'column' => '');
        $body = array();
        
        //日見出し出力
        $col_num_list = array();
        $start = date('Y', strtotime($conditions['start_date']));
        $end = date('Y', strtotime($conditions['end_date']));
        $col_num = 2;
        for ($i = $start; $i <= $end; $i = date('Y', strtotime($i.'-01-01 +1 year'))) {
            $header += array($col_num => date('Y年', strtotime($i.'-01-01')));
            $col_num_list += array($i => $col_num);
            $col_num++;
        }
        
        //行テンプレート作成
        $body_tmp_base = array('carrier_name' => '', 'category' => '', 'column' => '');
        foreach ($col_num_list as $key => $val) {
            $body_tmp_base += array($val => 0);
        }
        $body_tmp_base1 = $body_tmp_base;
        $body_tmp_base2 = $body_tmp_base;
        $body_tmp_base3 = $body_tmp_base;
        $body_tmp_base4 = $body_tmp_base;
        $body_tmp_index = 0;
        
        if ($conditions['summary_category'] == 1) {
            $body_tmp_base1['column'] = "請求売上";
            $body_tmp_base2['column'] = "庸車費用";
            $body_tmp_base3['column'] = "差益";
            $body_tmp_base4['column'] = "差益率";

            $body_tmp_base1['category'] = "チャーター便";
            $body_tmp_base2['category'] = "チャーター便";
            $body_tmp_base3['category'] = "チャーター便";
            $body_tmp_base4['category'] = "チャーター便";

            $body_tmps = array();
            $body_tmps[] = $body_tmp_base1;
            $body_tmps[] = $body_tmp_base2;
            $body_tmps[] = $body_tmp_base3;
            $body_tmps[] = $body_tmp_base4;

            $body_tmp_list = array("チャーター便" => $body_tmp_index);
        } elseif ($conditions['summary_category'] == 2) {
            $body_tmp_base1['column'] = "請求売上";
            $body_tmp_base2['column'] = "庸車費用";
            $body_tmp_base3['column'] = "差益";
            $body_tmp_base4['column'] = "差益率";

            $body_tmp_base1['category'] = "共配便";
            $body_tmp_base2['category'] = "共配便";
            $body_tmp_base3['category'] = "共配便";
            $body_tmp_base4['category'] = "共配便";

            $body_tmps = array();
            $body_tmps[] = $body_tmp_base1;
            $body_tmps[] = $body_tmp_base2;
            $body_tmps[] = $body_tmp_base3;
            $body_tmps[] = $body_tmp_base4;

            $body_tmp_list = array("共配便" => $body_tmp_index);
        }
        
        //売上区分リスト取得
        $sales_category_list = GenerateList::getSalesCategoryList(false, self::$db);
        
        //売上区分見出し出力
        foreach ($sales_category_list as $key => $value) {
            $body_tmp_base1['category'] = $value;
            $body_tmp_base2['category'] = $value;
            $body_tmp_base3['category'] = $value;
            $body_tmp_base4['category'] = $value;

            $body_tmps[] = $body_tmp_base1;
            $body_tmps[] = $body_tmp_base2;
            $body_tmps[] = $body_tmp_base3;
            $body_tmps[] = $body_tmp_base4;
            
            $body_tmp_index += 4;
            $body_tmp_list += array($key => $body_tmp_index);
        }
        
        if ($conditions['summary_category'] == 1) {
            //チャーター便
            
            //配車集計データ取得
            $dispatch_list = T0030::getDispatchList($conditions);
            //売上補正集計データ取得
            $sales_correction_list = T0030::getSalesCorrectionList($conditions);

            //庸車先リスト取得
            $carrier_list = T0030::createCarrierList($dispatch_list, $sales_correction_list);

            //庸車先見出し出力
            $row_num_list = array();
            $row_num = 0;
            foreach ($carrier_list as $carrier) {
                $row_num_list += array($carrier['carrier_name']=>$row_num);

                foreach ($body_tmps as $body_tmp) {
                    $body_tmp['carrier_name'] = $carrier['carrier_name'];
                    $body[] = $body_tmp;

                    $row_num++;
                }
            }

            //配車集計データ出力
            foreach ($dispatch_list as $dispatch) {
                $col_num = $col_num_list[$dispatch['stack_date']];
                $row_num = $row_num_list[$dispatch['carrier_name']];

                //差益
                $margin = $dispatch['claim_sales'] - $dispatch['carrier_payment'];

                //差益率
                $margin_rate = 0;
                if ($dispatch['carrier_payment'] > 0) {
                    $margin_rate = round($margin / $dispatch['carrier_payment'] * 100, 1);
                }

                $body[$row_num][$col_num] = $dispatch['claim_sales'];
                $body[$row_num + 1][$col_num] = $dispatch['carrier_payment'];
                $body[$row_num + 2][$col_num] = $margin;
                $body[$row_num + 3][$col_num] = $margin_rate;

            }
        } elseif ($conditions['summary_category'] == 2) {
            //共配便
            
            //配車集計データ取得
            $dispatch_list = T0030::getDispatchShareList($conditions);
            //売上補正集計データ取得
            $sales_correction_list = T0030::getSalesCorrectionList($conditions);

            //庸車先リスト取得
            $carrier_list = T0030::createCarrierList($dispatch_list, $sales_correction_list);

            //庸車先見出し出力
            $row_num_list = array();
            $row_num = 0;
            foreach ($carrier_list as $carrier) {
                $row_num_list += array($carrier['carrier_name']=>$row_num);

                foreach ($body_tmps as $body_tmp) {
                    $body_tmp['carrier_name'] = $carrier['carrier_name'];
                    $body[] = $body_tmp;

                    $row_num++;
                }
            }

            //配車集計データ出力
            foreach ($dispatch_list as $dispatch) {
                $col_num = $col_num_list[$dispatch['destination_date']];
                $row_num = $row_num_list[$dispatch['carrier_name']];

                //差益
                $margin = $dispatch['claim_sales'] - $dispatch['carrier_payment'];

                //差益率
                $margin_rate = 0;
                if ($dispatch['carrier_payment'] > 0) {
                    $margin_rate = round($margin / $dispatch['carrier_payment'] * 100, 1);
                }

                $body[$row_num][$col_num] = $dispatch['claim_sales'];
                $body[$row_num + 1][$col_num] = $dispatch['carrier_payment'];
                $body[$row_num + 2][$col_num] = $margin;
                $body[$row_num + 3][$col_num] = $margin_rate;
            }
        }
        
        //売上補正集計データ出力
        foreach ($sales_correction_list as $sales_correction) {
            $col_num = $col_num_list[$sales_correction['sales_date']];
            $shift_num = $body_tmp_list[$sales_correction['sales_category_code']];
            $row_num = (int)$row_num_list[$sales_correction['carrier_name']] + (int)$shift_num;
            
            //差益
            $margin = $sales_correction['sales'] - $sales_correction['carrier_cost'];
            
            //差益率
            $margin_rate = 0;
            if ($sales_correction['carrier_cost'] > 0) {
                $margin_rate = round($margin / $sales_correction['carrier_cost'] * 100, 1);
            }
            
            $body[$row_num][$col_num] = $sales_correction['sales'];
            $body[$row_num + 1][$col_num] = $sales_correction['carrier_cost'];
            $body[$row_num + 2][$col_num] = $margin;
            $body[$row_num + 3][$col_num] = $margin_rate;
            
        }
        
        try {
            \DB::start_transaction(self::$db);

            // 操作ログ出力
            $result = OpeLog::addOpeLog('TI0009', \Config::get('m_TI0009'), '', self::$db);
            if (!$result) {
                \Log::error(\Config::get('m_CE0009'));
            }
            
            \DB::commit_transaction(self::$db);
        } catch (Exception $e) {
            // トランザクションクエリをロールバックする
            \DB::rollback_transaction(self::$db);
            \Log::error($e->getMessage());
        }
        
        //ファイル名設定
        $title = mb_convert_encoding(T0030::getExcelName(), 'SJIS', 'UTF-8');
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
    
}