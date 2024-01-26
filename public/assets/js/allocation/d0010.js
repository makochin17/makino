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

    // 分載フォーム初期化（Hiddenフォーム含む）
    var init_all_carrying_form = function(dispatch_list_no) {
        for (i = 0;i < 3;i++) {
            // 分載フォーム情報

            $('input[id=carrying_stack_date_'+i+']').val('');
            $('input[id=carrying_drop_date_'+i+']').val('');
            $('input[id=carrying_stack_place_'+i+']').val('');
            $('input[id=carrying_drop_place_'+i+']').val('');
            $('input[id=carrying_client_code_'+i+']').val('');
            $('label[id=carrying_client_name_'+i+']').text('');
            $('input[id=carrying_client_name_'+i+']').val('');
            $('select[id=carrying_car_model_code_'+i+']').val('1');
            $('input[id=carrying_carrier_code_'+i+']').val('');
            $('label[id=carrying_carrier_name_'+i+']').text('');
            $('input[id=carrying_carrier_name_'+i+']').val('');
            $('input[id=carrying_car_number_'+i+']').val('');
            $('input[id=carrying_car_code_'+i+']').val('');
            $('input[id=carrying_driver_name_'+i+']').val('');
            $('input[id=carrying_member_code_'+i+']').val('');
            $('input[id=carrying_phone_number_'+i+']').val('');
            $('input[id=carrying_destination_'+i+']').val('');
            $('input[id=carrying_claim_sales_'+i+']').val(0);
            $('input[id=carrying_carrier_payment_'+i+']').val(0);
            $('input[id=carrying_claim_highway_fee_'+i+']').val('0');
            $('input[id=form_carrying_claim_highway_claim_'+i+']').val('1');
            $('label[id=carrying_claim_highway_claim_'+i+']').val('');
            $('input[id=carrying_carrier_highway_fee_'+i+']').val('0');
            $('input[id=form_carrying_carrier_highway_claim_'+i+']').val('1');
            $('label[id=carrying_carrier_highway_claim_'+i+']').val('');
            $('input[id=carrying_driver_highway_fee_'+i+']').val('0');
            $('input[id=form_carrying_driver_highway_claim_'+i+']').val('1');
            $('label[id=carrying_driver_highway_claim_'+i+']').val('');
            // 配車入力Hidden情報（分載データ）
            if (dispatch_list_no.search(/^[0-9]+$/) == 0) {

                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_carrying_number]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_dispatch_number]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_stack_date]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_drop_date]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_stack_place]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_drop_place]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_client_code]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_client_name]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_car_model_code]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_carrier_code]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_carrier_name]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_car_code]').val('');
                // $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_car_number]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_member_code]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_driver_name]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_phone_number]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_destination]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_claim_sales]').val(0);
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_carrier_payment]').val(0);
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_claim_highway_fee]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_claim_highway_claim]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_carrier_highway_fee]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_carrier_highway_claim]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_driver_highway_fee]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_driver_highway_claim]').val('');
            }
        }
    }

    // 分載フォームデータ設定（読み込み）
    var call_hidden_carrying_form = function(dispatch_list_no) {
        // 数値チェック
        if (dispatch_list_no.search(/^[0-9]+$/) == 0) {
            for (i = 0;i < 3;i++) {
                if ($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_client_code]').val() > 0) {
                    var client_code = zeropadding($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_client_code]').val(), 5);
                } else {
                    var client_code = '';
                }
                if ($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_carrier_code]').val() > 0) {
                    var carrier_code = zeropadding($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_carrier_code]').val(), 5);
                } else {
                    var carrier_code = '';
                }
                if ($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_car_code]').val() > 0) {
                    var car_code = zeropadding($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_car_code]').val(), 4);
                } else {
                    var car_code = $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_car_code]').val();
                }
                if ($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_member_code]').val() > 0) {
                    var member_code = zeropadding($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_member_code]').val(), 5);
                } else {
                    var member_code = '';
                }
                if ($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_claim_sales]').val() > 0) {
                    var claim_sales = $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_claim_sales]').val();
                } else {
                    var claim_sales = 0;
                    $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_claim_sales]').val(0);
                }
                if ($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_carrier_payment]').val() > 0) {
                    var carrier_payment = $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_carrier_payment]').val();
                } else {
                    var carrier_payment = 0;
                    $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_carrier_payment]').val(0);
                }
                if ($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_claim_highway_fee]').val() > 0) {
                    var claim_highway_fee = $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_claim_highway_fee]').val();
                } else {
                    var claim_highway_fee = 0;
                    $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_claim_highway_fee]').val(0);
                }
                if ($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_claim_highway_claim]').val() == '2') {
                    var claim_highway_claim = true;
                } else {
                    var claim_highway_claim = false;
                }
                if ($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_carrier_highway_fee]').val() > 0) {
                    var carrier_highway_fee = $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_carrier_highway_fee]').val();
                } else {
                    var carrier_highway_fee = 0;
                    $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_carrier_highway_fee]').val(0);
                }
                if ($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_carrier_highway_claim]').val() == '2') {
                    var carrier_highway_claim = true;
                } else {
                    var carrier_highway_claim = false;
                }
                if ($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_driver_highway_fee]').val() > 0) {
                    var driver_highway_fee = $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_driver_highway_fee]').val();
                } else {
                    var driver_highway_fee = 0;
                    $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_driver_highway_fee]').val(0);
                }
                if ($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_driver_highway_claim]').val() == '2') {
                    var driver_highway_claim = true;
                } else {
                    var driver_highway_claim = false;
                }

                // 配車入力Hidden情報（分載データ） -> 分載フォーム情報
                $('input[id=carrying_stack_date_'+i+']').val($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_stack_date]').val());
                $('input[id=carrying_drop_date_'+i+']').val($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_drop_date]').val());
                $('input[id=carrying_stack_place_'+i+']').val($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_stack_place]').val());
                $('input[id=carrying_drop_place_'+i+']').val($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_drop_place]').val());
                $('input[id=carrying_client_code_'+i+']').val(client_code);
                $('label[id=carrying_client_name_'+i+']').text($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_client_name]').val());
                $('input[id=carrying_client_name_'+i+']').val($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_client_name]').val());
                $('select[id=carrying_car_model_code_'+i+']').val($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_car_model_code]').val());
                $('input[id=carrying_carrier_code_'+i+']').val(carrier_code);
                $('label[id=carrying_carrier_name_'+i+']').text($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_carrier_name]').val());
                $('input[id=carrying_carrier_name_'+i+']').val($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_carrier_name]').val());
                // $('input[id=carrying_car_number_'+i+']').val($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_car_number]').val());
                $('input[id=carrying_car_code_'+i+']').val(car_code);
                $('input[id=carrying_driver_name_'+i+']').val($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_driver_name]').val());
                $('input[id=carrying_member_code_'+i+']').val(member_code);
                $('input[id=carrying_phone_number_'+i+']').val($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_phone_number]').val());
                $('input[id=carrying_destination_'+i+']').val($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_destination]').val());
                $('input[id=carrying_claim_sales_'+i+']').val(claim_sales);
                $('input[id=carrying_carrier_payment_'+i+']').val(carrier_payment);
                $('input[id=carrying_claim_highway_fee_'+i+']').val(claim_highway_fee);
                $('input[id=form_carrying_claim_highway_claim_'+i+']').val($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_claim_highway_claim]').val());
                $('input[id=form_carrying_claim_highway_claim_'+i+']').prop('checked', claim_highway_claim);
                $('input[id=carrying_carrier_highway_fee_'+i+']').val(carrier_highway_fee);
                $('input[id=form_carrying_carrier_highway_claim_'+i+']').val($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_carrier_highway_claim]').val());
                $('input[id=form_carrying_carrier_highway_claim_'+i+']').prop('checked', carrier_highway_claim);
                $('input[id=carrying_driver_highway_fee_'+i+']').val(driver_highway_fee);
                $('input[id=form_carrying_driver_highway_claim_'+i+']').val($('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_driver_highway_claim]').val());
                $('input[id=form_carrying_driver_highway_claim_'+i+']').prop('checked', driver_highway_claim);
            }
        }
    }

    // 分載Hiddenフォーム初期化
    var init_hidden_carrying_form = function(dispatch_list_no) {
        for (i = 0;i < 3;i++) {
            // 配車入力Hidden情報（分載データ）
            if (dispatch_list_no.search(/^[0-9]+$/) == 0) {
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_carrying_number]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_dispatch_number]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_stack_date]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_drop_date]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_stack_place]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_drop_place]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_client_code]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_client_name]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_car_model_code]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_carrier_code]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_carrier_name]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_car_code]').val('');
                // $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_car_number]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_member_code]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_driver_name]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_phone_number]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_destination]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_claim_sales]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_carrier_payment]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_claim_highway_fee]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_claim_highway_claim]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_carrier_highway_fee]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_carrier_highway_claim]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_driver_highway_fee]').val('');
                $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_driver_highway_claim]').val('');
            }
        }
    }

    // 分載フォーム設定（入力値設定）
    var set_hidden_carrying_form = function(dispatch_list_no) {
        // 数値チェック
        if (dispatch_list_no.search(/^[0-9]+$/) == 0) {
            for (i = 0;i < 3;i++) {
                // 分載フォーム情報 -> 配車入力Hidden情報（分載データ）
                if ($('input[id=carrying_car_code_'+i+']').val() != '' && $('input[id=carrying_driver_name_'+i+']').val() != '') {

                    $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_stack_date]').val($('input[id=carrying_stack_date_'+i+']').val());
                    $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_drop_date]').val($('input[id=carrying_drop_date_'+i+']').val());
                    $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_stack_place]').val($('input[id=carrying_stack_place_'+i+']').val());
                    $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_drop_place]').val($('input[id=carrying_drop_place_'+i+']').val());
                    $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_client_code]').val(zeropadding($('input[id=carrying_client_code_'+i+']').val(), 5));
                    $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_client_name]').val($('input[id=carrying_client_name_'+i+']').val());
                    $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_car_model_code]').val(zeropadding($('select[id=carrying_car_model_code_'+i+']').val(), 3));
                    $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_carrier_code]').val(zeropadding($('input[id=carrying_carrier_code_'+i+']').val(), 5));
                    $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_carrier_name]').val($('input[id=carrying_carrier_name_'+i+']').val());
                    $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_car_number]').val($('input[id=carrying_car_number_'+i+']').val());
                    $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_car_code]').val(zeropadding($('input[id=carrying_car_code_'+i+']').val(), 4));
                    $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_driver_name]').val($('input[id=carrying_driver_name_'+i+']').val());
                    $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_member_code]').val(zeropadding($('input[id=carrying_member_code_'+i+']').val(), 5));
                    $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_phone_number]').val($('input[id=carrying_phone_number_'+i+']').val());
                    $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_destination]').val($('input[id=carrying_destination_'+i+']').val());
                    $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_claim_sales]').val($('input[id=carrying_claim_sales_'+i+']').val());
                    $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_carrier_payment]').val($('input[id=carrying_carrier_payment_'+i+']').val());
                    $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_claim_highway_fee]').val($('input[id=carrying_claim_highway_fee_'+i+']').val());
                    $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_claim_highway_claim]').val($('input[id=form_carrying_claim_highway_claim_'+i+']').val());
                    $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_carrier_highway_fee]').val($('input[id=carrying_carrier_highway_fee_'+i+']').val());
                    $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_carrier_highway_claim]').val($('input[id=form_carrying_carrier_highway_claim_'+i+']').val());
                    $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_driver_highway_fee]').val($('input[id=carrying_driver_highway_fee_'+i+']').val());
                    $('input[id=hidden_list_'+dispatch_list_no+'_carrying_'+i+'_driver_highway_claim]').val($('input[id=form_carrying_driver_highway_claim_'+i+']').val());
                }
            }
        }
    }

    // モーダル初期化
    $('div[id=carrying_modal]').fadeOut();
    $('div[id=carrying_client_modal]').fadeOut();
    $('div[id=carrying_carrier_modal]').fadeOut();
    $('div[id=carrying_car_modal]').fadeOut();
    $('div[id=carrying_driver_modal]').fadeOut();

    // コード入力処理(配車入力)
    $('input[id^=client_code], input[id^=car_code], input[id^=carrier_code], input[id^=driver_name], select[id^=product_code]').change(function(e){

        var id      = $(this).attr('id');
        var names   = id.split('_');
        var cnt     = names[2];

        // URL
        var url     = $('input[name=current_url]').val();
        // データ
        var data    = 'code='+$(this).val()+'&type='+names[0];

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
                    $('#hidden_list_'+cnt+'_client_code').val('');
                    $('#hidden_list_'+cnt+'_client_name').val('');
                } else {
                    $('input[id^=client_code_'+cnt+']').val(zeropadding(data.client_code, 5));
                    $('input[id^=client_name_'+cnt+']').val(data.client_name);
                    $('label[id^=client_name_'+cnt+']').text(data.client_name);
                    $('#hidden_list_'+cnt+'_client_name').val(data.client_name);
                }
                break;
            case 'car':
                var pattern = /^\d*$/;
                //if (!data || data == 'undefined') {
                    // $('input[id=car_code_'+cnt+']').val('');
                //}
                if(!pattern.test($('input[id=car_code_'+cnt+']').val())) {
                    $('input[id=car_code_'+cnt+']').val('');
                    $('#hidden_list_'+cnt+'_car_code').val('');
                } else {
                    // $('input[id=car_number_'+cnt+']').val(data.car_number);
                    $('input[id=car_code_'+cnt+']').val(zeropadding(data.car_code, 4));
                }
                break;
            case 'product':
                if (!data || data == 'undefined') {
                    $('input[id=product_name_'+cnt+']').val('');
                    $('#hidden_list_'+cnt+'_product_name').val('');
                } else {
                    $('input[id=product_code_'+cnt+']').val(zeropadding(data.product_code, 4));
                    $('input[id=product_name_'+cnt+']').val(data.product_name);
                    $('#hidden_list_'+cnt+'_product_name').val(data.product_name);
                }
                break;
            case 'carrier':
                if (!data || data == 'undefined') {
                    $('input[id=carrier_code_'+cnt+']').val('');
                    $('input[id=carrier_name_'+cnt+']').val('');
                    $('label[id=carrier_name_'+cnt+']').text('');
                    $('#hidden_list_'+cnt+'_carrier_code').val('');
                    $('#hidden_list_'+cnt+'_carrier_name').val('');
                } else {
                    $('input[id=carrier_code_'+cnt+']').val(zeropadding(data.carrier_code, 5));
                    $('input[id=carrier_name_'+cnt+']').val(data.carrier_name);
                    $('label[id=carrier_name_'+cnt+']').text(data.carrier_name);
                    $('#hidden_list_'+cnt+'_carrier_name').val(data.carrier_name);
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

        return e.preventDefault();
    });

    // 配車入力モーダル戻る処理
    $('#dispatch_back').click(function(e){

        window.location.href = $('input[name=list_url]').val();

        return e.preventDefault();
    });

    // 分載入力モーダル処理
    $('input[id^=s_carrying]').click(function(e){

        var id                      = $(this).attr('id');
        var names                   = id.split('_');
        var cnt                     = names[2];

        // 入力チェック
        if ($('#claim_sales_'+cnt).val() == 0) {
            window.alert(processing_msg4);
            return e.preventDefault();
        }

        // プルダウン配列
        var product                 = JSON.parse(product_list);
        var processing              = JSON.parse(processing_division_list);
        var division                = JSON.parse(division_list);
        var position                = JSON.parse(position_list);
        var carmodel                = JSON.parse(car_model_list);
        var delivery                = JSON.parse(delivery_category_list);
        var tax                     = JSON.parse(tax_category_list);

        var claim_highway_claim     = '×';
        var carrier_highway_claim   = '×';
        var driver_highway_claim    = '×';

        if ($('#form_claim_highway_claim_'+cnt).val() == '2') {
            var claim_highway_claim = ' ○';
        }
        if ($('#form_carrier_highway_claim_'+cnt).val() == '2') {
            var carrier_highway_claim = ' ○';
        }
        if ($('#form_driver_highway_claim_'+cnt).val() == '2') {
            var driver_highway_claim = ' ○';
        }

        // 選択した配車入力情報
        // 選択配車リストNo
        $('input[id=dispatch_list_no]').val(cnt);
        // 選択配車売上請求額
        $('input[id=dispatch_claim_sales]').val($('#claim_sales_'+cnt).val());
        // 選択配車傭車先支払額
        $('input[id=dispatch_carrier_payment]').val($('#carrier_payment_'+cnt).val());
        // 選択配車請求高速料金
        $('input[id=dispatch_claim_highway_fee]').val($('#claim_highway_fee_'+cnt).val());
        // 選択配車傭車先高速料金
        $('input[id=dispatch_carrier_highway_fee]').val($('#carrier_highway_fee_'+cnt).val());
        // 選択配車ドライバー高速料金
        $('input[id=dispatch_driver_highway_fee]').val($('#driver_highway_fee_'+cnt).val());
        // 積日
        var today               = new Date($('#stack_date_'+cnt).val());
        var year                = today.getFullYear();
        var month               = today.getMonth() + 1;
        var day                 = today.getDate();
        var dispatch_stack_date = year+'年'+month+'月'+day+'日';
        // 降日
        var today               = new Date($('#drop_date_'+cnt).val());
        var year                = today.getFullYear();
        var month               = today.getMonth() + 1;
        var day                 = today.getDate();
        var dispatch_drop_date = year+'年'+month+'月'+day+'日';


        // 画面に表示
        $('#dispatch_stack_date').text(dispatch_stack_date);
        $('#dispatch_drop_date').text(dispatch_drop_date);
        $('#dispatch_client_code').text(zeropadding($('#client_code_'+cnt).val(), 5));
        $('#dispatch_product_code').text(product[$('#product_code_'+cnt+' option:selected').val()]);
        $('#dispatch_product_name').text(product[$('#product_code_'+cnt+' option:selected').val()]);
        $('#dispatch_car_model_code').text(carmodel[$('#car_model_code_'+cnt+' option:selected').val()]);
        $('#dispatch_carrier_code').text(zeropadding($('#carrier_code_'+cnt).val(), 5));
        $('#dispatch_car_code').text(zeropadding($('#car_code_'+cnt).val(), 4));
        $('#dispatch_driver_name').text($('#driver_name_'+cnt).val());
        $('#dispatch_delivery_category').text(delivery[$('#delivery_category_'+cnt+' option:selected').val()]);
        $('#dispatch_tax_category').text(tax[$('#tax_category_'+cnt+' option:selected').val()]);
        $('#dispatch_claim_sales_view').text(numberFormat($('#claim_sales_'+cnt).val(), ','));
        $('#dispatch_carrier_payment_view').text(numberFormat($('#carrier_payment_'+cnt).val(), ','));
        $('#dispatch_stack_place').text($('#stack_place_'+cnt).val());
        $('#dispatch_drop_place').text($('#drop_place_'+cnt).val());
        $('#dispatch_client_name').text($('#client_name_'+cnt).text());
        $('#dispatch_carrier_name').text($('#carrier_name_'+cnt).text());

        $('#dispatch_claim_highway_fee_view').text(numberFormat($('#claim_highway_fee_'+cnt).val(), ',')+claim_highway_claim);
        $('#dispatch_carrier_highway_fee_view').text(numberFormat($('#carrier_highway_fee_'+cnt).val(), ',')+carrier_highway_claim);
        $('#dispatch_driver_highway_fee_view').text(numberFormat($('#driver_highway_fee_'+cnt).val(), ',')+driver_highway_claim);

        // 分際情報入力履歴があれば設定する
        call_hidden_carrying_form(cnt);

        // 分載入力画面呼び出し
        $('#carrying_modal').fadeIn();

        return e.preventDefault();
    });

    // 分載入力モーダル確定処理
    $('#carrying_submit').click(function(e){
        if (!confirm(processing_carrying_msg1)) {
            return false;
        } else {
            var dispatch_list_no                = $('input[id=dispatch_list_no]').val();
            var dispatch_claim_sales            = $('input[id=dispatch_claim_sales]').val();
            var dispatch_carrier_payment        = $('input[id=dispatch_carrier_payment]').val();
            var dispatch_claim_highway_fee      = $('input[id=dispatch_claim_highway_fee]').val();
            var dispatch_carrier_highway_fee    = $('input[id=dispatch_carrier_highway_fee]').val();
            var dispatch_driver_highway_fee     = $('input[id=dispatch_driver_highway_fee]').val();
            var claim_sales                     = 0;
            var carrier_payment                 = 0;
            var claim_highway_fee               = 0;
            var carrier_highway_fee             = 0;
            var driver_highway_fee              = 0;
            var list_cnt                        = 0;

            // 売上請求合算
            $('input[id^=carrying_claim_sales]').each(function(idx, e) {
                claim_sales         = claim_sales + Number($(this).val());
            });
            // 傭車先支払合算
            $('input[id^=carrying_carrier_payment]').each(function(idx, e) {
                carrier_payment     = carrier_payment + Number($(this).val());
            });
            // 請求高速料金合算
            $('input[id^=carrying_claim_highway_fee]').each(function(idx, e) {
                claim_highway_fee   = claim_highway_fee + Number($(this).val());
            });
            // 傭車先高速料金合算
            $('input[id^=carrying_carrier_highway_fee]').each(function(idx, e) {
                carrier_highway_fee  = carrier_highway_fee + Number($(this).val());
            });
            // ドライバー高速料金合算
            $('input[id^=carrying_driver_highway_fee]').each(function(idx, e) {
                driver_highway_fee  = driver_highway_fee + Number($(this).val());
            });

            // 売上金額整合チェック
            if (claim_sales != dispatch_claim_sales) {
                window.alert(processing_carrying_msg3);
                return e.preventDefault();
            }
            // 傭車先支払整合チェック
            if (carrier_payment != dispatch_carrier_payment) {
                if (!confirm(processing_carrying_msg4)) {
            		return e.preventDefault();
        		}
            }
            // 請求高速料金整合チェック
            if (claim_highway_fee != dispatch_claim_highway_fee) {
                window.alert(processing_carrying_msg6);
                return e.preventDefault();
            }
            // 傭車先高速料金整合チェック
            if (carrier_highway_fee != dispatch_carrier_highway_fee) {
                if (!confirm(processing_carrying_msg8)) {
            		return e.preventDefault();
        		}
            }
            // ドライバー高速料金整合チェック
            if (driver_highway_fee != dispatch_driver_highway_fee) {
                window.alert(processing_carrying_msg7);
                return e.preventDefault();
            }

            // 分載情報は２件以上チェック
            // 車番
            $('input[id^=carrying_car_code]').each(function(idx, e) {
                if ($(this).val() != '') {
                    list_cnt++;
                }
            });
            // ドライバー
            $('input[id^=carrying_driver_name]').each(function(idx, e) {
                if ($(this).val() != '') {
                    list_cnt++;
                }
            });
            if (list_cnt < 4) {
                window.alert(processing_carrying_msg5);
                return e.preventDefault();
            }

            // 分載データ登録件数を配車データに格納
            hidden_list_0_carrying_count
            $('input[id=hidden_list_'+dispatch_list_no+'_carrying_count]').val((list_cnt / 2));
            $('#carrying_count_'+dispatch_list_no).text((list_cnt / 2)+'台');

            // 分載フォーム初期化（画面＆hidden）
            init_all_carrying_form('');
            // 選択配車入力リストNoを初期化
            $('input[id=dispatch_list_no]').val('');

            $('#carrying_modal').fadeOut();
        }
        return e.preventDefault();
    });

    // 分載入力モーダル入力取消(キャンセル)処理
    $('#carrying_cancel').click(function(e){
        if (!confirm(processing_carrying_msg2)) {
            return false;
        } else {
            // 分載フォーム初期化（画面＆hidden）
            init_all_carrying_form($('input[id=dispatch_list_no]').val());
            // 分載台数を初期化
            var dispatch_list_no = $('input[id=dispatch_list_no]').val();
            $('input[id=hidden_list_'+dispatch_list_no+'_carrying_count]').val(0);
            $('#carrying_count_'+dispatch_list_no).text('分載なし');
            // 選択配車入力リストNoを初期化
            $('input[id=dispatch_list_no]').val('');
            $('#carrying_modal').fadeOut();
        }
        return e.preventDefault();
    });

    // 分載入力モーダル戻る処理
    $('#carrying_back').click(function(e){
        // 選択配車入力リストNoを初期化
        $('input[id=dispatch_list_no]').val('');
        $('#carrying_modal').fadeOut();
        return e.preventDefault();
    });

    // コード入力処理(分載入力)
    $('input[id^=carrying_client_code], input[id^=carrying_carrier_code], input[id^=carrying_car_code], input[id^=carrying_driver_name]').change(function(e){

        var id                  = $(this).attr('id');
        var names               = id.split('_');
        var cnt                 = names[3];
        var dispatch_list_no    = $('input[id=dispatch_list_no]').val();

        // URL
        var url     = $('input[name=current_url]').val();
        // データ
        var data    = 'code='+$(this).val()+'&type='+names[1];

        $.ajax({
            type:       'POST',
            url:        url,
            data:       data,
            dataType:   'json'
        }).done(function(data) {
            switch(names[1]) {
            case 'client':
                if (!data) {
                    $('input[id^=carrying_client_code_'+cnt+']').val('');
                    $('input[id^=carrying_client_name_'+cnt+']').val('');
                    $('label[id^=carrying_client_name_'+cnt+']').text('');
                    $('#hidden_list_'+dispatch_list_no+'_carrying_'+cnt+'_client_code').val('');
                    $('#hidden_list_'+dispatch_list_no+'_carrying_'+cnt+'_client_name').val('');
                } else {
                    $('input[id^=carrying_client_code_'+cnt+']').val(zeropadding(data.client_code, 5));
                    $('input[id^=carrying_client_name_'+cnt+']').val(data.client_name);
                    $('label[id^=carrying_client_name_'+cnt+']').text(data.client_name);
                    $('#hidden_list_'+dispatch_list_no+'_carrying_'+cnt+'_client_name').val(data.client_name);
                }
                break;
            case 'carrier':
                if (!data) {
                    $('input[id^=carrying_carrier_code_'+cnt+']').val('');
                    $('input[id^=carrying_carrier_name_'+cnt+']').val('');
                    $('label[id^=carrying_carrier_name_'+cnt+']').text('');
                    $('#hidden_list_'+dispatch_list_no+'_carrying_'+cnt+'_carrier_code').val('');
                    $('#hidden_list_'+dispatch_list_no+'_carrying_'+cnt+'_carrier_name').val('');
                } else {
                    $('input[id^=carrying_carrier_code_'+cnt+']').val(zeropadding(data.carrier_code, 5));
                    $('input[id^=carrying_carrier_name_'+cnt+']').val(data.carrier_name);
                    $('label[id^=carrying_carrier_name_'+cnt+']').text(data.carrier_name);
                    $('#hidden_list_'+dispatch_list_no+'_carrying_'+cnt+'_carrier_name').val(data.carrier_name);
                }
                break;
            case 'car':
                var pattern = /^\d*$/;
                //if (!data) {
                    // $('input[id=carrying_car_code_'+cnt+']').val('');
                    // $('#hidden_list_'+dispatch_list_no+'_carrying_'+cnt+'_car_code').val('');
                //}
                if(!pattern.test($('input[id=carrying_car_code_'+cnt+']').val())) {
                    $('input[id=carrying_car_code_'+cnt+']').val('');
                    $('#hidden_list_'+dispatch_list_no+'_carrying_'+cnt+'_car_code').val('');
                } else {
                    // $('input[id=carrying_car_number_'+cnt+']').val(data.car_number);
                    $('input[id=carrying_car_code_'+cnt+']').val(zeropadding(data.car_code, 4));
                    $('#hidden_list_'+dispatch_list_no+'_carrying_'+cnt+'_car_code').val(zeropadding(data.car_code, 4));
                }
                break;
            case 'driver':
                if (!data) {
                    $('input[id=carrying_member_code_'+cnt+']').val('');
                    $('input[id=carrying_phone_number_'+cnt+']').val('');
                    $('#hidden_list_'+dispatch_list_no+'_carrying_'+cnt+'_member_code').val('');
                    $('#hidden_list_'+dispatch_list_no+'_carrying_'+cnt+'_phone_number').val('');
                } else {
                    $('input[id=carrying_driver_name_'+cnt+']').val(data.driver_name);
                    $('input[id=carrying_member_code_'+cnt+']').val(zeropadding(data.member_code, 5));
                    $('input[id=carrying_phone_number_'+cnt+']').val(data.phone_number);
                    $('#hidden_list_'+dispatch_list_no+'_carrying_'+cnt+'_member_code').val(zeropadding(data.member_code, 5));
                    $('#hidden_list_'+dispatch_list_no+'_carrying_'+cnt+'_phone_number').val(data.phone_number);
                }
                break;
            }
        }).fail(function(){
        }).always(function(){
        });

        return e.preventDefault();
    });

    // 分載入力用傭車先検索モーダル処理
    $('input[id^=c_client], input[id^=c_carrier], input[id^=c_car], input[id^=c_driver]').click(function(e){

        var id      = $(this).attr('id');
        var names   = id.split('_');
        var cnt     = names[2];

        // 選択分載リストNoを設定
        $('#carrying_list_no').val(cnt);

        switch(names[1]) {
        case 'car':
            $('#carrying_car_modal').fadeIn();
            break;
        case 'client':
            $('#carrying_client_modal').fadeIn();
            break;
        case 'carrier':
            $('#carrying_carrier_modal').fadeIn();
            break;
        case 'driver':
            $('#carrying_driver_modal').fadeIn();
            break;
        }

        return e.preventDefault();
    });
    // 分載入力用検索モーダルキャンセル
    $('#carrying_client_cancel, #carrying_carrier_cancel, #carrying_car_cancel, #carrying_driver_cancel').click(function(e){

        switch($(this).attr('id')) {
        case 'carrying_client_cancel':
            var selector = $('#carrying_client_modal');
            break;
        case 'carrying_carrier_cancel':
            var selector = $('#carrying_carrier_modal');
            break;
        case 'carrying_car_cancel':
            var selector = $('#carrying_car_modal');
            break;
        case 'carrying_driver_cancel':
            var selector = $('#carrying_driver_modal');
            break;
        }
        // 選択分載リストNoを初期化
        $('#carrying_list_no').val('');

        selector.fadeOut();

        return e.preventDefault();
    });
    // 分載入力用検索モーダル確定
    $('button[id^=carrying_select]').click(function(e){

        var id              = $(this).attr('id');
        var names           = id.split('_');
        var type            = names[2];
        var d_no            = $('#dispatch_list_no').val();
        var c_no            = $('#carrying_list_no').val();

        switch(type) {
        case 'car':
            var car_code        = $(this).attr('data-id');
            var car_number      = $(this).attr('data-name');
            $('input[id=carrying_car_code_'+c_no+']').val(zeropadding(car_code, 4));
            // $('input[id=carrying_car_number_'+c_no+']').val(car_number);
            $('#hidden_list_'+d_no+'_carrying_'+c_no+'_car_code').val(car_code);
            // $('#hidden_list_'+d_no+'_carrying_'+c_no+'_car_number').val(car_number);
            break;
        case 'client':
            var client_code    = $(this).attr('data-id');
            var client_name    = $(this).attr('data-name');
            $('input[id=carrying_client_code_'+c_no+']').val(zeropadding(client_code, 5));
            $('input[id=carrying_client_name_'+c_no+']').val(client_name);
            $('label[id=carrying_client_name_'+c_no+']').text(client_name);
            $('#hidden_list_'+d_no+'_carrying_'+c_no+'_client_code').val(zeropadding(client_code, 5));
            $('#hidden_list_'+d_no+'_carrying_'+c_no+'_client_name').val(client_name);
            break;
        case 'carrier':
            var carrier_code    = $(this).attr('data-id');
            var carrier_name    = $(this).attr('data-name');
            $('input[id=carrying_carrier_code_'+c_no+']').val(zeropadding(carrier_code, 5));
            $('input[id=carrying_carrier_name_'+c_no+']').val(carrier_name);
            $('label[id=carrying_carrier_name_'+c_no+']').text(carrier_name);
            $('#hidden_list_'+d_no+'_carrying_'+c_no+'_carrier_code').val(zeropadding(carrier_code, 5));
            $('#hidden_list_'+d_no+'_carrying_'+c_no+'_carrier_name').val(carrier_name);
            break;
        case 'driver':
            var member_code     = $(this).attr('data-id');
            var driver_name     = $(this).attr('data-name');
            var phone_number    = $(this).attr('data-phone');
            $('input[id=carrying_member_code_'+c_no+']').val(zeropadding(member_code, 5));
            $('input[id=carrying_driver_name_'+c_no+']').val(driver_name);
            $('input[id=carrying_phone_number_'+c_no+']').val(phone_number);
            $('#hidden_list_'+d_no+'_carrying_'+c_no+'_member_code').val(zeropadding(member_code, 5));
            $('#hidden_list_'+d_no+'_carrying_'+c_no+'_driver_name').val(driver_name);
            $('#hidden_list_'+d_no+'_carrying_'+c_no+'_phone_number').val(phone_number);
            break;
        }

        // 選択分載リストNoを初期化
        $('#carrying_list_no').val('');

        $('#carrying_'+type+'_modal').fadeOut();

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

