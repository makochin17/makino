// Enterキーによるsubmit無効化
document.onkeypress = enter;
function enter(){
    if( window.event.keyCode == 13 ){
        return false;
    }
}

$(function(){

    // 配車表出力
    $('#output').click(function(e){

        $('#output_dl').val(1);
        $('#excel_dl').val(0);
        $('#entryForm').submit();

        return e.preventDefault();
    });
    // エクセル出力
    $('#excel').click(function(e){

        $('#excel_dl').val(1);
        $('#output_dl').val(0);
        $('#entryForm').submit();

        return e.preventDefault();
    });

    // 分載照会モーダルキャンセル処理
    $('#carrying_modal_close').click(function(e){
        $('#carrying_modal').fadeOut();
        return e.preventDefault();
    });

    // 分載照会モーダル処理
    $('button[id^=carryingcharter]').click(function(e){

        var id          = $(this).data('id');
        var html_area   = $('#carrying_area');

        // プルダウン配列
        var product     = JSON.parse(product_list);
        var processing  = JSON.parse(processing_division_list);
        var division    = JSON.parse(division_list);
        var carmodel    = JSON.parse(car_model_list);
        var delivery    = JSON.parse(delivery_category_list);
        var tax         = JSON.parse(tax_category_list);

        // URL
        var url     = $('#carrying_url').val();
        // データ
        var data    = 'id='+id;

        $.ajax({
            type:       'POST',
            url:        url,
            data:       data,
            dataType:   'json'
        }).done(function(data) {
            $.each(data.dispatch, function(key, val){
                if (key == 'stack_date') {
                    var today   = new Date(val);
                    var year    = today.getFullYear();
                    var month   = today.getMonth() + 1;
                    var day     = today.getDate();
                    $('#dispatch_stack_date').text(year+'年'+month+'月'+day+'日');
                }
                if (key == 'drop_date') {
                    var today   = new Date(val);
                    var year    = today.getFullYear();
                    var month   = today.getMonth() + 1;
                    var day     = today.getDate();
                    $('#dispatch_drop_date').text(year+'年'+month+'月'+day+'日');
                }
                if (key == 'client_code') {
                    $('#dispatch_client_code').text(zeropadding(val, 5));
                }
                if (key == 'product_name') {
                    $('#dispatch_product_name').text(val);
                }
                if (key == 'car_model_code') {
                    $('#dispatch_car_model_code').text(carmodel[val]);
                }
                if (key == 'carrier_code') {
                    $('#dispatch_carrier_code').text(zeropadding(val, 5));
                }
                if (key == 'car_code') {
                    $('#dispatch_car_code').text(zeropadding(val, 4));
                }
                if (key == 'driver_name') {
                    $('#dispatch_driver_name').text(val);
                }
                if (key == 'delivery_category') {
                    $('#dispatch_delivery_category').text(delivery[val]);
                }
                if (key == 'tax_category') {
                    $('#dispatch_tax_category').text(tax[val]);
                }
                if (key == 'claim_sales') {
                    $('#dispatch_claim_sales').text(val.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, '$1,'));
                }
                if (key == 'carrier_payment') {
                    $('#dispatch_carrier_payment').text(val.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, '$1,'));
                }
                // if (key == 'highway_fee') {
                //     $('#dispatch_highway_fee').text(val.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, '$1,'));
                // }
                // if (key == 'highway_fee_claim') {
                //     var highway_fee_claim = '請求しない';
                //     if (val == '2') {
                //         var highway_fee_claim = '請求する';
                //     }
                //     $('#dispatch_highway_fee_claim').text(highway_fee_claim);
                // }
                if (key == 'driver_highway_fee') {
                    $('#dispatch_driver_highway_fee').text(val.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, '$1,'));
                }
                if (key == 'driver_highway_claim') {
                    var driver_highway_claim = '×';
                    if (val == '2') {
                        var driver_highway_claim = '○';
                    }
                    $('#dispatch_driver_highway_claim').text(driver_highway_claim);
                }
                if (key == 'stack_place') {
                    $('#dispatch_stack_place').text(val);
                }
                if (key == 'drop_place') {
                    $('#dispatch_drop_place').text(val);
                }
                if (key == 'client_name') {
                    $('#dispatch_client_name').text(val);
                }
                if (key == 'carrier_name') {
                    $('#dispatch_carrier_name').text(val);
                }
                if (key == 'claim_highway_fee') {
                    $('#dispatch_claim_highway_fee').text(val.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, '$1,'));
                }
                if (key == 'claim_highway_claim') {
                    var claim_highway_claim = '×';
                    if (val == '2') {
                        var claim_highway_claim = '○';
                    }
                    $('#dispatch_claim_highway_claim').text(claim_highway_claim);
                }
                if (key == 'carrier_highway_fee') {
                    $('#dispatch_carrier_highway_fee').text(val.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, '$1,'));
                }
                if (key == 'carrier_highway_claim') {
                    var carrier_highway_claim = '×';
                    if (val == '2') {
                        var carrier_highway_claim = '○';
                    }
                    $('#dispatch_carrier_highway_claim').text(carrier_highway_claim);
                }
            });

            // 分載エリア初期化
            var html_area_tr            = '';
            var claim_highway_claim     = '';
            var driver_highway_claim    = '';
            html_area.empty();
            $.each(data.carrying, function(idx, list){
                html_area_tr = html_area_tr + '<tr>';
                $.each(list, function(key, val){
                    switch(key) {
                        case 'stack_date':              // 積日
                        case 'drop_date':               // 降日
                            var today   = new Date(val);
                            var year    = today.getFullYear();
                            var month   = today.getMonth() + 1;
                            var day     = today.getDate();
                            html_area_tr = html_area_tr + '<td>'+year+'年'+month+'月'+day+'日</td>';
                            break;
                        case 'car_code':                // 車番
                            html_area_tr = html_area_tr + '<td>'+zeropadding(val, 4)+'</td>';
                            break;
                        case 'car_model_name':          // 車種
                        case 'driver_name':             // 運転手
                            html_area_tr = html_area_tr + '<td>'+val+'</td>';
                            break;
                        case 'client_code':             // 得意先No
                        case 'carrier_code':            // 傭車先No
                            html_area_tr = html_area_tr + '<td>'+zeropadding(val, 5)+'</td>';
                            break;
                        case 'claim_sales':             // 請求売上
                            html_area_tr = html_area_tr + '<td style="text-align: right;">'+val.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, '$1,')+'</td>';
                            break;
                        case 'claim_highway_claim':     // 売上請求フラグ
                            if (val == '2') {
                                claim_highway_claim = '○';
                            } else {
                                claim_highway_claim = '×';
                            }
                            break;
                        case 'driver_highway_claim':    // ドライバー請求フラグ
                            if (val == '2') {
                                driver_highway_claim = '○';
                            } else {
                                driver_highway_claim = '×';
                            }
                            break;
                        case 'claim_highway_fee':       // 請求高速料金
                            html_area_tr = html_area_tr + '<td style="text-align: right;">'+val.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, '$1,')+claim_highway_claim+'</td>';
                            break;
                        case 'driver_highway_fee':      // ドライバー高速料金
                            html_area_tr = html_area_tr + '<td rowspan="2" style="text-align: right;">'+val.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, '$1,')+driver_highway_claim+'</td>';
                            break;
                    }
                });
                html_area_tr = html_area_tr + '</tr>';
                html_area_tr = html_area_tr + '<tr>';
                $.each(list, function(key, val){
                    switch(key) {
                        case 'stack_place':     // 積地
                        case 'drop_place':      // 降地
                        case 'phone_number':    // 電話番号
                            html_area_tr = html_area_tr + '<td>'+val+'</td>';
                            break;
                        case 'client_name':     // 得意先
                        case 'carrier_name':    // 傭車先
                            html_area_tr = html_area_tr + '<td colspan="2">'+val+'</td>';
                            break;
                        case 'carrier_payment': // 傭車支払
                            html_area_tr = html_area_tr + '<td style="text-align: right;">'+val.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, '$1,')+'</td>';
                            break;
                        case 'carrier_highway_claim':     // 傭車請求フラグ
                            if (val == '2') {
                                carrier_highway_claim = '○';
                            } else {
                                carrier_highway_claim = '×';
                            }
                            break;
                        case 'carrier_highway_fee':       // 傭車高速料金
                            html_area_tr = html_area_tr + '<td style="text-align: right;">'+val.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, '$1,')+carrier_highway_claim+'</td>';
                            break;
                    }
                });
                html_area_tr = html_area_tr + '</tr>';
            });
            html_area.html(html_area_tr);
            $('#carrying_modal').fadeIn();
        }).fail(function(){
        }).always(function(){
        });

        return e.preventDefault();

    });

    var zeropadding = function(num, len) {
        return ('0000000000' + num).slice(-len);
    }

});

