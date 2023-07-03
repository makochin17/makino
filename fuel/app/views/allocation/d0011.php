<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'searchForm', 'name' => 'searchForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('processing_division', '');?>
        <?php echo Form::hidden('select_record', '');?>
        <?php echo Asset::js('allocation/d0011.js');?>
        <script>
            var list_count = <?php echo $list_count; ?>;
            var processing_msg1 = '<?php echo Config::get('m_DI0018'); ?>';
            var processing_msg2 = '<?php echo Config::get('m_DI0019'); ?>';
        </script>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■検索条件</label>
        <table class="search-area" style="width: 660px">
            <tbody>
                <tr>
                    <td style="width: 200px; height: 30px;">配車番号</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('dispatch_number', (!empty($data['dispatch_number'])) ? $data['dispatch_number'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'dispatch_number', 'style' => 'width:120px;', 'min' => '0', 'max' => '9999999999', 'tabindex' => '1')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">課</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::select('division', ($data['division'] != '') ? $data['division'] : $userinfo['division_code'], $division_list,
                        array('class' => 'select-item', 'id' => 'division', 'style' => 'width: 150px', 'tabindex' => '2')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">売上確定</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::select('sales_status', $data['sales_status'], $sales_status_list,
                        array('class' => 'select-item', 'id' => 'sales_status', 'style' => 'width: 100px', 'tabindex' => '3')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">積日</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('stack_date_from', (!empty($data['stack_date_from'])) ? $data['stack_date_from'] : '', 
                        array('class' => 'input-text', 'type' => 'date', 'id' => 'stack_date_from', 'style' => 'width:160px;', 'tabindex' => '4')); ?>
                        &emsp;～&emsp;
                        <?php echo Form::input('stack_date_to', (!empty($data['stack_date_to'])) ? $data['stack_date_to'] : '', 
                        array('class' => 'input-text', 'type' => 'date', 'id' => 'stack_date_to', 'style' => 'width:160px;', 'tabindex' => '5')); ?>
                        <p class="error-message"><?php echo $error_message_sub; ?></p>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">降日</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('drop_date_from', (!empty($data['drop_date_from'])) ? $data['drop_date_from'] : '', 
                        array('class' => 'input-text', 'type' => 'date', 'id' => 'drop_date_from', 'style' => 'width:160px;', 'tabindex' => '6')); ?>
                        &emsp;～&emsp;
                        <?php echo Form::input('drop_date_to', (!empty($data['drop_date_to'])) ? $data['drop_date_to'] : '', 
                        array('class' => 'input-text', 'type' => 'date', 'id' => 'drop_date_to', 'style' => 'width:160px;', 'tabindex' => '7')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">得意先</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('client_code', (!empty($data['client_code'])) ? $data['client_code'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'client_code', 'style' => 'width:100px;', 'min' => '0', 'max' => '99999', 'tabindex' => '8')); ?>
                        <input type="button" value="検索" tabindex="9" onclick="onClientSearch('<?php echo Uri::create('search/s0020'); ?>')"/>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">傭車先</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('carrier_code', (!empty($data['carrier_code'])) ? $data['carrier_code'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'carrier_code', 'style' => 'width:100px;', 'min' => '0', 'max' => '99999', 'tabindex' => '10')); ?>
                        <input type="button" value="検索" tabindex="11" onclick="onCarrierSearch('<?php echo Uri::create('search/s0030'); ?>')"/>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">商品</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::select('product', $data['product'], $product_list,
                        array('class' => 'select-item', 'id' => 'product', 'style' => 'width: 180px', 'tabindex' => '12')); ?>
                        <input type="button" value="検索" tabindex="13" onclick="onProductSearch('<?php echo Uri::create('search/s0060'); ?>')"/>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">車種</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::select('car_model', $data['car_model'], $car_model_list,
                        array('class' => 'select-item', 'id' => 'car_model', 'style' => 'width: 130px', 'tabindex' => '14')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">車番</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('car_number', (!empty($data['car_number'])) ? $data['car_number'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'car_number', 'style' => 'width:110px;', 'min' => '0', 'max' => '9999', 'tabindex' => '15')); ?>
                        <input type="button" value="検索" tabindex="16" onclick="onCarSearch('<?php echo Uri::create('search/s0050'); ?>')"/>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">運転手</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('driver_name', (!empty($data['driver_name'])) ? $data['driver_name'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'driver_name', 'style' => 'width:110px;', 'maxlength' => '6', 'tabindex' => '17')); ?>
                        <input type="button" value="検索" tabindex="18" onclick="onCustomerSearch('<?php echo Uri::create('search/s0010'); ?>')"/>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">配送区分</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::select('delivery_category', $data['delivery_category'], $delivery_category_list,
                        array('class' => 'select-item', 'id' => 'delivery_category', 'style' => 'width: 110px', 'tabindex' => '19')); ?></td>
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
            <?php echo Form::submit('search', '検索', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '100')); ?>
            <?php echo Form::submit('search_today', '本日分検索', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '101')); ?>
            <?php echo Form::submit('add', '新規登録', array('class' => 'buttonB', 'onclick' => 'onAdd(\''.Uri::create('allocation/d0010').'\')', 'tabindex' => '102')); ?>
        </div>
        <?php echo Form::close(); ?>
        <br />
        <?php echo Form::open(array('id' => 'selectForm', 'name' => 'selectForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden('processing_division', '');?>
        <?php echo Form::hidden('dispatch_number', '');?>
        <?php echo Form::hidden('select_record', '');?>
        <?php echo Form::hidden('list_count', $list_count);?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php if ($total > 0) : ?>
        <div style="width: 1650px;">
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
                <table class="table-inq" style="width: 1650px;">
                    <tr>
                        <th rowspan="2" style="width: 80px;">選択</th>
                        <!-- <th rowspan="2" style="width: 120px;">配車番号</th> -->
                        <th rowspan="2" style="width: 100px;">課</th>
                        <th rowspan="2" style="width: 60px;">売上<br>確定</th>
                        <th style="width: 110px;">積日</th>
                        <th style="width: 110px;">降日</th>
                        <th style="width: 150px;">得意先No</th>
                        <th style="width: 150px;">車種</th>
                        <th style="width: 100px;">請求売上</th>
                        <th style="width: 150px;">傭車先No</th>
                        <th style="width: 150px;">車番</th>
                        <th style="width: 170px;">運転手</th>
                        <th style="width: 150px;">電話番号</th>
                        <th>商品</th>
                        <!-- <th rowspan="2" style="width: 80px;">配送区分</th> -->
                    </tr>
                    <tr>
                        <th>積地</th>
                        <th>降地</th>
                        <th colspan="2">得意先</th>
                        <th>傭車支払</th>
                        <th colspan="2">傭車先</th>
                        <th>運行先</th>
                        <th colspan="2">社内向け備考</th>
                    </tr>
                    <?php if (!empty($list_data)) : ?>
                    <?php $i = 0; ?>
                      <?php foreach ($list_data as $key => $val) : ?>
                        <?php $i++; ?>
                        <tr>
                            <td style="width: 60px; text-align: center;">
                                <button type="button" onclick="onEdit('<?php echo Uri::create('allocation/d0012'); ?>', <?php echo $val['dispatch_number']; ?>)" class="buttonA" 
                                    <?php echo ($user_authority != '1' && $val['sales_status'] == '2') ? 'disabled' : ''; ?>><i class='fa fa-edit' style="font-size:14px;"></i> 編集</button>
                            </td>
                            <!-- <td rowspan="2">
                                <?php echo sprintf('%010d', $val['dispatch_number']); ?>
                            </td> -->
                            <?php echo Form::hidden('dispatch_number_'.$i, $val['dispatch_number']);?>
                            <td rowspan="2" style="text-align: center;"><?php echo $val['division']; ?></td>
                            <td rowspan="2" style="text-align: center;">
                                <?php echo Form::checkbox('sales_status_'.$i, 2, ($val['sales_status'] == '2') ? true : false, array('id' => 'form_sales_status_'.$i, 'class' => 'text', 'style' => 'display:inline;', ($user_authority != '1' && $val['sales_status'] == '2') ? 'disabled' : '')); ?>
                                <?php echo Form::label('', 'sales_status_'.$i, array('style' => 'display:inline;padding-left: 1.0em;')); ?>
                                <?php echo Form::hidden('old_sales_status_'.$i, $val['sales_status']);?>
                            </td>
                            <td><?php $stack_date = new DateTime($val['stack_date']);echo $stack_date->format('Y/m/d'); ?></td>
                            <td><?php $drop_date = new DateTime($val['drop_date']);echo $drop_date->format('Y/m/d'); ?></td>
                            <td><?php echo sprintf('%05d', $val['client_code']); ?></td>
                            <td><?php echo $val['car_model']; ?></td>
                            <td style="text-align: right;"><?php echo number_format($val['claim_sales']); ?></td>
                            <td><?php echo sprintf('%05d', $val['carrier_code']); ?></td>
                            <td><?php echo sprintf('%04d', $val['car_number']); ?></td>
                            <td><?php echo $val['driver_name']; ?></td>
                            <td><?php echo $val['phone_number']; ?></td>
                            <td><?php echo $val['product']; ?></td>
                            <!-- <td rowspan="2"><?php echo $delivery_category_list[$val['delivery_category']]; ?></td> -->
                        </tr>
                        <tr>
                            <td style="width: 60px; text-align: center;">
                                <button type="button" onclick="onDelete(<?php echo $val['dispatch_number']; ?>)" class="buttonA"
                                        <?php echo ($user_authority != '1' && $val['sales_status'] == '2') ? 'disabled' : ''; ?>><i class='fa fa-trash' style="font-size:15px;"></i> 削除</button>
                            </td>
                            <td><?php echo $val['stack_place']; ?></td>
                            <td><?php echo $val['drop_place']; ?></td>
                            <td colspan="2"><?php echo $val['client_name']; ?></td>
                            <td style="text-align: right;"><?php echo number_format($val['carrier_payment']); ?></td>
                            <td colspan="2"><?php echo $val['carrier_name']; ?></td>
                            <td><?php echo $val['destination']; ?></td>
                            <td colspan="2" style="white-space: normal;"><?php echo $val['in_house_remarks']; ?></td>
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