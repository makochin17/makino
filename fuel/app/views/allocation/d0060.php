<main class="l-main">
  <?php echo Form::open(array('id' => 'entryForm', 'name' => 'entryForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
  <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
  <?php echo Form::hidden('select_record', null);?>
  <?php echo Form::hidden('excel_dl', null, array('id' => 'excel_dl'));?>
  <script>
      var clear_msg     = '<?php echo Config::get('m_CI0005'); ?>';
      var error_msg1    = '<?php echo Config::get('m_MW0013'); ?>';
      var processing_msg1 = '<?php echo Config::get('m_DI0006'); ?>';
      var processing_msg2 = '<?php echo Config::get('m_DI0007'); ?>';
      var processing_msg3 = '<?php echo Config::get('m_DI0008'); ?>';
      var processing_msg4 = '<?php echo Config::get('m_MI0008'); ?>';
      var processing_msg5 = '<?php echo Config::get('m_MI0010'); ?>';
      var processing_msg6 = '<?php echo Config::get('m_DW0017'); ?>';
  </script>
  <p class="error-message-head"><?php echo $error_message; ?></p>
  <div class="content-row">
    <label>■検索条件</label>
  </div>
  <table class="search-area" style="width: 680px">
    <tbody>
      <tr>
        <td style="width: 200px; height: 30px;">
          売上補正番号
        </td>
        <td style="width: 480px; height: 30px;">
          <?php echo Form::input('sales_correction_number', (!empty($data['sales_correction_number'])) ? $data['sales_correction_number']:'', array('id' => 'sales_correction_number','class' => 'input-text','maxlength' => '20','tabindex' => '1')); ?>
        </td>
      </tr>
      <tr>
        <td style="width: 200px; height: 30px;">
          課
        </td>
        <td style="width: 480px; height: 30px;">
          <?php echo Form::select('division_code', $data['division_code'], $division_list, array('class' => 'select-item', 'id' => 'division_code', 'style' => 'width: 150px;', 'tabindex' => '2')); ?>
        </td>
      </tr>
      <tr>
        <td style="width: 200px; height: 30px;">
          日付
        </td>
        <td style="width: 480px; height: 30px;">
          <?php echo Form::input('from_sales_date', (!empty($data['from_sales_date'])) ? $data['from_sales_date']:'', array('type' => 'date', 'id' => 'from_sales_date','class' => 'input-date','tabindex' => '3')); ?>
          <label style="margin: 0 10px;">〜</label>
          <?php echo Form::input('to_sales_date', (!empty($data['to_sales_date'])) ? $data['to_sales_date']:'', array('type' => 'date', 'id' => 'to_sales_date','class' => 'input-date','tabindex' => '4')); ?>
          <p class="error-message"><?php echo $date_error_message; ?></p>
        </td>
      </tr>
      <tr>
        <td style="width: 200px; height: 30px;">
          売上区分
        </td>
        <td style="width: 480px; height: 30px;">
          <?php echo Form::select('sales_category_code', $data['sales_category_code'], $sales_category_list, array('class' => 'select-item', 'id' => 'sales_category_code', 'style' => 'width: 150px;', 'tabindex' => '5')); ?>
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
          車種
        </td>
        <td style="width: 480px; height: 30px;">
          <?php echo Form::select('car_model_code', $data['car_model_code'], $carmodel_list, array('id' => 'car_model_code', 'class' => 'select-item', 'style' => 'width: 150px;', 'tabindex' => '10')); ?>
        </td>
      </tr>
      <tr>
        <td style="width: 200px; height: 30px;">
          車番
        </td>
        <td style="width: 480px; height: 30px;">
          <?php echo Form::input('car_code', (!empty($data['car_code'])) ? sprintf('%04d', $data['car_code']):'', array('id' => 'car_code','class' => 'input-text', 'tabindex' => '11')); ?>
          <input type="button" name="s_carrier" value="検索" class='buttonA' tabindex="16" onclick="onCarSearch('<?php echo Uri::create('search/s0050'); ?>', 0)" />
        </td>
      </tr>
      <tr>
        <td style="width: 200px; height: 30px;">
          運転手
        </td>
        <td style="width: 480px; height: 30px;">
          <?php echo Form::input('driver_name', (!empty($data['driver_name'])) ? $data['driver_name']:'', array('id' => 'driver_name', 'class' => 'input-text', 'tabindex' => '12')); ?>
          <input type="button" name="s_driver" value="検索" class='buttonA' tabindex="12" onclick="onCustomerSearch('<?php echo Uri::create('search/s0010'); ?>', 0)" />
          <?php echo Form::hidden('member_code', $data['member_code'], array('id' => 'member_code'));?>
        </td>
      </tr>
      <tr>
        <td style="width: 200px; height: 30px;">
          売上確定
        </td>
        <td style="width: 480px; height: 30px;">
          <?php echo Form::select('sales_status', $data['sales_status'], $sales_status_list, array('class' => 'select-item', 'id' => 'sales_status', 'style' => 'width: 150px;', 'tabindex' => '13')); ?>
        </td>
      </tr>
      <tr>
        <td style="width: 200px; height: 30px;">
          配送区分
        </td>
        <td style="width: 480px; height: 30px;">
          <?php echo Form::select('delivery_category', $data['delivery_category'], $delivery_category_list, array('id' => 'delivery_category', 'class' => 'select-item', 'style' => 'width: 150px;', 'tabindex' => '14')); ?>
        </td>
      </tr>
    </tbody>
  </table>
  <div class="search-buttons">
    <?php echo Form::submit('search', '検索', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '900')); ?>
    <?php echo Form::submit('input_clear', '入力内容クリア', array('class' => 'buttonB', 'onclick' => 'return submitChkClear()', 'style' => 'margin-right: 20px;', 'tabindex' => '901')); ?>
    <?php echo Form::button('excel', 'エクセル出力', array('type' => 'button', 'id' => 'excel', 'class' => 'buttonB', 'tabindex' => '902')); ?>
  </div>
  <?php echo Form::close(); ?>

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
    <div class="table-wrap">
      <table class="table-inq">
        <thead>
          <tr>
            <th rowspan="2">売上補正番号</th>
            <th rowspan="2">課</th>
            <th rowspan="2">売上<br>確定</th>
            <th rowspan="2">日付</th>
            <th style="width: 150px;">得意先No</th>
            <th style="width: 150px;">売上区分</th>
            <th style="width: 150px;">傭車先No</th>
            <th style="width: 150px;">車種</th>
            <th style="width: 120px;">運転手</th>
            <th>稼働台数</th>
            <th style="width: 100px;">売上</th>
            <th style="width: 120px;">高速料金</th>
            <th rowspan="2" style="width: 200px;">備考</th>
          </tr>
          <tr>
            <th colspan="2" style="width: 300px;">得意先</th>
            <th colspan="2" style="width: 300px;">傭車先</th>
            <th>車番</th>
            <th>配送区分</th>
            <th>傭車費</th>
            <th>時間外</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($list_data) : ?>
            <?php foreach ($list_data as $key => $val) : ?>
              <tr>
                <td rowspan="2">
                  <?php echo sprintf('%010d', $val['sales_correction_number']); ?>
                </td>
                <td rowspan="2" style="text-align: center;">
                  <?php echo (isset($division_list[$val['division_code']]) && !empty($division_list[$val['division_code']])) ? $division_list[$val['division_code']]:''; ?>
                </td>
                <td rowspan="2" style="text-align: center;">
                  <?php echo ($val['sales_status'] == '1') ? '×':'○'; ?>
                </td>
                <td rowspan="2">
                  <?php echo (!empty($val['sales_date'])) ? date('Y年n月j日', strtotime($val['sales_date'])):''; ?>
                </td>
                <td>
                  <?php echo (!empty($val['client_code'])) ? sprintf('%05d', $val['client_code']):''; ?>
                </td>
                <td>
                  <?php echo (isset($sales_category_list[$val['sales_category_code']]) && !empty($sales_category_list[$val['sales_category_code']])) ? $sales_category_list[$val['sales_category_code']]:''; ?>
                </td>
                <td>
                  <?php echo (!empty($val['carrier_code'])) ? sprintf('%05d', $val['carrier_code']):''; ?>
                </td>
                <td>
                  <?php echo (isset($carmodel_list[$val['car_model_code']]) && !empty($carmodel_list[$val['car_model_code']])) ? $carmodel_list[$val['car_model_code']]:''; ?>
                </td>
                <td>
                  <?php echo (!empty($val['driver_name'])) ? $val['driver_name']:''; ?>
                </td>
                <td style="text-align: right;">
                  <?php echo (!empty($val['operation_count'])) ? number_format($val['operation_count']):'0'; ?>
                </td>
                <td style="text-align: right;">
                  <?php echo (!empty($val['sales'])) ? number_format($val['sales']):'0'; ?>
                </td>
                <td style="text-align: right;">
                  <?php echo (!empty($val['highway_fee'])) ? number_format($val['highway_fee']):'0'; ?>
                  <?php echo ($val['highway_fee_claim'] == '1') ? '×':'○'; ?>
                </td>
                <td rowspan="2" style="white-space: normal;">
                  <?php echo (!empty($val['remarks'])) ? $val['remarks']:''; ?>
                </td>
              </tr>
              <tr>
                <td colspan="2">
                  <?php echo $val['client_name']; ?>
                </td>
                <td colspan="2">
                  <?php echo $val['carrier_name']; ?>
                </td>
                <td>
                  <?php echo (!empty($val['car_code'])) ? sprintf('%04d', $val['car_code']):''; ?>
                </td>
                <td>
                  <?php echo (isset($delivery_category_list[$val['delivery_category']]) && !empty($delivery_category_list[$val['delivery_category']])) ? $delivery_category_list[$val['delivery_category']]:''; ?>
                </td>
                <td style="text-align: right;">
                  <?php echo (!empty($val['carrier_cost'])) ? number_format($val['carrier_cost']):'0'; ?>
                </td>
                <td style="text-align: right;">
                  <?php echo (!empty($val['overtime_fee'])) ? number_format($val['overtime_fee']):'0'; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="content-row">
    <div class="pagenation">
      <!-- ここからPager -->
      <?php echo $pager; ?>
      <!-- ここまでPager -->
    </div>
  </div>
</main>
