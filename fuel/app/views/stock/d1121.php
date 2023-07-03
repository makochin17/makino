<main class="l-main">
  <?php echo Form::open(array('id' => 'entryForm', 'name' => 'entryForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
  <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
  <?php echo Form::hidden('list_url', $list_url);?>
  <?php echo Form::hidden('current_url', $current_url);?>
  <?php echo Form::hidden('master_url', $master_url);?>
  <?php echo Form::hidden('select_record', null);?>
  <?php echo Form::hidden('list_no', null);?>

  <?php /* フォームデータ */ ?>
  <?php echo Form::hidden('processing_division', (!empty($data['processing_division'])) ? $data['processing_division']:'1', array('id' => 'hidden_processing_division'));?>
  <?php echo Form::hidden('stock_number', $stock_number);?>
    <script>
        var clear_msg                   = '<?php echo Config::get('m_CI0005'); ?>';
        var processing_msg1             = '<?php echo Config::get('m_DI0038'); ?>';
        var processing_msg2             = '<?php echo Config::get('m_DI0039'); ?>';
        var processing_msg3             = '<?php echo Config::get('m_DI0040'); ?>';
        var redirect_flag               = '<?php echo $redirect_flag; ?>';
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
        <br>
    </div>
    <div class="content-row">
      <?php echo Form::hidden('dispatch_processing_division', (!empty($data['processing_division'])) ? $data['processing_division']:'1', array('id' => 'processing_division'));?>
      <input type="button" name="search" value="入出庫履歴引用" class='buttonB' tabindex="800" onclick="onStockChangeSearch('<?php echo Uri::create('search/s1020'); ?>', 0,'<?php echo $stock['stock_number']; ?>')" style="margin-right: 20px;" />
      <input type="button" name="search" value="配車履歴引用" class='buttonB' tabindex="801" onclick="onDispatchCharterSearch('<?php echo Uri::create('search/s1010'); ?>', 0,'<?php echo $stock['client_code']; ?>','<?php echo $stock['product_name']; ?>')" style="margin-right: 20px;" />
      <?php echo Form::submit('input_clear', '入力内容クリア', array('class' => 'buttonB', 'onclick' => 'return submitChkClear()', 'tabindex' => '802')); ?>
    </div>
    <div class="content-row" style="margin-bottom: 40px;">
      <div class="tab-contents">
        <input id="tab1" type="radio" name="tab-radio" checked="checked">
        <div class="tab-content" id="tab-content1">
          <div class="content-row">
            <div class="table-wrap">
              <table class="table-mnt">
                <thead>
                  <tr>
                    <th style="width: 90px;">売上確定</th>
                    <th style="width: 140px;">日付<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 140px;">区分<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 250px;">運行先</th>
                    <th style="width: 100px;">数量<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 100px;">単位<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 100px;">料金<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th>備考</th>
                  </tr>
                </thead>
                <tbody>
                  <?php for ($i = 0;$i < 5;$i++) : ?>
                  <tr>
                    <?php /* 売上確定 */ ?>
                    <td style="text-align: center;">
                      <?php echo Form::checkbox('list['.$i.'][sales_status]', (!empty($data['list'][$i]['sales_status'])) ? $data['list'][$i]['sales_status']:'', ($data['list'][$i]['sales_status'] == '2') ? true:false, array('id' => 'form_sales_status_'.$i, 'class' => 'text', 'style' => 'display:inline;', 'tabindex' => $i.'01')); ?>
                      <?php echo Form::label('', 'sales_status_'.$i, array('style' => 'display:inline;padding-left: 1.2em;')); ?>
                    </td>
                    <?php /* 日付 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][destination_date]', (!empty($data['list'][$i]['destination_date'])) ? $data['list'][$i]['destination_date']:'', array('type' => 'date', 'id' => 'destination_date_'.$i,'style' => 'width: 140px;','class' => 'input-date','tabindex' => $i.'02')); ?>
                    </td>
                    <?php /* 区分 */ ?>
                    <td>
                      <?php echo Form::select('list['.$i.'][stock_change_code]', (!empty($data['list'][$i]['stock_change_code'])) ? $data['list'][$i]['stock_change_code']:'1', $stock_change_list, array('id' => 'stock_change_code_'.$i, 'class' => 'select-item', 'style' => 'width:130px;', 'maxlength' => '3', 'tabindex' => $i.'15')); ?>
                    </td>
                    <?php /* 運行先 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][destination]', (!empty($data['list'][$i]['destination'])) ? $data['list'][$i]['destination']:'', array('id' => 'destination_'.$i, 'class' => 'input-text', 'style' => 'width:230px;','maxlength' => '30','tabindex' => $i.'11')); ?>
                    </td>
                    <?php /* 数量 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][volume]', (!empty($data['list'][$i]['volume'])) ? floatval($data['list'][$i]['volume']):'0.00', array('id' => 'volume_'.$i, 'class' => 'input-text', 'style' => 'width:100px;', 'inputmode' => 'numeric', 'tabindex' => $i.'13')); ?>
                    </td>
                    <?php /* 単位 */ ?>
                    <td>
                      <?php echo (isset($unit_list[$stock['unit_code']]) && !empty($unit_list[$stock['unit_code']])) ? $unit_list[$stock['unit_code']]:''; ?>
                    </td>
                    <?php /* 料金 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][fee]', (!empty($data['list'][$i]['fee'])) ? $data['list'][$i]['fee']:'0', array('id' => 'fee_'.$i, 'class' => 'input-text', 'style' => 'width:100px;', 'inputmode' => 'numeric', 'tabindex' => $i.'13')); ?>
                    </td>
                    <?php /* 備考 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][remarks]', (!empty($data['list'][$i]['remarks'])) ? $data['list'][$i]['remarks']:'', array('id' => 'remarks_'.$i, 'class' => 'input-text', 'style' => 'width:140px;', 'maxlength' => '15', 'tabindex' => $i.'16')); ?>
                    </td>
                  </tr>
                  <?php endfor; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="content-row">
          <?php echo Form::submit('execution', '確　　　定', array('class' => 'buttonB', 'onclick' => 'return submitChkExecution(1)', 'tabindex' => '900')); ?>
          <?php echo Form::button('back', '戻　　　る', array('type' => 'button', 'class' => 'buttonB', 'id' => 'stock_change_back', 'tabindex' => '902')); ?>
        </div>
      </div>
    </div>
  <?php echo Form::close(); ?>
</main>