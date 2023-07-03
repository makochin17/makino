<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'searchForm', 'name' => 'searchForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('processing_division', '');?>
        <?php echo Form::hidden('select_record', '');?>
        <?php echo Asset::js('stock/d1110.js');?>
        <script>
            var list_count = <?php echo $list_count; ?>;
            var processing_msg1 = '<?php echo Config::get('m_DI0037'); ?>';
        </script>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■検索条件</label>
        <table class="search-area" style="width: 660px">
            <tbody>
                <tr>
                    <td style="width: 200px; height: 30px;">在庫番号</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('stock_number', (!empty($data['stock_number'])) ? $data['stock_number'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'stock_number', 'style' => 'width:150px;', 'min' => '0', 'max' => '9999999999', 'tabindex' => '1')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">課</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::select('division_code', ($data['division_code'] != '') ? $data['division_code'] : $userinfo['division_code'], $division_list,
                        array('class' => 'select-item', 'id' => 'division_code', 'style' => 'width: 150px', 'tabindex' => '2')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">得意先</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('client_code', (!empty($data['client_code'])) ? $data['client_code']:'', array('id' => 'client_code', 'class' => 'input-text', 'type' => 'number', 'style' => 'width: 100px;', 'min' => '0', 'max' => '99999','tabindex' => '3')); ?>
                        <input type="button" name="s_client" value="検索" class='buttonA' tabindex="15" onclick="onClientSearch('<?php echo Uri::create('search/s0020'); ?>', 0)" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">保管場所</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('storage_location', (!empty($data['storage_location'])) ? $data['storage_location']:'', array('class' => 'input-text', 'id' => 'storage_location', 'style' => 'width: 300px;', 'maxlength' => '15', 'tabindex' => '4')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">商品名</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('product_name', (!empty($data['product_name'])) ? $data['product_name']:'', array('class' => 'input-text', 'id' => 'product_name', 'style' => 'width: 300px;', 'maxlength' => '30', 'tabindex' => '5')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">メーカー名</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('maker_name', (!empty($data['maker_name'])) ? $data['maker_name']:'', array('class' => 'input-text', 'id' => 'maker_name', 'style' => 'width: 300px;', 'maxlength' => '15', 'tabindex' => '6')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">品番</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('part_number', (!empty($data['part_number'])) ? $data['part_number']:'', array('class' => 'input-text', 'id' => 'part_number', 'style' => 'width: 300px;', 'maxlength' => '15', 'tabindex' => '6')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">型番</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::input('model_number', (!empty($data['model_number'])) ? $data['model_number']:'', array('class' => 'input-text', 'id' => 'model_number', 'style' => 'width: 300px;', 'maxlength' => '15', 'tabindex' => '6')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">登録者</td>
                    <td style="width: 460px; height: 30px;">
                        <?php echo Form::select('create_user', $data['create_user'], $create_user_list,
                          array('class' => 'select-item', 'id' => 'create_user', 'style' => 'width: 180px', 'tabindex' => '7')); ?></td>
                </tr>
            </tbody>
        </table>
        <div class="search-buttons">
            <?php echo Form::submit('search', '検索', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '100')); ?>
            <?php echo Form::submit('search_today', '本日分検索', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '101')); ?>
            <?php echo Form::submit('add', '新規登録', array('class' => 'buttonB', 'onclick' => 'onAdd(\''.Uri::create('stock/d1111').'\')', 'style' => 'margin-right: 20px;', 'tabindex' => '102')); ?>
        </div>
        <?php echo Form::close(); ?>
        <br />
        <?php echo Form::open(array('id' => 'selectForm', 'name' => 'selectForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden('processing_division', '');?>
        <?php echo Form::hidden('stock_number', '');?>
        <?php echo Form::hidden('select_record', '');?>
        <?php echo Form::hidden('list_count', $list_count);?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php if ($total > 0) : ?>
        <div style="width: 1650px;">
            <div class="content-row">
                検索結果：<?php echo $total; ?> 件
            </div>
            <!-- ここからPager -->
            <div>
                <?php echo $pager; ?>
            </div>
        </div>
            <!-- ここまでPager -->
            <div class="table-wrap" style="clear: right">
                <table class="table-inq" style="width: 1450px;">
                    <tr>
                        <th rowspan="2" style="width: 80px;">選択</th>
                        <th rowspan="2" style="width: 80px;">入出庫</th>
                        <th rowspan="2" style="width: 90px;">課</th>
                        <th rowspan="2" style="width: 80px;">得意先No</th>
                        <th style="width: 250px;">得意先名</th>
                        <th style="width: 200px;">商品名</th>
                        <th style="width: 80px;">在庫数量</th>
                        <th style="width: 70px;">単位</th>
                        <th style="width: 200px;">備考</th>
                    </tr>
                    <tr>
                        <th>保管場所</th>
                        <th>メーカー名</th>
                        <th colspan="2">品番</th>
                        <th>型番</th>
                    </tr>
                    <?php if (!empty($list_data)) : ?>
                    <?php $i = 0; ?>
                      <?php foreach ($list_data as $key => $val) : ?>
                        <?php $i++; ?>
                        <tr>
                            <td style="width: 60px; text-align: center;">
                                <button type="button" onclick="onEdit('<?php echo Uri::create('stock/d1112'); ?>', <?php echo $val['stock_number']; ?>)" class="buttonA"><i class='fa fa-edit' style="font-size:14px;"></i> 編集</button>
                            </td>
                            <td rowspan="2" style="text-align: center;">
                                <button type="button" onclick="onInput('<?php echo Uri::create('stock/d1120'); ?>', <?php echo $val['stock_number']; ?>)" class="buttonA">入力</button>
                            </td>
                            <?php echo Form::hidden('stock_number_'.$i, $val['stock_number']);?>
                            <td rowspan="2" style="text-align: center;"><?php echo $val['division_name']; ?></td>
                            <td rowspan="2" style="text-align: center;"><?php echo sprintf('%05d', $val['client_code']); ?></td>
                            <td><?php echo $val['client_name']; ?></td>
                            <td><?php echo mb_substr($val['product_name'], 0, 15); ?></td>
                            <td style="text-align: right;"><?php echo floatval(number_format($val['total_volume'],6)); ?></td>
                            <td><?php echo (isset($unit_list[$val['unit_code']]) && !empty($unit_list[$val['unit_code']])) ? $unit_list[$val['unit_code']]:''; ?></td>
                            <td><?php echo $val['remarks']; ?></td>
                        </tr>
                        <tr>
                            <td style="width: 60px; text-align: center;">
                                <button type="button" onclick="onDelete(<?php echo $val['stock_number']; ?>)" class="buttonA"><i class='fa fa-trash' style="font-size:15px;"></i> 削除</button>
                            </td>
                            <td><?php echo $val['storage_location']; ?></td>
                            <td><?php echo $val['maker_name']; ?></td>
                            <td colspan="2"><?php echo $val['part_number']; ?></td>
                            <td><?php echo $val['model_number']; ?></td>
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
        <?php echo Form::close(); ?>
    </div>
</section>