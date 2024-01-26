// Enterキーによるsubmit無効化
document.onkeypress = enter;
function enter(){
    if( window.event.keyCode == 13 ){
        return false;
    }
}

function getSelectDispatchNumber() {
	let select_dispatch_number = window.sessionStorage.getItem(['select_dispatch_number']);
	if (select_dispatch_number == null || select_dispatch_number == '') {
		select_dispatch_number = [];
	} else {
		select_dispatch_number = select_dispatch_number.split(',');
	}
	
	return select_dispatch_number;
}

window.onload = function() {
	let select_dispatch_number = getSelectDispatchNumber();
	
	var f = document.forms["selectForm"];
    let record_count = f.record_count.value;
    
    for (let i = 1; i <= record_count; i++) {
    	let c_value = $("input[id=form_select_"+i+"]").val();
    	let index = select_dispatch_number.indexOf(c_value);
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
function onCheckBox(i, dispatch_number) {
	let select_dispatch_number = getSelectDispatchNumber();
//	let max_flag = false;
	
//	if (select_dispatch_number.length >= 5) {
//		max_flag = true;
//	}
	
	let selevt_value = $("input[id=form_select_"+i+"]:checked").val();
	
	if (selevt_value != null) {
		let index = select_dispatch_number.indexOf(dispatch_number);
		select_dispatch_number.splice(index, 1);
	} else {
		select_dispatch_number.push(dispatch_number);
//		if (select_dispatch_number.length < 5) {
//			select_dispatch_number.push(dispatch_number);
//		}
	}
	
	window.sessionStorage.setItem(['select_dispatch_number'],[select_dispatch_number]);
	
	var $not = $('input[type=checkbox]').not(':checked')
	//5つならdisabledを付ける
//	if (max_flag) {
//        $not.attr("disabled",true);
//        $not.attr("checked",false);
//    }
    
    //5つ以下ならdisabledを外す
//    if (select_dispatch_number.length < 5) {
//        $not.attr("disabled",false);
//    }
}

// 選択ボタン押下時処理
function onSelect(dispatch_number) {
    var f = document.forms["selectForm"];
    f.select_dispatch_info.value = [dispatch_number];
    
    var fe = document.forms["entryForm"];
    f.delivery_slip_code.value = fe.delivery_slip_code.value;
    
    f.method = "POST";
    f.submit();
    
    return true;
}

// 複数レコード引用ボタン押下時処理
function onMultipleSelect() {
    var f = document.forms["selectForm"];
    let record_count = f.record_count.value;
    
    let select_dispatch_number = getSelectDispatchNumber();
    
    if (select_dispatch_number.length == 0){
    	//1つも選択されていなければメッセージ出力して中断
    	alert(processing_msg1);
    	return false;
    }
    
    var fe = document.forms["entryForm"];
    f.delivery_slip_code.value = fe.delivery_slip_code.value;
    
    f.select_dispatch_info.value = select_dispatch_number;
    f.method = "POST";
    f.submit();
    
    return true;
}

// 車両検索ボタン押下時処理
function onSearch() {
	window.sessionStorage.removeItem('select_dispatch_number');
	return true;
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

