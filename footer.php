<div id="foot">
    <div id="foot_banner"><?php echo $website_footer_ad;?></div>
    <?php
    $region_list = get_region_enum($cityInfo['city_id'], 'sort');
    $house_type_array = array(
        array(
            'text' => '二手房',
            'url' => '/sale/list_0_{region_id}.html'
        ),
        array(
            'text' => '租房',
            'url' => '/rent/list_0_{region_id}.html'
        ),
        array(
            'text' => '写字楼出租',
            'url' => '/xzlcz/list_0_{region_id}.html'
        ),
        array(
            'text' => '商铺出租',
            'url' => '/spcz/list_0_{region_id}.html'
        ),
    );
    $condition = array(
        'website_id' => WEBHOSTID,
        'status' => 1,
        'link_type' => 1,
        'link_class' => 5,
        '(expire_time > ' . time() . ' OR expire_time = 0)'
    );
    if ($currentColumn == 'citylist'){
        $condition['city_website_id'] = 0;
    } else {
        $condition['city_website_id'] = intval($cityInfo['id']);
    }
    $linkNav = $query->field('id, link_text, link_url')->table('outlink')->where($condition)->order('list_order asc')->cache(3600)->all();

    //合并链接
    $link_list = array();
    foreach ($house_type_array as $house_type_item) {
        foreach ($region_list as $region_item) {
            $link_text = $region_item['region_name'] . $house_type_item['text'];
            $link_url = str_replace('{region_id}', $region_item['region_id'], $house_type_item['url']);
            $link_list[] = array(
                'link_text' => $link_text,
                'link_url' => $link_url
            );
            foreach ($linkNav as $key => $link_item) {
                if ($link_text == $link_item['link_text']) {
                    unset($linkNav[$key]);
                    break;
                }
            }
        }
    }

    foreach ($linkNav as $key => $link_item) {
        array_unshift($link_list, array('link_text' => $link_item['link_text'], 'link_url' => $link_item['link_url']));
    }

    if (!empty($link_list)) {
    ?>
    <div id="link_nav" class="nav-link">
        <h3>区域直达</h3>
        <?php
        foreach ($link_list as $key => $item){
            echo '<a href="'.$item['link_url'].'" target="_blank">'.$item['link_text'].'</a>';
        }
        ?>
        <div class="clear"></div>
    </div>
    <?php
    }
    if ($website_footer_ad_2) {
        echo '<div id="footer-ad-container">' . $website_footer_ad_2 . '</div>';
    }
    if ($currentColumn == 'citylist' || $currentColumn == 'index'){
    ?>
    <div id="links" class="nav-link">
        <h3>友情链接</h3>
        <?php
        $condition = array(
            'website_id' => WEBHOSTID,
            'link_class' => 1,
            '(expire_time > ' . time() . ' OR expire_time = 0)'
        );
        if ($currentColumn == 'citylist'){
            $condition['city_website_id'] = 0;
        }else{
            $condition['city_website_id'] = intval($cityInfo['id']);
        }
        $link = $query->table('outlink')->field('id, link_text, link_url')->where($condition)->order('list_order asc')->cache(3600)->all();
        foreach ($link as $key=>$item){
            echo '<a href="'.$item['link_url'].'" target="_blank">'.$item['link_text'].'</a>';
        }
        ?>
        <div class="clear"></div>
    </div>
    <?php
    }
    ?>
    <div class="section-split-line"></div>
    <div id="foot_left">
        <div class="copyright">
            Copyright &copy; 2015 版权所有 <?php echo $cfg['page']['gongsi'];?><br /><?php echo $cfg['page']['beian'];?> <?php echo $cfg['page']['tongji'];?>
        </div>
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
        <?php
        foreach ($aboutList as $about){
            ?>
            <a href="//www.<?php echo $cfg['page']['basehost'];?>/about/show-<?php echo $about['id'];?>.html"><?php echo $about['title'];?></a>
            <?php
        }
        ?>
        <a href="//www.<?php echo $cfg['page']['basehost'];?>/suggest.html">投诉与建议</a>
        <a href="//www.<?php echo $cfg['page']['basehost']; ?>/tools/broker.html">房产经纪人电话号码识别</a>
    </div>
    <div class="clear"></div>
</div>
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
            <img src="/upfile/qrcode.jpg?data=<?php echo urlencode($page_url);?>" />
            <div class="qrcode-title share-title">
                微信扫一扫<br />分享到好友或朋友圈
            </div>
        </div>
    </div>
    <div class="item" style="display: none;">
        <a class="icon suggest" href="/suggest.html" target="_blank"></a>
    </div>
    <div class="item <?php echo ($currentColumn == 'index' && $show == true) ? 'hover' : '';?>" id="wechat-qrcode-item" style="display: none;">
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
<?php
if ($currentColumn == 'index' && $show == true) {
    ?>
<script type="text/javascript">
    $(document).ready(function () {
        setTimeout(function () {
            $('#wechat-qrcode-item').removeClass('hover');
        }, 180000);
    });
</script>
<?php
}
?>
<?php
$disable_pop_dialog_count = intval($_COOKIE['disable_pop_dialog_count']);
if($show_foter_ad == true && $disable_pop_dialog_count < 1 && $currentColumn != 'city_list'
    && strpos($_SERVER['PHP_SELF'], 'member/reg.php') === false
    && strpos($_SERVER['PHP_SELF'], 'member/login.php') === false
    && strpos($_SERVER['PHP_SELF'], 'detail.php') === false){
    $disable_pop_dialog_count++;
?>
<div class="invite-box">
    <div class="container">
        <div class="main">
            <div class="title">温馨提示</div>
            <div class="title-sub">助力房产经纪人，疫情期间本站房源推广套餐买一个月送一个月，多买多送！</div>
            <div class="invite-btn">前往购买</div>
        </div>
        <div class="close-btn"></div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('.invite-box .close-btn').click(function (e) {
            e.stopPropagation();
            $('.invite-box').hide();
            var date = new Date();
            date.setTime(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()) + 57600000);
            $.cookie('disable_pop_dialog_count', <?php echo $disable_pop_dialog_count;?>, {path: '/', expires: date});
        });

        $('.invite-btn').click(function (e) {
            window.location.href = '/member/member_vip.php';
        });
    });
</script>
<?php
}
?>