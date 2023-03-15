<div id="foot">
    <!--{if $linknav}-->
    <div id="link_nav">
        <!--{foreach from=$linknav item=item key=key}--><a href="<!--{$item.link_url}-->" target="_blank"><!--{$item.link_text}--></a><!--{/foreach}-->
        <div class="clear"></div>
    </div>
    <!--{/if}-->
    <div class="section-split-line"></div>
    <div id="foot_left">
        <div class="copyright">Copyright &copy; 2020 版权所有 <!--{$cfg.page.gongsi}--><br /><!--{$cfg.page.beian}--> <!--{$cfg.page.tongji}--></div>
        <div class="footer-app-list clear-fix">
            <div class="item">
                <img src="/images/app/qrcode.jpg">
                <div class="name">第一房源公众号</div>
            </div>
            <div class="item">
                <img src="/images/app/applet.jpg">
                <div class="name">第一房源小程序</div>
            </div>
            <div class="item">
                <img src="/images/app/app.png">
                <div class="name">第一房源APP</div>
            </div>
        </div>
    </div>
    <div id="foot_right">
        <!--{foreach name=aboutlist from=$aboutlist item=item key=key}-->
        <a href="http://www.<!--{$cfg.page.basehost}-->/about/show-<!--{$item.id}-->.html"><!--{$item.title}--></a>
        <!--{/foreach}-->
        <a href="http://www.<!--{$cfg.page.basehost}-->/suggest.html">投诉与建议</a>
        <a href="http://www.<!--{$cfg.page.basehost}-->/tools/broker.html">房产经纪人电话号码识别</a>
    </div>
    <div class="clear"></div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('.close-btn').click(function () {
            $('.broker-identity-tips').slideUp();
            //设置Cookie，一天内不显示
            $.cookie('<!--{$cookie_prefix}-->hide_auth_tips', '1', {expires: 1, path: '/', domain: '<!--{$cfg.page.basehost}-->'});
        });
    });
</script>
<div class="float-box" id="float-box">
    <div class="item menu-item-box" style="display: none;">
        <a class="icon publish" href="/member/housePublish.php" target="_blank"></a>
        <div class="sub-menu-box">
            <div class="menu-item">
                <a href="/member/houseRent.php" target="_blank">发布出租</a>
                <a href="/member/houseSale.php" target="_blank">发布出售</a>
                <a href="/member/houseQiuzu.php" target="_blank">发布求租</a>
                <a href="/member/houseQiugou.php" target="_blank">发布求购</a>
            </div>
        </div>
    </div>
    <div class="item" style="display: none;">
        <div class="icon share"></div>
        <div class="wechat-qrcode">
            <img src="/upfile/qrcode.jpg?data=<!--{$page_url|urlencode}-->" />
            <div class="qrcode-title share-title">
                微信扫一扫<br />分享到好友或朋友圈
            </div>
        </div>
    </div>
    <div class="item" style="display: none;">
        <a class="icon suggest" href="/suggest.html" target="_blank"></a>
    </div>
    <div class="item" style="display: none;">
        <div class="icon wechat"></div>
        <div class="wechat-qrcode">
            <img src="/images/qrcode/wechat.jpg" />
            <div class="qrcode-title">扫描关注微信公众号方便查询房源</div>
        </div>
    </div>
    <div class="item toggle-btn">
        <div class="icon go-top"></div>
    </div>
</div>
<!--{if $show_score_increase == 1 && $memberInfo.increased_score > 0}-->
<script type="text/javascript">
    $(document).ready(function () {
        layer.msg('恭喜您赚取了 <!--{$memberInfo.increased_score}--> 个积分');
    });
</script>
<!--{/if}-->