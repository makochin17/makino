window.onload = function(){
	change();
}

// 項目の活性状態制御
function change() {
	
	//車種ラジオボタン操作
	var carRs = document.getElementsByName("car_radio");
	if(carRs[1].checked) {
		document.getElementById('car_model_code').disabled = false;
	} else {
		document.getElementById('car_model_code').disabled = true;
	}
	
	//集計単位操作
	var v = document.getElementById('aggregation_unit_date').value;
	flgD = (v=='2' || v=='3')?true:false;
	flgM = (v=='3')?true:false;
	document.getElementById('start_day').disabled = flgD;
	document.getElementById('end_day').disabled = flgD;
	document.getElementById('start_month').disabled = flgM;
	document.getElementById('end_month').disabled = flgM;
}

// Enterキーによるsubmit無効化
document.onkeypress = enter;
function enter(){
    if( window.event.keyCode == 13 ){
        return false;
    }
}