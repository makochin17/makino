<script type="text/javascript">
    function CloseWindow() {
        if (/Chrome/i.test(navigator.userAgent)) {
            window.open("about:blank", "_self").close();
        } else {
            window.close();
        }
        return false;
    }
</script>

<section id="banner" style="padding-top:40px;">
    <div class="content">
        <div style="text-align:center;font-size:20px;margin-top:10px;">
            ログアウトしました
        </div>
        <br><br><br>
        <div style="text-align:center;margin-top:30px;">
            <?php echo Html::anchor(\Uri::create('auth/c0010'), Form::button('input_clear', 'ログインページへ', array('class' => 'buttonB'))); ?>
            <br><br><br><br>
            <?php echo Html::anchor('#', '完全にログアウトする', array('style' => 'color:blue;', 'onClick' => 'CloseWindow()')); ?>
        </div>
    </div>
</section>