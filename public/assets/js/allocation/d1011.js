// Enterキーによるsubmit無効化
document.onkeypress = enter;
function enter(){
    if( window.event.keyCode == 13 ){
        return false;
    }
}

$(document).ready( function(){
	if( redirect_flag ){
		window.location.href = $('input[name=list_url]').val();
	}
});

$(function(){

    var zeropadding = function(num, len) {
        return ('0000000000' + num).slice(-len);
    }

    // コード入力処理(配車入力)
    $('input[id^=client_code], input[id^=car_code], input[id^=carrier_code], input[id^=driver_name], select[id^=product_code]').keypress(function(e){
        if (e.keyCode == 13) {
            get_master($(this).attr('id'), $(this).val());
            return e.preventDefault();
        }
    });

    $('input[id^=client_code], input[id^=car_code], input[id^=carrier_code], input[id^=driver_name], select[id^=product_code]').change(function(e){
        get_master($(this).attr('id'), $(this).val());
        return e.preventDefault();
    });

    var get_master = function(id, val) {

        var names   = id.split('_');
        var cnt     = names[2];

        // URL
        var url     = $('input[name=current_url]').val();
        // データ
        var data    = 'code='+val+'&type='+names[0];

        $.ajax({
            type:       'POST',
            url:        url,
            data:       data,
            dataType:   'json'
        }).done(function(data) {
            switch(names[0]) {
            case 'client':
                if (!data || data == 'undefined') {
                    $('input[id^=client_code_'+cnt+']').val('');
                    $('input[id^=client_name_'+cnt+']').val('');
                    $('label[id^=client_name_'+cnt+']').text('');
                    // $('#hidden_list_'+cnt+'_client_code').val('');
                    // $('#hidden_list_'+cnt+'_client_name').val('');
                } else {
                    $('input[id^=client_code_'+cnt+']').val(zeropadding(data.client_code, 5));
                    $('input[id^=client_name_'+cnt+']').val(data.client_name);
                    $('label[id^=client_name_'+cnt+']').text(data.client_name);
                    // $('#hidden_list_'+cnt+'_client_name').val(data.client_name);
                }
                break;
            case 'car':
                var pattern = /^\d*$/;
                //if (!data || data == 'undefined') {
                    // $('input[id=car_code_'+cnt+']').val('');
                //}
                if(!pattern.test($('input[id=car_code_'+cnt+']').val())) {
                    $('input[id=car_code_'+cnt+']').val('');
                    // $('#hidden_list_'+cnt+'_car_code').val('');
                } else {
                    // $('input[id=car_number_'+cnt+']').val(data.car_number);
                    $('input[id=car_code_'+cnt+']').val(zeropadding(data.car_code, 4));
                }
                break;
            case 'product':
                if (!data || data == 'undefined') {
                    $('input[id=product_name_'+cnt+']').val('');
                    // $('#hidden_list_'+cnt+'_product_name').val('');
                } else {
                    $('input[id=product_code_'+cnt+']').val(zeropadding(data.product_code, 4));
                    $('input[id=product_name_'+cnt+']').val(data.product_name);
                    // $('#hidden_list_'+cnt+'_product_name').val(data.product_name);
                }
                break;
            case 'carrier':
                if (!data || data == 'undefined') {
                    $('input[id=carrier_code_'+cnt+']').val('');
                    $('input[id=carrier_name_'+cnt+']').val('');
                    $('label[id=carrier_name_'+cnt+']').text('');
                    // $('#hidden_list_'+cnt+'_carrier_code').val('');
                    // $('#hidden_list_'+cnt+'_carrier_name').val('');
                } else {
                    $('input[id=carrier_code_'+cnt+']').val(zeropadding(data.carrier_code, 5));
                    $('input[id=carrier_name_'+cnt+']').val(data.carrier_name);
                    $('label[id=carrier_name_'+cnt+']').text(data.carrier_name);
                    // $('#hidden_list_'+cnt+'_carrier_name').val(data.carrier_name);
                }
                break;
            case 'driver':
                if (!data || data == 'undefined') {
                    $('input[id=member_code_'+cnt+']').val('');
                    $('input[id=phone_number_'+cnt+']').val('');
                } else {
                    $('input[id=driver_name_'+cnt+']').val(data.driver_name);
                    $('input[id=member_code_'+cnt+']').val(zeropadding(data.member_code, 5));
                    $('input[id=phone_number_'+cnt+']').val(data.phone_number);
                }
                break;
            }
        }).fail(function(){
        }).always(function(){
        });
    };


    // 配車入力モーダル戻る処理
    $('#dispatch_back').click(function(e){

        window.location.href = $('input[name=list_url]').val();

        return e.preventDefault();
    });

    // 金額入力処理
    $('input[id^=carrier_payment]').on('focus input',function () {
        // 数値しか入力させない
        var num = $(this).val().replace(/\D/g, '');
        $(this).val(num);
    }).on('blur',function () {
        // 0から始まる数値は0を除去
        var num = $(this).val().replace(/^[0]+/,'').replace(/(\d)(?=(\d\d\d)+$)/g, '$1,');
        $(this).val(num);
    });

    // 数値入力処理
    $('input[id^=volume]').on('focus input',function () {
        var num = $(this).val().replace(/[^\d.]/g, '');
        $(this).val(num);
    }).on('blur',function (e) {
        var num     = $(this).val();
        var float   = parseFloat(num).toFixed(6);
        var res     = float.split('.');
        if (float.length > 6) {
            if (res[1] == '00') {
                $(this).val(Number(float).toLocaleString() + '.00');
            } else {
                $(this).val(Number(float).toLocaleString(undefined, { maximumFractionDigits: 6 }));
            }
        } else {
            $(this).val(float.toLocaleString(undefined, { maximumFractionDigits: 6 }));
        }
        return e.preventDefault();
    });
    
    // 現場
    $('input[id^=form_onsite_flag]').on('change', function(e) {

        var prop    = $(this).prop('checked');

        if (!prop) {
            $(this).val('0').prop("checked", false);
        } else {
            $(this).val('1').prop("checked", true);
        }

        return e.preventDefault();
    });

    /**
     * 数字の書式設定（区切り）
     * @param {number} number 数字
     * @param {string} delimiter 区切り文字
     * @return {string} 書式設定された文字列を返す
     */
    var numberFormat = function(number, delimiter) {
        delimiter = delimiter || ',';
        if (isNaN(number)) return number;
        if (typeof delimiter !== 'string' || delimiter === '') return number;
        var reg = new RegExp(delimiter.replace(/\./, '\\.'), 'g');
        number = String(number).replace(reg, '');
        while (number !== (number = number.replace(/^(-?[0-9]+)([0-9]{3})/, '$1' + delimiter + '$2')));
        return number;
    };
});

// 配列の文字列を削除
function arrayunique ( array ) {
    return array.filter( function( value, index ) {
        return index === array.lastIndexOf( value ) ;
    } ) ;
}

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
function submitChkExecution(value) {

    var flag = false;
    make_hidden('dispatch_processing_division', value);
    make_hidden('processing_division', value);

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

// 配車履歴ボタン押下時処理
function onDispatchCharterSearch(url_str, list) {

	window.sessionStorage.setItem(['select_dispatch_number'],[]);
    var callback_id = 'callback_s0080';
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.entryForm.select_record.value = '1';
        document.entryForm.list_no.value = list;
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
        document.entryForm.list_no.value = list;
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
        document.entryForm.list_no.value = list;
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
        document.entryForm.list_no.value = list;
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
        document.entryForm.list_no.value = list;
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
        document.entryForm.list_no.value = list;
        document.entryForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=700,height=700');
}

