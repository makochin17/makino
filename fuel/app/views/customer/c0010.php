<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'searchForm', 'name' => 'searchForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('processing_division', '');?>
        <?php echo Form::hidden('select_record', '');?>
        <?php echo Asset::js('customer/c0010.js');?>
        <script>
            var list_count = <?php echo $list_count; ?>;
            var processing_msg1 = '<?php echo Config::get('m_CUS006'); ?>';
        </script>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■検索条件</label>
        <table class="search-area" style="width: 660px">
            <tbody>
                <tr>
                    <td style="width: 200px; height: 30px;">お客様番号</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('customer_code', (!empty($data['customer_code'])) ? $data['customer_code'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'customer_code', 'style' => 'width:150px;', 'maxlength' => '10', 'min' => '0', 'max' => '9999999999', 'tabindex' => '1')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">お客様名</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('customer_name', (!empty($data['customer_name'])) ? $data['customer_name']:'', array('class' => 'input-text', 'id' => 'customer_name', 'maxlength' => '5', 'style' => 'width: 250px;', 'tabindex' => '2')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">お客様名かな</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('customer_name_kana', (!empty($data['customer_name_kana'])) ? $data['customer_name_kana']:'', array('class' => 'input-text', 'id' => 'customer_name_kana', 'maxlength' => '5', 'style' => 'width: 250px;', 'tabindex' => '3')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">お客様区分</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::select('customer_type', ($data['customer_type'] != '') ? $data['customer_type'] : '', $customer_type_list,
                        array('class' => 'select-item', 'id' => 'customer_type', 'style' => 'width: 150px', 'tabindex' => '4')); ?></td>
                </tr>
            </tbody>
        </table>
        <div class="search-buttons">
            <?php echo Form::submit('search', '検索', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '100')); ?>
            <?php /* ?>
            <?php echo Form::submit('search_today', '本日分検索', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '101')); ?>
            <?php echo Form::submit('add', '新規登録', array('class' => 'buttonB', 'onclick' => 'onAdd(\''.Uri::create('customer/c0011').'\')', 'style' => 'margin-right: 20px;', 'tabindex' => '102')); ?>
            <?php echo Form::submit('import_regist', '一括登録', array('class' => 'buttonB', 'onclick' => 'onJump(\''.Uri::create('customer/c0020').'\')', 'style' => 'margin-right: 20px;', 'tabindex' => '103')); ?>
            <?php echo Form::submit('import_file', '雛形ファイル出力', array('class' => 'buttonB', 'onclick' => 'onJump(\''.Uri::create('customer/c0030').'\')', 'style' => 'margin-right: 20px;', 'tabindex' => '104')); ?>
            <?php */ ?>
        </div>
        <?php echo Form::close(); ?>
        <br />
        <?php echo Form::open(array('id' => 'selectForm', 'name' => 'selectForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden('processing_division', '');?>
        <?php echo Form::hidden('customer_code', '');?>
        <?php echo Form::hidden('select_record', '');?>
        <?php echo Form::hidden('list_count', $list_count);?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php if ($total > 0) : ?>
        <div style="width: 1400px;">
            <div class="content-row" style="float: right">
                検索結果：<?php echo $total; ?> 件
            </div>
            <div class="content-row">&nbsp;</div>
            <!-- ここからPager -->
            <div style="float: right">
                <?php echo $pager; ?>
            </div>
        </div>
            <!-- ここまでPager -->
            <div class="table-wrap" style="clear: right">
                <table class="table-inq" style="width: 1400px;">
                    <tr>
                        <th style="width: 70px;">選択</th>
                        <th style="width: 60px;">お客様番号</th>
                        <th style="width: 100px;">お客様名</th>
                        <th style="width: 80px;">TEL</th>
                        <th style="width: 80px;">FAX</th>
                        <th style="width: 100px;">メールアドレス</th>
                    </tr>
                    <?php if (!empty($list_data)) : ?>
                    <?php $i = 0; ?>
                      <?php foreach ($list_data as $key => $val) : ?>
                        <?php $i++; ?>
                        <tr>
                            <td style="width: 60px; text-align: center;">
                                <button type="button" onclick="onEdit('<?php echo Uri::create('customer/c0012'); ?>', <?php echo $val['customer_code']; ?>)" class="buttonA">
                                    <i class='fa fa-edit' style="font-size:14px;"></i> 編集</button>
                                <button type="button" onclick="onDelete(<?php echo $val['customer_code']; ?>)" class="buttonA">
                                    <i class='fa fa-trash' style="font-size:15px;"></i> 削除</button>
                            </td>
                            <td style="text-align: left;padding-left:10px;"><?php echo $val['customer_code']; ?></td>
                            <td style="text-align: left;padding-left:10px;"><?php echo $val['customer_name']; ?></td>
                            <td style="font-size: 15px;"><?php echo $val['tel']; ?></td>
                            <td style="font-size: 15px;"><?php echo $val['fax']; ?></td>
                            <td style="font-size: 15px;"><?php echo $val['mail_address']; ?></td>
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