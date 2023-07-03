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

    // 売上区分
    $('input[id^=sales_category_value]').prop('disabled', true);
    if ($('select[id^=sales_category_code]').get() && $('select[id^=sales_category_code]').val() == '99') {
        $('input[id=sales_category_value_0]').prop('disabled', false);
    }
    $('select[id^=sales_category_code]').change(function(e){
        var sales_category_code = $(this).val();
        var id                  = $(this).attr('id');
        var names               = id.split('_');
        var cnt                 = names[3];

        if (sales_category_code == '99') {
            $('input[id=sales_category_value_'+cnt+']').prop('disabled', false);
        } else {
            $('input[id=sales_category_value_'+cnt+']').prop('disabled', true);
        }

        return e.preventDefault();
    });

	// 売上確定
	$('input[id^=form_sales_status]').change(function(e){
		var prop 	= $(this).prop('checked');
		var id 		= $(this).attr('id');
		var names 	= id.split('_');
		var cnt 	= names[3];

		if (!prop) {
            $(this).val('1');
            $('input[id^=sales_status_'+cnt+']').val('1');
        } else {
            $(this).val('2');
            $('input[id^=sales_status_'+cnt+']').val('2');
        }

		return e.preventDefault();
	});

	// 高速料金請求有無
	$('input[id^=form_highway_fee_claim]').change(function(e){
		var prop 	= $(this).prop('checked');
		var id 		= $(this).attr('id');
		var names 	= id.split('_');
		var cnt 	= names[4];

        if (!prop) {
            $(this).val('1');
            $('input[id^=highway_fee_claim_'+cnt+']').val('1');
        } else {
            $(this).val('2');
            $('input[id^=highway_fee_claim_'+cnt+']').val('2');
        }

		return e.preventDefault();
	});

	$('input[id^=client_code], input[id^=car_code], input[id^=carrier_code], input[id^=driver_name]').change(function(e){

		var id 		= $(this).attr('id');
		var names 	= id.split('_');
		var cnt 	= names[2];
        var code    = $(this).val();

		// URL
		var url 	= $('input[name=current_url]').val();
        // データ
        var data    = 'code='+code+'&type='+names[0];

        $.ajax({
            type:       'POST',
            url:        url,
            data:       data,
            dataType:   'json'
        }).done(function(data) {
            switch(names[0]) {
            case 'client':
                if (!data) {
                    $('input[id=client_name_'+cnt+']').val('');
                    $('label[id=client_name_'+cnt+']').text('');
                    $('input[id=client_code_'+cnt+']').val('');
                } else {
                    $('input[id=client_name_'+cnt+']').val(data.client_name);
                    $('label[id=client_name_'+cnt+']').text(data.client_name);
                    $('input[id=client_code_'+cnt+']').val(zeropadding(code, 5));
                }
                break;
            case 'car':
                var pattern = /^\d*$/;
                if(!pattern.test($('input[id=car_code_'+cnt+']').val())) {
                    $('input[id=car_code_'+cnt+']').val('');
                } else {
                    $('input[id=car_code_'+cnt+']').val(zeropadding(data.car_code, 4));
                }
                break;
            case 'carrier':
                if (!data) {
                    $('input[id=carrier_name_'+cnt+']').val('');
                    $('label[id=carrier_name_'+cnt+']').text('');
                    $('input[id=carrier_code_'+cnt+']').val('');
                } else {
                    $('input[id=carrier_name_'+cnt+']').val(data.carrier_name);
                    $('label[id=carrier_name_'+cnt+']').text(data.carrier_name);
                    $('input[id=carrier_code_'+cnt+']').val(zeropadding(code, 5));
                }
                break;
            case 'driver':
                $('input[id=member_code_'+cnt+']').val(zeropadding(data.member_code, 5));
                $('input[id=phone_number_'+cnt+']').val(data.phone_number);
                break;
            }
        }).fail(function(){
        }).always(function(){
        });

		return e.preventDefault();
	});

    var zeropadding = function(num, len) {
        return ('0000000000' + num).slice(-len);
    }

    // 月極その他入力戻る処理
    $('#sales_correction_back').click(function(e){

        window.location.href = $('input[name=list_url]').val();

        return e.preventDefault();
    });

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

// 月極その他引用ボタン押下時処理
function onSalesCorrectionSearch(url_str, list) {

	var callback_id = 'callback_s0090';
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.entryForm.select_record.value = '1';
        document.entryForm.list_no.value = list;
        document.entryForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=1610,height=900');
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
