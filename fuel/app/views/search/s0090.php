<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'searchForm', 'name' => 'searchForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('select_record', null);?>
        <?php echo Asset::js('search/s0090.js');?>
        <script>
            var processing_msg1 = '<?php echo str_replace('XXXXX','月極その他情報',Config::get('m_CW0015')); ?>';
        </script>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■検索条件</label>
        <table class="search-area" style="width: 660px">
            <tbody>
                <tr>
                    <td style="width: 200px; height: 30px;">月極その他番号</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('sales_correction_number', (!empty($data['sales_correction_number'])) ? $data['sales_correction_number'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'sales_correction_number', 'style' => 'width:120px;', 'min' => '0', 'max' => '9999999999', 'tabindex' => '1')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">課</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::select('division', $data['division'], $division_list,
                        array('class' => 'select-item', 'id' => 'division', 'style' => 'width: 150px', 'tabindex' => '2')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">売上確定</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::select('sales_status', $data['sales_status'], $sales_status_list,
                        array('class' => 'select-item', 'id' => 'sales_status', 'style' => 'width: 100px', 'tabindex' => '3')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">日付</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('sales_date_from', (!empty($data['sales_date_from'])) ? $data['sales_date_from'] : '', 
                        array('class' => 'input-text', 'type' => 'date', 'id' => 'sales_date_from', 'style' => 'width:160px;', 'tabindex' => '3')); ?>
                        &emsp;～&emsp;
                        <?php echo Form::input('sales_date_to', (!empty($data['sales_date_to'])) ? $data['sales_date_to'] : '', 
                        array('class' => 'input-text', 'type' => 'date', 'id' => 'sales_date_to', 'style' => 'width:160px;', 'tabindex' => '4')); ?>
                        <p class="error-message"><?php echo $error_message_sub; ?></p>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">売上区分</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::select('sales_category', $data['sales_category'], $sales_category_list,
                        array('class' => 'select-item', 'id' => 'sales_category', 'style' => 'width: 100px', 'tabindex' => '5')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">得意先</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('client_code', (!empty($data['client_code'])) ? $data['client_code'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'client_code', 'style' => 'width:100px;', 'min' => '0', 'max' => '99999', 'tabindex' => '6')); ?>
                        <input type="button" value="検索" tabindex="9" onclick="onClientSearch('<?php echo Uri::create('search/s0020'); ?>')"/>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">傭車先</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('carrier_code', (!empty($data['carrier_code'])) ? $data['carrier_code'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'carrier_code', 'style' => 'width:100px;', 'min' => '0', 'max' => '99999', 'tabindex' => '8')); ?>
                        <input type="button" value="検索" tabindex="11" onclick="onCarrierSearch('<?php echo Uri::create('search/s0030'); ?>')"/>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">車種</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::select('car_model', $data['car_model'], $car_model_list,
                        array('class' => 'select-item', 'id' => 'car_model', 'style' => 'width: 130px', 'tabindex' => '10')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">車番</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('car_code', (!empty($data['car_code'])) ? $data['car_code'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'car_code', 'style' => 'width:110px;', 'maxlength' => '4', 'tabindex' => '11')); ?>
                        <input type="button" value="検索" tabindex="16" onclick="onCarSearch('<?php echo Uri::create('search/s0050'); ?>')"/>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">運転手</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('driver_name', (!empty($data['driver_name'])) ? $data['driver_name'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'driver_name', 'style' => 'width:110px;', 'maxlength' => '6', 'tabindex' => '13')); ?>
                        <input type="button" value="検索" tabindex="18" onclick="onCustomerSearch('<?php echo Uri::create('search/s0010'); ?>')"/>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">配送区分</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::select('delivery_category', $data['delivery_category'], $delivery_category_list,
                        array('class' => 'select-item', 'id' => 'delivery_category', 'style' => 'width: 110px', 'tabindex' => '15')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">登録者</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::select('create_user', $data['create_user'], $create_user_list,
                        array('class' => 'select-item', 'id' => 'create_user', 'style' => 'width: 180px', 'tabindex' => '20')); ?></td>
                </tr>
            </tbody>
        </table>
        <div class="search-buttons">
            <?php echo Form::submit('search', '検索', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '900')); ?>
            <?php echo Form::submit('cancel', 'キャンセル', array('class' => 'buttonB', 'tabindex' => '901')); ?>
        </div>
        <?php echo Form::close(); ?>
        <br />
        <?php echo Form::open(array('id' => 'selectForm', 'name' => 'selectForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden('select_sales_correction_number', '');?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php if ($total > 0) : ?>
        <div style="width: 1480px;">
            <div class="content-row">
                検索結果：<?php echo $total; ?> 件
            </div>
            <!-- ここからPager -->
            <div class="content-row">
                <?php echo $pager; ?>
                <button type="button" onclick="onMultipleSelect()" class="buttonA">チェックしたものを選択</button>
			</div>
            <!-- ここまでPager -->
            <div class="table-wrap" style="clear: right">
                <table class="table-inq" style="width: 1480px;">
                    <tr>
                        <th rowspan="2" style="width: 120px;">選択</th>
                        <!-- <th rowspan="2">月極その他番号</th> -->
                        <th rowspan="2" style="width: 100px;">課</th>
                        <th rowspan="2" style="width: 60px;">売上<br>確定</th>
                        <th rowspan="2" style="width: 110px;">日付</th>
                        <th style="width: 150px;">得意先No</th>
                        <th style="width: 150px;">売上区分</th>
                        <th style="width: 100px;">売上</th>
                        <th style="width: 150px;">傭車先No</th>
                        <th style="width: 150px;">車種</th>
                        <th style="width: 100px;">運転手</th>
                        <th style="width: 110px;">稼働台数</th>
                        <th rowspan="2">備考</th>
                    </tr>
                    <tr>
                        <th colspan="2" style="width: 300px;">得意先</th>
                        <th>傭車費</th>
                        <th colspan="2" style="width: 300px;">傭車先</th>
                        <th>車番</th>
                        <th>配送区分</th>
                    </tr>
                    <?php if (!empty($list_data)) : ?>
                    <?php $i = 0; ?>
                      <?php foreach ($list_data as $key => $val) : ?>
                        <?php $i++; ?>
                        <tr>
                            <td rowspan="2" style="width: 60px; text-align: center;">
                                <?php echo Form::checkbox('select_'.$i, $val['sales_correction_number'], false, array('id' => 'form_select_'.$i, 'class' => 'text', 'style' => 'display:inline;')); ?>
                                <?php echo Form::label('', 'select_'.$i, array('style' => 'display:inline;padding-left: 1.0em;', 'onclick' => 'onCheckBox('.$i.', '.$val['sales_correction_number'].');')); ?>
                                &nbsp;
                                <button type="button" onclick="onSelect('<?php echo $val['sales_correction_number']; ?>')" class="buttonA">選択</button>
                            </td>
                            <!-- <td rowspan="2"><?php echo sprintf('%010d', $val['sales_correction_number']); ?></td> -->
                            <td rowspan="2" style="text-align: center;"><?php echo $val['division']; ?></td>
                            <td rowspan="2" style="text-align: center;"><?php echo $sales_status_list[$val['sales_status']]; ?></td>
                            <td rowspan="2"><?php $stack_date = new DateTime($val['sales_date']);echo $stack_date->format('Y/m/d'); ?></td>
                            <td><?php echo (!empty($val['client_code'])) ? sprintf('%05d', $val['client_code']):''; ?></td>
                            <td><?php echo ($val['sales_category_code'] == '99') ? $val['sales_category_value'] : $val['sales_category_name']; ?></td>
                            <td style="text-align: right;"><?php echo number_format($val['sales']); ?></td>
                            <td><?php echo (!empty($val['carrier_code'])) ? sprintf('%05d', $val['carrier_code']):''; ?></td>
                            <td><?php echo $val['car_model']; ?></td>
                            <td><?php echo $val['driver_name']; ?></td>
                            <td style="text-align: right;"><?php echo number_format($val['operation_count']); ?></td>
                            <td rowspan="2" style="white-space: normal;"><?php echo $val['remarks']; ?></td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo $val['client_name']; ?></td>
                            <td style="text-align: right;"><?php echo number_format($val['carrier_cost']); ?></td>
                            <td colspan="2"><?php echo $val['carrier_name']; ?></td>
                            <td><?php echo (!empty($val['car_code'])) ? sprintf('%04d', $val['car_code']):''; ?></td>
                            <td><?php echo $delivery_category_list[$val['delivery_category']]; ?></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php echo Form::hidden('record_count', $i);?>
                    <?php endif ; ?>
                </table>
            </div>
            <!-- ここからPager -->
            <div>
                <?php echo $pager; ?>
            </div>
            <!-- ここまでPager -->
        </div>
        <?php endif ; ?>
        <?php echo Form::close(); ?>
    </div>
</section>