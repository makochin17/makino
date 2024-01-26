<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'searchForm', 'name' => 'searchForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.min.js"></script>
        <script src="https://unpkg.com/chartjs-plugin-colorschemes"></script>
        <script>
            var caption_list = JSON.parse('<?php echo json_encode($caption_list); ?>');
            var summary_data_dispatch = JSON.parse('<?php echo json_encode($summary_data_dispatch); ?>');
            var summary_data_sales_correction = JSON.parse('<?php echo json_encode($summary_data_sales_correction); ?>');
            var summary_data_dispatch_share = JSON.parse('<?php echo json_encode($summary_data_dispatch_share); ?>');
            var summary_data_stock = JSON.parse('<?php echo json_encode($summary_data_stock); ?>');
        </script>
        <?php echo Asset::js('summary/t0010.js');?>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■出力条件</label>
        <table class="search-area" style="width: 380px">
            <tbody>
                <tr>
                    <td style="width: 150px; height: 30px;">集計対象</td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::select('summary_category', $data['summary_category'], $summary_category_list,
                        array('class' => 'select-item', 'id' => 'summary_category', 'style' => 'width: 150px', 'tabindex' => '1')); ?></td>
                </tr>
                <tr>
                    <td style="width: 150px; height: 30px;">配送区分</td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::select('delivery_category', $data['delivery_category'], $delivery_category_list,
                        array('class' => 'select-item', 'id' => 'delivery_category', 'style' => 'width: 130px', 'tabindex' => '1')); ?></td>
                </tr>
                <tr>
                    <td style="width: 150px; height: 30px;">集計単位</td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::select('aggregation_unit_date', $data['aggregation_unit_date'], $aggregation_unit_date_list,
                        array('class' => 'select-item', 'id' => 'aggregation_unit_date', 'style' => 'width: 100px', 'tabindex' => '2', 'onchange' => 'change(this)')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 150px; height: 30px;">集計開始日</td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::select('start_year', $data['start_year'], $year_list,
                        array('class' => 'select-item', 'id' => 'start_year', 'style' => 'width: 100px', 'tabindex' => '3')); ?>
                        年
                        <?php echo Form::select('start_month', $data['start_month'], $month_list,
                        array('class' => 'select-item', 'id' => 'start_month', 'style' => 'width: 80px', 'tabindex' => '4')); ?>
                        月
                        <?php echo Form::select('start_day', $data['start_day'], $day_list,
                        array('class' => 'select-item', 'id' => 'start_day', 'style' => 'width: 80px', 'tabindex' => '5')); ?>
                        日
                    </td>
                </tr>
                <tr>
                    <td style="width: 150px; height: 30px;">集計終了日</td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::select('end_year', $data['end_year'], $year_list,
                        array('class' => 'select-item', 'id' => 'end_year', 'style' => 'width: 100px', 'tabindex' => '6')); ?>
                        年
                        <?php echo Form::select('end_month', $data['end_month'], $month_list,
                        array('class' => 'select-item', 'id' => 'end_month', 'style' => 'width: 80px', 'tabindex' => '7')); ?>
                        月
                        <?php echo Form::select('end_day', $data['end_day'], $day_list,
                        array('class' => 'select-item', 'id' => 'end_day', 'style' => 'width: 80px', 'tabindex' => '8')); ?>
                        日
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="search-buttons">
            <?php echo Form::submit('summary', '集計実行', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '900')); ?>
            <?php echo Form::submit('output', 'エクセル出力', array('class' => 'buttonB', 'tabindex' => '901')); ?>
        </div>
        <?php echo Form::close(); ?>
        <?php if ($graph_output){ ?>
        <?php if ($data['summary_category'] == 1){ ?>
            <div class="content-row">
                <br />
                <h3>■チャーター便</h3>
            </div>
            <div class="content-row">
                項目切替：
                <?php echo Form::select('d_graph_item', 1, $graph_item_list,
                array('class' => 'select-item', 'id' => 'd_graph_item', 'style' => 'width: 150px', 'onchange' => 'changeGraphItemD()')); ?>
            </div>
            <div class="content-row" style="width: 1500px">
                <canvas id="dispatch_chart" width="100" height="30"></canvas>
            </div>
            <div class="table-wrap" style="width: 1500px;overflow-x:auto;">
                <table class="table-mnt" id="d_table">
                    <tr>
                        <th style="width: 100px">日付</th>
                        <?php foreach ($caption_list as $caption) : ?>
                            <th style="width: 100px"><?php echo $caption; ?></th>
                        <?php endforeach; ?>
                    </tr>
                </table>
            </div>
        <?php } elseif ($data['summary_category'] == 2){ ?>
            <div class="content-row">
                <br />
                <h3>■共配便</h3>
            </div>
            <div class="content-row">
                項目切替：
                <?php echo Form::select('ds_graph_item', 1, $graph_item_list,
                array('class' => 'select-item', 'id' => 'ds_graph_item', 'style' => 'width: 150px', 'onchange' => 'changeGraphItemDS()')); ?>
            </div>
            <div class="content-row" style="width: 1500px">
                <canvas id="dispatch_share_chart" width="100" height="30"></canvas>
            </div>
            <div class="table-wrap" style="width: 1500px;overflow-x:auto;">
                <table class="table-mnt" id="ds_table">
                    <tr>
                        <th style="width: 100px">日付</th>
                        <?php foreach ($caption_list as $caption) : ?>
                            <th style="width: 100px"><?php echo $caption; ?></th>
                        <?php endforeach; ?>
                    </tr>
                </table>
            </div>
        <?php } elseif ($data['summary_category'] == 3){ ?>
            <div class="content-row">
                <br />
                <h3>■入出庫料・保管料</h3>
            </div>
            <div class="content-row">
                項目切替：
                <?php echo Form::select('sc_graph_item', 1, $graph_item_list_sc,
                array('class' => 'select-item', 'id' => 'sc_graph_item', 'style' => 'width: 150px', 'onchange' => 'changeGraphItemSC()')); ?>
            </div>
            <div class="content-row" style="width: 1500px">
                <canvas id="stock_chart" width="100" height="30"></canvas>
            </div>
            <div class="table-wrap" style="width: 1500px;overflow-x:auto;">
                <table class="table-mnt" id="sc_table">
                    <tr>
                        <th style="width: 100px">日付</th>
                        <?php foreach ($caption_list as $caption) : ?>
                            <th style="width: 100px"><?php echo $caption; ?></th>
                        <?php endforeach; ?>
                    </tr>
                </table>
            </div>
        <?php } ?>
        <?php if ($data['summary_category'] == 1 || $data['summary_category'] == 2){ ?>
            <div class="content-row">
                <br />
                <h3>■月極その他情報</h3>
            </div>
            <div class="content-row">
                項目切替：
                <?php echo Form::select('s_graph_item', 1, $graph_item_list,
                array('class' => 'select-item', 'id' => 's_graph_item', 'style' => 'width: 150px', 'onchange' => 'changeGraphItemS()')); ?>
                <?php echo Form::select('sales_category', 1, $sales_category_list,
                array('class' => 'select-item', 'id' => 'sales_category', 'style' => 'width: 150px', 'onchange' => 'changeGraphItemS()')); ?>
            </div>
            <div class="content-row" style="width: 1500px">
                <canvas id="sales_correction_chart" width="100" height="30"></canvas>
            </div>
            <div class="table-wrap" style="width: 1500px;overflow-x:auto;">
                <table class="table-mnt" id="s_table">
                    <tr>
                        <th style="width: 100px">日付</th>
                        <?php foreach ($caption_list as $caption) : ?>
                            <th style="width: 100px"><?php echo $caption; ?></th>
                        <?php endforeach; ?>
                    </tr>
                </table>
            </div>
        <?php } ?>
        <?php } ?>
    </div>
</section>