<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'searchForm', 'name' => 'searchForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('processing_division', '');?>
        <?php echo Form::hidden('select_record', '');?>
        <?php echo Form::hidden('stock_number', $stock_number);?>
        <?php echo Asset::js('stock/d1120.js');?>
        <script>
            var list_count = <?php echo $list_count; ?>;
            var processing_msg1 = '<?php echo Config::get('m_DI0040'); ?>';
            var processing_msg2 = '<?php echo Config::get('m_DI0019'); ?>';
        </script>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■在庫情報</label>
        <div class="table-wrap" style="clear: right">
            <table class="table-inq" style="width: 1000px;">
                <tr>
                    <th style="width: 100px;">課</th>
                    <th style="width: 90px;">得意先No</th>
                    <th style="width: 300px;">得意先名</th>
                    <th style="width: 300px;">商品名</th>
                    <th style="width: 100px;">在庫数量</th>
                    <th style="width: 70px;">単位</th>
                </tr>
                <tr>
                    <td style="text-align: center;"><?php echo $stock['division_name']; ?></td>
                    <td style="text-align: center;"><?php echo sprintf('%05d', $stock['client_code']); ?></td>
                    <td><?php echo $stock['client_name']; ?></td>
                    <td><?php echo mb_substr($stock['product_name'], 0, 15); ?></td>
                    <td style="text-align: right;"><?php echo floatval(number_format($stock['total_volume'], 6)); ?></td>
                    <td><?php echo (isset($unit_list[$stock['unit_code']]) && !empty($unit_list[$stock['unit_code']])) ? $unit_list[$stock['unit_code']]:''; ?></td>
                </tr>
            </table>
        </div>
        <label>■検索条件</label>
        <table class="search-area" style="width: 660px">
            <tbody>
                <tr>
                    <td style="width: 200px; height: 30px;">入出庫番号</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('stock_change_number', (!empty($data['stock_change_number'])) ? $data['stock_change_number'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'stock_change_number', 'style' => 'width:120px;', 'min' => '0', 'max' => '9999999999', 'tabindex' => '1')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">売上確定</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::select('sales_status', $data['sales_status'], $sales_status_list,
                        array('class' => 'select-item', 'id' => 'sales_status', 'style' => 'width: 100px', 'tabindex' => '3')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">区分<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::select('stock_change_code', $data['stock_change_code'], $stock_change_list, array('class' => 'select-item', 'id' => 'stock_change_code', 'style' => 'width: 150px;', 'tabindex' => '8')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">運行日</td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::input('from_destination_date', (!empty($data['from_destination_date'])) ? $data['from_destination_date']:'', array('type' => 'date', 'id' => 'from_destination_date','class' => 'input-date','tabindex' => '9')); ?>
                        <label style="margin: 0 10px;">〜</label>
                        <?php echo Form::input('to_destination_date', (!empty($data['to_destination_date'])) ? $data['to_destination_date']:'', array('type' => 'date', 'id' => 'to_destination_date','class' => 'input-date','tabindex' => '10')); ?>
                        <p class="error-message"><?php echo $error_message_sub; ?></p>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">運行先</td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::input('destination', (!empty($data['destination'])) ? $data['destination']:'', array('class' => 'input-text', 'id' => 'destination', 'style' => 'width: 300px;', 'maxlength' => '30', 'tabindex' => '11')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">登録者</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::select('create_user', $data['create_user'], $create_user_list,
                        array('class' => 'select-item', 'id' => 'create_user', 'style' => 'width: 180px', 'tabindex' => '20')); ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="search-buttons">
            <?php echo Form::submit('search', '検索', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '100')); ?>
            <?php echo Form::submit('search_today', '本日分検索', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '101')); ?>
            <?php echo Form::submit('add', '新規登録', array('class' => 'buttonB', 'onclick' => 'onAdd(\''.Uri::create('stock/d1121').'\')', 'style' => 'margin-right: 20px;', 'tabindex' => '102')); ?>
            <?php echo Form::submit('back', '在庫検索に戻る', array('class' => 'buttonB', 'onclick' => 'onBack(\''.Uri::create('stock/d1110').'\')', 'tabindex' => '103')); ?>
        </div>
        <?php echo Form::close(); ?>
        <br />
        <?php echo Form::open(array('id' => 'selectForm', 'name' => 'selectForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden('processing_division', '');?>
        <?php echo Form::hidden('stock_number', $stock_number);?>
        <?php echo Form::hidden('stock_change_number', '');?>
        <?php echo Form::hidden('select_record', '');?>
        <?php echo Form::hidden('list_count', $list_count);?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php if ($total > 0) : ?>
        <div style="width: 1300px;">
            <div class="content-row" style="float: right">
                検索結果：<?php echo $total; ?> 件
            </div>
            <div class="content-row">
                売上確定の操作
            </div>
            <!-- ここからPager -->
            <div style="float: right">
                <?php echo $pager; ?>
            </div>
            <div class="content-row">
                <button type="button" onclick="onSalesUpdate()" class="buttonA">　更新　</button>
                <button type="button" onclick="allChecked()" class="buttonA">全て選択</button>
				<button type="button" onclick="allUncheck()" class="buttonA">全て解除</button>
			</div>
            <!-- ここまでPager -->
            <div class="table-wrap" style="clear: right">
                <table class="table-inq" style="width: 1300px;">
                    <tr>
                        <th style="width: 80px;">選択</th>
                        <th style="width: 60px;">売上<br>確定</th>
                        <th style="width: 100px;">運行日</th>
                        <th style="width: 90px;">区分</th>
                        <th style="width: 200px;">運行先</th>
                        <th style="width: 80px;">数量</th>
                        <th style="width: 70px;">単位</th>
                        <th style="width: 90px;">料金</th>
                        <th style="width: 200px;">備考</th>
                    </tr>
                    <?php if (!empty($list_data)) : ?>
                    <?php $i = 0; ?>
                      <?php foreach ($list_data as $key => $val) : ?>
                        <?php $i++; ?>
                        <tr>
                            <?php echo Form::hidden('stock_change_number_'.$i, $val['stock_change_number']);?>
                            <td style="width: 60px; text-align: center;">
                                <button type="button" onclick="onEdit('<?php echo Uri::create('stock/d1122'); ?>', <?php echo $val['stock_change_number']; ?>)" class="buttonA"
                                        <?php echo ($user_authority != '1' && $val['sales_status'] == '2') ? 'disabled' : ''; ?>><i class='fa fa-edit' style="font-size:14px;"></i> 編集</button>
                            </td>
                            <td rowspan="2" style="text-align: center;">
                                <?php echo Form::checkbox('sales_status_'.$i, 2, ($val['sales_status'] == '2') ? true : false, array('id' => 'form_sales_status_'.$i, 'class' => 'text', 'style' => 'display:inline;', ($user_authority != '1' && $val['sales_status'] == '2') ? 'disabled' : '')); ?>
                                <?php echo Form::label('', 'sales_status_'.$i, array('style' => 'display:inline;padding-left: 1.0em;')); ?>
                                <?php echo Form::hidden('old_sales_status_'.$i, $val['sales_status']);?>
                            </td>
                            <td rowspan="2"><?php $destination_date = new DateTime($val['destination_date']);echo !empty($val['destination_date']) ? $destination_date->format('Y/m/d'):''; ?></td>
                            <td rowspan="2" style="text-align: center;"><?php echo (isset($stock_change_list[$val['stock_change_code']]) && !empty($stock_change_list[$val['stock_change_code']])) ? $stock_change_list[$val['stock_change_code']]:''; ?></td>
                            <td rowspan="2"><?php echo mb_substr($val['destination'], 0, 15); ?></td>
                            <td rowspan="2" style="text-align: right;"><?php echo floatval(number_format($val['volume'], 6)); ?></td>
                            <td rowspan="2"><?php echo (isset($unit_list[$val['unit_code']]) && !empty($unit_list[$val['unit_code']])) ? $unit_list[$val['unit_code']]:''; ?></td>
                            <td rowspan="2" style="text-align: right;"><?php echo number_format($val['fee']); ?></td>
                            <td rowspan="2"><?php echo $val['remarks']; ?></td>
                        </tr>
                        <tr>
                            <td style="width: 60px; text-align: center;">
                                <button type="button" onclick="onDelete(<?php echo $val['stock_change_number']; ?>)" class="buttonA"
                                        <?php echo ($user_authority != '1' && $val['sales_status'] == '2') ? 'disabled' : ''; ?>><i class='fa fa-trash' style="font-size:15px;"></i> 削除</button>
                            </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif ; ?>
                </table>
            </div>
            <!-- ここからPager -->
            <div style="float: right">
                <?php echo $pager; ?>
            </div>
            <!-- ここまでPager -->
        </div>
        <?php endif ; ?>
        <?php echo Form::close(); ?>
    </div>
</section>