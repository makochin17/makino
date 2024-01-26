<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'searchForm', 'name' => 'searchForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('select_record', '');?>
        <?php echo Asset::js('mainte/m0011.js');?>
        <script>
            var list_count      = <?php echo $list_count; ?>;
            var clear_msg       = '<?php echo Config::get('m_CI0005'); ?>';
            var processing_msg1 = '<?php echo Config::get('m_CI0009'); ?>';
        </script>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■検索条件</label>
        <table class="search-area" style="width: 660px">
            <tbody>
                <tr>
                    <td style="width: 200px; height: 30px;">ユーザー番号</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('member_code', (!empty($data['member_code'])) ? $data['member_code'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'member_code', 'style' => 'width:250px;', 'maxlength' => '10', 'min' => '0', 'max' => '9999999999', 'tabindex' => '1')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">ユーザー名</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('full_name', (!empty($data['full_name'])) ? $data['full_name']:'', array('class' => 'input-text', 'id' => 'full_name', 'maxlength' => '5', 'style' => 'width: 250px;', 'tabindex' => '2')); ?>
                        <input type="button" name="s_client" value="検索" class='buttonA' tabindex="15" onclick="carModelSearch('<?php echo Uri::create('search/s0090'); ?>')" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">ログインID</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('user_id', (!empty($data['user_id'])) ? $data['user_id']:'', array('class' => 'input-text', 'id' => 'user_id', 'maxlength' => '5', 'style' => 'width: 250px;', 'tabindex' => '3')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">お客様名</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('customer_name', (!empty($data['customer_name'])) ? $data['customer_name']:'', array('class' => 'input-text', 'id' => 'customer_name', 'style' => 'width: 250px;', 'tabindex' => '4')); ?>
                        <input type="button" name="s_client" value="検索" class='buttonA' tabindex="15" onclick="onCustomerSearch('<?php echo Uri::create('search/s0010'); ?>', 0)" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">ユーザー権限</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::select('user_authority', ($data['user_authority'] != '') ? $data['user_authority'] : '', $authority_list,
                        array('class' => 'select-item', 'id' => 'user_authority', 'style' => 'width: 150px', 'tabindex' => '5')); ?></td>
                </tr>
            </tbody>
        </table>
        <div class="search-buttons">
            <?php echo Form::submit('search', '検索', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '100')); ?>
            <?php echo Form::submit('input_clear', '入力項目クリア', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'onclick' => 'return submitChkClear()' , 'tabindex' => '101')); ?>
            <?php echo Form::submit('add', '新規登録', array('class' => 'buttonB', 'onclick' => 'onJump(\''.Uri::create('mainte/m0010').'\')', 'style' => 'margin-right: 20px;', 'tabindex' => '102')); ?>
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
        <?php echo Form::hidden('member_code', '');?>
        <?php echo Form::hidden('user_id', '');?>
        <?php echo Form::hidden('list', '');?>
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
                        <th style="width: 60px;">ユーザー番号</th>
                        <th style="width: 100px;">ユーザー名</th>
                        <th style="width: 80px;">ログインID</th>
                        <th style="width: 80px;">ユーザー権限</th>
                        <th style="width: 100px;">お客様コード</th>
                        <th style="width: 100px;">お客様名</th>
                    </tr>
                    <?php if (!empty($list_data)) : ?>
                    <?php $i = 0; ?>
                      <?php foreach ($list_data as $key => $val) : ?>
                        <?php $i++; ?>
                        <tr>
                            <td style="width: 60px; text-align: center;">
                                <button type="button" onclick="onEdit('<?php echo Uri::create('mainte/m0010'); ?>', <?php echo $val['member_code']; ?>, '<?php echo $val['user_id']; ?>')" class="buttonA">
                                    <i class='fa fa-edit' style="font-size:14px;"></i> 編集</button>
                                <button type="button" onclick="onDelete(<?php echo $val['member_code']; ?>)" class="buttonA">
                                    <i class='fa fa-trash' style="font-size:15px;"></i> 削除</button>
                            </td>
                            <td style="text-align: left;padding-left:10px;"><?php echo $val['member_code']; ?></td>
                            <td style="text-align: left;padding-left:10px;"><?php echo $val['full_name']; ?></td>
                            <td style="font-size: 15px;"><?php echo $val['user_id']; ?></td>
                            <td style="font-size: 15px;"><?php echo (isset($authority_list[$val['user_authority']])) ? $authority_list[$val['user_authority']]:''; ?></td>
                            <td style="font-size: 15px;"><?php echo $val['customer_code']; ?></td>
                            <td style="font-size: 15px;"><?php echo $val['customer_name']; ?></td>
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