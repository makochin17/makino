// Enterキーによるsubmit無効化
document.onkeypress = enter;
function enter(){
    if( window.event.keyCode == 13 ){
        return false;
    }
}

function getSelectBillNumber() {
	let select_bill_number = window.sessionStorage.getItem(['select_bill_number']);
	if (select_bill_number == null || select_bill_number == '') {
		select_bill_number = [];
	} else {
		select_bill_number = select_bill_number.split(',');
	}
	
	return select_bill_number;
}

window.onload = function() {
	let select_bill_number = getSelectBillNumber();
	
	var f = document.forms["selectForm"];
    let record_count = f.record_count.value;
    
    for (let i = 1; i <= record_count; i++) {
    	let c_value = $("input[id=form_select_"+i+"]").val();
    	let index = select_bill_number.indexOf(c_value);
    	if (index != -1) {
    		$("input[id=form_select_"+i+"]").prop("checked", true);
    	}
    }
    
    let search_client_code = window.sessionStorage.getItem('search_client_code');
    let search_product_name = window.sessionStorage.getItem('search_product_name');
    
    var f = document.forms["searchForm"];
    if(search_client_code)f.client_code.value = search_client_code;
    if(search_product_name)f.product_name.value = search_product_name;
    
    window.sessionStorage.removeItem('search_client_code');
    window.sessionStorage.removeItem('search_product_name');
}

//チェックボックスの制御
function onCheckBox(i, bill_number) {
	let select_bill_number = getSelectBillNumber();
	let max_flag = false;
	
	if (select_bill_number.length >= 5) {
		max_flag = true;
	}
	
	let selevt_value = $("input[id=form_select_"+i+"]:checked").val();
	
	if (selevt_value != null) {
		let index = select_bill_number.indexOf(bill_number);
		select_bill_number.splice(index, 1);
	} else {
		if (select_bill_number.length < 5) {
			select_bill_number.push(bill_number);
		}
	}
	
	window.sessionStorage.setItem(['select_bill_number'],[select_bill_number]);
	
	var $not = $('input[type=checkbox]').not(':checked')
	//5つならdisabledを付ける
	if (max_flag) {
        $not.attr("disabled",true);
        $not.attr("checked",false);
    }
    
    //5つ以下ならdisabledを外す
    if (select_bill_number.length < 5) {
        $not.attr("disabled",false);
    }
}

// 選択ボタン押下時処理
function onSelect(bill_number) {
    var f = document.forms["selectForm"];
    f.select_bill_number.value = [bill_number];
    f.method = "POST";
    f.submit();
    
    return true;
}

// 複数レコード引用ボタン押下時処理
function onMultipleSelect() {
    var f = document.forms["selectForm"];
    let record_count = f.record_count.value;
    
    let select_bill_number = getSelectBillNumber();
    
    if (select_bill_number.length == 0){
    	//1つも選択されていなければメッセージ出力して中断
    	alert(processing_msg1);
    	return false;
    }
    
    f.select_bill_number.value = select_bill_number;
    f.method = "POST";
    f.submit();
    
    return true;
}

// 得意先検索ボタン押下時処理
function onClientSearch(url_str) {

    var callback_id = 'callback_s0020';
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.searchForm.select_record.value = '1';
        document.searchForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=700,height=700');
}

// 傭車先検索ボタン押下時処理
function onCarrierSearch(url_str) {

    var callback_id = 'callback_s0030';
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.searchForm.select_record.value = '1';
        document.searchForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=700,height=700');
}

// 車両検索ボタン押下時処理
function onCarSearch(url_str) {

    var callback_id = 'callback_s0050';
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.searchForm.select_record.value = '1';
        document.searchForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=1000,height=700');
}

// 社員検索ボタン押下時処理
function onCustomerSearch(url_str) {

    var callback_id = 'callback_s0010';
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.searchForm.select_record.value = '1';
        document.searchForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=1500,height=700');
}