// 項目の活性状態制御
window.onload = change;
var flag = null;

// 動的にhidden追加
function make_hidden(name, value, formname) {
    var q = document.createElement('input');
    q.type = 'hidden';
    q.name = name;
    q.value = value;
    if (formname) {
    	if ( document.forms[formname] == undefined ){
    		console.error( "ERROR: form " + formname + " is not exists." );
    	}
    	document.forms[formname].appendChild(q);
    } else {
    	document.forms[0].appendChild(q);
    }
}

function change() {
    flag = (document.entryForm.division_code.value == '1') ? false : true;
    // document.entryForm.text_member_code.disabled = flag;
}

// 処理区分変更
function submitChengeProcessingDivision() {
    make_hidden('processing_division_clear', '1');
    document.entryForm.submit();
}

function submitChengeClear() {
	change();
    make_hidden('input_clear', '1');
    document.entryForm.submit();
}

// 「入力項目クリア」の確認ダイアログ表示
function submitChkClear(kind) {

	var flag = true;
	if (!kind) {
	    var flag = window.confirm (clear_msg);
	}
    return flag;
}

// 「確定」の確認ダイアログ表示
function submitChkExecution() {
    var value = document.entryForm.processing_division.value;
    var flag = false;

    // 処理区分によってメッセージを変える
    if (value == '1') {
        flag = window.confirm (processing_msg1);
    } else if(value == '2') {
        flag = window.confirm (processing_msg2);
    } else if(value == '3') {
        flag = window.confirm (processing_msg3);
    }

    return flag;
}

