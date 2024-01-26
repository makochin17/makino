// Enterキーによるsubmit無効化
document.onkeypress = enter;
function enter(){
    if( window.event.keyCode == 13 ){
        return false;
    }
}

$(function(){

    var zeropadding = function(num, len) {
        return ('0000000000' + num).slice(-len);
    }

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
        }).fail(function(){
        }).always(function(){
        });
    };

    // お客様情報入力モーダル戻る処理
    $('[name=back]').click(function(e){

        window.location.href = $('input[name=list_url]').val();

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
    $('input[name=mode]').val(value);

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

// 入庫シール印刷ボタン押下時処理
function submitReceiptPrint(url_str, logistics_id) {

    var flag = window.confirm(processing_msg4);
    if (!flag)return false;

    var f = document.forms["entryForm"];

    make_hidden('print_status_id', logistics_id, 'entryForm');
    // f.processing_division.value = '2';
    f.logistics_id.value = logistics_id;
    f.mode.value = 'print';
    f.method = "POST";
    f.action = url_str;
    f.submit();

    return true;
}

// 車両検索ボタン押下時処理（車両番号）
function onCarCodeSearch(url_str) {

    var callback_id = 'callback_s0020';
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.entryForm.select_record.value = '1';
        document.entryForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=1500,height=700');
}

// 保管場所検索ボタン押下時処理
function onLocationCodeSearch(url_str) {
    var callback_id = 'callback_s0040';
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.entryForm.select_record.value = '1';
        document.entryForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=1200,height=700');
}

