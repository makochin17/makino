// Enterキーによるsubmit無効化
document.onkeypress = enter;
function enter(){
    if( window.event.keyCode == 13 ){
        return false;
    }
}

function getSelectStockChangeNumber() {
	let select_storage_fee_number = window.sessionStorage.getItem(['select_storage_fee_number']);
	if (select_storage_fee_number == null || select_storage_fee_number == '') {
		select_storage_fee_number = [];
	} else {
		select_storage_fee_number = select_storage_fee_number.split(',');
	}
	
	return select_storage_fee_number;
}

window.onload = function() {
	let select_storage_fee_number = getSelectStockChangeNumber();
	
	var f = document.forms["selectForm"];
    let record_count = f.record_count.value;
    
    for (let i = 1; i <= record_count; i++) {
    	let c_value = $("input[id=form_select_"+i+"]").val();
    	let index = select_storage_fee_number.indexOf(c_value);
    	if (index != -1) {
    		$("input[id=form_select_"+i+"]").prop("checked", true);
    	}
    }
}

//チェックボックスの制御
function onCheckBox(i, storage_fee_number) {
	let select_storage_fee_number = getSelectStockChangeNumber();
	let max_flag = false;
	
	if (select_storage_fee_number.length >= 5) {
		max_flag = true;
	}
	
	let selevt_value = $("input[id=form_select_"+i+"]:checked").val();
	
	if (selevt_value != null) {
		let index = select_storage_fee_number.indexOf(storage_fee_number);
		select_storage_fee_number.splice(index, 1);
	} else {
		if (select_storage_fee_number.length < 5) {
			select_storage_fee_number.push(storage_fee_number);
		}
	}
	
	window.sessionStorage.setItem(['select_storage_fee_number'],[select_storage_fee_number]);
	
	var $not = $('input[type=checkbox]').not(':checked')
	//5つならdisabledを付ける
	if (max_flag) {
        $not.attr("disabled",true);
        $not.attr("checked",false);
    }
    
    //5つ以下ならdisabledを外す
    if (select_storage_fee_number.length < 5) {
        $not.attr("disabled",false);
    }
}

// 選択ボタン押下時処理
function onSelect(storage_fee_number) {
    var f = document.forms["selectForm"];
    f.select_storage_fee_number.value = [storage_fee_number];
    f.method = "POST";
    f.submit();
    
    return true;
}

// 複数レコード引用ボタン押下時処理
function onMultipleSelect() {
    var f = document.forms["selectForm"];
    let record_count = f.record_count.value;
    
    let select_storage_fee_number = getSelectStockChangeNumber();
    
    if (select_storage_fee_number.length == 0){
    	//1つも選択されていなければメッセージ出力して中断
    	alert(processing_msg1);
    	return false;
    }
    
    f.select_storage_fee_number.value = select_storage_fee_number;
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
