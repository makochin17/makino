<?php
namespace Model\Common;
use \Model\Common\GenerateList;

class ClosingDate extends \Model {

    /**
     * 締日を成形して返す
     * $closing_date 締日（1～99）
     * $closing_date_1 締日1
     * $closing_date_2 締日2
     * $closing_date_3 締日3
     */
    public static function genClosingDate($closing_date, $closing_date_1, $closing_date_2, $closing_date_3) {
        $result = "";
        
        // 締日リスト取得
        $closing_date_list = GenerateList::getClosingDateList(true);
        
        switch ($closing_date){
            case "51": //月2回
                $result = $closing_date_1."、".$closing_date_list[$closing_date_2];
                break;
            case "52": //月3回
                $result = $closing_date_1."、".$closing_date_2."、".$closing_date_list[$closing_date_3];
                break;
            case "50": //都度
            default: //月1回
                $result = $closing_date_list[$closing_date];
                break;
        }
        
        return $result;
    }
    
    /**
     * 集計開始日と集計終了日の計算
     * $target_date 対象月
     * $target_day 対象日付
     * $closing_date 締日情報を含む連想配列
     */
    public static function getFromToDate($target_date, $target_day, $closing_date) {
        $result = array("from_date" => "", "to_date" => "");
        
        $from_date = date('Y-m-d',  strtotime($target_date.'-01'.' -1 months'));
        $to_date = date('Y-m-d',  strtotime($target_date.'-01'));
        
        if (empty($target_day)) {
            $target_day = date('Y-m-d',  strtotime($target_date.'-01'));
        }
        
        switch ($closing_date['closing_date']){
            case "51": //月2回
                if (date('d',  strtotime($target_day)) <= $closing_date['closing_date_1']) {
                    $result['from_date'] = self::getClosingDate($from_date, $closing_date['closing_date_2']);
                    $result['to_date'] = self::getClosingDate($to_date, $closing_date['closing_date_1']);
                } elseif (date('d',  strtotime($target_day)) > $closing_date['closing_date_2']) {
                    $result['from_date'] = self::getClosingDate($to_date, $closing_date['closing_date_2']);
                    $result['to_date'] = self::getClosingDate(date('Y-m-d',  strtotime($to_date.' +1 months')), $closing_date['closing_date_1']);
                } else {
                    $result['from_date'] = self::getClosingDate($to_date, $closing_date['closing_date_1']);
                    $result['to_date'] = self::getClosingDate($to_date, $closing_date['closing_date_2']);
                }
                break;
            case "52": //月3回
                if (date('d',  strtotime($target_day)) <= $closing_date['closing_date_1']) {
                    $result['from_date'] = self::getClosingDate($from_date, $closing_date['closing_date_3']);
                    $result['to_date'] = self::getClosingDate($to_date, $closing_date['closing_date_1']);
                } elseif (date('d',  strtotime($target_day)) <= $closing_date['closing_date_2']) {
                    $result['from_date'] = self::getClosingDate($to_date, $closing_date['closing_date_1']);
                    $result['to_date'] = self::getClosingDate($to_date, $closing_date['closing_date_2']);
                } elseif (date('d',  strtotime($target_day)) > $closing_date['closing_date_3']) {
                    $result['from_date'] = self::getClosingDate($to_date, $closing_date['closing_date_3']);
                    $result['to_date'] = self::getClosingDate(date('Y-m-d',  strtotime($to_date.' +1 months')), $closing_date['closing_date_1']);
                } else {
                    $result['from_date'] = self::getClosingDate($to_date, $closing_date['closing_date_2']);
                    $result['to_date'] = self::getClosingDate($to_date, $closing_date['closing_date_3']);
                }
                break;
            case "50": //都度
                $result['from_date'] = date('Y-m-d',  strtotime($target_day));
                $result['to_date'] = date('Y-m-d',  strtotime($target_day.' +1 day'));
                break;
            case "99": //月1回（月末締）
            default: //月1回
                $result['from_date'] = self::getClosingDate($from_date, $closing_date['closing_date']);
                $result['to_date'] = self::getClosingDate($to_date, $closing_date['closing_date']);
                break;
        }
        
        return $result;
    }
    
    /**
     * 締日（1～28、99）から締日付を返す
     * $target_date 対象月
     * $closing_date 締日（1～28、99）
     */
    private static function getClosingDate($target_date, $closing_date) {
        if ($closing_date == "99") {
            return date('Y-m-d',  strtotime($target_date.' +1 months'));
        } else {
            return date('Y-m-d',  strtotime($target_date.' +'.$closing_date.' day'));
        }
    }
}