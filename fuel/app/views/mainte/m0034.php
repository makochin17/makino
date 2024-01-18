<section id="banner" style="padding-top:20px;">
	<div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'entryForm', 'name' => 'entryForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('select_record', null);?>
        <?php echo Asset::js('mainte/m0034.js');?>
        <script>
            var clear_msg 		= '<?php echo Config::get('m_CI0005'); ?>';
            var processing_msg1 = '<?php echo Config::get('m_MI0001'); ?>';
        </script>
        <script src="https://ajaxzip3.github.io/ajaxzip3.js" charset="UTF-8"></script>

        <p class="error-message-head"><?php echo $error_message; ?></p>
<!--        <div style="padding-top:10px;">
            <?php echo Form::submit('input_clear', '入力項目クリア', array('class' => 'buttonB', 'onclick' => 'return submitChkClear()' , 'tabindex' => '3')); ?>
            <?php //echo Form::submit('csv_download', 'CSVフォーマット', array('class' => 'buttonB', 'tabindex' => '4')); ?>
            <?php //echo Form::submit('csv_capture', 'CSV取込', array('id' => 'csv_capture', 'data-trigger' => '#fileUpload', 'class' => 'buttonB', 'tabindex' => '5')); ?>
        </div>-->
        <br />
			<table class="search-area" style="height: 90px; width: 780px">
				<tr>
					<td style="width: 140px; height: 30px;">
						保管場所倉庫<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
                        <?php echo Form::select('storage_warehouse_id', $data['storage_warehouse_id'], $storage_warehouse_list,
				            array('class' => 'select-item', 'id' => 'storage_warehouse_id', 'style' => 'width: 140px;margin-right: 10px;', 'tabindex' => '1')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 140px; height: 30px;">
						保管場所列<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
                        <?php echo Form::select('storage_column_id', $data['storage_column_id'], $storage_column_list,
				            array('class' => 'select-item', 'id' => 'storage_column_id', 'style' => 'width: 140px;margin-right: 10px;', 'tabindex' => '1')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 140px; height: 30px;">
						保管場所奥行<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
                        <?php echo Form::select('storage_depth_id', $data['storage_depth_id'], $storage_depth_list,
				            array('class' => 'select-item', 'id' => 'storage_depth_id', 'style' => 'width: 140px;margin-right: 10px;', 'tabindex' => '1')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 140px; height: 30px;">
						保管場所高さ<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
                        <?php echo Form::select('storage_height_id', $data['storage_height_id'], $storage_height_list,
				            array('class' => 'select-item', 'id' => 'storage_height_id', 'style' => 'width: 140px;margin-right: 10px;', 'tabindex' => '1')); ?>
					</td>
				</tr>
			</table>

			<br />
        <div class="search-buttons">
            <?php echo Form::submit('cancel', 'キャンセル', array('class' => 'buttonB', 'tabindex' => '900')); ?>
            <?php echo Form::submit('execution', '登　　録', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'onclick' => 'return submitChkExecution()', 'tabindex' => '901')); ?>
        </div>
        <?php echo Form::close(); ?>
	</div>
</section>
