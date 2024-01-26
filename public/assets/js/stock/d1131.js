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
	// 戻るボタン処理
    $('#dispatch_back').click(function(e){

        window.location.href = $('input[name=list_url]').val();

        return e.preventDefault();
    });

    // 金額入力処理
    $('input[id^=storage_fee]').on('focus input',function () {
        // 数値しか入力させない
        var num = $(this).val();
        if (num != '') {
            num = num.replace(/[^\d.]/g, '');
        } else {
            num = 0;
        }
        $(this).val(num);
    }).on('blur',function () {
        // 0から始まる数値は0を除去
        var num = $(this).val();
        if (num == '') {
            num = 0;
        } else {
            // num = num.replace(/^[0]+/,'').replace(/(\d)(?=(\d\d\d)+$)/g, '$1,');
            num = num.replace(/(\d)(?=(\d\d\d)+$)/g, '$1,');
        }
        $(this).val(num).focus(function() {
            $(this).select();
        });
    });

    // 数値入力処理
    $('input[id^=volume], input[id^=unit_price]').on('focus input',function () {
        var num = $(this).val()
        if (num == '') {
            num = 0;
        } else {
            num = num.replace(/[^\d.]/g, '');
        }
        $(this).val(num);
    }).on('blur',function (e) {
        var num     = $(this).val();
        if (num == '') {
            num     = 0;
        }
        var float   = parseFloat(num).toFixed(6);
        var res     = float.split('.');
        if (float.length > 6) {
            if (res[1] == '00') {
                $(this).val(Number(float).toLocaleString() + '.00').focus(function() {
                    $(this).select();
                });
            } else {
                $(this).val(Number(float).toLocaleString(undefined, { maximumFractionDigits: 6 })).focus(function() {
                    $(this).select();
                });
            }
        } else {
            $(this).val(Number(float).toLocaleString(undefined, { maximumFractionDigits: 6 })).focus(function() {
                $(this).select();
            });
        }
        return e.preventDefault();
    });

    // 単価
    $('input[id^=unit_price]').on('change', function(e) {

        var unit_price      = $(this).val();
        var tmp             = $(this).attr('id').split('_');
        var listno          = tmp[2];
        var rounding_code   = $('select[id=rounding_code_'+listno+']').val();
        var volume          = $('input[id=volume_'+listno+']').val();

        // 未入力対応
        if (unit_price == '') { unit_price = 0; }
        if (volume == '') { volume = 0; }

        // 保管料更新
        $('input[id=storage_fee_'+listno+']').val(auto_amount_fee(unit_price, volume, rounding_code));

        return e.preventDefault();
    });
    // 数量
    $('input[id^=volume]').on('change', function(e) {

        var volume          = $(this).val();
        var tmp             = $(this).attr('id').split('_');
        var listno          = tmp[1];
        var unit_price      = $('input[id=unit_price_'+listno+']').val();
        var rounding_code   = $('select[id=rounding_code_'+listno+']').val();

        // 未入力対応
        if (unit_price == '') { unit_price = 0; }
        if (volume == '') { volume = 0; }

        // 保管料更新
        $('input[id=storage_fee_'+listno+']').val(auto_amount_fee(unit_price, volume, rounding_code));

        return e.preventDefault();
    });
    // 端数処理
    $('select[id^=rounding_code]').on('change', function(e) {

        var rounding_code   = $(this).val();
        var tmp             = $(this).attr('id').split('_');
        var listno          = tmp[2];
        var unit_price      = $('input[id=unit_price_'+listno+']').val();
        var volume          = $('input[id=volume_'+listno+']').val();

        // 未入力対応
        if (unit_price == '') { unit_price = 0; }
        if (volume == '') { volume = 0; }

        // 保管料更新
        $('input[id=storage_fee_'+listno+']').val(auto_amount_fee(unit_price, volume, rounding_code));

        return e.preventDefault();
    });

    // 売上確定
    $('input[id^=form_sales_status]').on('change', function(e) {

        var prop    = $(this).prop('checked');

        if (!prop) {
            $(this).val('1').prop("checked", false);
        } else {
            $(this).val('2').prop("checked", true);
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


    var auto_amount_fee = function(unit_price, volume, rounding_code) {

        var amount_fee  = 0;
        var fee         = (parseFloat(unit_price.replace(',', '')) * parseFloat(volume.replace(',', '')));

        switch(rounding_code) {
            case '1':
                // 四捨五入
                amount_fee = Math.round(fee);
            break;
            case '2':
                // 切り上げ
                amount_fee = Math.ceil(fee);
            break;
            case '3':
                // 切り捨て
                amount_fee = Math.floor(fee);
            break;
        }
        return numberFormat(amount_fee, ',');
    };
});

// 配列の文字列を削除
function arrayunique ( array ) {
    return array.filter( function( value, index ) {
        return index === array.lastIndexOf( value ) ;
    } ) ;
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

// 履歴ボタン押下時処理
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

// 「確定」の確認ダイアログ表示
function submitChkExecution(value) {

    var flag = false;
    document.entryForm.processing_division.value = value;

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
