		<script>
			var holiday_list 	= JSON.parse('<?php echo json_encode($holiday_list); ?>');
			var calendar 		= null;
			document.addEventListener('DOMContentLoaded', function() {
				var calendarEl = document.getElementById('calendar');
				var calendarHeight = window.innerHeight - 130;
				if (calendarHeight > 1300) {
					calendarHeight = 1300;
				};
				if (calendarHeight < 600) {
					calendarHeight = 600;
				};
				calendar = new FullCalendar.Calendar(calendarEl, {
					schedulerLicenseKey: '<?php echo $data['fullcalendar_key']; ?>',
					plugins: [ 'interaction', 'resourceDayGrid', 'resourceTimeGrid' ],
					defaultView: 'resourceTimeGridDay',
					defaultDate: '<?php echo $data['default_day']; ?>',
					locale: 'ja',
					editable: true,
					selectable: true,
					// slotEventOverlap: true, 	// イベントを重ねて表示
					eventLimit: false,
					minTime: '<?php echo $data['start_time']; ?>',
					maxTime: '<?php echo $data['end_time']; ?>',
					snapMinutes: <?php echo $data['span_min']; ?>,
					slotDuration: '00:<?php echo $data['span_min']; ?>:00',
					snapDuration: '00:<?php echo $data['span_min']; ?>:00',
					// timeZone: 'Asia/Tokyo',
					buttonText: {
						// prev:     '&lsaquo;', // <
						// next:     '&rsaquo;', // >
						// prevYear: '&laquo;',  // <<
						// nextYear: '&raquo;',  // >>
						// month:    '月',
						// week:     '週',
						// day:      '日',
						today:    '今日'
					},
					timeFormat : 'HH:mm',
					header: {
						left: 'prev,next today',
						center: 'title',
						right: 'resourceTimeGridDay,resourceTimeGridTwoDay,timeGridWeek,dayGridMonth'
					},
					views: {
						resourceTimeGridTwoDay: {
							// titleFormat: 'YYYY/M/D[(]ddd[)]',
							type: 'resourceTimeGrid',
							duration: { days: 2 },
							buttonText: '2 days',
						},
						resourceTimeGridDay: {
							type: 'resourceTimeGrid',
							// titleFormat: 'YYYY/M/D[(]dddd[)]',

							titleFormat: {
								month: 'long',
								year: 'numeric',
								day: 'numeric',
								weekday: 'long'
							}
						},
						// titleFormat: 'YYYY/M/D[(]ddd[)]',
					},
					// titleFormat: 'YYYY/M/D[(]ddd[)]',
					// titleFormat: 'dddd, MMMM D, YYYY'

					timeFormat: {
						agenda: 'H:mm'
					},
					// events: [function(info, successCallback, failureCallback) {
					// 	alert('a');
					// }],
					// titleFormat: {
					// 	day: "yyyy年M月d日'('ddd')'"
					// 	year: 'numeric', month: 'long', day: 'numeric'
					// } ,
					allDaySlot: true,
					height: calendarHeight,
					// windowResize: function(view) {
					// 	alert('The calendar has adjusted to a window resize.'+innerHeight);
					// 	$('#calendar').fullCalendar('option', 'height', parseInt(window.innerHeight) - 330);
					// },

					// ユニットデータ設定
					resources: [
						// 参考
						// { id: 'c', title: 'ユニット C', eventColor: 'orange' },
						// { id: 'd', title: 'ユニット D', eventColor: 'red' },
						// { id: 'e', title: 'ユニット E', eventColor: 'pink' },
						// { id: 'f', title: 'ユニット F', eventColor: 'blue' }

						<?php $mark = ''; ?>
						<?php $cnt 	= 0; ?>
						<?php if (!empty($unit)) : ?>
							<?php foreach ($unit as $unit_id => $unit_name) : ?>
								<?php $mark = ($cnt > 0) ? ',':''; ?>
								<?php echo $mark; ?>{ id: '<?php echo $unit_id; ?>', title: '<?php echo $unit_name; ?>' }
								<?php $cnt++; ?>
							<?php endforeach; ?>
						<?php endif; ?>
					],
					// eventSources: [
					// 	{
					// 		events: function(info, successCallback, failureCallback) {
					// 		// events: function(start, end, timezone, callback) {
					// 			// var yy = info.start.getFullYear();
					// 			// var mm = info.start.getMonth()+1;
					// 			// var dd = info.start.getDate();
					// 			// var datetime_fr = yy + '-' + mm + '-'  + dd;
					// 			// var datetime_fr = yy + '-' + mm + '-'  + dd + 'T12:30';
					// 			// var datetime_to = yy + '-' + mm + '-'  + dd + 'T14:00';
					// 			alert('a');
					// 			var today = new Date();
					// 			if (today.getFullYear() == yy && today.getMonth()+1 == mm && today.getDate() == dd ){
					// 			} else {
					// 			    add_lunch_break(datetime_fr);
					// 			}
					// 		}
					// 	}
					// ],

					// 予約データ設定
					events: [
						// 参考
						// ,{ id: '', resourceId: '0001', start: '2020-01-01T12:00:00', end: '2020-01-01T13:00:00', title: 'お昼休み' }
						// ,{ id: '', resourceId: '0002', start: '2020-01-01T12:00:00', end: '2020-01-01T13:00:00', title: 'お昼休み' }
						// ,{ id: '', resourceId: '0003', start: '2020-01-01T12:00:00', end: '2020-01-01T13:00:00', title: 'お昼休み' }
						// ,{ id: '', resourceId: '0004', start: '2020-01-01T12:00:00', end: '2020-01-01T13:00:00', title: 'お昼休み' }
						// ,{ id: '', resourceId: '0005', start: '2020-01-01T12:00:00', end: '2020-01-01T13:00:00', title: 'お昼休み' }
						// ,{ id: '', resourceId: '0006', start: '2020-01-01T12:00:00', end: '2020-01-01T13:00:00', title: 'お昼休み' }
						// ,{ id: 'bg1', rendering: 'background', start: '2020-01-26T12:30:00', end: '2020-01-26T14:00:00' }
						// { id: '1', resourceId: '0001', start: '2019-07-05', end: '2019-06-08', title: '小林様　入れ歯調整' },
						// { id: '2', resourceId: '0001', start: '2019-07-05T09:00:00', end: '2019-06-07T14:00:00', title: '鈴木様　インプラント相談' },
						<?php if (!empty($schedule_all_list)) : ?>
							<?php foreach ($schedule_all_list as $key => $val) : ?>
								<?php ($key == 0) ? '':','; ?>
								// お客様コード
								<?php $customer_code 		= (!empty($val["customer_code"])) ? "[".$val["customer_code"]."]":'[新]'; ?>
								// ご要望
								<?php $request_memo 		= (!empty($val["request_memo"])) ? $val["request_memo"]:''; ?>
								// メモ
								<?php $memo 				= (!empty($val["memo"])) ? $val["memo"]:''; ?>
								// 確定フラグ
								<?php $commit 				= (!empty($val["commit"]) && $val["commit"] == "1") ? '〇':''; ?>
								// 背景色
								<?php if (!empty($val["commit"]) && $val["commit"] == "1") : ?>
									<?php $back_color 		= '#DDDDDD'; ?>
								<?php elseif (!empty($val["cancel"]) && $val["cancel"] == "1") : ?>
									<?php $back_color 		= '#7A7A7A'; ?>
								<?php else: ?>
									<?php if (!empty($val["back_color"])) : ?>
										<?php $back_color 	= ''.$val["back_color"].''; ?>
									<?php else: ?>
										<?php $back_color 	= '#FFFFFF'; ?>
									<?php endif; ?>
								<?php endif; ?>
								// 文字色
								<?php $for_color 			= '#000000'; ?>
								<?php if (!empty($val["memo"])) : ?>
									<?php $for_color 		= '#FF0000'; ?>
								<?php else: ?>
									<?php if (!empty($val["commit"]) && $val["commit"] == "1") : ?>
										<?php $for_color 	= '#000000'; ?>
									<?php elseif (!empty($val["cancel"]) && $val["cancel"] == "1") : ?>
										<?php $for_color 	= '#FFFFFF'; ?>
									<?php else: ?>
										<?php if (!empty($val["text_color"])) : ?>
											<?php $for_color = ''.$val["text_color"].''; ?>
										<?php endif; ?>
									<?php endif; ?>
								<?php endif; ?>
								// 開始時間
								<?php $start_datetime 		= ''; ?>
								<?php if (!empty($val["start_date"])) : ?>
									<?php $start_datetime 	= $val["start_date"]; ?>
									<?php if (!empty($val["start_time"])) : ?>
										<?php $start_datetime 	= $start_datetime.'T'.$val["start_time"]; ?>
									<?php endif; ?>
								<?php endif; ?>
								// 終了時間
								<?php $end_datetime 		= ''; ?>
								<?php if (!empty($val["end_date"])) : ?>
									<?php $end_datetime 	= $val["end_date"]; ?>
									<?php if (!empty($val["end_time"])) : ?>
										<?php $end_datetime 	= $end_datetime.'T'.$val["end_time"]; ?>
									<?php endif; ?>
								<?php endif; ?>
								// 予約イベントのユーザーチェック
								// ユーザーが管理者以外の場合、他のお客様のタイトルは「予約あり」に変更
								<?php $editable = false; ?>
								<?php $title 	= '予約あり'; ?>
								<?php if ($val["customer_code"] == $userinfo['customer_code'] || in_array($userinfo['user_authority'], $schedule_authority)) : ?>
									<?php $editable = true; ?>
									<?php $title 	= $commit.$customer_code.$val['customer_name']."[".$val["car_code"]."] "."[".$val["car_name"]."] ".$memo; ?>
								<?php endif; ?>

								// 予約イベントを設定
								{
									id: 				'<?php echo $val['id']; ?>',
									resourceId: 		'<?php echo $val['unit_id']; ?>',
									start: 				'<?php echo $start_datetime; ?>',
									end: 				'<?php echo $end_datetime; ?>',
									title: 				'<?php echo $title; ?>',
									color: 				'<?php echo $back_color; ?>',
									textColor: 			'<?php echo $for_color; ?>',
									editable:   		'<?php echo $editable; ?>'
								},
							<?php endforeach; ?>
						<?php endif; ?>
					],
					select: function(info) {

					// console.log(info);
						$("[id=dialog]]").jqmHide();

						$("[id=id_error]").empty();
						$("[id=unit_error]").empty();
						$("[id=menu_error]").empty();

						$("[id=event_id]").val("");
						$("[id=car_id]").val("");
						$("[id=car_code]").val("");
						$("[id=customer_name]").val("");
						$("[id=car_name]").val("");
						$("[id=consumer_name]").val("");
						$("[id=unit_id]").val("");
						$("[id=request_class_id]").val("");
						$("[id=text_color]").val("");
						$("[id=back_color]").val("#FFFFFF");
						$("[id=request_memo]").val("");
						$("[id=memo]").val("");
						$("[id=carry_flg]").val("");
						$("[id=disp_customer_name]").text("");
						$("[id=disp_car_name]").text("");
						$("[id=disp_consumer_name]").text("");

						var str 	= info.startStr;
						var res 	= str.split('T');
						var today 	= res[0];
						$("[id=reserve_day]").val(res[0]);

						if (res[1]) {
							res = res[1].split('+');
							var time = res[0].split(':');

							$("[id=start_hour]").val(parseInt(time[0]));
							$("[id=start_time]").val(time[1]);
						}

						str = info.endStr;
						res = str.split('T');
						if (res[1]) {
							res = res[1].split('+');
							time = res[0].split(':');

							$("[id=end_hour]").val(parseInt(time[0]));
							$("[id=end_time]").val(time[1]);
						}
						var resource_id = info.resource.id;
						$("[id=unit_id]").val(resource_id);

						$('[id=cmdCancel]').css('display', 'none');
						$('[id=cmdCommit]').css('display', 'none');

						// 休日情報と本日が一致しなかった時は入力フォームを表示する
						if ($.inArray(today, holiday_list) == -1) {
							$('#dialog').jqm();
							// $('#dialog_button')[0].click();
							$('#dialog_button')[0].click(function(e){
								$('#dialog').jqm();
							});
							$('[id=car_code]').focus();
						}
					},
					eventClick: function(info) {

					// alert('id: ' + info.event.id);
					// alert('Event: ' + info.event.title);
					// alert('Coordinates: ' + info.jsEvent.pageX + ',' + info.jsEvent.pageY);
					// alert('View: ' + info.view.type);

						$("[id=id_error]").empty();
						$("[id=unit_error]").empty();
						$("[id=menu_error]").empty();

						$("[id=car_id]").val("");
						$("[id=car_code]").val("");
						$("[id=customer_name]").val("");
						$("[id=car_name]").val("");
						$("[id=consumer_name]").val("");
						$("[id=unit_id]").val("");
						$("[id=request_class_id]").val("");
						$("[id=text_color]").val("");
						$("[id=back_color]").val("#FFFFFF");
						$("[id=request_memo]").val("");
						$("[id=memo]").val("");
						$("[id=carry_flg]").val("");
						$("[id=disp_customer_name]").text("");
						$("[id=disp_car_name]").text("");
						$("[id=disp_consumer_name]").text("");

						var event_id = info.event.id;
						$("[id=event_id]").val(event_id);
						// 予約がぞんざいしない場合は処理しない
						if (event_id == "") {return; }

						var postData = {"id":event_id};
						$.post(
						     '<?php echo $geteventinfo_url; ?>',
						     postData,
						     function(xml){
					// console.log(xml);return;
								$(xml).find("item").each(function(){

									var message = $(this).find("return").text();

									if (message != "") {

										$("[id=id_error]").text(message);

									} else {
										$("[id=reserve_day]").val($(this).find("start_date").text());

										var start_time = $(this).find("start_time").text();
										time = start_time.split(':');

										$("[id=start_hour]").val(parseInt(time[0]));
										$("[id=start_time]").val(time[1]);

										var end_time = $(this).find("end_time").text();
										time = end_time.split(':');

										$("[id=end_hour]").val(parseInt(time[0]));
										$("[id=end_time]").val(time[1]);

										$("[id=car_id]").val($(this).find("car_id").text());
										$("[id=car_code]").val($(this).find("car_code").text());
										$("[id=customer_code]").val($(this).find("customer_code").text());
										$("[id=customer_name]").val($(this).find("customer_name").text());
										$("[id=car_name]").val($(this).find("car_name").text());
										$("[id=consumer_name]").val($(this).find("consumer_name").text());
										$("[id=unit_id]").val($(this).find("unit_id").text());
										$("[id=request_class_id]").val($(this).find("request_class_id").text());
										$("[id=request_class]").val($(this).find("request_class").text());
										$("[id=text_color]").val($(this).find("text_color").text());
										$("[id=back_color]").val($(this).find("back_color").text());
										$("[id=carry_flg]").val($(this).find("carry_flg").text());

										// 車両情報表示用
										set_patient($(this).find("car_id").text(), $(this).find("car_code").text(), $(this).find("customer_code").text(), $(this).find("customer_name").text(), $(this).find("car_name").text(), $(this).find("consumer_name").text())

										// if ($(this).find("cancel").text() == "1") {
										// 	var obj = document.getElementById("cancel_flg");
										// 	obj.checked = true;
										// } else {
										// 	var obj = document.getElementById("cancel_flg");
										// 	obj.checked = false;
										// }

										if ($(this).find("carry_flg").text() == "YES") {
											var obj = document.getElementById("carry_flg");
											obj.checked = true;
										} else {
											var obj = document.getElementById("carry_flg");
											obj.checked = false;
										}

										if ($(this).find("car_code").text() == "") {
											//$('#chkNew').prop('checked',true);
											$('[id=car_code]').attr('readonly',true);
											$('[id=car_code]').css('background-color', '#EEE');
											$('[id=car_name]').attr('readonly',false);
											$('[id=car_name]').css('background-color', '#FFF');
										}

										$("[id=request_memo]").val($(this).find("request_memo").text());
										$("[id=memo]").val($(this).find("memo").text());
									}
								});
						     }
						);

						$('[id=cmdCancel]').css('display', 'inline');
						$('[id=cmdCommit]').css('display', 'inline');

						<?php if (!in_array($userinfo['user_authority'], $schedule_authority)) : ?>
							var postData = {"id":event_id, "customer_code":<?php echo (!empty($userinfo['customer_code'])) ? $userinfo['customer_code']:"''";?>};
							// 操作しているお客様が自分のスケジュールをクリックしたか判定
							$.post(
							     '<?php echo $customer_check_url; ?>',
							     postData,
							     function(xml){
									$(xml).find("item").each(function(){
										var status = $(this).find("status").text();
										if (status == 1) {
											$('#dialog').jqm();
											// $('#dialog_button')[0].click();
											$('#dialog_button')[0].click(function(e){
												$('#dialog').jqm();
											});
										}
									});
							     }
							);
						<?php else: ?>
							$('#dialog').jqm();
							// $('#dialog_button')[0].click();
							$('#dialog_button')[0].click(function(e){
								$('#dialog').jqm();
							});
						<?php endif; ?>

						// change the border color just for fun
						//info.el.style.borderColor = 'red';
					},
					// イベントをドラッグして時間を減らした増やした時のイベント
					eventResize : function(info) {

					// console.log(info);
					// alert('id: ' + info.event.id);
					// alert('start_datetime: ' + info.event.start.toISOString());
					// alert('end_datetime: ' + info.event.end.toISOString());
					// alert('start_time: ' + info.event.start.getHours() + ':' + info.event.start.getMinutes());
					// alert('end_time: ' + info.event.end.getHours() + ':' + info.event.end.getMinutes());

						//イベントIDを取得
						var seq = info.event.id.toString();

						//開始日時
						var str = info.event.start.toISOString();
						var res = str.split('T');

						var date_fr = res[0];
						var time = res[1].split(':');
						var time_fr = ('0' + info.event.start.getHours()).slice(-2) + ':' + ('0' + info.event.start.getMinutes()).slice(-2);

						//終了日時
						var str = info.event.end.toISOString();
						var res = str.split('T');

						var date_to = res[0];

						var time = res[1].split(':');
						// var time_to = time[0] + ':' + time[1];
						var time_to = ('0' + info.event.end.getHours()).slice(-2) + ':' + ('0' + info.event.end.getMinutes()).slice(-2);

						//ユニットコード
						var unit_id = "";

						var postData = {"id":seq,"unit_id":unit_id,"start_date":date_fr,"start_time":time_fr,"end_date":date_to,"end_time":time_to};
						$.post(
						     '<?php echo $changedateschedule_url; ?>',
						     postData,
						     function(xml){
					// console.log(xml);
								$(xml).find("item").each(function(){
									var message = $(this).find("return").text();
									if (message != "") {
										$("[id=id_error]").text(message);
									}
								});
						     }
						);
					},
					// イベントをドラッグして別日に移動させた時のイベント
					eventDrop: function(info) {

					// console.log(info);
					// alert('id: ' + info.event.id);
					// alert('unit_id: ' + info.newResource.id);

						// alert(info.event.id.toString());
						// alert(info.event.start.toString());
						// alert(info.event.start.toISOString());
						// alert(info.event.end.toString());
						// if (info.newResource == null) {
						// 	alert("変更なし");
						// } else {
						// 	alert(info.newResource.id);
						// }

						// alert(info.event.start.getFullYear());
						// alert(info.event.start.getMonth());
						// alert(info.event.start.getDate());
						// alert(info.event.start.getHours());
						// alert(info.event.start.getMinutes());

						//イベントIDを取得
						var seq = info.event.id.toString();

						//開始日時
						var str = info.event.start.toISOString();
						var res = str.split('T');

						var date_fr = res[0];
						var time = res[1].split(':');
						var time_fr = ('0' + info.event.start.getHours()).slice(-2) + ':' + ('0' + info.event.start.getMinutes()).slice(-2);

						//終了日時
						var str = info.event.end.toISOString();
						var res = str.split('T');

						var date_to = res[0];

						var time = res[1].split(':');
						// var time_to = time[0] + ':' + time[1];
						var time_to = ('0' + info.event.end.getHours()).slice(-2) + ':' + ('0' + info.event.end.getMinutes()).slice(-2);

						//ユニットコード
						var unit_id = "";
						if (info.newResource == null) {

						} else {
							unit_id = info.newResource.id;
						}

						var postData = {"id":seq,"unit_id":unit_id,"start_date":date_fr,"start_time":time_fr,"end_date":date_to,"end_time":time_to};
						$.post(
						     '<?php echo $changedateschedule_url; ?>',
						     postData,
						     function(xml){
					// alert(xml);
								$(xml).find("item").each(function(){
									var message = $(this).find("return").text();
									if (message != "") {
										$("[id=id_error]").text(message);
									}
								});
						     }
						);

					},

					// 日付クリック時処理（いらないかも）→要ります
					dateClick: function(info) {
						// alert('clicked ' + info.dateStr);
					}

				});
				calendar.render();
			});
		</script>

		<script type="text/javascript">

			function get_car_info(){

				var car_code = $("[id=car_code]").val();

				if (car_code == "") {
					alert("検索条件を入力してください");
					$("[id=car_code]").focus();
					return false;
				}

				var postData = {"car_code":car_code};
				$.post(
				     '<?php echo $getcar_url; ?>',
				     postData,
				     function(xml){
				// alert($(xml));
						$(xml).find("item").each(function(){

							if ($(this).find("car_cnt").text() == "0") {
								$("[id=id_error]").text($(this).find("return").text());
								$("[id=car_id]").val("");
								$("[id=car_code]").val("");
								$("[id=customer_code]").val("");
								$("[id=customer_name]").val("");
								$("[id=car_name]").val("");
								$("[id=consumer_name]").val("");
								$("[id=disp_customer_name]").text("");
								$("[id=disp_car_name]").text("");
								$("[id=disp_consumer_name]").text("");
								$('[id=car_data_area]').css('display', 'none');
								$('[id=y_data_area]').css('display', 'none');
							} else {

								if ($(this).find("car_cnt").text() == "1") {
									$("[id=car_id]").val($(this).find("car_id").text());
									$("[id=car_code]").val($(this).find("car_code").text());
									$("[id=customer_code]").val($(this).find("customer_code").text());
									$("[id=customer_name]").val($(this).find("customer_name").text());
									$("[id=car_name]").val($(this).find("car_name").text());
									$("[id=consumer_name]").val($(this).find("consumer_name").text());

									$("[id=disp_customer_name]").text($(this).find("customer_name").text());
									$("[id=disp_car_name]").text($(this).find("car_name").text());
									$("[id=disp_consumer_name]").text($(this).find("consumer_name").text());
									$("[id=id_error]").empty();
									$('[id=y_data_area]').css('display', 'none');
									$('[id=car_data_area]').css('display', 'block');
								} else {

									$('[id=car_data_area]').css('display', 'none');
									$('[id=y_data_area]').css('display', 'block');
									$("[id=car_id]").val("");
									$("[id=car_code]").val("");
									$("[id=customer_code]").val("");
									$("[id=customer_name]").val("");
									$("[id=car_name]").val("");
									$("[id=consumer_name]").val("");
									$("[id=disp_customer_name]").text("");
									$("[id=disp_car_name]").text("");
									$("[id=disp_consumer_name]").text("");
									$("[id=id_error]").empty();

									var item_cnt 		= $(this).find("car_cnt").text();

									var car_id 			= $(this).find("car_id").text();
									var code 			= $(this).find("car_code").text();
									var customer_code 	= $(this).find("customer_code").text();
									var customer_name 	= $(this).find("customer_name").text();
									var car_name 		= $(this).find("car_name").text();
									var consumer_name 	= $(this).find("consumer_name").text();

									var car_id_list 		= car_id.split(',');
									var code_list 			= code.split(',');
									var customer_code_list 	= customer_code.split(',');
									var customer_name_list 	= customer_name.split(',');
									var car_name_list 		= car_name.split(',');
									var consumer_name_list 	= consumer_name.split(',');

									$("[id=tbl1 tr]").remove();

									for (var i = 0; i < item_cnt; i++){
										$('[id=tbl1]').append("<tr><td style=\"text-align:center;\"><a href=\"#\" style=\"border:none;\" onclick=\"set_patient('"+car_id_list[i]+"','"+code_list[i]+"','"+customer_code_list[i]+"','"+customer_name_list[i]+"','"+car_name_list[i]+"','"+consumer_name_list[i]+"');\" ><i class='fa fa-check' style='font-size:24px;'></i></a></td><td>"+code_list[i]+"</td><td>"+customer_name_list[i]+"</td><td>"+car_name_list[i]+"</td><td>"+consumer_name_list[i]+"</td></tr>");
									}
								}
							}

							// message = $(this).find("return]").text();

						});
				     }
				);
			}

			function set_patient(car_id, code, customer_code, customer_name, car_name, consumer_name) {

				// alert(
				// 	'コード：'+code+"\n"+'お客様：'+customer_name+"\n"+'車種：'+car_name+"\n"+'使用者：'+consumer_name+"\n"
				// 	);

				$("[id=car_id]").val(car_id);
				$("[id=car_code]").val(code);
				$("[id=customer_code]").val(customer_code);
				$("[id=customer_name]").val(customer_name);
				$("[id=car_name]").val(car_name);
				$("[id=consumer_name]").val(consumer_name);

				$("[id=disp_customer_name]").text(customer_name);
				$("[id=disp_car_name]").text(car_name);
				$("[id=disp_consumer_name]").text(consumer_name);
				$("[id=id_error]").empty();
				$('[id=y_data_area]').css('display', 'none');
				$('[id=car_data_area]').css('display', 'block');
			}

			function add_new(){
				var check = $("[id=chkNew]").is(':checked');

				if (check == true) {

					$('[id=car_code]').attr('readonly',true);
					$('[id=car_code]').css('background-color', '#EEE');
					$('[id=car_name]').attr('readonly',false);
					$('[id=car_name]').css('background-color', '#FFF');

				} else {

					$('[id=car_code]').attr('readonly',false);
					$('[id=car_code]').css('background-color', '#FFF');
					$('[id=car_name]').attr('readonly',true);
					$('[id=car_name]').css('background-color', '#EEE');

				}
			}

		    function update_check() {
				var result = true;

				var reserv_day 			= $("[id=reserve_day]").val();
				var hour_fr 			= $("[id=start_hour]").val();
				var min_fr  			= $("[id=start_time]").val();
				var hour_to 			= $("[id=end_hour]").val();
				var min_to  			= $("[id=end_time]").val();

				var car_code  			= $("[id=car_code]").val();
				var customer_name  		= $("[id=customer_name]").val();
				var car_name  			= $("[id=car_name]").val();
				var consumer_name  		= $("[id=consumer_name]").val();
				var unit_id     		= $("[id=unit_id]").val();
				var request_class_id    = $("[id=request_class_id]").val();
				var request_class_flg   = $("[id=request_class]").val();

				$("[id=date_error]").empty();
				$("[id=time_error]").empty();
				$("[id=id_error]").empty();
				$("[id=unit_error]").empty();
				$("[id=menu_error]").empty();

				var check = $("[id=chkNew]").is(':checked');

				// 日付
				if(reserv_day == ""){
				    $("[id=date_error]").html("<i class='fa fa-exclamation-circle'></i> 日付を入力してください。");
				    result = false;
				}

				// 時間
				if(hour_fr == "" || min_fr == "" || hour_to == "" || min_to == ""){
				    $("[id=time_error]").html("<i class='fa fa-exclamation-circle'></i> 時間を入力してください。");
				    result = false;
				}

				// 車両番号、お客様名、車種、使用者
				if (check == true) {
					if(car_name == ""){
						$("[id=id_error]").html("<i class='fa fa-exclamation-circle'></i> 車種は必須です。");
						$("[id=car_name]").addClass("inp_error");
						result = false;
					}else if(car_name.length > 20){
						$("[id=id_error]").html("<i class='fa fa-exclamation-circle'></i> 車種は20文字以内で入力してください。");
						$("[id=car_name]").addClass("inp_error");
						result = false;
					}
				}else{
					if(car_code == ""){
						$("[id=id_error]").html("<i class='fa fa-exclamation-circle'></i> 車両IDは必須です。");
						$("[id=car_code]").addClass("inp_error");
						result = false;
					}else if(car_code.length > 10){
						$("[id=id_error]").html("<i class='fa fa-exclamation-circle'></i> 車両IDは10文字以内で入力してください。");
						$("[id=car_code]").addClass("inp_error");
						result = false;
					}
				}

				// ユニット
				if(unit_id == ""){
				    $("[id=unit_error]").html("<i class='fa fa-exclamation-circle'></i> ユニットを選択してください。");
				    $("[id=unit_id]").addClass("inp_error");
				    result = false;
				}

				// 依頼区分
				if(request_class_flg != 'other' && request_class_id == ""){
				    $("[id=request_class_error]").html("<i class='fa fa-exclamation-circle'></i> 依頼区分を選択してください。");
				    $("[id=request_class_id]").addClass("inp_error");
				    result = false;
				}

				if (result == false) {return false;}

				// return confirm("診療メニュー情報を更新します。\nよろしいですか？");
				return true;
			}

			// 予約登録
			function schedule_update() {

				if (update_check() == false ){return false; }

				var seq          		= $("[id=event_id]").val();
				var reserv_day   		= $("[id=reserve_day]").val();
				var hour_fr      		= $("[id=start_hour]").val();
				var min_fr       		= $("[id=start_time]").val();
				var hour_to      		= $("[id=end_hour]").val();
				var min_to       		= $("[id=end_time]").val();

				var car_id   			= $("[id=car_id]").val();
				var car_code   			= $("[id=car_code]").val();
				var car_name   			= $("[id=car_name]").val();
				var customer_code  		= $("[id=customer_code]").val();
				var customer_name  		= $("[id=customer_name]").val();
				var consumer_name  		= $("[id=consumer_name]").val();
				var unit_id      		= $("[id=unit_id]").val();
				var text_color      	= $("[id=text_color]").val();
				var back_color      	= $("[id=back_color]").val();
				var request_memo        = $("[id=request_memo]").val();
				var memo         		= $("[id=memo]").val();
				var schedule_type 		= 'usually';

			// alert('seq[event_id]:'+seq);

				// 依頼区分
				if($("[id=request_class]").val() == 'other'){
					var request_class = $("[id=request_class]").val();
				} else {
					var request_class = $("[id=request_class_id]").val();
				}

				// キャンセルフラグ
				var cancel = "";
				// if (document.getElementById("cancel_flg").checked) {
				// 	cancel = "1";
				// } else {
				// 	cancel = "0";
				// }

				// 持込みフラグ
				var carry = "";
				if (document.getElementById("carry_flg").checked) {
					carry = "YES";
				} else {
					carry = "NO";
				}

				var date_fr = reserv_day;
				var date_to = reserv_day;

				var time_fr = "";
				if (hour_fr != "" && min_fr != "") {
					if (hour_fr < 10) {
						time_fr = '0'+hour_fr + ':' + min_fr;
					} else {
						time_fr = hour_fr + ':' + min_fr;
					}
				}

				var time_to = "";
				if (hour_to != "" && min_to!= "") {
					if (hour_to < 10) {
						time_to = '0'+hour_to + ':' + min_to;
					} else {
						time_to = hour_to + ':' + min_to;
					}
				}

				var title = "";
				if (customer_code == "") {
					title = title + "[新]";
				} else {
					title = title + "[" + customer_code + "]";
				}

				title = title + customer_name;

				if (car_code != "") {
					title = title + "["+car_code+"]";
				}
				if (car_name != "") {
					title = title + "["+car_name+"]";
				}
				if (request_memo != "") {
					title = title + request_memo;
				}
				if (memo != "") {
					title = title + memo;
				}

				var postData = {
					"id":seq,
					"schedule_type":"usually",
					"start_date":date_fr,"start_time":time_fr,
					"end_date":date_to,"end_time":time_to,
					"car_id":car_id,
					"car_code":car_code,
					"car_name":car_name,
					"customer_code":customer_code,
					"customer_name":customer_name,
					"consumer_name":consumer_name,
					"unit_id":unit_id,
					"request_class":request_class,
					"text_color":text_color,
					"back_color":back_color,
					"request_memo":request_memo,
					"memo":memo,
					"cancel":cancel,
					"carry_flg":carry,
					"title":title
				};
			// console.log(postData);
			// try {
				$.post(
				     '<?php echo $addschedule_url; ?>',
				     postData,
				     function(xml){
			// console.log(xml);
						$(xml).find("item").each(function(){

							$("[id=event_id]").val("");
							$("[id=car_id]").val("");
							$("[id=car_code]").val("");
							$("[id=customer_code]").val("");
							$("[id=customer_name]").val("");
							$("[id=car_name]").val("");
							$("[id=consumer_name]").val("");
							$("[id=disp_customer_name]").text("");
							$("[id=disp_car_name]").text("");
							$("[id=disp_consumer_name]").text("");

							var new_seq    		= $(this).find("schedule_id").text();
							var unit_id    		= $(this).find("unit_id").text();
							var customer_code   = $(this).find("customer_code").text();
							var customer_name   = $(this).find("customer_name").text();
							var car_code   		= $(this).find("car_code").text();
							var car_name   		= $(this).find("car_name").text();
							var cancel     		= $(this).find("cancel").text();
							var commit     		= $(this).find("commit").text();
							var carry     		= $(this).find("carry_flg").text();
							var back_color 		= $(this).find("back_color").text();
							var text_color 		= $(this).find("text_color").text();
							var request_memo 	= $(this).find("request_memo").text();
							var memo 			= $(this).find("memo").text();
							var message    		= $(this).find("return").text();
			// alert('schedule_id: '+new_seq);
			// alert('message: '+message);

							if (message == "") {

								var datetime_fr = new Date(date_fr + 'T' + time_fr + ':00');
								var datetime_to = new Date(date_fr + 'T' + time_to + ':00');

								var title = "";
								if (commit == "1") {
									title = title + "〇";
								}
								if (customer_code == "") {
									title = title + "[新]";
								} else {
									title = title + "[" + customer_code + "]";
								}

								title = title + customer_name;

								if (car_code != "") {
									title = title + "["+car_code+"]";
								}
								if (car_name != "") {
									title = title + "["+car_name+"]";
								}
								if (request_memo != "") {
									title = title + request_memo;
								}
								if (memo != "") {
									title = title + memo;
								}

								if (memo != "") {
									text_color = "#FF0000";
								} else {
									if (cancel == "1") {
										text_color = "#000000";
									}
								}

								if (cancel == "1") {
									back_color = "#F5E4E4";
								}
								if (commit == "1") {
									back_color = "#DDDDDD";
								}
						// alert('seq[event_id]: '+seq);
						// alert('schedule_id: '+new_seq);
						// alert('unit_id: '+unit_id);
						// alert('datetime_fr: '+datetime_fr);
						// alert('datetime_to: '+datetime_to);
						// alert('title: '+title);
						// alert('back_color: '+back_color);
						// alert('text_color: '+text_color);
						// alert('cancel: '+cancel);
						// alert('commit: '+commit);
						// console.log(calendar);

								if (seq == "") {
									//イベントを新規登録
									calendar.addEvent({
										 id        : new_seq
										,resourceId: unit_id
										,start     : datetime_fr
										,end       : datetime_to
										,title     : title
										,allDay    : false
										,color     : back_color
										,textColor : text_color
									});
								} else {
									//イベントを編集
									//イベントオブジェクトを取得
									var event = calendar.getEventById(seq);
									event.remove();

									// event.setStart(datetime_fr);
									// event.setEnd(datetime_to);

									calendar.addEvent({
										 id        : seq
										,resourceId: unit_id
										,start     : datetime_fr
										,end       : datetime_to
										,title     : title
										,allDay    : false
										,color     : back_color
										,textColor : text_color
									});

								}
								calendar.unselect();
							} else {
								alert(message);
							}

						});
				     }
				);
			// } catch(e) {
			// alert( e.message );
			// }
			//alert("b");

				$('#dialog').jqmHide();
				// $("[id=dialog]]").jqmHide();
				return false;
			}

			function add_lunch_break(target_day) {

		//alert(target_day);
		//alert(target_day+'T12:30');
				calendar.addEvent({
					 id        : 'bk01'
					,rendering : 'background'
					// ,resourceId: '0003'
					,start     : target_day+'T12:30'
					,end       : target_day+'T14:00'
					// ,title     : 'あああ'
					// ,allDay    : false
					// ,color     : back_color
					// ,textColor : text_color
				});

			}

			// 予約取消
			function schedule_cancel() {

				if (confirm('この予約を取り消します。\nよろしいですか？') == false ){return false; }

				var seq = $("[id=event_id]").val();
				var car_code  = $("[id=car_code]").val();

				var postData = {"id":seq,"car_code":car_code,"cancel":"1","cancel_flg":"YES"};
				$.post(
				     '<?php echo $cancelschedule_url; ?>',
				     postData,
				     function(xml){
						$(xml).find("item").each(function(){

							$("[id=event_id]").val("");
							$("[id=car_code]").val("");
							$("[id=car_name]").val("");

							// message = $(this).find("return]").text();

							//イベントオブジェクトを取得
							var event = calendar.getEventById(seq);
							event.remove();

						});
				     }
				);

				$('#dialog').jqmHide();
				return false;
			}

			function schedule_commit() {

				if (confirm('処置を完了します。\nよろしいですか？') == false ){return false; }

				var seq = $("[id=event_id]").val();

				var postData = {"id":seq,"commit":"1","commit_flg":"YES"};
				$.post(
				     '<?php echo $commitschedule_url; ?>',
				     postData,
				     function(xml){
						$(xml).find("item").each(function(){

							$("[id=event_id]").val("");
							$("[id=car_code]").val("");
							$("[id=car_name]").val("");

							// message = $(this).find("return]").text();

							// 再読み込み
							// calendar.refetchEvents();
							location.reload();
							//イベントオブジェクトを取得
							// var event = calendar.getEventById(seq);
							// event.refetchEvents();
						});
				     }
				);

				$('#dialog').jqmHide();
				return false;
			}


			function form_up(){

				$('[id=header]').css('display', 'none');
			//	$('[id=logout]').css('display', 'none');
			//	$('[id=header]').css('height','0px');

				var h = $('[id=calendar]').css('height').replace('px','');
				var new_h = parseInt(h)+200;

			//	$('[id=calendar]').height(String(new_h));
				$('[id=calendar]').fullCalendar('option', 'height', new_h);

				return false;
			}

			function form_down(){

				$('[id=header]').css('display', 'block');
			//	$('[id=logout]').css('display', 'block');
			//	$('[id=header]').height(155);

				var h = $('[id=calendar]').css('height').replace('px','');
				var new_h = parseInt(h)-200;

			//	$('[id=calendar]').height(String(new_h));
				$('[id=calendar]').fullCalendar('option', 'height', 500);

				return false;
			}

		</script>


		<style type="text/css">
		<!--
			table.calendar thead th,
			table.calendar thead td {
				padding:0 0 2px;
				text-align:center;
			}

			/* 日曜日 */
			.fc-sun {
			    color: red;
			    background-color: #fff0f0;
				/*background-color: #fff054;*/
			}
			/* 土曜日 */
			.fc-sat {
			    color: blue;
			    background-color: #f0f0ff;
			}

			#cboxClose {
				color: #025948 !important;
			}

				button,
				.button {
					color: #FFF !important;
			}
		//-->
		</style>

		<style type="text/css">
		<!--
			/* 外枠部分 */
			div.car_data_area {
			  width: 720px;
			  margin-top:10px;
			  margin-left:80px;
			  font-size: 14px;
			}

			/* 外枠部分 */
			div.y_data_area {

			  width: 720px;
			  margin-left:50px;
			  border-right: 1px solid #CCC;
			  border-bottom: 1px solid #CCC;
			  border-left: 1px solid #CCC;
			}

			/* タイトル部分 */
			table.y_data_title {
			  width: 720px;
			  height:30px;
			  margin:0px;
			  table-layout: fixed;
			  border-collapse: separate;
			  border-spacing: 0; /* tableのcellspacing="0"の代わり */
			}

			table.y_data_title th {
			  border-top: 1px solid #CCC;
			  background: #EEF1F4;
			}

			table.y_data_title th,
			table.y_data_title td {
			  padding: 2px;
			  border-right: 1px solid #CCC;
			  border-bottom: 1px solid #CCC;
			}

			table.y_data_title th.r_none,
			table.y_data_title td.r_none {
			  border-right: none; /* 右ボーダーの重なりを防止 */
			}

			/* データ部分 */
			div.y_scroll_box {
			  width: 720px;
			  max-height: 355px;
			  height: auto !important; /* IE6 max-height対応 */
			  height: 100px;　/* IE6 max-height対応 */
			  overflow-x: hidden; /* 横スクロール非表示 */
			  overflow-y: scroll; /* 縦スクロール */
			}

			div.y_hidden {
			  overflow: hidden; /* IE系でデータ部分のテーブルをドラッグした際のズレを防止 */
			  padding: 0 0 10px; /* スクロール仕切った際の下ボーダーの重なりを防止 */
			}

			table.y_data {
			  width: 720px;
			  border-collapse: separate;
			  border-spacing: 0; /* tableのcellspacing="0"の代わり */
			  table-layout: fixed;　/* 内容を固定 */
			}

			table.y_data th,
			table.y_data td {
			  height:40px;
			  padding: 2px;
			  border-right: 1px solid #CCC;
			  border-bottom: 1px solid #CCC;
			  vertical-align:middle;
			}

			table.y_data a {
			  border: none;
			}

			table.y_data td {
			  overflow: hidden; /* データが幅を超えたとき非表示に */
			  white-space: nowrap; /* データの折り返しを防止 */
			}

			table.y_data td p {
			  margin: 0; /* 余分なマージンを消去 */
			}

			/* IE6 */
			table.y_data_title,
			table.y_data {
			  _border-collapse: collapse; /* IE6がborder-spacing: 0;に対応していないので */
			}

			/* IE7 */
			*:first-child+html table.y_data_title,
			*:first-child+html table.y_data {
			  border-collapse: collapse; /* IE7がborder-spacing: 0;に対応していないので */
			}
		//-->
		</style>

		<!--[if lte IE 8]><script src="../js/ie/respond.min.js"></script><![endif]-->
		<!--[if lte IE 8]><script src="../js/ie/html5shiv.js"></script><![endif]-->
		<!--[if lte IE 9]><link rel="stylesheet" href="../css/ie9.css" /><![endif]-->
		<!--[if lte IE 8]><link rel="stylesheet" href="../css/ie8.css" /><![endif]-->

	<section id="banner_section" style="padding-top:10px;">
		<div id="wrapper">

			<!-- Main -->
			<div id="main_section">
				<div class="inner">
					<!-- コンボボックスに設定するユニット一覧設定 -->
					<?php if (!empty($unit_list)) : ?>
				        <?php echo Form::open(array('id' => 'head_form', 'name' => 'head_form', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
				        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
				        <?php echo Form::hidden('select_record', 1);?>
						<div style="display:inline;" class="s_form">
							ユニット：
	                        <?php echo Form::select('cboUnit', (!empty($data['unit_id'])) ? $data['unit_id']:'', $unit_list, array('class' => 'select-item', 'id' => 'cboUser', 'style' => 'width: 200px')); ?>
        					<?php echo Form::submit('search', '表示', array('class' => 'buttonB', 'style' => 'margin-right:20px;width:80px;height:40px;')); ?>
						</div>
						<div style="display:inline;float:right;" class="s_form" >
							お客様：
                    		<?php echo Form::input('txtCustomer', (!empty($data['customer_name'])) ? $data['customer_name']:'', array('class' => 'input-text', 'type' => 'text', 'id' => 'txtCustomer', 'style' => 'width:200px;', 'maxlength' => '50')); ?>
	                        <input type="button" name="search_button1" value="検索" class='buttonB' id="search_button1" style="cursor:pointer;display:inline;color:red;width:80px;height:40px;" onclick="onCustomerSearch('<?php echo Uri::create('search/s0010'); ?>', 0)" />
        					<?php echo Form::submit('reset', 'リセット', array('class' => 'buttonB', 'style' => 'margin-right:20px;width:120px;height:40px;')); ?>
							<?php echo Form::hidden('txtCustomerCode', (!empty($data['customer_code'])) ? $data['customer_code']:'');?>
						</div>
				        <?php echo Form::close(); ?>
					<?php endif; ?>
					<!-- 個別予約情報取得 -->
					<?php if (!empty($schedule_list)) : ?>
						<?php $cnt = 0; ?>
						<?php foreach ($schedule_list as $key => $val) : ?>
							<?php $cnt++; ?>
							<?php if ($cnt == 1) : ?>
								<div style="margin-top:-10px;">
									本日の予定<br />
							<?php endif; ?>
							・<?php echo $val['title']; ?><br />
						<?php endforeach; ?>
					<?php endif; ?>
					<?php ($cnt > 0) ? '</div>':''; ?>
					<section id="banner_section" style="padding-top:10px;">
						<div class="content" style="margin-top:0px;">
							<div style="margin:0px;">
								<!-- <a href="./corp_edit.php" class="button" style=""> ＞ 新しい会社を追加</a> -->
								<a href="#" id="dialog_button" class="button jqModal" style="margin:0px;padding:0px;"></a>
								<div class="jqmWindow" id="dialog" style="width:800px;top:5%;left:40%;">
									<a id="close_button" href="#" class="jqmClose" id="CancelButton" style="border:none;margin-left:730px;" ><?php echo Asset::img('close.png'); ?></a>
									<div style="font-size:20px;font-weight:bold;margin:-25px 0px 0px 20px;">予約登録</div>
									<form id="yoyakuForm" enctype="" method="post" action="">
										<?php echo Form::hidden('car_id', '', array('id' => 'car_id'));?>
										<?php echo Form::hidden('customer_code', '', array('id' => 'customer_code'));?>
										<?php echo Form::hidden('customer_name', '', array('id' => 'customer_name'));?>
										<?php echo Form::hidden('car_name', '', array('id' => 'car_name'));?>
										<?php echo Form::hidden('consumer_name', '', array('id' => 'consumer_name'));?>
										<?php echo Form::hidden('request_class', 'other', array('id' => 'request_class'));?>

										<fieldset style="font-size: 10pt;margin-top:10px;">
											<div style="px;margin:10px 0px 0px 0px;">
												<span id="id_error" class="error_m" style="margin-left:100px;"></span>
											</div>
											<div style="px;margin:10px 0px 0px 0px;">
												<span style="width:80px;display:inline-block;">日付</span>
												<?php echo Form::input('reserve_day', '', array('type' => 'date', 'id' => 'reserve_day', 'class' => 'text', 'tabindex' => '1', 'style' => 'width:200px;'));?>
												<?php echo Form::hidden('event_id', '', array('id' => 'event_id', 'style' => 'width:80px;'));?>
												<input type="checkbox" id="carry_flg" name="carry_flg" class ="text" style="vertical-align:middle;" value="1" tabindex="3" />
												<label for="carry_flg" style="display:inline;margin-left:10px;"> <span style="">&nbsp;持込&nbsp;</span></label>
												<span id="date_error" class="error_m"></span>
											</div>
											<div style="margin:10px 0px 0px 0px;">
												<span style="width:80px;display:inline-block;">時間</span>
												<select id="start_hour" name="start_hour" tabindex="2" style="width:100px;">
													<option value=""></option>
													<?php for ($i=$start_h; $i<=$end_h; $i++) : ?>
														<?php if (!empty($start_hour) && $i === intval($start_hour)) : ?>
															<option value="<?php echo $i; ?>" selected><?php echo $i; ?>時</option>
														<?php else: ?>
															<option value="<?php echo $i; ?>"><?php echo $i; ?>時</option>
														<?php endif; ?>
													<?php endfor; ?>
												</select>
												<?php if (!empty($select_work_time_list)) : ?>
							                        <?php echo Form::select('start_time', (!empty($start_time)) ? $start_time:'', $select_work_time_list, array('class' => 'select-item', 'id' => 'start_time', 'style' => 'width: 100px', 'tabindex' => '3')); ?>
												<?php endif; ?>
												～
												<select id="end_hour" name="end_hour" tabindex="4" style="width:100px;">
													<option value=""></option>
													<?php for ($i=$start_h; $i<=$end_h; $i++) : ?>
														<?php if (!empty($end_hour) && $i === intval($end_hour)) : ?>
															<option value="<?php echo $i; ?>" selected><?php echo $i; ?>時</option>
														<?php else: ?>
															<option value="<?php echo $i; ?>"><?php echo $i; ?>時</option>
														<?php endif; ?>
													<?php endfor; ?>
												</select>
												<?php if (!empty($select_work_time_list)) : ?>
							                        <?php echo Form::select('end_time', (!empty($end_time)) ? $end_time:'', $select_work_time_list, array('class' => 'select-item', 'id' => 'end_time', 'style' => 'width: 100px', 'tabindex' => '5')); ?>
												<?php endif; ?>
												<span id="time_error" class="error_m"></span>
											</div>
											<div style="margin:20px 0px 0px 0px;">
												<span style="width:80px;display:inline-block;">車両ID</span>
												<input type="text" id="car_code" name="car_code" value="" tabindex="6" style="width:320px;" />
												<input type="button" id="cmdPatientCd" name="cmdPatientCd" class="text" value="検索" tabindex="7" style="width:100px;" onclick="get_car_info();" />
												<br />
												<!-- 車両情報リストを表示 -->
												<div id="y_data_area" class="y_data_area" style="display:none;">
													<table class="y_data_title">
														<col style="width:60px;" />
														<col style="width:110px;" />
														<col style="width:180px;" />
														<col style="width:160px;" />
														<col style="width:100px;" />
														<tr>
															<th style="text-align:center;">選択</th>
															<th>車両番号</th>
															<th>お客様</th>
															<th>車種</th>
															<th>使用者</th>
														</tr>
													</table>
													<div class="y_scroll_box">
														<div class="y_hidden">
															<table id="tbl1" class="y_data">
																<col style="width:60px;" />
																<col style="width:110px;" />
																<col style="width:180px;" />
																<col style="width:160px;" />
																<col style="width:100px;" />
															</table>
														</div>
													</div>
												</div>
												<!-- 選択した車両情報を表示 -->
												<div id="car_data_area" class="car_data_area" style="display:none;">
													<ul style="list-style-type:none;list-style-position:inside;">
														<li id="disp_customer_name"></li>
														<li id="disp_car_name"></li>
														<li id="disp_consumer_name"></li>
													</ul>
												</div>
											</div>
											<div style="margin:10px 0px 0px 0px;">
												<span style="width:80px;display:inline-block;">ユニット</span>
						                        <?php echo Form::select('unit_id', '', $unit, array('tabindex' => '9', 'id' => 'unit_id', 'style' => 'width: 180px')); ?>
												<span id="unit_error" class="error_m"></span>
											</div>
											<div style="margin:10px 0px 0px 0px;vertical-align:middle;">
												<span style="width:80px;display:inline-block;vertical-align:super;">文字色</span>
												<?php echo Form::input('text_color', '', array('type' => 'color', 'id' => 'text_color', 'class' => 'text', 'tabindex' => '11', 'style' => 'width:180px;'));?>
												<span id="text_color_error" class="error_m"></span>
											</div>
											<div style="margin:10px 0px 0px 0px;vertical-align:middle;">
												<span style="width:80px;display:inline-block;vertical-align:super;">背景色</span>
												<?php echo Form::input('back_color', '#FFFFFF', array('type' => 'color', 'id' => 'back_color', 'class' => 'text', 'tabindex' => '12', 'style' => 'width:180px;'));?>
												<span id="back_color_error" class="error_m"></span>
											</div>
											<div style="margin:30px 0px 0px 0px;vertical-align:middle;">
												<span style="width:80px;display:inline-block;vertical-align:top;">ご要望</span>
												<textarea id="request_memo" name="request_memo" style="width:600px;height:80px;" maxlength="1000" tabindex="13" wrap="soft"></textarea>
											</div>
											<div style="margin:10px 0px 0px 0px;vertical-align:middle;">
												<span style="width:80px;display:inline-block;vertical-align:top;">備考</span>
												<textarea id="memo" name="memo" style="width:600px;height:80px;" maxlength="1000" tabindex="14" wrap="soft"></textarea>
											</div>
											<br />
											<span id="unit_error" class="error_m"></span>
											<div style="margin:20px 0px 0px 0px;vertical-align:middle;padding-left:90px;">
												<input type="button" id="button"    name="search"    class="button" value="登録&更新" tabindex="20" onclick="return schedule_update();" style="width:130px;margin-top:-9px;" />
												<input type="submit" id="button" name="cmdCancel" class="button" value="予約取消" tabindex="20" onclick="return schedule_cancel();" style="width:130px;margin-top:-9px;" />
												<input type="submit" id="button" name="cmdComplete" class="button" value="予約完了" tabindex="20" onclick="return schedule_commit();" style="width:130px;margin-top:-9px;" />
											</div>
										</fieldset>
									</form>
								</div>
							</div>

							<div id="calendar" name="calendar" style="margin-top:-50px;"></div>
						</div>
					</section>
					<!-- カレンダー表示 -->
					<?php echo $calendar; ?>
				</div>
			</div>
		</div>
	</section>
