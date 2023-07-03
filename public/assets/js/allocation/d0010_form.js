// フォームデータ処理
$(function(){

    var zeropadding = function(num, len) {
        return ('0000000000' + num).slice(-len);
    }

    // =================================================
    // 配車入力
    // =================================================
    // 処理区分
    $('#processing_division').on('change', function(e) {
        $('#hidden_processing_division').val($(this).val());
        return e.preventDefault();
    });
    // 課コード
    $('#division_code').on('change', function(e) {
        $('#hidden_division_code').val($(this).val());
        return e.preventDefault();
    });

    // 売上確定
    $('input[id^=form_sales_status]').on('change', function(e) {

        var prop    = $(this).prop('checked');
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var tabno   = names[3];
        var listno  = names[4];

        if (!prop) {
            $('#hidden_list_'+listno+'_sales_status').val('1');
            $('#form_sales_status_0_'+listno).val('1').prop("checked", false);
            $('#form_sales_status_1_'+listno).val('1').prop("checked", false);
            $('#form_sales_status_2_'+listno).val('1').prop("checked", false);
        } else {
            $('#hidden_list_'+listno+'_sales_status').val('2');
            $('#form_sales_status_0_'+listno).val('2').prop("checked", true);
            $('#form_sales_status_1_'+listno).val('2').prop("checked", true);
            $('#form_sales_status_2_'+listno).val('2').prop("checked", true);
        }

        return e.preventDefault();
    });

    // 積日
    $('input[id^=stack_date]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[2];

        $('#hidden_list_'+listno+'_stack_date').val($(this).val());
        $('input[id^=stack_date_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 降日
    $('input[id^=drop_date]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[2];

        $('#hidden_list_'+listno+'_drop_date').val($(this).val());
        $('input[id^=drop_date_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 積地
    $('input[id^=stack_place]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[2];

        $('#hidden_list_'+listno+'_stack_place').val($(this).val());
        $('input[id^=stack_place_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 降地
    $('input[id^=drop_place]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[2];

        $('#hidden_list_'+listno+'_drop_place').val($(this).val());
        $('input[id^=drop_place_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 得意先No
    $('input[id^=client_code]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[2];
        var code    = '';

        if ($(this).val()) {
            code    = zeropadding($(this).val(), 5);
        }
        $('#hidden_list_'+listno+'_client_code').val(code);
        $('input[id^=client_code_'+listno+']').val(code);

        return e.preventDefault();
    });

    // 得意先名
    $('input[id^=client_name]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[2];

        $('#hidden_list_'+listno+'_client_name').val($(this).val());
        $('input[id^=client_name_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 商品
    $('select[id^=product_code]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[2];

        $('#hidden_list_'+listno+'_product_code').val($(this).val());
        $('select[id^=product_code_'+listno+']').val($(this).val());

        return e.preventDefault();
    });
    $('select[id^=product_code]').each(function(idx, e) {
        var product = JSON.parse(product_list);
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[2];

        $('#hidden_list_'+listno+'_product_name').val(product[$(this).val()]);
    });

    // 車種
    $('select[id^=car_model_code]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[3];

        $('#hidden_list_'+listno+'_car_model_code').val($(this).val());
        $('select[id^=car_model_code_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 傭車先No
    $('input[id^=carrier_code]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[2];
        var code    = '';

        if ($(this).val()) {
            code    = zeropadding($(this).val(), 5);
        }
        $('#hidden_list_'+listno+'_carrier_code').val(code);
        $('input[id^=carrier_code_'+listno+']').val(code);

        return e.preventDefault();
    });

    // 傭車先名
    $('input[id^=carrier_name]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[2];

        $('#hidden_list_'+listno+'_carrier_name').val($(this).val());
        $('input[id^=carrier_name_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 車番
    $('input[id^=car_number]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[2];

        $('#hidden_list_'+listno+'_car_number').val($(this).val());
        $('input[id^=car_number_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 車両コード
    $('input[id^=car_code]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[2];
        var code    = '';

        if ($(this).val()) {
            code    = zeropadding($(this).val(), 4);
        }
        $('#hidden_list_'+listno+'_car_code').val(code);
        $('input[id^=car_code_'+listno+']').val(code);

        return e.preventDefault();
    });

    // ドライバー名
    $('input[id^=driver_name]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[2];

        $('#hidden_list_'+listno+'_driver_name').val($(this).val());
        $('input[id^=driver_name_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 社員コード
    $('input[id^=member_code]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[2];
        var code    = '';

        if ($(this).val()) {
            code    = zeropadding($(this).val(), 5);
        }
        $('#hidden_list_'+listno+'_member_code').val(code);
        $('input[id^=member_code_'+listno+']').val(code);

        return e.preventDefault();
    });

    // 社員電話番号
    $('input[id^=phone_number]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[2];

        $('#hidden_list_'+listno+'_phone_number').val($(this).val());
        $('input[id^=phone_number_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 運行先
    $('input[id^=destination]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[1];

        $('#hidden_list_'+listno+'_destination').val($(this).val());
        $('input[id^=destination_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 備考
    $('input[id^=remarks]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[1];

        $('#hidden_list_'+listno+'_remarks').val($(this).val());
        $('input[id^=remarks_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 配車区分
    $('select[id^=delivery_category]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[2];

        $('#hidden_list_'+listno+'_delivery_category').val($(this).val());
        $('select[id^=delivery_category_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 税区分
    $('select[id^=tax_category]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[2];

        $('#hidden_list_'+listno+'_tax_category').val($(this).val());
        $('select[id^=tax_category_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 請求売上
    $('input[id^=claim_sales]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[2];

        $('#hidden_list_'+listno+'_claim_sales').val($(this).val());
        $('input[id^=claim_sales_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 傭車支払
    $('input[id^=carrier_payment]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[2];

        $('#hidden_list_'+listno+'_carrier_payment').val($(this).val());
        $('input[id^=carrier_payment_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 請求高速料金
    $('input[id^=claim_highway_fee]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[3];

        $('#hidden_list_'+listno+'_claim_highway_fee').val($(this).val());
        $('input[id^=claim_highway_fee_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 請求高速料金請求有無
    $('input[id^=form_claim_highway_claim]').on('change', function(e) {
        var prop    = $(this).prop('checked');
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[4];

        if (!prop) {
            $(this).val('1');
            $('#hidden_list_'+listno+'_claim_highway_claim').val('1');
            $('input[id^=form_claim_highway_claim_'+listno+']').val('1');
        } else {
            $(this).val('2');
            $('#hidden_list_'+listno+'_claim_highway_claim').val('2');
            $('input[id^=form_claim_highway_claim_'+listno+']').val('2');
        }

        return e.preventDefault();
    });

    // 庸車高速料金
    $('input[id^=carrier_highway_fee]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[3];

        $('#hidden_list_'+listno+'_carrier_highway_fee').val($(this).val());
        $('input[id^=carrier_highway_fee_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 庸車高速料金請求有無
    $('input[id^=form_carrier_highway_claim]').on('change', function(e) {
        var prop    = $(this).prop('checked');
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[4];

        if (!prop) {
            $(this).val('1');
            $('#hidden_list_'+listno+'_carrier_highway_claim').val('1');
            $('input[id^=form_carrier_highway_claim_'+listno+']').val('1');
        } else {
            $(this).val('2');
            $('#hidden_list_'+listno+'_carrier_highway_claim').val('2');
            $('input[id^=form_carrier_highway_claim_'+listno+']').val('2');
        }

        return e.preventDefault();
    });

    // ドライバー高速料金
    $('input[id^=driver_highway_fee]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[3];

        $('#hidden_list_'+listno+'_driver_highway_fee').val($(this).val());
        $('input[id^=driver_highway_fee_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // ドライバー高速料金請求有無
    $('input[id^=form_driver_highway_claim]').on('change', function(e) {
        var prop    = $(this).prop('checked');
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[4];

        if (!prop) {
            $(this).val('1');
            $('#hidden_list_'+listno+'_driver_highway_claim').val('1');
            $('input[id^=form_driver_highway_claim_'+listno+']').val('1');
        } else {
            $(this).val('2');
            $('#hidden_list_'+listno+'_driver_highway_claim').val('2');
            $('input[id^=form_driver_highway_claim_'+listno+']').val('2');
        }

        return e.preventDefault();
    });

    // 時間外
    $('input[id^=overtime_fee]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[2];

        $('#hidden_list_'+listno+'_overtime_fee').val($(this).val());
        $('input[id^=overtime_fee_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 泊まり
    $('input[id^=stay]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[1];

        $('#hidden_list_'+listno+'_stay').val($(this).val());
        $('input[id^=stay_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 連結・ラップ
    $('input[id^=linking_wrap]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[2];

        $('#hidden_list_'+listno+'_linking_wrap').val($(this).val());
        $('input[id^=linking_wrap_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 手当
    $('input[id^=allowance]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[1];

        $('#hidden_list_'+listno+'_allowance').val($(this).val());
        $('input[id^=allowance_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

	// 往復
    $('input[id^=form_round_trip]').on('change', function(e) {
        var prop    = $(this).prop('checked');
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[3];

        if (!prop) {
            $(this).val('1');
            $('#hidden_list_'+listno+'_round_trip').val('1');
            $('input[id^=form_round_trip_'+listno+']').val('1');
        } else {
            $(this).val('2');
            $('#hidden_list_'+listno+'_round_trip').val('2');
            $('input[id^=form_round_trip_'+listno+']').val('2');
        }

        return e.preventDefault();
    });

	// 卸日計上
    $('input[id^=form_drop_appropriation]').on('change', function(e) {
        var prop    = $(this).prop('checked');
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[3];

        if (!prop) {
            $(this).val('1');
            $('#hidden_list_'+listno+'_drop_appropriation').val('1');
            $('input[id^=form_drop_appropriation_'+listno+']').val('1');
        } else {
            $(this).val('2');
            $('#hidden_list_'+listno+'_drop_appropriation').val('2');
            $('input[id^=form_drop_appropriation_'+listno+']').val('2');
        }

        return e.preventDefault();
    });

    // 受領書送付日
    $('input[id^=receipt_send_date]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[3];

        $('#hidden_list_'+listno+'_receipt_send_date').val($(this).val());
        $('input[id^=receipt_send_date_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 受領書受領日
    $('input[id^=receipt_receive_date]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[3];

        $('#hidden_list_'+listno+'_receipt_receive_date').val($(this).val());
        $('input[id^=receipt_receive_date_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 社内向け備考
    $('input[id^=in_house_remarks]').on('change', function(e) {
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[3];

        $('#hidden_list_'+listno+'_in_house_remarks').val($(this).val());
        $('input[id^=in_house_remarks_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // =================================================
    // 分載入力
    // =================================================
    // 積日
    $('input[id^=carrying_stack_date]').on('change', function(e) {

        var dispatch_list_no = $('input[id=dispatch_list_no]').val();
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[3];

        $('#hidden_list_'+dispatch_list_no+'_carrying_'+listno+'_stack_date').val($(this).val());
        $('input[id=carrying_stack_date_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 降日
    $('input[id^=carrying_drop_date]').on('change', function(e) {

        var dispatch_list_no = $('input[id=dispatch_list_no]').val();
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[3];

        $('#hidden_list_'+dispatch_list_no+'_carrying_'+listno+'_drop_date').val($(this).val());
        $('input[id=carrying_drop_date_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 積地
    $('input[id^=carrying_stack_place]').on('change', function(e) {

        var dispatch_list_no = $('input[id=dispatch_list_no]').val();
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[3];

        $('#hidden_list_'+dispatch_list_no+'_carrying_'+listno+'_stack_place').val($(this).val());
        $('input[id=carrying_stack_place_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 降地
    $('input[id^=carrying_drop_place]').on('change', function(e) {

        var dispatch_list_no = $('input[id=dispatch_list_no]').val();
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[3];

        $('#hidden_list_'+dispatch_list_no+'_carrying_'+listno+'_drop_place').val($(this).val());
        $('input[id=carrying_drop_place_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 得意先No
    $('input[id^=carrying_client_code]').on('change', function(e) {

        var dispatch_list_no = $('input[id=dispatch_list_no]').val();
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[3];
        var code    = '';

        if ($(this).val()) {
            code    = zeropadding($(this).val(), 5);
        }

        $('#hidden_list_'+dispatch_list_no+'_carrying_'+listno+'_client_code').val(code);
        $('input[id=carrying_client_code_'+listno+']').val(code);

        return e.preventDefault();
    });

    // 得意先名
    $('input[id^=carrying_client_name]').on('change', function(e) {

        var dispatch_list_no = $('input[id=dispatch_list_no]').val();
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[3];

        $('#hidden_list_'+dispatch_list_no+'_carrying_'+listno+'_client_name').val($(this).val());
        $('input[id=carrying_client_name_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 車種
    $('select[id^=carrying_car_model_code]').on('change', function(e) {

        var dispatch_list_no = $('input[id=dispatch_list_no]').val();
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[4];

        $('#hidden_list_'+dispatch_list_no+'_carrying_'+listno+'_car_model_code').val($(this).val());
        $('select[id=carrying_car_model_code_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 傭車先No
    $('input[id^=carrying_carrier_code]').on('change', function(e) {

        var dispatch_list_no = $('input[id=dispatch_list_no]').val();
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[3];
        var code    = '';

        if ($(this).val()) {
            code    = zeropadding($(this).val(), 5);
        }

        $('#hidden_list_'+dispatch_list_no+'_carrying_'+listno+'_carrier_code').val(code);
        $('input[id=carrying_carrier_code_'+listno+']').val(code);

        return e.preventDefault();
    });

    // 傭車先名
    $('input[id^=carrying_carrier_name]').on('change', function(e) {

        var dispatch_list_no = $('input[id=dispatch_list_no]').val();
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[3];

        $('#hidden_list_'+dispatch_list_no+'_carrying_'+listno+'_carrier_name').val($(this).val());
        $('input[id=carrying_carrier_name_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 車番
    $('input[id^=carrying_car_number]').on('change', function(e) {
        var dispatch_list_no = $('input[id=dispatch_list_no]').val();
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[3];

        $('#hidden_list_'+dispatch_list_no+'_carrying_'+listno+'_car_number').val($(this).val());
        $('input[id^=carrying_car_number_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 車両コード
    // 車番
    $('input[id^=carrying_car_code]').on('change', function(e) {
        var dispatch_list_no = $('input[id=dispatch_list_no]').val();
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[3];
        var code    = '';

        if ($(this).val()) {
            code    = zeropadding($(this).val(), 4);
        }

        $('#hidden_list_'+dispatch_list_no+'_carrying_'+listno+'_car_code').val(code);
        $('input[id^=carrying_car_code_'+listno+']').val(code);

        return e.preventDefault();
    });

    // ドライバー名
    $('input[id^=carrying_driver_name]').on('change', function(e) {
        var dispatch_list_no = $('input[id=dispatch_list_no]').val();
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[3];

        $('#hidden_list_'+dispatch_list_no+'_carrying_'+listno+'_driver_name').val($(this).val());
        $('input[id^=carrying_driver_name_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 社員コード
    $('input[id^=carrying_member_code]').on('change', function(e) {
        var dispatch_list_no = $('input[id=dispatch_list_no]').val();
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[3];
        var code    = '';

        if ($(this).val()) {
            code    = zeropadding($(this).val(), 5);
        }

        $('#hidden_list_'+dispatch_list_no+'_carrying_'+listno+'_member_code').val(code);
        $('input[id^=carrying_member_code_'+listno+']').val(code);

        return e.preventDefault();
    });

    // 社員電話番号
    $('input[id^=carrying_phone_number]').on('change', function(e) {
        var dispatch_list_no = $('input[id=dispatch_list_no]').val();
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[3];

        $('#hidden_list_'+dispatch_list_no+'_carrying_'+listno+'_phone_number').val($(this).val());
        $('input[id^=carrying_phone_number_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 運行先
    $('input[id^=carrying_destination]').on('change', function(e) {
        var dispatch_list_no = $('input[id=dispatch_list_no]').val();
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[2];

        $('#hidden_list_'+dispatch_list_no+'_carrying_'+listno+'_destination').val($(this).val());
        $('input[id^=carrying_destination_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 請求売上
    $('input[id^=carrying_claim_sales]').on('change', function(e) {
        var dispatch_list_no = $('input[id=dispatch_list_no]').val();
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[3];

        $('#hidden_list_'+dispatch_list_no+'_carrying_'+listno+'_claim_sales').val($(this).val());
        $('input[id^=carrying_claim_sales_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 傭車支払
    $('input[id^=carrying_carrier_payment]').on('change', function(e) {
        var dispatch_list_no = $('input[id=dispatch_list_no]').val();
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[3];

        $('#hidden_list_'+dispatch_list_no+'_carrying_'+listno+'_carrier_payment').val($(this).val());
        $('input[id^=carrying_carrier_payment_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 請求高速料金
    $('input[id^=carrying_claim_highway_fee]').on('change', function(e) {
        var dispatch_list_no = $('input[id=dispatch_list_no]').val();
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[4];

        $('#hidden_list_'+dispatch_list_no+'_carrying_'+listno+'_claim_highway_fee').val($(this).val());
        $('input[id^=carrying_claim_highway_fee_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 請求高速料金請求有無
    $('input[id^=form_carrying_claim_highway_claim]').on('change', function(e) {
        var dispatch_list_no = $('input[id=dispatch_list_no]').val();
        var prop    = $(this).prop('checked');
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[5];

        if (!prop) {
            $(this).val('1');
            $('#hidden_list_'+dispatch_list_no+'_carrying_'+listno+'_claim_highway_claim').val('1');
            $('input[id^=form_carrying_claim_highway_claim_'+listno+']').val('1');
        } else {
            $(this).val('2');
            $('#hidden_list_'+dispatch_list_no+'_carrying_'+listno+'_claim_highway_claim').val('2');
            $('input[id^=form_carrying_claim_highway_claim_'+listno+']').val('2');
        }

        return e.preventDefault();
    });

    // 傭車先高速料金
    $('input[id^=carrying_carrier_highway_fee]').on('change', function(e) {
        var dispatch_list_no = $('input[id=dispatch_list_no]').val();
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[4];

        $('#hidden_list_'+dispatch_list_no+'_carrying_'+listno+'_carrier_highway_fee').val($(this).val());
        $('input[id^=carrying_carrier_highway_fee_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // 傭車先高速料金請求有無
    $('input[id^=form_carrying_carrier_highway_claim]').on('change', function(e) {
        var dispatch_list_no = $('input[id=dispatch_list_no]').val();
        var prop    = $(this).prop('checked');
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[5];

        if (!prop) {
            $(this).val('1');
            $('#hidden_list_'+dispatch_list_no+'_carrying_'+listno+'_carrier_highway_claim').val('1');
            $('input[id^=form_carrying_carrier_highway_claim_'+listno+']').val('1');
        } else {
            $(this).val('2');
            $('#hidden_list_'+dispatch_list_no+'_carrying_'+listno+'_carrier_highway_claim').val('2');
            $('input[id^=form_carrying_carrier_highway_claim_'+listno+']').val('2');
        }

        return e.preventDefault();
    });

    // ドライバー高速料金
    $('input[id^=carrying_driver_highway_fee]').on('change', function(e) {
        var dispatch_list_no = $('input[id=dispatch_list_no]').val();
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[4];

        $('#hidden_list_'+dispatch_list_no+'_carrying_'+listno+'_driver_highway_fee').val($(this).val());
        $('input[id^=carrying_driver_highway_fee_'+listno+']').val($(this).val());

        return e.preventDefault();
    });

    // ドライバー高速料金請求有無
    $('input[id^=form_carrying_driver_highway_claim]').on('change', function(e) {
        var dispatch_list_no = $('input[id=dispatch_list_no]').val();
        var prop    = $(this).prop('checked');
        var id      = $(this).attr('id');
        var names   = id.split('_');
        var listno  = names[5];

        if (!prop) {
            $(this).val('1');
            $('#hidden_list_'+dispatch_list_no+'_carrying_'+listno+'_driver_highway_claim').val('1');
            $('input[id^=form_carrying_driver_highway_claim_'+listno+']').val('1');
        } else {
            $(this).val('2');
            $('#hidden_list_'+dispatch_list_no+'_carrying_'+listno+'_driver_highway_claim').val('2');
            $('input[id^=form_carrying_driver_highway_claim_'+listno+']').val('2');
        }

        return e.preventDefault();
    });

});
