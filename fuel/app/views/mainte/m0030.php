<?php use \Model\Common\closingdate; ?>
<section id="banner" style="padding-top:20px;">
	<div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'searchForm', 'name' => 'searchForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('processing_division', '');?>
        <?php echo Form::hidden('select_record', '');?>
        <?php echo Asset::js('mainte/m0030.js');?>
        <script>
            var processing_msg1 = '<?php echo Config::get('m_MI0015'); ?>';
        </script>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■検索条件</label>
        <table class="search-area" style="width: 380px">
            <tbody>
                <tr>
                    <td style="width: 130px; height: 30px;">保管場所コード</td>
                    <td style="width: 250px; height: 30px;">
                        <?php echo Form::input('storage_location_id', (!empty($data['storage_location_id'])) ? $data['storage_location_id'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'storage_location_id', 'style' => 'width:90px;', 'min' => '0', 'max' => '99999', 'tabindex' => '1')); ?></td>
                </tr>
                <tr>
                    <td style="width: 130px; height: 30px;">保管場所名</td>
                    <td style="width: 250px; height: 30px;">
                        <?php echo Form::input('storage_location_name', (!empty($data['storage_location_name'])) ? $data['storage_location_name'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'storage_location_name', 'style' => 'width:130px;', 'maxlength' => '8', 'tabindex' => '2')); ?></td>
                </tr>
                <tr>
                    <td style="width: 130px; height: 30px;">保管場所倉庫名</td>
                    <td style="width: 250px; height: 30px;">
                        <?php echo Form::input('storage_warehouse_name', (!empty($data['storage_warehouse_name'])) ? $data['storage_warehouse_name'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'storage_warehouse_name', 'style' => 'width:130px;', 'maxlength' => '5', 'tabindex' => '3')); ?></td>
                </tr>
                <tr>
                    <td style="width: 130px; height: 30px;">保管場所列名</td>
                    <td style="width: 250px; height: 30px;">
                        <?php echo Form::input('storage_column_name', (!empty($data['storage_column_name'])) ? $data['storage_column_name'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'storage_column_name', 'style' => 'width:130px;', 'maxlength' => '5', 'tabindex' => '3')); ?></td>
                </tr>
                <tr>
                    <td style="width: 130px; height: 30px;">保管場所奥行名</td>
                    <td style="width: 250px; height: 30px;">
                        <?php echo Form::input('storage_depth_name', (!empty($data['storage_depth_name'])) ? $data['storage_depth_name'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'storage_depth_name', 'style' => 'width:130px;', 'maxlength' => '5', 'tabindex' => '4')); ?></td>
                </tr>
                <tr>
                    <td style="width: 130px; height: 30px;">保管場所高さ名</td>
                    <td style="width: 250px; height: 30px;">
                        <?php echo Form::input('storage_height_name', (!empty($data['storage_height_name'])) ? $data['storage_height_name'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'storage_height_name', 'style' => 'width:130px;', 'maxlength' => '5', 'tabindex' => '4')); ?></td>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="search-buttons">
            <?php echo Form::submit('search', '検索', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '900')); ?>
            <?php echo Form::submit('excel', 'エクセル出力', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '901')); ?>
            <?php echo Form::submit('add', '新規登録', array('class' => 'buttonB', 'onclick' => 'onAdd(\''.Uri::create('mainte/m0034').'\')', 'tabindex' => '902')); ?>
        </div>
        <div class="search-buttons" style="margin-top: 10px;">
            <button type="button" onclick="onSubReg('<?php echo Uri::create('mainte/m0036'); ?>')" class="buttonB" style="margin-right: 20px;vertical-align:middle;">保管場倉庫 登録</button>
            <button type="button" onclick="onSubReg('<?php echo Uri::create('mainte/m0031'); ?>')" class="buttonB" style="margin-right: 20px;vertical-align:middle;">保管場所列 登録</button>
            <button type="button" onclick="onSubReg('<?php echo Uri::create('mainte/m0032'); ?>')" class="buttonB" style="margin-right: 20px;vertical-align:middle;">保管場所奥行 登録</button>
            <button type="button" onclick="onSubReg('<?php echo Uri::create('mainte/m0033'); ?>')" class="buttonB" style="margin-right: 20px;vertical-align:middle;">保管場所高さ 登録</button>
        </div>
        <?php echo Form::close(); ?>
        <br />
        <?php echo Form::open(array('id' => 'selectForm', 'name' => 'selectForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('processing_division', '');?>
        <?php echo Form::hidden('storage_location_id', '');?>
        <?php echo Form::hidden('select_record', '');?>
        <?php echo Form::hidden('update_url', $update_url);?>
        <?php if ($total > 0) : ?>
            <div class="content-row">
                検索結果：<?php echo $total; ?> 件
            </div>
            <!-- ここからPager -->
            <div>
                <?php echo $pager; ?>
            </div>
            <!-- ここまでPager -->
            <div class="table-wrap">
                <table class="table-inq" style="width: 1400px">
                    <tr>
                        <th style="width: 100px">操作</th>
                        <th style="width: 60px">保管場所ID</th>
                        <th style="width: 180px">保管場所名</th>
                        <th style="width: 100px">保管場所倉庫名</th>
                        <th style="width: 100px">保管場所列名</th>
                        <th style="width: 100px">保管場所奥行名</th>
                        <th style="width: 100px">保管場所高さ名</th>
                        <th style="width: 60px">バーコード</th>
                    </tr>
                    <?php if (!empty($list_data)) : ?>
                      <?php $i = 0; ?>
                      <?php foreach ($list_data as $key => $val) : ?>
                        <?php $i++; ?>
                        <tr>
                            <td style="width: 80px; text-align: center;">
                                <button type="button" onclick="onEdit('<?php echo Uri::create('mainte/m0035'); ?>', <?php echo $val['storage_location_id']; ?>)" class="buttonA"><i class='fa fa-edit' style="font-size:14px;"></i> 編集</button>
                                <button type="button" onclick="onDelete(<?php echo $val['storage_location_id']; ?>, '<?php echo $val['storage_location_name']; ?>')" class="buttonA"><i class='fa fa-trash' style="font-size:15px;"></i> 削除</button>
                            </td>
                            <td style="width: 80px"><?php echo $val['storage_location_id']; ?></td>
                            <td style="width: 180px"><?php echo str_replace('-', ' - ', $val['storage_location_name']); ?></td>
                            <td style="width: 100px"><?php echo $val['storage_warehouse_name']; ?></td>
                            <td style="width: 100px"><?php echo $val['storage_column_name']; ?></td>
                            <td style="width: 100px"><?php echo $val['storage_depth_name']; ?></td>
                            <td style="width: 100px"><?php echo $val['storage_height_name']; ?></td>
                            <td style="width: 100px;text-align: center;">
                                <?php echo Form::hidden('storage_location_id_'.$i, $val['storage_location_id'], array('id' => 'storage_location_id_'.$i));?>
                                <?php echo Form::checkbox('barcode_flg_'.$i, $val['barcode_flg'], ($val['barcode_flg'] == 'YES') ? true:false, array('id' => 'form_barcode_flg_'.$i, 'class' => 'text', 'style' => 'display:inline;')); ?>
                                <?php echo Form::label('', 'barcode_flg_'.$i, array('style' => 'display:inline;padding-left: 1.0em;')); ?>
                            </td>
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
        <?php endif ; ?>
	</div>
</section>
