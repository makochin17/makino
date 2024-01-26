<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'searchForm', 'name' => 'searchForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('select_record', null);?>
        <?php echo Asset::js('search/s1040.js');?>
        <script>
            var processing_msg1 = '<?php echo str_replace('XXXXX','配車データ',Config::get('m_CW0015')); ?>';
        </script>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■検索条件</label>
        <table class="search-area" style="width: 660px">
            <tbody>
                <tr>
                  <td style="width: 200px; height: 30px;">
                    請求番号
                  </td>
                  <td style="width: 480px; height: 30px;">
                    <?php echo Form::input('bill_number', (!empty($data['bill_number'])) ? $data['bill_number']:'', array('id' => 'bill_number', 'type' => 'number', 'class' => 'input-text', 'min' => '0', 'max' => '9999999999', 'tabindex' => '1')); ?>
                  </td>
                </tr>
                <tr>
                  <td style="width: 200px; height: 30px;">
                    課<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
                  </td>
                  <td style="width: 480px; height: 30px;">
                    <?php echo Form::select('division_code', $data['division_code'], $division_list, array('class' => 'select-item', 'id' => 'division_code', 'style' => 'width: 150px;', 'tabindex' => '2')); ?>
                  </td>
                </tr>
                <tr>
                  <td style="width: 200px; height: 30px;">
                    売上確定<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
                  </td>
                  <td style="width: 480px; height: 30px;">
                    <?php echo Form::select('sales_status', $data['sales_status'], $sales_status_list, array('class' => 'select-item', 'id' => 'sales_status', 'style' => 'width: 150px;', 'tabindex' => '3')); ?>
                  </td>
                </tr>
                <tr>
                  <td style="width: 200px; height: 30px;">
                    配送区分<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
                  </td>
                  <td style="width: 480px; height: 30px;">
                    <?php echo Form::select('delivery_code', $data['delivery_code'], $delivery_category_list, array('class' => 'select-item', 'id' => 'delivery_code', 'style' => 'width: 150px;', 'tabindex' => '4')); ?>
                  </td>
                </tr>
                <tr>
                  <td style="width: 200px; height: 30px;">
                    地区<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
                  </td>
                  <td style="width: 480px; height: 30px;">
                    <?php echo Form::select('area_code', $data['area_code'], $area_list, array('class' => 'select-item', 'id' => 'area_code', 'style' => 'width: 150px;', 'tabindex' => '6')); ?>
                  </td>
                </tr>
                <tr>
                  <td style="width: 200px; height: 30px;">
                    運行日
                  </td>
                  <td style="width: 480px; height: 30px;">
                    <?php echo Form::input('from_destination_date', (!empty($data['from_destination_date'])) ? $data['from_destination_date']:'', array('type' => 'date', 'id' => 'from_destination_date','class' => 'input-date','tabindex' => '8')); ?>
                    <label style="margin: 0 10px;">〜</label>
                    <?php echo Form::input('to_destination_date', (!empty($data['to_destination_date'])) ? $data['to_destination_date']:'', array('type' => 'date', 'id' => 'to_destination_date','class' => 'input-date','tabindex' => '9')); ?>
                    <p class="error-message"><?php echo $date_error_message1; ?></p>
                    <p class="error-message"><?php echo $error_message_sub; ?></p>
                  </td>
                </tr>
                <tr>
                  <td style="width: 200px; height: 30px;">
                    運行先
                  </td>
                  <td style="width: 480px; height: 30px;">
                    <?php echo Form::input('destination', (!empty($data['destination'])) ? $data['destination']:'', array('class' => 'input-text', 'id' => 'destination', 'style' => 'width: 300px;', 'tabindex' => '12')); ?>
                  </td>
                </tr>
                <tr>
                  <td style="width: 200px; height: 30px;">
                    得意先
                  </td>
                  <td style="width: 480px; height: 30px;">
                    <?php echo Form::input('client_code', (!empty($data['client_code'])) ? $data['client_code']:'', array('id' => 'client_code', 'class' => 'input-text', 'type' => 'number', 'style' => 'width: 100px;', 'min' => '0', 'max' => '99999','tabindex' => '14')); ?>
                    <input type="button" name="s_client" value="検索" class='buttonA' tabindex="15" onclick="onClientSearch('<?php echo Uri::create('search/s0020'); ?>', 0)" />
                  </td>
                </tr>
                <tr>
                  <td style="width: 200px; height: 30px;">
                    傭車先
                  </td>
                  <td style="width: 480px; height: 30px;">
                    <?php echo Form::input('carrier_code', (!empty($data['carrier_code'])) ? $data['carrier_code']:'', array('id' => 'carrier_code', 'class' => 'input-text', 'type' => 'number', 'style' => 'width: 100px;', 'min' => '0', 'max' => '99999','tabindex' => '16')); ?>
                    <input type="button" name="s_carrier" value="検索" class='buttonA' tabindex="17" onclick="onCarrierSearch('<?php echo Uri::create('search/s0030'); ?>', 0)" />
                  </td>
                </tr>
                <tr>
                  <td style="width: 200px; height: 30px;">
                    商品名
                  </td>
                  <td style="width: 480px; height: 30px;">
                    <?php echo Form::input('product_name', (!empty($data['product_name'])) ? $data['product_name']:'', array('class' => 'input-text', 'id' => 'product_name', 'style' => 'width: 300px;', 'tabindex' => '18')); ?>
                  </td>
                </tr>
                <tr>
                  <td style="width: 200px; height: 30px;">
                    車種<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
                  </td>
                  <td style="width: 480px; height: 30px;">
                    <?php echo Form::select('car_model_code', $data['car_model_code'], $carmodel_list, array('class' => 'select-item', 'style' => 'width: 150px;', 'tabindex' => '19')); ?>
                  </td>
                </tr>
                <tr>
                  <td style="width: 200px; height: 30px;">
                    車両番号
                  </td>
                  <td style="width: 480px; height: 30px;">
                    <?php echo Form::input('car_code', (!empty($data['car_code'])) ? sprintf('%04d', $data['car_code']):'', array('id' => 'car_code', 'type' => 'number' ,'class' => 'input-text', 'min' => '0', 'max' => '9999', 'tabindex' => '20')); ?>
                    <input type="button" name="s_carrier" value="検索" class='buttonA' tabindex="21" onclick="onCarSearch('<?php echo Uri::create('search/s0050'); ?>', 0)" />
                  </td>
                </tr>
                <tr>
                  <td style="width: 200px; height: 30px;">
                    運転手
                  </td>
                  <td style="width: 480px; height: 30px;">
                    <?php echo Form::input('driver_name', (!empty($data['driver_name'])) ? $data['driver_name']:'', array('id' => 'driver_name', 'class' => 'input-text', 'tabindex' => '22')); ?>
                    <input type="button" name="s_driver" value="検索" class='buttonA' tabindex="23" onclick="onCustomerSearch('<?php echo Uri::create('search/s0010'); ?>', 0)" />
                  </td>
                </tr>
                <tr>
                  <td style="width: 200px; height: 30px;">登録者</td>
                  <td style="width: 460px; height: 30px;">
                    <?php echo Form::select('create_user', $data['create_user'], $create_user_list,
                      array('class' => 'select-item', 'id' => 'create_user', 'style' => 'width: 180px', 'tabindex' => '24')); ?></td>
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
        <?php echo Form::hidden('select_bill_number', '');?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php $i = 0; ?>
        <?php if ($total > 0) : ?>
        <div style="width: 1650px;">
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
                <table class="table-inq" style="width: 1620px;">
                    <tr>
                        <th rowspan="2" style="width: 120px;">選択</th>
                        <th rowspan="2" style="width: 100px;">課</th>
                        <th rowspan="2" style="width: 60px;">売上<br>確定</th>
                        <th rowspan="2" style="width: 90px;">地区</th>
                        <th style="width: 100px;">運行日付</th>
                        <th style="width: 90px;">得意先No</th>
                        <th style="width: 300px;">得意先名</th>
                        <th rowspan="2" style="width: 90px;">金額</th>
                        <th style="width: 90px;">単価</th>
                        <th style="width: 90px;">数量</th>
                        <th style="width: 70px;">単位</th>
                        <th style="width: 100px;">車両番号</th>
                        <th rowspan="2" style="width: 70px;">現場</th>
                        <th rowspan="2">備考</th>
                    </tr>
                    <tr>
                        <th>配送区分</th>
                        <th colspan="2">運行先</th>
                        <th colspan="3">商品名</th>
                        <th>運転手</th>
                    </tr>
                    <?php if (!empty($list_data)) : ?>
                      <?php foreach ($list_data as $key => $val) : ?>
                        <?php $i++; ?>
                        <tr>
                            <td rowspan="2" style="width: 60px; text-align: center;">
                                <?php echo Form::checkbox('select_'.$i, $val['bill_number'], false, array('id' => 'form_select_'.$i, 'class' => 'text', 'style' => 'display:inline;')); ?>
                                <?php echo Form::label('', 'select_'.$i, array('style' => 'display:inline;padding-left: 1.0em;', 'onclick' => 'onCheckBox('.$i.', '.$val['bill_number'].');')); ?>
                                &nbsp;
                                <button type="button" onclick="onSelect('<?php echo $val['bill_number']; ?>')" class="buttonA">選択</button>
                            </td>
                            <td rowspan="2" style="text-align: center;"><?php echo $val['division_name']; ?></td>
                            <td rowspan="2" style="text-align: center;"><?php echo (isset($sales_status_list[$val['sales_status']]) && !empty($sales_status_list[$val['sales_status']])) ? $sales_status_list[$val['sales_status']]:''; ?></td>
                            <td rowspan="2"><?php echo (isset($area_list[$val['area_code']]) && !empty($area_list[$val['area_code']])) ? $area_list[$val['area_code']]:''; ?></td>
                            <td style="font-size: 14px;"><?php $destination_date = new DateTime($val['destination_date']);echo !empty($val['destination_date']) ? $destination_date->format('Y/m/d'):''; ?></td>
                            <td><?php echo sprintf('%05d', $val['client_code']); ?></td>
                            <td><?php echo $val['client_name']; ?></td>
                            <td rowspan="2" style="text-align: right;"><?php echo number_format($val['price'], 0); ?></td>
                            <td style="text-align: right;"><?php echo number_format($val['unit_price'], 2); ?></td>
                            <td style="text-align: right;"><?php echo number_format($val['volume'], 2); ?></td>
                            <td><?php echo (isset($unit_list[$val['unit_code']]) && !empty($unit_list[$val['unit_code']])) ? $unit_list[$val['unit_code']]:''; ?></td>
                            <td><?php echo sprintf('%04d', $val['car_code']); ?></td>
                            <td rowspan="2" style="text-align: center;"><?php echo (isset($val['onsite_flag']) && $val['onsite_flag'] == '1') ? '〇':'×'; ?></td>
                            <td rowspan="2" style="font-size: 15px;"><?php echo $val['remarks']; ?></td>
                        </tr>
                        <tr>
                            <td><?php echo (isset($delivery_category_list[$val['delivery_code']]) && !empty($delivery_category_list[$val['delivery_code']])) ? $delivery_category_list[$val['delivery_code']]:''; ?></td>
                            <td colspan="2"><?php echo mb_substr($val['destination'], 0, 15); ?></td>
                            <td colspan="3"><?php echo mb_substr($val['product_name'], 0, 15); ?></td>
                            <td><?php echo $val['driver_name']; ?></td>
                        </tr>
                      <?php endforeach; ?>
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
        <?php echo Form::hidden('record_count', $i);?>
        <?php echo Form::close(); ?>
    </div>
</section>