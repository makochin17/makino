<?php // echo Asset::js('fullcalendar/index.global.js'); ?>

		<script>
			var calendar20;
			document.addEventListener('DOMContentLoaded', function() {
				var calendarEl = document.getElementById('calendar');
				var calendarHeight = window.innerHeight - 330;
				if (calendarHeight > 1300) {
					calendarHeight = 1300;
				};
				if (calendarHeight < 600) {
					calendarHeight = 600;
				};
				// var calendar20 = new FullCalendar.Calendar(calendarEl, {
				calendar20 = new FullCalendar.Calendar(calendarEl, {
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
						<?php if (!empty($unit_list)) : ?>
							<?php foreach ($unit_list as $unit_id => $unit_name) : ?>
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
								<?php $customer_cd 			= (!empty($val["customer_code"])) ? "[".$val["customer_code"]."]":'[新]'; ?>
								// メモ
								<?php $memo 				= (!empty($val["memo"])) ? $val["memo"]:''; ?>
								// 確定フラグ
								<?php $commit 				= (!empty($val["commit"]) && $val["commit"] == "1") ? '〇':''; ?>
								// 背景色
								<?php if (!empty($val["cancel"]) && $val["cancel"] == "1") : ?>
									<?php $back_color 		= ", color: '#DDDDDD'"; ?>
								<?php else: ?>
									<?php if (!empty($val["back_color"])) : ?>
										<?php $back_color 	= ", color: '".$val["back_color"]."'"; ?>
									<?php else: ?>
										<?php $back_color 	= ", color: '#FFFFFF'"; ?>
									<?php endif; ?>
								<?php endif; ?>
								// 文字色
								<?php $for_color 			= ", textColor: '#000000'"; ?>
								<?php if (!empty($val["memo"])) : ?>
									<?php $for_color 		= ", textColor: '#FF0000'"; ?>
								<?php else: ?>
									<?php if (!empty($val["cancel"]) && $val["cancel"] == "1") : ?>
										<?php $for_color 	= ", textColor: '#000000'"; ?>
									<?php else: ?>
										<?php if (!empty($val["fore_color"])) : ?>
											<?php $for_color = ", textColor: '".$row["fore_color"]."'"; ?>
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
								// 予約イベントを設定
								{
									id: 		'<?php echo $val['id']; ?>',
									resourceId: '<?php echo $val['unit_id']; ?>',
									start: 		'<?php echo $start_datetime; ?>',
									end: 		'<?php echo $end_datetime; ?>',
									title: 		'<?php echo $commit.$customer_cd.$val['customer_name']."[".$val["car_code"]."] "."[".$val["car_name"]."] ".$memo; ?>',
									color: 		'<?php echo $back_color; ?>',
									textColor: 	'<?php echo $for_color; ?>'
								},
							<?php endforeach; ?>
						<?php endif; ?>
					],

					select: function(info) {

						$("[id=id_error]").empty();
						$("[id=unit_error]").empty();
						$("[id=menu_error]").empty();

						$("[id=txtEventId]").val("");
						$("[id=txtPatientCd]").val("");
						$("[id=txtPatientNm]").val("");

						// $("[id=cboClassCd]").val(0);
						// $("[id=cboClassCd2]").val(0);
						// $("[id=cboClassCd3]").val(0);
						// $("[id=cboMenuCd]").val(0);
						// $("[id=cboMenuCd2]").val(0);
						// $("[id=cboMenuCd3]").val(0);
						$("[id=txtMemo]").val("");

						var str = info.startStr;
						var res = str.split('T');

						$("[id=txtReservDay]").val(res[0]);

						res = res[1].split('+');
						var time = res[0].split(':');

						$("[id=cboHourS]").val(parseInt(time[0]));
						$("[id=cboTimeS]").val(time[1]);

						str = info.endStr;
						res = str.split('T');
						res = res[1].split('+');
						time = res[0].split(':');

						$("[id=cboHourE]").val(parseInt(time[0]));
						$("[id=cboTimeE]").val(time[1]);

						var resource_id = info.resource.id;
						$("[id=cboUnitCd]").val(resource_id);

						// document.getElementById("chkFinish").checked = false;
						// document.getElementById("chkCancel2").checked = false;
						// document.getElementById("chkTelWait1").checked = false;
						// document.getElementById("chkTelWait2").checked = false;
						// document.getElementById("chkTelWait3").checked = false;
						// document.getElementById("chkTelWait").checked = false;

						$('[id=cmdCancel]').css('display', 'none');
						$('[id=cmdCommit]').css('display', 'none');

						$('[id=dialog_button]')[0].click();
						$('[id=txtPatientCd]').focus();
					},

					eventClick: function(info) {

					// alert('id: ' + info.event.id);
					// alert('Event: ' + info.event.title);
					// alert('Coordinates: ' + info.jsEvent.pageX + ',' + info.jsEvent.pageY);
					// alert('View: ' + info.view.type);

						$("[id=id_error]").empty();
						$("[id=unit_error]").empty();
						$("[id=menu_error]").empty();

						$("[id=txtPatientCd]").val("");
						// $("[id=cboClassCd]").val(0);
						// $("[id=cboClassCd2]").val(0);
						// $("[id=cboClassCd3]").val(0);
						// $("[id=cboMenuCd]").val(0);
						// $("[id=cboMenuCd2]").val(0);
						// $("[id=cboMenuCd3]").val(0);
						$("[id=txtMemo]").val("");
						$("[id=cboNextContact]").val(0);

						var event_id = info.event.id;
						$("[id=txtEventId]").val(event_id);

						// 予約がぞんざいしない場合は処理しない
						if (event_id == "") {return; }

						var postData = {"seq":event_id};
						$.post(
						     "./get_event_info.php",
						     postData,
						     function(xml){
					// alert($(xml));
								$(xml).find("item]").each(function(){

									if ($(this).find("seq]").text() == "0") {

										$("[id=id_error]").text("該当する予約情報は見つかりませんでした");
										// $("[id=txtPatientCd]").val("");
										// $("[id=txtPatientNm]").val("");
										// $('#y_data_area').css('display', 'none');
									} else {


										$("[id=txtReservDay]").val($(this).find("start_date]").text());

										var start_time = $(this).find("start_time]").text();
										time = start_time.split(':');

										$("[id=cboHourS]").val(parseInt(time[0]));
										$("[id=cboTimeS]").val(time[1]);

										var end_time = $(this).find("end_time]").text();
										time = end_time.split(':');

										$("[id=cboHourE]").val(parseInt(time[0]));
										$("[id=cboTimeE]").val(time[1]);

										$("[id=txtPatientCd]").val($(this).find("patient_cd").text());
										$("[id=txtPatientNm]").val($(this).find("patient_nm").text());
										$("[id=cboUnitCd]").val($(this).find("unit_cd").text());
										// $("[id=cboClassCd]").val($(this).find("class_cd").text());
										// $("[id=cboClassCd2]").val($(this).find("class_cd2").text());
										// $("[id=cboClassCd3]").val($(this).find("class_cd3").text());
										$("[id=cboNextContact]").val($(this).find("next_contact").text());


										if ($(this).find("patient_cd").text() == "") {
											var obj = document.getElementById("chkNew");
											obj.checked = true;
										} else {
											var obj = document.getElementById("chkNew");
											obj.checked = false;
										}

										if ($(this).find("cancel").text() == "1") {
											var obj = document.getElementById("chkCancel");
											obj.checked = true;
										} else {
											var obj = document.getElementById("chkCancel");
											obj.checked = false;
										}

										if ($(this).find("unauthorized").text() == "1") {
											var obj = document.getElementById("chkCancel2");
											obj.checked = true;
										} else {
											var obj = document.getElementById("chkCancel2");
											obj.checked = false;
										}

										if ($(this).find("tel_wait").text() == "1") {
											var obj = document.getElementById("chkTelWait");
											obj.checked = true;
										} else {
											var obj = document.getElementById("chkTelWait");
											obj.checked = false;
										}

										if ($(this).find("tel_wait1").text() == "1") {
											var obj = document.getElementById("chkTelWait1");
											obj.checked = true;
										} else {
											var obj = document.getElementById("chkTelWait1");
											obj.checked = false;
										}

										if ($(this).find("tel_wait2").text() == "1") {
											var obj = document.getElementById("chkTelWait2");
											obj.checked = true;
										} else {
											var obj = document.getElementById("chkTelWait2");
											obj.checked = false;
										}

										if ($(this).find("tel_wait3").text() == "1") {
											var obj = document.getElementById("chkTelWait3");
											obj.checked = true;
										} else {
											var obj = document.getElementById("chkTelWait3");
											obj.checked = false;
										}

										if ($(this).find("patient_cd").text() == "") {
											//$('#chkNew').prop('checked',true);
											$('[id=txtPatientCd]').attr('readonly',true);
											$('[id=txtPatientCd]').css('background-color', '#EEE');
											$('[id=txtPatientNm]').attr('readonly',false);
											$('[id=txtPatientNm]').css('background-color', '#FFF');
										}


										//選択されたメニュー分類のvalueを取得し変数に入れる
										var val1 = $(this).find("class_cd").text();

										//削除された要素をもとに戻すため.html(original)を入れておく
										$children.html(original).find('option').each(function() {

											var val2 = $("[id=cboClassCd]").val(); 

											var val = val2.split('-');
											var val3 = val[0];

											//valueと異なるdata-valを持つ要素を削除
											if (val1 != val3) {
												$("[id=cboClassCd]").not(':first-child').remove();
											}

										});


										//選択されたメニュー分類のvalueを取得し変数に入れる
										var val1 = $(this).find("class_cd2").text();

										//削除された要素をもとに戻すため.html(original)を入れておく
										$children2.html(original2).find('option').each(function() {

											var val2 = $("[id=cboClassCd2]").val(); 

											var val = val2.split('-');
											var val3 = val[0];

											//valueと異なるdata-valを持つ要素を削除
											if (val1 != val3) {
												$("[id=cboClassCd2]").not(':first-child').remove();
											}

										});


										//選択されたメニュー分類のvalueを取得し変数に入れる
										var val1 = $(this).find("class_cd3").text();

										//削除された要素をもとに戻すため.html(original)を入れておく
										$children3.html(original3).find('option').each(function() {

											var val2 = $("[id=cboClassCd3]").val(); 

											var val = val2.split('-');
											var val3 = val[0];

											//valueと異なるdata-valを持つ要素を削除
											if (val1 != val3) {
												$("[id=cboClassCd3]").not(':first-child').remove();
											}

										});

										//親のselect要素が未選択の場合、子をdisabledにする
										if ($("[id=cboClassCd]").val() == "") {
											$children.attr('disabled', 'disabled');
										} else {
											$children.removeAttr('disabled');
										}

										//親のselect要素が未選択の場合、子をdisabledにする
										if ($("[id=cboClassCd2]").val() == "") {
											$children2.attr('disabled', 'disabled');
										} else {
											$children2.removeAttr('disabled');
										}

										//親のselect要素が未選択の場合、子をdisabledにする
										if ($("[id=cboClassCd3]").val() == "") {
											$children3.attr('disabled', 'disabled');
										} else {
											$children3.removeAttr('disabled');
										}

										$("[id=cboMenuCd]").val($(this).find("menu_cd").text());
										$("[id=cboMenuCd2]").val($(this).find("menu_cd2").text());
										$("[id=cboMenuCd3]").val($(this).find("menu_cd3").text());
										$("[id=txtMemo]").val($(this).find("memo").text());
									}
									// message = $(this).find("return]").text();
								});
						     }
						);

						$('[id=cmdCancel]').css('display', 'inline');
						$('[id=cmdCommit]').css('display', 'inline');

						$('[id=dialog_button]')[0].click();

						// change the border color just for fun
						//info.el.style.borderColor = 'red';
					},

					// イベントをドラッグして時間を減らした増やした時のイベント
					eventResize : function(info) {

						//alert('id: ' + info.event.id);

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
						var unit_cd = "";

						var postData = {"seq":seq,"unit_cd":unit_cd,"date_fr":date_fr,"time_fr":time_fr,"date_to":date_to,"time_to":time_to};
						$.post(
						     "./change_date_schedule.php",
						     postData,
						     function(xml){
					// alert(xml);
								$(xml).find("item").each(function(){
									// message = $(this).find("return]").text();
								});
						     }
						);
					},

					// イベントをドラッグして別日に移動させた時のイベント
					eventDrop: function(info) {

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
						var unit_cd = "";
						if (info.newResource == null) {

						} else {
							unit_cd = info.newResource.id;
						}

						var postData = {"seq":seq,"unit_cd":unit_cd,"date_fr":date_fr,"time_fr":time_fr,"date_to":date_to,"time_to":time_to};
						$.post(
						     "./change_date_schedule.php",
						     postData,
						     function(xml){
					// alert(xml);
								$(xml).find("item]").each(function(){
									// message = $(this).find("return]").text();
								});
						     }
						);

					},

					// 日付クリック時処理（いらないかも）→要ります
					dateClick: function(info) {
						// alert('clicked ' + info.dateStr);
					}

				});

				calendar20.render();
			});
		</script>

		<script type="text/javascript">

			function get_patient_nm(){

				var patient_cd = $("[id=txtPatientCd]").val();

				if (patient_cd == "") {
					alert("検索条件を入力してください");
					$("[id=txtPatientCd]").focus();
					return false;
				}

				var postData = {"patient_cd":patient_cd};
				$.post(
				     "./get_patient_cd.php",
				     postData,
				     function(xml){
			// alert($(xml));
						$(xml).find("item]").each(function(){

							if ($(this).find("patient_cnt").text() == "0") {

								$("[id=id_error]").text("該当する患者IDは見つかりませんでした("+$("[id=txtPatientCd]").val()+")");
								$("[id=txtPatientCd]").val("");
								$("[id=txtPatientNm]").val("");
								$('#y_data_area').css('display', 'none');
							} else {

								if ($(this).find("patient_cnt").text() == "1") {
									$("[id=txtPatientCd]").val($(this).find("patient_cd").text());
									$("[id=txtPatientNm]").val($(this).find("patient_nm").text());
									$("[id=id_error]").empty();
									$('[id=y_data_area]').css('display', 'none');
								} else {

									$('[id=y_data_area]').css('display', 'block');
									$("[id=txtPatientNm]").val("");
									$("[id=id_error]").empty();

									var item_cnt = $(this).find("patient_cnt").text();

									var code = $(this).find("patient_cd").text();
									var name = $(this).find("patient_nm").text();

									var code_list = code.split(',');
									var name_list = name.split(',');

									$("[id=tbl1 tr]").remove();

									for (var i = 0; i < item_cnt; i++){
										$('[id=tbl1]').append("<tr><td style=\"text-align:center;\"><a href=\"#\" style=\"border:none;\" onclick=\"set_patient('"+code_list[i]+"','"+name_list[i]+"');\" ><i class='fa fa-check' style='font-size:24px;'></i></a></td><td>"+code_list[i]+"</td><td>"+name_list[i]+"</td></tr>");
									}
								}
							}

							// message = $(this).find("return]").text();

						});
				     }
				);
			}

			function set_patient(code,name) {
				$("[id=txtPatientCd]").val(code);
				$("[id=txtPatientNm]").val(name);
				$("[id=id_error]").empty();
				$('[id=y_data_area]').css('display', 'none');
			}

			function add_new(){
				var check = $("[id=chkNew]").is(':checked');

				if (check == true) {

					$('[id=txtPatientCd]').attr('readonly',true);
					$('[id=txtPatientCd]').css('background-color', '#EEE');
					$('[id=txtPatientNm]').attr('readonly',false);
					$('[id=txtPatientNm]').css('background-color', '#FFF');

				} else {

					$('[id=txtPatientCd]').attr('readonly',false);
					$('[id=txtPatientCd]').css('background-color', '#FFF');
					$('[id=txtPatientNm]').attr('readonly',true);
					$('[id=txtPatientNm]').css('background-color', '#EEE');

				}
			}

		    function update_check() {
				var result = true;

				var reserv_day = $("[id=txtReservDay]").val();
				var hour_fr = $("[id=cboHourS]").val();
				var min_fr  = $("[id=cboTimeS]").val();
				var hour_to = $("[id=cboHourE]").val();
				var min_to  = $("[id=cboTimeE]").val();

				var patient_id  = $("[id=txtPatientCd]").val();
				var patient_nm  = $("[id=txtPatientNm]").val();
				var menu_cd     = $("[id=cboMenuCd]").val();
				var unit_cd     = $("[id=cboUnitCd]").val();

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

				// 患者ID、名前
				if (check == true) {
					if(patient_nm == ""){
						$("[id=id_error]").html("<i class='fa fa-exclamation-circle'></i> 患者名は必須です。");
						$("[id=txtPatientNm]").addClass("inp_error");
						result = false;
					}else if(patient_nm.length > 20){
						$("[id=id_error]").html("<i class='fa fa-exclamation-circle'></i> 患者名は20文字以内で入力してください。");
						$("[id=txtPatientNm]").addClass("inp_error");
						result = false;
					}
				}else{
					if(patient_id == ""){
						$("[id=id_error]").html("<i class='fa fa-exclamation-circle'></i> 患者IDは必須です。");
						$("[id=txtPatientCd]").addClass("inp_error");
						result = false;
					}else if(patient_id.length > 10){
						$("[id=id_error]").html("<i class='fa fa-exclamation-circle'></i> 患者IDは10文字以内で入力してください。");
						$("[id=txtPatientCd]").addClass("inp_error");
						result = false;
					}
				}

				// ユニット
				if(unit_cd == ""){
				    $("[id=unit_error]").html("<i class='fa fa-exclamation-circle'></i> ユニットを選択してください。");
				    $("[id=cboUnitCd]").addClass("inp_error");
				    result = false;
				}

				// 診療内容
				// if(menu_cd == ""){
				//     $("[id=menu_error]").html("<i class='fa fa-exclamation-circle'></i> 診療内容を選択してください。");
				//     $("[id=cboMenuCd]").addClass("inp_error");
				//     result = false;
				// }


				if (result == false) {return false;}

				// return confirm("診療メニュー情報を更新します。\nよろしいですか？");
				return true;
			}

			// 予約登録
			function schedule_update() {

				if (update_check() == false ){return false; }

				var seq          = $("[id=txtEventId]").val();
				var reserv_day   = $("[id=txtReservDay]").val();
				var hour_fr      = $("[id=cboHourS]").val();
				var min_fr       = $("[id=cboTimeS]").val();
				var hour_to      = $("[id=cboHourE]").val();
				var min_to       = $("[id=cboTimeE]").val();

				var patient_cd   = $("[id=txtPatientCd]").val();
				var patient_nm   = $("[id=txtPatientNm]").val();
				var menu_cd      = $("[id=cboMenuCd]").val();
				var menu_cd2     = $("[id=cboMenuCd2]").val();
				var menu_cd3     = $("[id=cboMenuCd3]").val();
				var unit_cd      = $("[id=cboUnitCd]").val();
				var memo         = $("[id=txtMemo]").val();
				var next_contact = $("[id=cboNextContact]").val();

				var cancel = "";
				if (document.getElementById("chkCancel").checked) {
					cancel = "1";
				} else {
					cancel = "0";
				}

				var finished = "";
				if (document.getElementById("chkFinish").checked) {
					finished = "1";
				} else {
					finished = "0";
				}

				var unauthorized = "";
				if (document.getElementById("chkCancel2").checked) {
					unauthorized = "1";
				} else {
					unauthorized = "0";
				}

				var tel_wait = "";
				if (document.getElementById("chkTelWait").checked) {
					tel_wait = "1";
				} else {
					tel_wait = "0";
				}

				var tel_wait1 = "";
				if (document.getElementById("chkTelWait1").checked) {
					tel_wait1 = "1";
				} else {
					tel_wait1 = "0";
				}

				var tel_wait2 = "";
				if (document.getElementById("chkTelWait2").checked) {
					tel_wait2 = "1";
				} else {
					tel_wait2 = "0";
				}

				var tel_wait3 = "";
				if (document.getElementById("chkTelWait3").checked) {
					tel_wait3 = "1";
				} else {
					tel_wait3 = "0";
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

				var postData = {"seq":seq,"date_fr":date_fr,"time_fr":time_fr,"date_to":date_to,"time_to":time_to,"patient_cd":patient_cd,"patient_nm":patient_nm,"unit_cd":unit_cd,"menu_cd":menu_cd,"menu_cd2":menu_cd2,"menu_cd3":menu_cd3,"memo":memo,"cancel":cancel,"unauthorized":unauthorized,"finished":finished,"tel_wait":tel_wait,"tel_wait1":tel_wait1,"tel_wait2":tel_wait2,"tel_wait3":tel_wait3,"next_contact":next_contact,"title":"abcz"};
			//try {
				$.post(
				     "./add_schedule.php",
				     postData,
				     function(xml){
			// alert(xml);
						$(xml).find("item]").each(function(){

							$("[id=txtEventId]").val("");
							$("[id=txtPatientCd]").val("");
							$("[id=txtPatientNm]").val("");

							var new_seq    = $(this).find("new_seq").text();
							var menu_nm1   = $(this).find("menu_nm1").text();
							var menu_nm2   = $(this).find("menu_nm2").text();
							var menu_nm3   = $(this).find("menu_nm3").text();
							var cancel     = $(this).find("cancel").text();
							var commit     = $(this).find("commit").text();
							var back_color = $(this).find("back_color").text();
							var fore_color = $(this).find("fore_color").text();
							var message    = $(this).find("return").text();
			//alert(new_seq);
			//alert(message);

							if (message == "") {

								var datetime_fr = new Date(date_fr + 'T' + time_fr + ':00');
								var datetime_to = new Date(date_fr + 'T' + time_to + ':00');

								var title = "";
								if (commit == "1") {
									title = title + "〇";
								}
								if (patient_cd == "") {
									title = title + "[新]";
								} else {
									title = title + "[" + patient_cd + "]";
								}

								title = title + patient_nm;

								if (menu_nm1 != "") {
									title = title + "["+menu_nm1+"]";
								}
								if (menu_nm2 != "") {
									title = title + "["+menu_nm2+"]";
								}
								if (menu_nm3 != "") {
									title = title + "["+menu_nm3+"]";
								}
								if (memo != "") {
									title = title + memo;
								}

								if (memo != "") {
									fore_color = "#FF0000";
								} else {
									if (cancel == "1") {
										fore_color = "#000000";
									}
								}

								if (cancel == "1") {
									back_color = "#DDDDDD";
								}

								if (seq == "") {
									//イベントを新規登録
									calendar20.addEvent({
										 id        : new_seq
										,resourceId: unit_cd
										,start     : datetime_fr
										,end       : datetime_to
										,title     : title
										,allDay    : false
										,color     : back_color
										,textColor : fore_color
									});
								} else {
									//イベントを編集

									//イベントオブジェクトを取得
									var event = calendar20.getEventById(seq);
									event.remove();

									// event.setStart(datetime_fr);
									// event.setEnd(datetime_to);

									calendar20.addEvent({
										 id        : seq
										,resourceId: unit_cd
										,start     : datetime_fr
										,end       : datetime_to
										,title     : title
										,allDay    : false
										,color     : back_color
										,textColor : fore_color
									});

								}

							} else {
								alert(message);
							}

						});
				     }
				);
			//} catch(e) {
			//alert( e.message );
			//}
			//alert("b");

				$("[id=[id=dialog]]").jqmHide();
				return false;
			}

			function add_lunch_break(target_day) {

		//alert(target_day);
		//alert(target_day+'T12:30');
				calendar20.addEvent({
					 id        : 'bk01'
					,rendering : 'background'
					// ,resourceId: '0003'
					,start     : target_day+'T12:30'
					,end       : target_day+'T14:00'
					// ,title     : 'あああ'
					// ,allDay    : false
					// ,color     : back_color
					// ,textColor : fore_color
				});

			}

			// 予約取消
			function schedule_cancel() {

				if (confirm('この予約を取り消します。\nよろしいですか？') == false ){return false; }

				var seq = $("[id=txtEventId]").val();
				var patient_cd  = $("[id=txtPatientCd]").val();

				var postData = {"seq":seq,"patient_cd":patient_cd};
				$.post(
				     "./cancel_schedule.php",
				     postData,
				     function(xml){
						$(xml).find("item]").each(function(){

							$("[id=txtEventId]").val("");
							$("[id=txtPatientCd]").val("");
							$("[id=txtPatientNm]").val("");

							// message = $(this).find("return]").text();

							//イベントオブジェクトを取得
							var event = calendar20.getEventById(seq);
							event.remove();

						});
				     }
				);

				$("[id=dialog]").jqmHide();
				return false;
			}

			function schedule_commit() {

				if (confirm('処置を完了します。\nよろしいですか？') == false ){return false; }

				var seq = $("[id=txtEventId]").val();

				var postData = {"seq":seq,"state":"1"};
				$.post(
				     "./commit_schedule.php",
				     postData,
				     function(xml){
						$(xml).find("item]").each(function(){

							$("[id=txtEventId]").val("");
							$("[id=txtPatientCd]").val("");
							$("[id=txtPatientNm]").val("");

							// message = $(this).find("return]").text();

							//イベントオブジェクトを取得
							// var event = calendar20.getEventById(seq);
							// event.remove();

						});
				     }
				);

				$("[id=dialog]").jqmHide();
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
			div.y_data_area {

			  width: 600px;
			  margin-left:95px;
			  border-right: 1px solid #CCC;
			  border-bottom: 1px solid #CCC;
			  border-left: 1px solid #CCC;
			}

			/* タイトル部分 */
			table.y_data_title {
			  width: 600px;
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
			  width: 600px;
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
			  width: 600px;
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

		<!-- Wrapper -->
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
	                        <?php echo Form::select('cboUnit', (!empty($unit_cd)) ? $unit_cd:'', $unit_list, array('class' => 'select-item', 'id' => 'cboUser', 'style' => 'width: 200px')); ?>
        					<?php echo Form::submit('search', '表示', array('class' => 'buttonB', 'style' => 'margin-right: 20px;')); ?>
						</div>
						<div style="display:inline;float:right;" class="s_form" >
                    		<?php echo Form::input('txtCustomer', (!empty($data['customer_name'])) ? $data['customer_name']:'', array('class' => 'input-text', 'type' => 'text', 'id' => 'txtCustomer', 'style' => 'width:200px;', 'maxlength' => '50')); ?>
	                        <input type="button" name="search_button1" value="" class='iframe' id="search_button1" style="cursor:pointer;display:inline;color:red;" onclick="onClientSearch('<?php echo Uri::create('search/s0010'); ?>', 0)" />
    						<span class="icon fa-search" style="font-size:21px;margin-left:5px;"></span>
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
							・<?php $val['title']; ?><br />
						<?php endforeach; ?>
					<?php endif; ?>
					<?php ($cnt > 0) ? '</div>':''; ?>
					<section id="banner_section" style="padding-top:10px;">
						<div class="content" style="margin-top:0px;">
							<div style="margin:0px;">
								<!-- <a href="./corp_edit.php" class="button" style=""> ＞ 新しい会社を追加</a> -->
								<a href="#" id="dialog_button" class="button jqModal" style="margin:0px;padding:0px;"></a>
								<div class="jqmWindow" id="dialog" style="width:800px;">
									<a id="close_button" href="#" class="jqmClose" id="CancelButton" style="border:none;margin-left:730px;" ><img src="../images/close.png" /></a>
									<div style="font-size:20px;font-weight:bold;margin:-10px 0px 0px 20px;">予約登録</div>
									<form id="yoyakuForm" enctype="" method="post" action="">
										<fieldset>
											<div style="margin:20px 0px 0px 0px;">
												日付
												<input type="date" id="txtReservDay" name="txtReservDay" class="text" tabindex="1" value="" style="width:200px;" />
												<input type="hidden" id="txtEventId" name="txtEventId"  style="width:80px;" />
												<input type="checkbox" id="chkCancel" name="chkCancel" class ="text" style="vertical-align:middle;" value="1" tabindex="3" />
												<label for="chkCancel" style="display:inline;margin-left:10px;"> <span style="">&nbsp;キャンセル&nbsp;</span></label>
												<span id="date_error" class="error_m"></span>
											</div>
											<div style="margin:10px 0px 0px 0px;">
												時間
												<select id="cboHourS" name="cboHourS" tabindex="2" style="width:100px;">
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
							                        <?php echo Form::select('cboTimeS', (!empty($start_time)) ? $start_time:'', $select_work_time_list, array('class' => 'select-item', 'id' => 'cboTimeS', 'style' => 'width: 100px', 'tabindex' => '3')); ?>
												<?php endif; ?>
												～
												<select id="cboTimeE" name="cboTimeE" tabindex="5" style="width:100px;">
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
							                        <?php echo Form::select('cboTimeE', (!empty($end_time)) ? $end_time:'', $select_work_time_list, array('class' => 'select-item', 'id' => 'cboTimeE', 'style' => 'width: 100px', 'tabindex' => '3')); ?>
												<?php endif; ?>
												<span id="time_error" class="error_m"></span>
											</div>
											<div style="margin:20px 0px 0px 0px;">
												患者ID
												<input type="text" id="txtPatientCd" name="txtPatientCd" class="text" value="" tabindex="6" style="width:100px;" />
												<input type="button" id="cmdPatientCd" name="cmdPatientCd" class="text" value="検索" tabindex="7" style="width:100px;" onclick="get_patient_nm();" />
												<!-- <span id="txtPatientNm" name="txtPatientNm" ></span> -->
												<input type="text" id="txtPatientNm" name="txtPatientNm" style="background-color:#EEE;width:200px;" readonly />
												<input type="checkbox" id="chkNew" name="chkNew" class ="text" style="vertical-align:middle;" value="1" tabindex="3" onclick="add_new();" />
												<label for="chkNew" style="display:inline;margin-left:10px;"> <span style="">&nbsp;新規&nbsp;</span></label>
												<span id="id_error" class="error_m"></span>
												<div id="y_data_area" class="y_data_area" style="display:none;">
													<table class="y_data_title">
														<col style="width: 100px;" />
														<col style="width: 100px;" />
														<col style="" />
														<tr>
															<th style="text-align:center;">選択</th>
															<th>患者ID</th>
															<th>お名前</th>
														</tr>
													</table>
													<div class="y_scroll_box">
														<div class="y_hidden">
															<table id="tbl1" class="y_data">
																<col style="width:100px;" />
																<col style="width:100px;" />
																<col style="" />
																<!-- 
																<tr>
																    <td><p title="データ"><input type="button" value="選択" /></p></td>
																    <td><p title="データ">データデータデータデータデータデータデータデータデータ</p></td>
																    <td class="r_none"><p title="データ">データ</p></td>
																</tr>
																<tr>
																    <td><p title="データ"><input type="button" value="選択" /></p></td>
																    <td><p title="データ">データ</p></td>
																    <td class="r_none"><p title="データ">データ</p></td>
																</tr>
																 -->
															</table>
														</div>
													</div>
												</div>
											</div>
											<div style="margin:10px 0px 0px 0px;">
												ユニット
												<select id="cboUnitCd" name="cboUnitCd" tabindex="9" class="" style="width:180px;" >
													<option value=""></option>
												</select>
												<span id="unit_error" class="error_m"></span>
											</div>
											<div style="margin:10px 0px 0px 0px;">
												診療内容
												<select id="cboClassCd" name="cboClassCd" tabindex="8" class="parent" style="width:180px;" >
													<option value=""></option>
												</select>
												<select id="cboMenuCd" name="cboMenuCd" tabindex="9" class="children" style="width:200px;">
													<option value=""></option>
												</select>
												<span id="menu_error" class="error_m"></span>
												<br />
												<br />
												<br />
											</div>
											<div style="margin:10px 0px 0px 0px;">
												メモ
												<textarea id="txtMemo" name="txtMemo" style="width:600px;height:80px;" maxlength="1000" tabindex="17" wrap="soft"></textarea>
											</div>
											<br />
											<span id="unit_error" class="error_m"></span>
											<br />
											<br />
											<input type="button" id="button"    name="search"    class="button" value="登録" tabindex="20" onclick="return schedule_update();" style="width:130px;margin-top:-9px;" />
											<input type="submit" id="button" name="cmdCancel" class="button" value="予約取消" tabindex="20" onclick="return schedule_cancel();" style="width:130px;margin-top:-9px;" />
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

		<script type="text/javascript">
			$(function(){
				$('[id=dialog]').jqm();
			});
		</script>

		<script type="text/javascript">

			var $children = $('[class=children]'); //子の要素を変数に入れます。
			var original = $children.html(); //後のイベントで、不要なoption要素を削除するため、オリジナルをとっておく

			$(function(){

				//親側のselect要素が変更になるとイベントが発生
				$('[class=parent]').change(function() {

					//選択された店舗グループのvalueを取得し変数に入れる
					var val1 = $(this).val();

					//現時点の中分類の選択値を取得しておく
					// var current_val = $('[name="cboClassM"] option:selected').val();

					//削除された要素をもとに戻すため.html(original)を入れておく
					$children.html(original).find('option').each(function() {

						var val2 = $(this).val(); 

						var val = val2.split('-');
						var val3 = val[0];

						//valueと異なるdata-valを持つ要素を削除
						if (val1 != val3) {
							$(this).not(':first-child').remove();
						}

					});

					//親のselect要素が未選択の場合、子をdisabledにする
					if ($(this).val() == "") {
						$children.attr('disabled', 'disabled');
					} else {
						$children.removeAttr('disabled');
					}
				}).change();
			});

			var $children2 = $('[class=children2]'); //子の要素を変数に入れます。
			var original2 = $children2.html(); //後のイベントで、不要なoption要素を削除するため、オリジナルをとっておく

			$(function(){

				//親側のselect要素が変更になるとイベントが発生
				$('[class=parent2]').change(function() {

					//選択された店舗グループのvalueを取得し変数に入れる
					var val1 = $(this).val();

					//現時点の中分類の選択値を取得しておく
					// var current_val = $('[name="cboClassM"] option:selected').val();

					//削除された要素をもとに戻すため.html(original)を入れておく
					$children2.html(original2).find('option').each(function() {

						var val2 = $(this).val(); 

						var val = val2.split('-');
						var val3 = val[0];

						//valueと異なるdata-valを持つ要素を削除
						if (val1 != val3) {
							$(this).not(':first-child').remove();
						}

					});

					//親のselect要素が未選択の場合、子をdisabledにする
					if ($(this).val() == "") {
						$children2.attr('disabled', 'disabled');
					} else {
						$children2.removeAttr('disabled');
					}
				}).change();
			});

			var $children3 = $('[class=children3]'); //子の要素を変数に入れます。
			var original3 = $children3.html(); //後のイベントで、不要なoption要素を削除するため、オリジナルをとっておく

			$(function(){

				//親側のselect要素が変更になるとイベントが発生
				$('[class=parent3]').change(function() {

					//選択された店舗グループのvalueを取得し変数に入れる
					var val1 = $(this).val();

					//現時点の中分類の選択値を取得しておく
					// var current_val = $('[name="cboClassM"] option:selected').val();

					//削除された要素をもとに戻すため.html(original)を入れておく
					$children3.html(original3).find('option').each(function() {

						var val2 = $(this).val(); 

						var val = val2.split('-');
						var val3 = val[0];

						//valueと異なるdata-valを持つ要素を削除
						if (val1 != val3) {
							$(this).not(':first-child').remove();
						}

					});

					//親のselect要素が未選択の場合、子をdisabledにする
					if ($(this).val() == "") {
						$children3.attr('disabled', 'disabled');
					} else {
						$children3.removeAttr('disabled');
					}
				}).change();
			});

		</script>

		<!-- ▼ColorboxのCSSを読み込む記述 -->
		<!-- <link href="../css/colorbox/colorbox.css" rel="stylesheet" /> -->
        <?php echo Asset::css('schedule/colorbox/colorbox.css', array('rel' => 'stylesheet'));?>

		<!-- ▼jQueryとColorboxのスクリプトを読み込む記述 -->
        <?php echo Asset::js('fullcalendar/jquery.colorbox-min.js');?>
        <?php echo Asset::js('fullcalendar/jquery.colorbox-ja.js');?>

		<!-- ▼Colorboxの適用対象の指定とオプションの記述 -->
		<script>
		   $(document).ready(function(){
		      $(".iframe").colorbox({iframe:true, width:"80%", height:"90%"});
		   });
		</script>
	</section>
