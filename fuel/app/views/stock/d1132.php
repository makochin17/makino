<main class="l-main">
  <?php echo Form::open(array('id' => 'entryForm', 'name' => 'entryForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
  <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
  <?php echo Form::hidden('list_url', $list_url);?>
  <?php echo Form::hidden('current_url', $current_url);?>
  <?php echo Form::hidden('master_url', $master_url);?>
  <?php echo Form::hidden('select_record', null);?>
  <?php echo Form::hidden('list_no', null);?>

  <?php /* フォームデータ */ ?>
  <?php echo Form::hidden('processing_division', (!empty($data['processing_division'])) ? $data['processing_division']:'2', array('id' => 'processing_division'));?>
  <?php echo Form::hidden('division_code', (!empty($data['division_code'])) ? $data['division_code']:$userinfo['division_code'], array('id' => 'hidden_division_code'));?>
  <?php echo Form::hidden('storage_fee_number', (!empty($data['storage_fee_number'])) ? $data['storage_fee_number']:'', array('id' => 'hidden_storage_fee_number'));?>
    <script>
        var clear_msg                   = '<?php echo Config::get('m_CI0005'); ?>';
        var processing_msg1             = '<?php echo Config::get('m_DI0046'); ?>';
        var processing_msg2             = '<?php echo Config::get('m_DI0047'); ?>';
        var processing_msg3             = '<?php echo Config::get('m_DI0048'); ?>';
        var redirect_flag               = '<?php echo $redirect_flag; ?>';
    </script>
    <p class="error-message-head"><?php echo $error_message; ?></p>
    <div class="content-row">
      <label class="item-name">課</label>
      <?php echo Form::select('division_code', (!empty($data['division_code'])) ? $data['division_code']:$userinfo['division_code'], $division_list,
          array('class' => 'select-item', 'id' => 'division_code', 'onchange' => 'change()', 'tabindex' => '1')); ?>
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
                    <th rowspan="2" style="width: 90px;">売上<br>状態</th>
                    <th rowspan="2" style="width: 100px;">締日<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th rowspan="2" style="width: 90px;">得意先No<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 300px;">得意先名</th>
                    <th style="width: 90px;">保管料区分</th>
                    <th style="width: 90px;">保管料<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 80px;">単価</th>
                    <th style="width: 80px;">数量</th>
                    <th style="width: 70px;">単位</th>
                    <th style="width: 90px;">端数処理</th>
                    <th rowspan="2" style="width: 200px;">備考</th>
                  </tr>
                  <tr>
                    <th>保管場所</th>
                    <th colspan="3">商品名</th>
                    <th colspan="3">メーカー名</th>
                  </tr>
                </thead>
                <tbody>
                  <?php for ($i = 0;$i < 1;$i++) : ?>
                  <?php /* 上段 */ ?>
                  <tr>
                    <?php /* 売上確定 */ ?>
                    <td rowspan="2" style="text-align: center;">
                      <?php echo Form::checkbox('list['.$i.'][sales_status]', (!empty($data['list'][$i]['sales_status'])) ? $data['list'][$i]['sales_status']:'1', ($data['list'][$i]['sales_status'] == '2') ? true:false, array('id' => 'form_sales_status_'.$i, 'class' => 'text', 'style' => 'display:inline;', 'tabindex' => '1')); ?>
                      <?php echo Form::label('', 'sales_status_'.$i, array('style' => 'display:inline;padding-left: 1.2em;')); ?>
                    </td>
                    <?php /* 締日 */ ?>
                    <td rowspan="2">
                      <?php echo Form::input('list['.$i.'][closing_date]', (!empty($data['list'][$i]['closing_date'])) ? $data['list'][$i]['closing_date']:'', array('type' => 'date', 'id' => 'closing_date_'.$i,'class' => 'input-date','style' => 'width: 140px;','maxlength' => '20','tabindex' => '2')); ?>
                    </td>
                    <?php /* 得意先No */ ?>
                    <td rowspan="2">
                      <?php echo Form::input('list['.$i.'][client_code]', (!empty($data['list'][$i]['client_code'])) ? sprintf('%05d', $data['list'][$i]['client_code']):'', array('id' => 'client_code_'.$i, 'class' => 'input-text', 'style' => 'width:80px;', 'inputmode' => 'numeric', 'maxlength' => '5','tabindex' => $i.'10')); ?>
                      <input type="button" name="s_client_<?php echo $i; ?>" value="検索" class='buttonA' tabindex="<?php echo $i; ?>11" onclick="onClientSearch('<?php echo Uri::create('search/s0020'); ?>', <?php echo $i; ?>)" />
                    </td>
                    <?php /* 得意先名 */ ?>
                    <td>
                      <?php echo Form::label((!empty($data['list'][$i]['client_name'])) ? $data['list'][$i]['client_name']:'', 'list['.$i.'][client_name]', array('id' => 'client_name_'.$i, 'style' => 'display:inline;font-size:12px;')); ?>
                      <?php echo Form::hidden('list['.$i.'][client_name]', (!empty($data['list'][$i]['client_name'])) ? $data['list'][$i]['client_name']:'', array('id' => 'client_name_'.$i));?>
                    </td>
                    <?php /* 保管料区分 */ ?>
                    <td>
                      <?php echo Form::select('list['.$i.'][storage_fee_code]', (!empty($data['list'][$i]['storage_fee_code'])) ? $data['list'][$i]['storage_fee_code']:'1', $storage_fee_list, array('id' => 'storage_fee_code_'.$i, 'class' => 'select-item', 'style' => 'width:100px;', 'maxlength' => '3', 'tabindex' => $i.'15')); ?>
                    </td>
                    <?php /* 保管料 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][storage_fee]', (!empty($data['list'][$i]['storage_fee'])) ? $data['list'][$i]['storage_fee']:'0', array('id' => 'storage_fee_'.$i, 'class' => 'input-text', 'style' => 'width:100px;', 'inputmode' => 'numeric', 'tabindex' => $i.'13')); ?>
                    </td>
                    <?php /* 単価 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][unit_price]', (!empty($data['list'][$i]['unit_price'])) ? $data['list'][$i]['unit_price']:'0.00', array('id' => 'unit_price_'.$i, 'class' => 'input-text', 'style' => 'width:90px;', 'inputmode' => 'numeric', 'tabindex' => $i.'16')); ?>
                    </td>
                    <?php /* 数量 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][volume]', (!empty($data['list'][$i]['volume'])) ? floatval($data['list'][$i]['volume']):'0.00', array('id' => 'volume_'.$i, 'class' => 'input-text', 'style' => 'width:100px;', 'inputmode' => 'numeric', 'tabindex' => $i.'13')); ?>
                    </td>
                    <?php /* 単位 */ ?>
                    <td>
                      <?php echo Form::select('list['.$i.'][unit_code]', (!empty($data['list'][$i]['unit_code'])) ? $data['list'][$i]['unit_code']:'1', $unit_list, array('id' => 'unit_code_'.$i, 'class' => 'select-item', 'style' => 'width:100px;', 'maxlength' => '3', 'tabindex' => $i.'15')); ?>
                    </td>
                    <?php /* 端数処理 */ ?>
                    <td>
                      <?php echo Form::select('list['.$i.'][rounding_code]', (!empty($data['list'][$i]['rounding_code'])) ? $data['list'][$i]['rounding_code']:'1', $rounding_list, array('id' => 'rounding_code_'.$i, 'class' => 'select-item', 'style' => 'width:100px;', 'maxlength' => '3', 'tabindex' => $i.'15')); ?>
                    </td>
                    <?php /* 備考 */ ?>
                    <td rowspan="2">
                      <?php echo Form::input('list['.$i.'][remarks]', (!empty($data['list'][$i]['remarks'])) ? $data['list'][$i]['remarks']:'', array('id' => 'remarks_'.$i, 'class' => 'input-text', 'style' => 'width:140px;', 'maxlength' => '15', 'tabindex' => $i.'16')); ?>
                    </td>
                  </tr>
                  <?php /* 下段 */ ?>
                  <tr>
                    <?php /* 保管場所 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][storage_location]', (!empty($data['list'][$i]['storage_location'])) ? $data['list'][$i]['storage_location']:'', array('id' => 'storage_location_'.$i, 'class' => 'input-text', 'style' => 'width:300px;','maxlength' => '15','tabindex' => $i.'12')); ?>
                    </td>
                    <?php /* 商品名 */ ?>
                    <td colspan="3">
                      <?php echo Form::input('list['.$i.'][product_name]', (!empty($data['list'][$i]['product_name'])) ? $data['list'][$i]['product_name']:'', array('id' => 'product_name_'.$i, 'class' => 'input-text', 'style' => 'width:310px;','maxlength' => '30','tabindex' => $i.'11')); ?>
                    </td>
                    <?php /* メーカー名 */ ?>
                    <td colspan="3">
                      <?php echo Form::input('list['.$i.'][maker_name]', (!empty($data['list'][$i]['maker_name'])) ? $data['list'][$i]['maker_name']:'', array('id' => 'maker_name_'.$i, 'class' => 'input-text', 'style' => 'width:320px;','maxlength' => '15','tabindex' => $i.'14')); ?>
                    </td>
                  </tr>
                  <?php endfor; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="content-row">
          <?php echo Form::submit('execution', '更　　　新', array('class' => 'buttonB', 'onclick' => 'return submitChkExecution(2)', 'tabindex' => '900')); ?>
          <?php echo Form::submit('execution', '削　　　除', array('class' => 'buttonB', 'onclick' => 'return submitChkExecution(3)', 'tabindex' => '901')); ?>
          <?php echo Form::button('back', '戻　　　る', array('type' => 'button', 'class' => 'buttonB', 'id' => 'dispatch_back', 'tabindex' => '902')); ?>
        </div>
      </div>
    </div>
  <?php echo Form::close(); ?>
</main>