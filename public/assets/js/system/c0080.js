// フォームデータ処理
$(function(){

    var zeropadding = function(num, len) {
        return ('0000000000' + num).slice(-len);
    }

    // =================================================
    // 車両情報入力
    // =================================================
    // 残溝数
    $('input[id^=summer_tire_warning], input[id^=summer_tire_caution], input[id^=winter_tire_warning], input[id^=winter_tire_caution]').on('focus input',function () {
        if ($(this).val().length > 0) {
            var num = $(this).val().replace(/[^\d.]/g, '');
            $(this).val(num);
        }
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
            if (num.length > 0) {
                $(this).val(float.toLocaleString(undefined, { maximumFractionDigits: 6 }));
            }
        }
        return e.preventDefault();
    });
});

// Enterキーによるsubmit無効化
document.onkeypress = enter;
function enter(){
    if( window.event.keyCode == 13 ){
        return false;
    }
}

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
    // flag = (document.entryForm.division_code.value == '1') ? false : true;
    // document.entryForm.text_member_code.disabled = flag;
}

function submitChkBack() {
    change();
    make_hidden('back', '1');
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
    make_hidden('mode', value);

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
