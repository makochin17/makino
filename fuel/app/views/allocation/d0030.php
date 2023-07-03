<main class="l-main">
  <?php echo Form::open(array('id' => 'entryForm', 'name' => 'entryForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
  <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
  <?php echo Form::hidden('list_url', $list_url);?>
  <?php echo Form::hidden('current_url', $current_url);?>
  <?php echo Form::hidden('select_record', null);?>
  <?php echo Form::hidden('list_no', null);?>
  <?php /* フォームデータ */ ?>
  <?php echo Form::hidden('processing_division', (!empty($data['processing_division'])) ? $data['processing_division']:'1', array('id' => 'hidden_processing_division'));?>
    <script>
        var clear_msg     = '<?php echo Config::get('m_CI0005'); ?>';
        var error_msg1    = '<?php echo Config::get('m_MW0013'); ?>';
        var processing_msg1 = '<?php echo Config::get('m_DI0006'); ?>';
        var processing_msg2 = '<?php echo Config::get('m_DI0007'); ?>';
        var processing_msg3 = '<?php echo Config::get('m_DI0008'); ?>';
        var processing_msg4 = '<?php echo Config::get('m_MI0008'); ?>';
        var processing_msg5 = '<?php echo Config::get('m_MI0010'); ?>';
        var processing_msg6 = '<?php echo Config::get('m_DW0017'); ?>';
        var redirect_flag               = '<?php echo $redirect_flag; ?>';
    </script>
    <p class="error-message-head"><?php echo $error_message; ?></p>
    <div class="content-row" style="margin-bottom: 20px;">
      <label class="item-name">課</label>
      <?php echo Form::select('division_code', (!empty($data['division_code'])) ? $data['division_code']:$userinfo['division_code'], $division_list,
          array('class' => 'select-item', 'id' => 'division_code', 'tabindex' => '2')); ?>

      <input type="button" name="search" value="月極その他情報引用" class='buttonB' tabindex="3" onclick="onSalesCorrectionSearch('<?php echo Uri::create('search/s0090'); ?>', 0)" style="margin-left: 60px;" />
      <?php echo Form::submit('input_clear', '入力内容クリア', array('class' => 'buttonB', 'onclick' => 'return submitChkClear()', 'style' => 'margin-left: 20px;', 'tabindex' => '4')); ?>
    </div>
		<div class="content-row">
      <div class="table-wrap">
  			<table class="table-mnt">
          <thead>
            <tr>
              <th rowspan="2">売上<br>確定</th>
              <th rowspan="2">日付<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
              <th>得意先No<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
              <th>売上区分<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
              <th style="width: 150px;">傭車先No<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
              <th style="width: 150px;">車種<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
              <th>運転手</th>
              <th>稼働台数</th>
              <th>売上<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
              <th>高速料金</th>
              <th rowspan="2">備考</th>
            </tr>
            <tr>
              <th colspan="2" style="width: 300px;">得意先</th>
              <th colspan="2" style="width: 300px;">傭車先</th>
              <th>車番</th>
              <th>配送区分<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
              <th>傭車費<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
              <th>時間外</th>
            </tr>
          </thead>
          <tbody>
            <?php for ($i = 0;$i < 5;$i++) : ?>
            <?php /* 上段 */ ?>
            <tr>
              <?php /* 売上補正テーブル番号 */ ?>
              <?php echo Form::hidden('list['.$i.'][sales_correction_number]', (!empty($data['list'][$i]['sales_correction_number'])) ? $data['list'][$i]['sales_correction_number']:'');?>
              <?php /* 売上確定 */ ?>
              <td rowspan="2" style="text-align: center;">
                <?php echo Form::checkbox('list['.$i.'][sales_status]', (!empty($data['list'][$i]['sales_status'])) ? $data['list'][$i]['sales_status']:'', ($data['list'][$i]['sales_status'] == '2') ? true:false, array('id' => 'form_sales_status_'.$i, 'class' => 'text', 'style' => 'display:inline;', 'tabindex' => '1')); ?>
                <?php echo Form::label('', 'sales_status_'.$i, array('style' => 'display:inline;padding-left: 1.2em;')); ?>
              </td>
              <?php /* 積日 */ ?>
              <td rowspan="2">
                <?php echo Form::input('list['.$i.'][sales_date]', (!empty($data['list'][$i]['sales_date'])) ? $data['list'][$i]['sales_date']:'', array('type' => 'date', 'id' => 'sales_date_'.$i,'class' => 'input-date','style' => 'width: 160px;','maxlength' => '20','tabindex' => '2')); ?>
              </td>
              <?php /* 得意先No */ ?>
              <td>
                <?php echo Form::input('list['.$i.'][client_code]', (!empty($data['list'][$i]['client_code'])) ? sprintf('%05d', $data['list'][$i]['client_code']):'', array('id' => 'client_code_'.$i, 'class' => 'input-text', 'style' => 'width:80px;','maxlength' => '5','tabindex' => '3')); ?>
                <input type="button" name="s_client" value="検索" class='buttonA' tabindex="3" onclick="onClientSearch('<?php echo Uri::create('search/s0020'); ?>', <?php echo $i; ?>)" />
              </td>
              <?php /* 売上区分 */ ?>
              <td>
                <?php echo Form::select('list['.$i.'][sales_category_code]', (!empty($data['list'][$i]['sales_category_code'])) ? $data['list'][$i]['sales_category_code']:'01', $sales_category_list, array('class' => 'select-item', 'id' => 'sales_category_code_'.$i, 'tabindex' => '4')); ?>
                <?php echo Form::input('list['.$i.'][sales_category_value]', (!empty($data['list'][$i]['sales_category_value'])) ? $data['list'][$i]['sales_category_value']:'', array('id' => 'sales_category_value_'.$i, 'class' => 'input-text', 'style' => 'width:100px;', 'maxlength' => '10', 'tabindex' => '5')); ?>
              </td>
              <?php /* 傭車先No */ ?>
              <td>
                <?php echo Form::input('list['.$i.'][carrier_code]', (!empty($data['list'][$i]['carrier_code'])) ? sprintf('%05d', $data['list'][$i]['carrier_code']):'', array('id' => 'carrier_code_'.$i, 'class' => 'input-text', 'style' => 'width:80px;','maxlength' => '5', 'tabindex' => '6')); ?>
                <input type="button" name="s_carrier" value="検索" class='buttonA' tabindex="7" onclick="onCarrierSearch('<?php echo Uri::create('search/s0030'); ?>', <?php echo $i; ?>)" />
              </td>
              <?php /* 車種 */ ?>
              <td>
                <?php echo Form::select('list['.$i.'][car_model_code]', (!empty($data['list'][$i]['car_model_code'])) ? $data['list'][$i]['car_model_code']:'001', $carmodel_list, array('class' => 'select-item','maxlength' => '3', 'tabindex' => '8')); ?>
              </td>
              <?php /* 運転手 */ ?>
              <td>
                <?php echo Form::input('list['.$i.'][driver_name]', (!empty($data['list'][$i]['driver_name'])) ? $data['list'][$i]['driver_name']:'', array('id' => 'driver_name_'.$i, 'class' => 'input-text', 'style' => 'width:80px;','tabindex' => '9')); ?>
                <input type="button" name="s_driver" value="検索" class='buttonA' tabindex="10" onclick="onCustomerSearch('<?php echo Uri::create('search/s0010'); ?>', <?php echo $i; ?>)" />
                <?php echo Form::hidden('list['.$i.'][member_code]', (!empty($data['list'][$i]['member_code'])) ? $data['list'][$i]['member_code']:'', array('id' => 'member_code_'.$i));?>
              </td>
              <?php /* 稼働台数 */ ?>
              <td>
                <?php echo Form::input('list['.$i.'][operation_count]', (!empty($data['list'][$i]['operation_count'])) ? $data['list'][$i]['operation_count']:'', array('type' => 'number', 'id' => 'operation_count_'.$i, 'class' => 'input-text', 'style' => 'width:100px;','tabindex' => '11')); ?>
              </td>
              <?php /* 売上 */ ?>
              <td>
                <?php echo Form::input('list['.$i.'][sales]', (!empty($data['list'][$i]['sales'])) ? $data['list'][$i]['sales']:'0', array('type' => 'number', 'id' => 'sales_'.$i, 'class' => 'input-text', 'style' => 'width:120px;','maxlength' => '8','tabindex' => '12')); ?>
              </td>
              <?php /* 高速料金＆高速料金請求有無 */ ?>
              <td>
                <?php echo Form::input('list['.$i.'][highway_fee]', (!empty($data['list'][$i]['highway_fee'])) ? $data['list'][$i]['highway_fee']:'0', array('type' => 'number', 'id' => 'highway_fee_'.$i, 'class' => 'input-text', 'style' => 'width:120px;','maxlength' => '8','tabindex' => '13')); ?>
                <?php echo Form::checkbox('list['.$i.'][highway_fee_claim]', (!empty($data['list'][$i]['highway_fee_claim'])) ? $data['list'][$i]['highway_fee_claim']:'1', (!empty($data['list'][$i]['highway_fee_claim']) && $data['list'][$i]['highway_fee_claim'] == '2') ? true:false, array('id' => 'form_highway_fee_claim_'.$i, 'class' => 'input-checkbox', 'style' => 'display:inline;', 'tabindex' => '14')); ?>
                <?php echo Form::label('', 'highway_fee_claim_'.$i, array('style' => 'display:inline;margin-left:20px;')); ?>
                <?php echo Form::hidden('list['.$i.'][highway_fee_claim]', (!empty($data['list'][$i]['highway_fee_claim'])) ? $data['list'][$i]['highway_fee_claim']:'1', array('id' => 'highway_fee_claim_'.$i));?>
              </td>
              <?php /* 備考 */ ?>
              <td rowspan="2">
                <?php echo Form::input('list['.$i.'][remarks]', (!empty($data['list'][$i]['remarks'])) ? $data['list'][$i]['remarks']:'', array('id' => 'remarks_'.$i, 'class' => 'input-text', 'style' => 'width:170px;', 'maxlength' => '15', 'tabindex' => '15')); ?>
              </td>
            </tr>
            <?php /* 下段 */ ?>
            <tr>
              <?php /* 得意先 */ ?>
              <td colspan="2">
                <?php echo Form::label((!empty($data['list'][$i]['client_name'])) ? $data['list'][$i]['client_name']:'', 'client_name', array('id' => 'client_name_'.$i, 'style' => 'display:inline;')); ?>
                <?php echo Form::hidden('list['.$i.'][client_name]', (!empty($data['list'][$i]['client_name'])) ? $data['list'][$i]['client_name']:'', array('id' => 'client_name_'.$i));?>
              </td>
              <?php /* 傭車先 */ ?>
              <td colspan="2">
                <?php echo Form::label((!empty($data['list'][$i]['carrier_name'])) ? $data['list'][$i]['carrier_name']:'', 'carrier_name', array('id' => 'carrier_name_'.$i, 'style' => 'display:inline;')); ?>
                <?php echo Form::hidden('list['.$i.'][carrier_name]', (!empty($data['list'][$i]['carrier_name'])) ? $data['list'][$i]['carrier_name']:'', array('id' => 'carrier_name_'.$i));?>
              </td>
              <?php /* 車番 */ ?>
              <td>
                <?php echo Form::input('list['.$i.'][car_code]', (!empty($data['list'][$i]['car_code'])) ? sprintf('%04d', $data['list'][$i]['car_code']):'', array('id' => 'car_code_'.$i, 'class' => 'input-text', 'style' => 'width:80px;','maxlength' => '4','tabindex' => '16')); ?>
                <input type="button" name="s_car" value="検索" class='buttonA' tabindex="12" onclick="onCarSearch('<?php echo Uri::create('search/s0050'); ?>', <?php echo $i; ?>)" />
              </td>
              <?php /* 配送区分 */ ?>
              <td>
                <?php echo Form::select('list['.$i.'][delivery_category]', (!empty($data['list'][$i]['delivery_category'])) ? $data['list'][$i]['delivery_category']:'1', $delivery_category_list, array('class' => 'select-item', 'style' => 'width:100px;', 'tabindex' => '17')); ?>
              </td>
              <?php /* 傭車費 */ ?>
              <td>
                <?php echo Form::input('list['.$i.'][carrier_cost]', (!empty($data['list'][$i]['carrier_cost'])) ? $data['list'][$i]['carrier_cost']:'0', array('type' => 'number', 'id' => 'carrier_cost_'.$i, 'class' => 'input-text', 'style' => 'width:120px;','maxlength' => '8','tabindex' => '18')); ?>
              </td>
              <?php /* 時間外 */ ?>
              <td>
                <?php echo Form::input('list['.$i.'][overtime_fee]', (!empty($data['list'][$i]['overtime_fee'])) ? $data['list'][$i]['overtime_fee']:'0', array('type' => 'number', 'id' => 'overtime_fee_'.$i, 'class' => 'input-text', 'style' => 'width:120px;','maxlength' => '8','tabindex' => '19')); ?>
              </td>
            </tr>
            <?php endfor; ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="content-row">
      <?php echo Form::submit('execution', '確　　　定', array('class' => 'buttonB', 'onclick' => 'return submitChkExecution(1)', 'tabindex' => '900')); ?>
      <?php echo Form::button('back', '戻　　　る', array('type' => 'button', 'class' => 'buttonB', 'id' => 'sales_correction_back', 'tabindex' => '902')); ?>
		</div>
  <?php echo Form::close(); ?>
</main>
