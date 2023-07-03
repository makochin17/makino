<main class="l-main">
  <?php echo Form::open(array('id' => 'entryForm', 'name' => 'entryForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
  <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
  <?php echo Form::hidden('carrying_url', $carrying_url, array('id' => 'carrying_url'));?>
  <?php echo Form::hidden('select_record', null);?>
  <?php echo Form::hidden('excel_dl', null, array('id' => 'excel_dl'));?>
  <?php echo Form::hidden('output_dl', null, array('id' => 'output_dl'));?>
  <script>
      var processing_division_list    = '<?php echo json_encode($processing_division_list); ?>';
      var division_list               = '<?php echo json_encode($division_list); ?>';
      var product_list                = '<?php echo json_encode($product_list); ?>';
      var car_model_list              = '<?php echo json_encode($carmodel_list); ?>';
      var delivery_category_list      = '<?php echo json_encode($delivery_category_list); ?>';
      var tax_category_list           = '<?php echo json_encode($tax_category_list); ?>';

      var clear_msg                   = '<?php echo Config::get('m_CI0005'); ?>';
      var error_msg1                  = '<?php echo Config::get('m_MW0013'); ?>';
      var processing_msg1             = '<?php echo Config::get('m_DI0006'); ?>';
      var processing_msg2             = '<?php echo Config::get('m_DI0007'); ?>';
      var processing_msg3             = '<?php echo Config::get('m_DI0008'); ?>';
      var processing_msg4             = '<?php echo Config::get('m_MI0008'); ?>';
      var processing_msg5             = '<?php echo Config::get('m_MI0010'); ?>';
      var processing_msg6             = '<?php echo Config::get('m_DW0017'); ?>';
  </script>
    <p class="error-message-head"><?php echo $error_message; ?></p>
    <div class="content-row">
      <label>■検索条件</label>
    </div>
    <table class="search-area" style="width: 680px">
      <tbody>
        <tr>
          <td style="width: 200px; height: 30px;">
            課<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
          </td>
          <td style="width: 480px; height: 30px;">
            <?php echo Form::select('division_code', $data['division_code'], $division_list, array('class' => 'select-item', 'id' => 'division_code', 'style' => 'width: 150px;', 'tabindex' => '1')); ?>
            <p class="error-message"><?php echo $division_error_msg; ?></p>
          </td>
        </tr>
        <tr>
          <td style="width: 200px; height: 30px;">
            積日
          </td>
          <td style="width: 480px; height: 30px;">
            <?php echo Form::input('from_stack_date', (!empty($data['from_stack_date'])) ? $data['from_stack_date']:'', array('type' => 'date', 'id' => 'from_stack_date','class' => 'input-date','tabindex' => '2')); ?>
            <label style="margin: 0 10px;">〜</label>
            <?php echo Form::input('to_stack_date', (!empty($data['to_stack_date'])) ? $data['to_stack_date']:'', array('type' => 'date', 'id' => 'to_stack_date','class' => 'input-date','tabindex' => '3')); ?>
            <p class="error-message"><?php echo $date_error_message1; ?></p>
          </td>
        </tr>
        <tr>
          <td style="width: 200px; height: 30px;">
            降日
          </td>
          <td style="width: 480px; height: 30px;">
            <?php echo Form::input('from_drop_date', (!empty($data['from_drop_date'])) ? $data['from_drop_date']:'', array('type' => 'date', 'id' => 'from_drop_date','class' => 'input-date','tabindex' => '4')); ?>
            <label style="margin: 0 10px;">〜</label>
            <?php echo Form::input('to_drop_date', (!empty($data['to_drop_date'])) ? $data['to_drop_date']:'', array('type' => 'date', 'id' => 'to_drop_date','class' => 'input-date','tabindex' => '5')); ?>
            <p class="error-message"><?php echo $date_error_message2; ?></p>
          </td>
        </tr>
        <tr>
          <td style="width: 200px; height: 30px;">
            得意先
          </td>
          <td style="width: 480px; height: 30px;">
            <?php echo Form::input('client_code', (!empty($data['client_code'])) ? $data['client_code']:'', array('id' => 'client_code','class' => 'input-text', 'type' => 'number','style' => 'width: 100px;', 'min' => '0', 'max' => '99999','tabindex' => '6')); ?>
            <input type="button" name="s_client" value="検索" class='buttonA' tabindex="7" onclick="onClientSearch('<?php echo Uri::create('search/s0020'); ?>', 0)" />
            <?php echo Form::hidden('client_name', $data['client_name'], array('id' => 'client_name'));?>
          </td>
        </tr>
        <tr>
          <td style="width: 200px; height: 30px;">
            傭車先
          </td>
          <td style="width: 480px; height: 30px;">
            <?php echo Form::input('carrier_code', (!empty($data['carrier_code'])) ? $data['carrier_code']:'', array('id' => 'carrier_code','class' => 'input-text', 'type' => 'number','style' => 'width: 100px;', 'min' => '0', 'max' => '99999','tabindex' => '8')); ?>
            <input type="button" name="s_carrier" value="検索" class='buttonA' tabindex="9" onclick="onCarrierSearch('<?php echo Uri::create('search/s0030'); ?>', 0)" />
            <?php echo Form::hidden('carrier_name', $data['carrier_name'], array('id' => 'carrier_name'));?>
          </td>
        </tr>
        <tr>
          <td style="width: 200px; height: 30px;">
            商品
          </td>
          <td style="width: 480px; height: 30px;">
            <?php echo Form::select('product_code', $data['product_code'], $product_list, array('class' => 'select-item', 'id' => 'product_code', 'style' => 'width: 150px;', 'tabindex' => '10')); ?>
            <input type="button" name="s_product" value="検索" class='buttonA' tabindex="11" onclick="onProductSearch('<?php echo Uri::create('search/s0060'); ?>', 0)" />
            <?php echo Form::hidden('product_name', (!empty($data['product_name'])) ? $data['product_name']:'', array('id' => 'product_name')); ?>
          </td>
        </tr>
        <tr>
          <td style="width: 200px; height: 30px;">
            売上確定<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
          </td>
          <td style="width: 480px; height: 30px;">
            <?php echo Form::select('sales_status', $data['sales_status'], $sales_status_list, array('class' => 'select-item', 'id' => 'sales_status', 'style' => 'width: 150px;', 'tabindex' => '12')); ?>
          </td>
        </tr>
        <tr>
          <td style="width: 200px; height: 30px;">
            配車番号
          </td>
          <td style="width: 480px; height: 30px;">
            <?php echo Form::input('dispatch_number', (!empty($data['dispatch_number'])) ? $data['dispatch_number']:'', array('id' => 'dispatch_number','class' => 'input-text', 'tabindex' => '13')); ?>
          </td>
        </tr>
        <tr>
          <td style="width: 200px; height: 30px;">
            車種<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
          </td>
          <td style="width: 480px; height: 30px;">
            <?php echo Form::select('car_model_code', $data['car_model_code'], $carmodel_list, array('class' => 'select-item', 'style' => 'width: 150px;', 'tabindex' => '14')); ?>
          </td>
        </tr>
        <tr>
          <td style="width: 200px; height: 30px;">
            車番
          </td>
          <td style="width: 480px; height: 30px;">
            <?php echo Form::input('car_code', (!empty($data['car_code'])) ? sprintf('%04d', $data['car_code']):'', array('id' => 'car_code','class' => 'input-text', 'tabindex' => '15')); ?>
            <input type="button" name="s_carrier" value="検索" class='buttonA' tabindex="16" onclick="onCarSearch('<?php echo Uri::create('search/s0050'); ?>', 0)" />
          </td>
        </tr>
        <tr>
          <td style="width: 200px; height: 30px;">
            運転手
          </td>
          <td style="width: 480px; height: 30px;">
            <?php echo Form::input('driver_name', (!empty($data['driver_name'])) ? $data['driver_name']:'', array('id' => 'driver_name', 'class' => 'input-text', 'tabindex' => '17')); ?>
            <input type="button" name="s_driver" value="検索" class='buttonA' tabindex="18" onclick="onCustomerSearch('<?php echo Uri::create('search/s0010'); ?>', 0)" />
            <?php echo Form::hidden('member_code', $data['member_code']);?>
          </td>
        </tr>
        <tr>
          <td style="width: 200px; height: 30px;">
            配送区分<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
          </td>
          <td style="width: 480px; height: 30px;">
            <?php echo Form::select('delivery_category', $data['delivery_category'], $delivery_category_list, array('class' => 'select-item', 'style' => 'width: 150px;', 'tabindex' => '13')); ?>
          </td>
        </tr>
      </tbody>
    </table>
    <div class="search-buttons">
      <?php echo Form::submit('search', '検索', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '900')); ?>
      <?php echo Form::submit('input_clear', '入力内容クリア', array('class' => 'buttonB', 'onclick' => 'return submitChkClear()', 'style' => 'margin-right: 20px;', 'tabindex' => '901')); ?>
      <?php echo Form::button('output', '配車表出力', array('type' => 'button', 'id' => 'output', 'class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '902')); ?>
      <?php echo Form::button('excel', 'エクセル出力', array('type' => 'button', 'id' => 'excel', 'class' => 'buttonB', 'tabindex' => '903')); ?>
    </div>
  <?php echo Form::close(); ?>

  <form action="" class="formBody">
    <div class="content-row">
      <label>検索結果：<?php echo $total; ?> 件</label>
    </div>
    <div class="content-row">
      <div class="pagenation">
        <!-- ここからPager -->
        <?php echo $pager; ?>
        <!-- ここまでPager -->
      </div>
    </div>
    <div class="content-row">
      <div class="tab-contents">
        <input id="tab1" type="radio" name="tab-radio" checked="checked">
        <input id="tab2" type="radio" name="tab-radio">
        <input id="tab3" type="radio" name="tab-radio">
        <label class="tab-item" id="tab-item1" for="tab1">配車</label>
        <label class="tab-item" id="tab-item2" for="tab2">売上</label>
        <label class="tab-item" id="tab-item3" for="tab3">その他</label>
        <div class="tab-content" id="tab-content1">
          <div class="content-row">
            <div class="table-wrap">
              <table class="table-inq">
                <thead>
                  <tr>
                    <th rowspan="2" style="width: 120px;">配車番号</th>
                    <th rowspan="2">課</th>
                    <th rowspan="2">売上<br>確定</th>
                    <th>積日</th>
                    <th>降日</th>
                    <th style="width: 120px;">得意先No</th>
                    <th>車種</th>
                    <th>請求売上</th>
                    <th style="width: 150px;">傭車先No</th>
                    <th style="width: 150px;">車番</th>
                    <th>運転手</th>
                    <th>電話番号</th>
                    <th rowspan="2" style="width: 150px;">備考</th>
                  </tr>
                  <tr>
                    <th>積地</th>
                    <th>降地</th>
                    <th colspan="2" style="width: 300px;">得意先</th>
                    <th>傭車支払</th>
                    <th colspan="2" style="width: 300px;">傭車先</th>
                    <th>運行先</th>
                    <th>分載</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($list_data) : ?>
                    <?php foreach ($list_data as $key => $val) : ?>
                      <tr>
                        <td rowspan="2">
                          <?php echo sprintf('%010d', $val['dispatch_number']); ?>
                        </td>
                        <td rowspan="2" style="text-align: center;">
                          <?php echo (isset($division_list[$val['division_code']]) && !empty($division_list[$val['division_code']])) ? $division_list[$val['division_code']]:''; ?>
                        </td>
                        <td rowspan="2" style="text-align: center;">
                          <?php echo ($val['sales_status'] == '1') ? '×':'○'; ?>
                        </td>
                        <td>
                          <?php echo (!empty($val['stack_date'])) ? date('Y年n月j日', strtotime($val['stack_date'])):''; ?>
                        </td>
                        <td>
                          <?php echo (!empty($val['drop_date'])) ? date('Y年n月j日', strtotime($val['drop_date'])):''; ?>
                        </td>
                        <td>
                          <?php echo (!empty($val['client_code'])) ? sprintf('%05d', $val['client_code']):''; ?>
                        </td>
                        <td>
                          <?php echo (isset($carmodel_list[$val['car_model_code']]) && !empty($carmodel_list[$val['car_model_code']])) ? $carmodel_list[$val['car_model_code']]:''; ?>
                        </td>
                        <td style="text-align: right;">
                          <?php echo (!empty($val['claim_sales'])) ? number_format($val['claim_sales']):'0'; ?>
                        </td>
                        <td>
                          <?php echo (!empty($val['carrier_code'])) ? sprintf('%05d', $val['carrier_code']):''; ?>
                        </td>
                        <td>
                          <?php echo (!empty($val['car_code'])) ? sprintf('%04d', $val['car_code']):''; ?>
                        </td>
                        <td>
                          <?php echo (!empty($val['driver_name'])) ? $val['driver_name']:''; ?>
                        </td>
                        <td>
                          <?php echo (!empty($val['phone_number'])) ? $val['phone_number']:''; ?>
                        </td>
                        <td rowspan="2">
                          <?php echo (!empty($val['remarks'])) ? $val['remarks']:''; ?>
                        </td>
                      </tr>
                      <tr>
                        <td>
                          <?php echo $val['stack_place']; ?>
                        </td>
                        <td>
                          <?php echo $val['drop_place']; ?>
                        </td>
                        <td colspan="2">
                          <?php echo $val['client_name']; ?>
                        </td>
                        <td style="text-align: right;">
                          <?php echo (!empty($val['carrier_payment'])) ? number_format($val['carrier_payment']):'0'; ?>
                        </td>
                        <td colspan="2">
                          <?php echo $val['carrier_name']; ?>
                        </td>
                        <td>
                          <?php echo $val['destination']; ?>
                        </td>
                        <td>
                          <?php ($val['carrying_count'] > 0) ? $flg = '' : $flg = 'disabled'; ?>
                          <button type="button" id="carryingcharter_"<?php echo $key; ?> data-id="<?php echo $val['dispatch_number'];?>" class="buttonA" style="margin-right: 10px;" <?php echo $flg;?>>
                            照会
                          </button>
                          <label>
                            <?php echo ($val['carrying_count'] > 0) ? $val['carrying_count'].'台':'分載なし'; ?>
                          </label>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="tab-content" id="tab-content2">
          <div class="content-row">
            <div class="table-wrap">
              <table class="table-inq">
                <thead>
                  <tr>
                    <th rowspan="2" style="width: 120px;">配車番号</th>
                    <th rowspan="2">課</th>
                    <th rowspan="2">売上<br>確定</th>
                    <th>積日</th>
                    <th>降日</th>
                    <th style="width: 120px;">得意先No</th>
                    <th>車種</th>
                    <th>請求売上</th>
                    <th rowspan="2" style="width: 180px;">商品</th>
                    <th rowspan="2" style="width: 120px;">配送区分</th>
                    <th rowspan="2" style="width: 120px;">税区分</th>
                    <th>請求高速料金</th>
                    <th rowspan="2">ドライバー<br>高速料金</th>
                  </tr>
                  <tr>
                    <th>積地</th>
                    <th>降地</th>
                    <th colspan="2" style="width: 300px;">得意先</th>
                    <th>傭車支払</th>
                    <th>傭車高速料金</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($list_data) : ?>
                    <?php foreach ($list_data as $key => $val) : ?>
                      <tr>
                        <td rowspan="2">
                          <?php echo sprintf('%010d', $val['dispatch_number']); ?>
                        </td>
                        <td rowspan="2" style="text-align: center;">
                          <?php echo (isset($division_list[$val['division_code']]) && !empty($division_list[$val['division_code']])) ? $division_list[$val['division_code']]:''; ?>
                        </td>
                        <td rowspan="2" style="text-align: center;">
                          <?php echo ($val['sales_status'] == '1') ? '×':'○'; ?>
                        </td>
                        <td>
                          <?php echo (!empty($val['stack_date'])) ? date('Y年n月j日', strtotime($val['stack_date'])):''; ?>
                        </td>
                        <td>
                          <?php echo (!empty($val['drop_date'])) ? date('Y年n月j日', strtotime($val['drop_date'])):''; ?>
                        </td>
                        <td>
                          <?php echo (!empty($val['client_code'])) ? sprintf('%05d', $val['client_code']):''; ?>
                        </td>
                        <td>
                          <?php echo (isset($carmodel_list[$val['car_model_code']]) && !empty($carmodel_list[$val['car_model_code']])) ? $carmodel_list[$val['car_model_code']]:''; ?>
                        </td>
                        <td style="text-align: right;">
                          <?php echo (!empty($val['claim_sales'])) ? number_format($val['claim_sales']):'0'; ?>
                        </td>
                        <td rowspan="2">
                          <?php echo (!empty($val['product_name'])) ? $val['product_name']:''; ?>
                        </td>
                        <td rowspan="2">
                          <?php echo (isset($delivery_category_list[$val['delivery_category']]) && !empty($delivery_category_list[$val['delivery_category']])) ? $delivery_category_list[$val['delivery_category']]:''; ?>
                        </td>
                        <td rowspan="2">
                          <?php echo (isset($tax_category_list[$val['tax_category']]) && !empty($tax_category_list[$val['tax_category']])) ? $tax_category_list[$val['tax_category']]:''; ?>
                        </td>
                        <td style="text-align: right;">
                          <?php echo (!empty($val['claim_highway_fee'])) ? number_format($val['claim_highway_fee']):'0'; ?>
                          <span style="margin-left: 10px;"><?php echo ($val['claim_highway_claim'] == '1') ? '×':'○'; ?></span>
                        </td>
                        <td rowspan="2" style="text-align: right;">
                          <?php echo (!empty($val['driver_highway_fee'])) ? number_format($val['driver_highway_fee']):'0'; ?>
                          <span style="margin-left: 10px;"><?php echo ($val['driver_highway_claim'] == '1') ? '×':'○'; ?></span>
                        </td>
                      </tr>
                      <tr>
                        <td>
                          <?php echo $val['stack_place']; ?>
                        </td>
                        <td>
                          <?php echo $val['drop_place']; ?>
                        </td>
                        <td colspan="2">
                          <?php echo $val['client_name']; ?>
                        </td>
                        <td style="text-align: right;">
                          <?php echo (!empty($val['carrier_payment'])) ? number_format($val['carrier_payment']):'0'; ?>
                        </td>
                        <td style="text-align: right;">
                          <?php echo (!empty($val['carrier_highway_fee'])) ? number_format($val['carrier_highway_fee']):'0'; ?>
                          <span style="margin-left: 10px;"><?php echo ($val['carrier_highway_claim'] == '1') ? '×':'○'; ?></span>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="tab-content" id="tab-content3">
          <div class="content-row">
            <div class="table-wrap">
              <table class="table-inq">
                <thead>
                  <tr>
                    <th rowspan="2" style="width: 120px;">配車番号</th>
                    <th rowspan="2">課</th>
                    <th rowspan="2">売上<br>確定</th>
                    <th>積日</th>
                    <th>降日</th>
                    <th style="width: 120px;">得意先No</th>
                    <th>車種</th>
                    <th>請求売上</th>
                    <th>手当</th>
                    <th>時間外</th>
                    <th>往復</th>
                    <th>受領書送付日</th>
                    <th rowspan="2" style="width: 180px;">社内向け備考</th>
                  </tr>
                  <tr>
                    <th>積地</th>
                    <th>降地</th>
                    <th colspan="2" style="width: 300px;">得意先</th>
                    <th>傭車支払</th>
                    <th>泊まり</th>
                    <th>連結・ラップ</th>
                    <th>降日計上</th>
                    <th>受領書受領日</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($list_data) : ?>
                    <?php foreach ($list_data as $key => $val) : ?>
                      <tr>
                        <td rowspan="2">
                          <?php echo sprintf('%010d', $val['dispatch_number']); ?>
                        </td>
                        <td rowspan="2" style="text-align: center;">
                          <?php echo (isset($division_list[$val['division_code']]) && !empty($division_list[$val['division_code']])) ? $division_list[$val['division_code']]:''; ?>
                        </td>
                        <td rowspan="2" style="text-align: center;">
                          <?php echo ($val['sales_status'] == '1') ? '×':'○'; ?>
                        </td>
                        <td>
                          <?php echo (!empty($val['stack_date'])) ? date('Y年n月j日', strtotime($val['stack_date'])):''; ?>
                        </td>
                        <td>
                          <?php echo (!empty($val['drop_date'])) ? date('Y年n月j日', strtotime($val['drop_date'])):''; ?>
                        </td>
                        <td>
                          <?php echo (!empty($val['client_code'])) ? sprintf('%05d', $val['client_code']):''; ?>
                        </td>
                        <td>
                          <?php echo (isset($carmodel_list[$val['car_model_code']]) && !empty($carmodel_list[$val['car_model_code']])) ? $carmodel_list[$val['car_model_code']]:''; ?>
                        </td>
                        <td style="text-align: right;">
                          <?php echo (!empty($val['claim_sales'])) ? number_format($val['claim_sales']):'0'; ?>
                        </td>
                        <td style="text-align: right;">
                          <?php echo (!empty($val['allowance'])) ? number_format($val['allowance']):'0'; ?>
                        </td>
                        <td style="text-align: right;">
                          <?php echo (!empty($val['overtime_fee'])) ? number_format($val['overtime_fee']):'0'; ?>
                        </td>
                        <td style="text-align: center;">
                          <?php echo ($val['round_trip'] == '1') ? '×':'○'; ?>
                        </td>
                        <td>
                          <?php echo (!empty($val['receipt_send_date'])) ? date('Y年n月j日', strtotime($val['receipt_send_date'])):''; ?>
                        </td>
                        <td rowspan="2" style="white-space: normal;">
                          <?php echo (!empty($val['in_house_remarks'])) ? $val['in_house_remarks']:''; ?>
                        </td>
                      </tr>
                      <tr>
                        <td>
                          <?php echo $val['stack_place']; ?>
                        </td>
                        <td>
                          <?php echo $val['drop_place']; ?>
                        </td>
                        <td colspan="2">
                          <?php echo $val['client_name']; ?>
                        </td>
                        <td style="text-align: right;">
                          <?php echo (!empty($val['carrier_payment'])) ? number_format($val['carrier_payment']):'0'; ?>
                        </td>
                        <td style="text-align: right;">
                          <?php echo (!empty($val['stay'])) ? number_format($val['stay']):'0'; ?>
                        </td>
                        <td style="text-align: right;">
                          <?php echo (!empty($val['linking_wrap'])) ? number_format($val['linking_wrap']):'0'; ?>
                        </td>
                        <td style="text-align: center;">
                          <?php echo ($val['drop_appropriation'] == '1') ? '×':'○'; ?>
                        </td>
                        <td>
                          <?php echo (!empty($val['receipt_receive_date'])) ? date('Y年n月j日', strtotime($val['receipt_receive_date'])):''; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="content-row">
      <div class="pagenation">
        <!-- ここからPager -->
        <?php echo $pager; ?>
        <!-- ここまでPager -->
      </div>
    </div>
  </form>
</main>

<section id="carrying_modal" class="modalArea">
  <div id="modalBg" class="modalBg"></div>
  <div class="modalWrapper">

    <header id="header" style="margin-top: 40px;">
      <h1 class="page-title">分載照会</h1>
    </header>

    <main class="l-main">
      <form action="" class="formBody">
        <div class="content-row">
          <label>■配車情報</label>
        </div>
        <div class="content-row" style="margin-bottom: 20px;">
          <div class="table-wrap">
            <table class="table-inq">
              <thead>
                <tr>
                  <th>積日</th>
                  <th>降日</th>
                  <th style="width: 120px;">得意先No</th>
                  <th>車種</th>
                  <th>請求売上</th>
                  <th style="width: 120px;">傭車先No</th>
                  <th style="width: 120px;">車番</th>
                  <th rowspan="2" style="width: 100px;">運転手</th>
                  <th rowspan="2" style="width: 180px;">商品</th>
                  <th>配送区分</th>
                  <th>請求高速料金</th>
                  <th rowspan="2">ドライバー<br>高速料金</th>
                </tr>
                <tr>
                  <th>積地</th>
                  <th>降地</th>
                  <th colspan="2" style="width: 300px;">得意先</th>
                  <th>傭車支払</th>
                  <th colspan="2" style="width: 300px;">傭車先</th>
                  <th>税区分</th>
                  <th>傭車高速料金</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <span id="dispatch_stack_date"></span>
                  </td>
                  <td>
                    <span id="dispatch_drop_date"></span>
                  </td>
                  <td>
                    <span id="dispatch_client_code"></span>
                  </td>
                  <td>
                    <span id="dispatch_car_model_code"></span>
                  </td>
                  <td style="text-align: right;">
                    <span id="dispatch_claim_sales"></span>
                  </td>
                  <td>
                    <span id="dispatch_carrier_code"></span>
                  </td>
                  <td>
                    <span id="dispatch_car_code"></span>
                  </td>
                  <td rowspan="2">
                    <span id="dispatch_driver_name"></span>
                  </td>
                  <td rowspan="2">
                    <span id="dispatch_product_name"></span>
                  </td>
                  <td>
                    <span id="dispatch_delivery_category"></span>
                  </td>
                  <td style="text-align: right;">
                    <span id="dispatch_claim_highway_fee"></span><span id="dispatch_claim_highway_claim"></span>
                  </td>
                  <td rowspan="2" style="text-align: right;">
                    <span id="dispatch_driver_highway_fee"></span><span id="dispatch_driver_highway_claim"></span>
                  </td>
                </tr>
                <tr>
                  <td>
                    <span id="dispatch_stack_place"></span>
                  </td>
                  <td>
                    <span id="dispatch_drop_place"></span>
                  </td>
                  <td colspan="2">
                    <span id="dispatch_client_name"></span>
                  </td>
                  <td style="text-align: right;">
                    <span id="dispatch_carrier_payment"></span>
                  </td>
                  <td colspan="2">
                    <span id="dispatch_carrier_name"></span>
                  </td>
                  <td>
                    <span id="dispatch_tax_category"></span>
                  </td>
                  <td style="text-align: right;">
                    <span id="dispatch_carrier_highway_fee"></span><span id="dispatch_carrier_highway_claim"></span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="content-row">
          <label>■分載情報</label>
        </div>
        <div class="content-row">
          <div class="table-wrap">
            <table class="table-inq">
              <thead>
                <tr>
                  <th>積日</th>
                  <th>降日</th>
                  <th style="width: 120px;">得意先No</th>
                  <th>車種</th>
                  <th>請求売上</th>
                  <th style="width: 120px;">傭車先No</th>
                  <th style="width: 130px;">車番</th>
                  <th style="width: 150px;">運転手</th>
                  <th>請求高速料金</th>
                  <th rowspan="2" style="width: 140px;">ドライバー<br>高速料金</th>
                </tr>
                <tr>
                  <th>積地</th>
                  <th>降地</th>
                  <th colspan="2" style="width: 300px;">得意先</th>
                  <th>傭車支払</th>
                  <th colspan="2" style="width: 300px;">傭車先</th>
                  <th style="width: 170px;">電話番号</th>
                  <th style="width: 170px;">傭車高速料金</th>
                </tr>
              </thead>
              <tbody id="carrying_area">
                <?php /* 動的にリストを生成するエリア */ ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="content-row">
          <button type="button" id="carrying_modal_close" class="buttonB">戻る</button>
        </div>
      </form>
    </main>

  </div>
</section>
