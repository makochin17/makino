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
  <?php echo Form::hidden('division_code', (!empty($data['division_code'])) ? $data['division_code']:$userinfo['division_code'], array('id' => 'hidden_division_code'));?>
  <?php echo Form::hidden('stock_number', (!empty($data['stock_number'])) ? $data['stock_number']:'', array('id' => 'hidden_stock_number'));?>
    <script>
        var clear_msg                   = '<?php echo Config::get('m_CI0005'); ?>';
        var processing_msg1             = '<?php echo Config::get('m_DI0035'); ?>';
        var processing_msg2             = '<?php echo Config::get('m_DI0036'); ?>';
        var processing_msg3             = '<?php echo Config::get('m_DI0037'); ?>';
        var redirect_flag               = '<?php echo $redirect_flag; ?>';
    </script>
    <p class="error-message-head"><?php echo $error_message; ?></p>
    <div class="content-row">
      <?php echo Form::hidden('dispatch_processing_division', (!empty($data['processing_division'])) ? $data['processing_division']:'1', array('id' => 'processing_division'));?>
      <label class="item-name">課</label>
      <?php echo Form::select('division_code', (!empty($data['division_code'])) ? $data['division_code']:$userinfo['division_code'], $division_list,
          array('class' => 'select-item', 'id' => 'division_code', 'onchange' => 'change()', 'tabindex' => '1')); ?>
      <?php echo Form::submit('input_clear', '入力内容クリア', array('class' => 'buttonB', 'onclick' => 'return submitChkClear()', 'style' => 'margin-left: 20px;', 'tabindex' => '801')); ?>
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
                    <th rowspan="2" style="width: 140px;">得意先No<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 280px;">得意先名</th>
                    <th style="width: 250px;">商品名<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 100px;">在庫数量<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th style="width: 100px;">単位<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></th>
                    <th >備考</th>
                  </tr>
                  <tr>
                    <th>保管場所</th>
                    <th>メーカー名</th>
                    <th colspan="2">品番</th>
                    <th>型番</th>
                  </tr>
                </thead>
                <tbody>
                  <?php for ($i = 0;$i < 5;$i++) : ?>
                  <?php /* 上段 */ ?>
                  <tr>
                    <?php /* 得意先No */ ?>
                    <td rowspan="2">
                      <?php echo Form::input('list['.$i.'][client_code]', (!empty($data['list'][$i]['client_code'])) ? sprintf('%05d', $data['list'][$i]['client_code']):'', array('id' => 'client_code_'.$i, 'class' => 'input-text', 'style' => 'width:70px;', 'inputmode' => 'numeric', 'maxlength' => '5','tabindex' => $i.'10')); ?>
                      <input type="button" name="s_client_<?php echo $i; ?>" value="検索" class='buttonA' tabindex="<?php echo $i; ?>11" onclick="onClientSearch('<?php echo Uri::create('search/s0020'); ?>', <?php echo $i; ?>)" />
                    </td>
                    <?php /* 得意先名 */ ?>
                    <td>
                      <?php echo Form::label((!empty($data['list'][$i]['client_name'])) ? $data['list'][$i]['client_name']:'', 'list['.$i.'][client_name]', array('id' => 'client_name_'.$i, 'style' => 'display:inline;font-size:14px;')); ?>
                      <?php echo Form::hidden('list['.$i.'][client_name]', (!empty($data['list'][$i]['client_name'])) ? $data['list'][$i]['client_name']:'', array('id' => 'client_name_'.$i));?>
                    </td>
                    <?php /* 商品名 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][product_name]', (!empty($data['list'][$i]['product_name'])) ? $data['list'][$i]['product_name']:'', array('id' => 'product_name_'.$i, 'class' => 'input-text', 'style' => 'width:230px;','maxlength' => '30','tabindex' => $i.'11')); ?>
                    </td>
                    <?php /* 在庫数量 */ ?>
                    <td style="text-align: right;">
                      <?php echo Form::input('list['.$i.'][total_volume]', (!empty($data['list'][$i]['total_volume'])) ? floatval($data['list'][$i]['total_volume']):'0.00', array('id' => 'total_volume_'.$i, 'class' => 'input-text', 'style' => 'width:100px;', 'inputmode' => 'numeric', 'tabindex' => $i.'13')); ?>
                    </td>
                    <?php /* 単位 */ ?>
                    <td>
                      <?php echo Form::select('list['.$i.'][unit_code]', (!empty($data['list'][$i]['unit_code'])) ? $data['list'][$i]['unit_code']:'1', $unit_list, array('id' => 'unit_code_'.$i, 'class' => 'select-item', 'style' => 'width:100px;', 'maxlength' => '3', 'tabindex' => $i.'15')); ?>
                    </td>
                    <?php /* 備考 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][remarks]', (!empty($data['list'][$i]['remarks'])) ? $data['list'][$i]['remarks']:'', array('id' => 'remarks_'.$i, 'class' => 'input-text', 'style' => 'width:150px;', 'maxlength' => '15', 'tabindex' => $i.'16')); ?>
                    </td>
                  </tr>
                  <?php /* 下段 */ ?>
                  <tr>
                    <?php /* 保管場所 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][storage_location]', (!empty($data['list'][$i]['storage_location'])) ? $data['list'][$i]['storage_location']:'', array('id' => 'storage_location_'.$i, 'class' => 'input-text', 'style' => 'width:260px;','maxlength' => '15','tabindex' => $i.'12')); ?>
                    </td>
                    <?php /* メーカー名 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][maker_name]', (!empty($data['list'][$i]['maker_name'])) ? $data['list'][$i]['maker_name']:'', array('id' => 'maker_name_'.$i, 'class' => 'input-text', 'style' => 'width:230px;','maxlength' => '15','tabindex' => $i.'14')); ?>
                    </td>
                    <?php /* 品番 */ ?>
                    <td colspan="2">
                      <?php echo Form::input('list['.$i.'][part_number]', (!empty($data['list'][$i]['part_number'])) ? $data['list'][$i]['part_number']:'', array('id' => 'part_number_'.$i, 'class' => 'input-text', 'style' => 'width:210px;','maxlength' => '15','tabindex' => $i.'14')); ?>
                    </td>
                    <?php /* 型番 */ ?>
                    <td>
                      <?php echo Form::input('list['.$i.'][model_number]', (!empty($data['list'][$i]['model_number'])) ? $data['list'][$i]['model_number']:'', array('id' => 'model_number_'.$i, 'class' => 'input-text', 'style' => 'width:150px;','maxlength' => '15','tabindex' => $i.'14')); ?>
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
          <?php echo Form::button('back', '戻　　　る', array('type' => 'button', 'class' => 'buttonB', 'id' => 'dispatch_back', 'tabindex' => '902')); ?>
        </div>
      </div>
    </div>
  <?php echo Form::close(); ?>
</main>