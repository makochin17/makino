<?php
use \Model\Init;
use \Model\Excel\Data;
use \Model\Mainte\M0010\M0010;
use \Model\Summary\T0020;
use \Model\Summary\T0050;
use \Model\Allocation\D0041;

class Controller_File_Filedownload extends Controller_Rest {

    protected $format = 'csv';

    public function action_index() {

        $file_format    = Input::get('f', '');
        $type           = Input::param('type', '');
        $date           = (new DateTime())->format('Ymd');
        $title          = '大西運輸';
        $data           = array();

        if (!empty($file_format)) {
            $this->format = $file_format;
        }
        switch ($type) {
            case 'm0010':
                $title  = $title.'社員マスタ';
                $data[] = array_values(M0010::getCsvHeaders('csv'));
                break;
        }
        $file    = $title.'_'.$date.'.'.$this->format;

        if ($this->format == 'csv') {
            // CSVは配列のまま渡せばダウンロードされる
            $content = $data;
        } else {
            $content = Data::create($this->format, $title, $data);
        }
        // エクスポート
        $this->response->set_header('Content-Disposition', 'attachment; filename="'.$file.'"');
        // キャッシュをなしに
        $this->response->set_header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
        $this->response->set_header('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
        $this->response->set_header('Pragma', 'no-cache');

        return $this->response($content);

    }

}