// 月極その他引用ボタン押下時処理
function onSalesCorrectionSearch(url_str, list) {

	var callback_id = 'callback_s0090';
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.entryForm.select_record.value = '1';
        document.entryForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=1200,height=900');
}

// 社員検索ボタン押下時処理
function onCustomerSearch(url_str, list) {

    var callback_id = 'callback_s0010';
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.entryForm.select_record.value = '1';
        document.entryForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=700,height=700');
}

// 車両検索ボタン押下時処理
function onCarSearch(url_str, list) {

    var callback_id = 'callback_s0050';
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.entryForm.select_record.value = '1';
        document.entryForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=700,height=700');
}

// 傭車先検索ボタン押下時処理
function onCarrierSearch(url_str, list) {

    var callback_id = 'callback_s0030';
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.entryForm.select_record.value = '1';
        document.entryForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=700,height=700');
}

// 商品検索ボタン押下時処理
function onProductSearch(url_str, list) {

    var callback_id = 'callback_s0060';
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.entryForm.select_record.value = '1';
        document.entryForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=700,height=700');
}

// 得意先検索ボタン押下時処理
function onClientSearch(url_str, list) {

    var callback_id = 'callback_s0020';
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.entryForm.select_record.value = '1';
        document.entryForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=700,height=700');
}
