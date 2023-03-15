<?php
if (empty($cityInfo['city_name'])){
    if (!empty($_COOKIE[COOKIE_PREFIX . 'cityid'])){
        $cityID = intval($_COOKIE[COOKIE_PREFIX . 'cityid']);
        $website_info = $query->field('id, url_name')->table('city_website')->where('id = ' . intval($cityID))->cache(true)->one();
        $url_name = $website_info['url_name'];
        $cityInfo = get_city_info($url_name);
        $current_city_name = $cityInfo['city_name'];
    }
}else{
    $current_city_name = $cityInfo['city_name'];
}
?>
<?php
if ($website_all_top_ad && $currentColumn != 'citylist') {
    echo '<div class="site-top-ad">' . $website_all_top_ad . '</div>';
}
?>
<div class="section-wrap">
    <div id="head">
        <div id="logo"><a href="/"><img src="/images/logo.png" alt="<?php echo $current_city_name.$page->titlec;?>" /></a></div>
        <?php
        if($currentColumn != 'citylist') {
            ?>
            <div id="select-city">
                <div id="current-city"><a href="//www.<?php echo $cfg['page']['basehost']; ?>/index.htm"><img src="/images/icon/icon-position.png" /><?php echo $current_city_name; ?></a></div>
            </div>
            <?php
        }
        ?>
        <?php
        if($currentColumn != 'citylist') {
            ?>
            <div id="nav">
                <ul>
                    <li class="menu<?php if ($currentColumn == 'index') {
                        echo ' on';
                    } ?>"><a href="/">首页</a></li>
                    <li class="menu<?php if ($currentColumn == 'new') {
                        echo ' on';
                    } ?>">
                        <a href="/new/">新房</a>
                    </li>
                    <li class="menu<?php if ($currentColumn == 'sale') {
                        echo ' on';
                    } ?>">
                        <a href="/sale/">二手房<span class="caret"></span></a>
                        <div class="sub-menu">
                            <div class="item"><a href="/sale/list_2.html">个人房源</a></div>
                            <div class="item"><a href="/sale/list_1.html">经纪人房源</a></div>
                            <div class="item"><a href="/sale/list_3.html">非中介机构房源</a></div>
                            <div class="item"><a href="/qiugou/">求购信息</a></div>
                        </div>
                    </li>
                    <li class="menu<?php if ($currentColumn == 'rent' && $mtype != 5) {
                        echo ' on';
                    } ?>">
                        <a href="/rent/">租房<span class="caret"></span></a>
                        <div class="sub-menu">
                            <div class="item"><a href="/rent/list_2.html">个人房源</a></div>
                            <div class="item"><a href="/rent/list_1.html">经纪人房源</a></div>
                            <div class="item"><a href="/rent/list_3.html">非中介机构房源</a></div>
                            <div class="item"><a href="/rent/list_5.html">品牌公寓</a></div>
                            <div class="item"><a href="/qiuzu/">求租信息</a></div>
                        </div>
                    </li>
                    <li<?php
                    if ($currentColumn == 'rent' && $mtype == 5) {
                        echo ' class="on"';
                    } ?>><a href="/rent/list_5.html" style="border-right:none;">品牌公寓</a></li>
                    <li class="menu<?php if ($currentColumn == 'xzl') {
                        echo ' on';
                    } ?>">
                        <a href="/xzlcz/">写字楼<span class="caret"></span></a>
                        <div class="sub-menu">
                            <div class="item"><a href="/xzlcz/">写字楼出租</a></div>
                            <div class="item"><a href="/xzlqz/">写字楼求租</a></div>
                            <div class="item"><a href="/xzlcs/">写字楼出售</a></div>
                            <div class="item"><a href="/xzlqg/">写字楼求购</a></div>
                        </div>
                    </li>
                    <li class="menu<?php if ($currentColumn == 'sp') {
                        echo ' on';
                    } ?>">
                        <a href="/spcz/">商铺<span class="caret"></span></a>
                        <div class="sub-menu">
                            <div class="item"><a href="/spcz/">商铺出租</a></div>
                            <div class="item"><a href="/spqz/">商铺求租</a></div>
                            <div class="item"><a href="/spcs/">商铺出售</a></div>
                            <div class="item"><a href="/spqg/">商铺求购</a></div>
                        </div>
                    </li>
                    <li class="menu<?php if ($currentColumn == 'cw') {
                        echo ' on';
                    } ?>">
                        <a href="/cwcz/">车位<span class="caret"></span></a>
                        <div class="sub-menu">
                            <div class="item"><a href="/cwcz/">车位出租</a></div>
                            <div class="item"><a href="/cwqz/">车位求租</a></div>
                            <div class="item"><a href="/cwzr/">车位转让</a></div>
                            <div class="item"><a href="/cwcs/">车位出售</a></div>
                            <div class="item"><a href="/cwqg/">车位求购</a></div>
                        </div>
                    </li>
                    <li class="menu<?php if ($currentColumn == 'cf' || $currentColumn == 'ck') {
                        echo ' on';
                    } ?>">
                        <a href="/cfcz/">厂房/仓库<span class="caret"></span></a>
                        <div class="sub-menu">
                            <div class="item"><a href="/cfcz/">厂房出租</a></div>
                            <div class="item"><a href="/cfqz/">厂房求租</a></div>
                            <div class="item"><a href="/cfzr/">厂房转让</a></div>
                            <div class="item"><a href="/cfcs/">厂房出售</a></div>
                            <div class="item"><a href="/cfqg/">厂房求购</a></div>
                            <div class="item"><a href="/ckcz/">仓库出租</a></div>
                            <div class="item"><a href="/ckqz/">仓库求租</a></div>
                            <div class="item"><a href="/ckzr/">仓库转让</a></div>
                            <div class="item"><a href="/ckcs/">仓库出售</a></div>
                            <div class="item"><a href="/ckqg/">仓库求购</a></div>
                        </div>
                    </li>
                    <li class="menu<?php if ($currentColumn == 'broker') {
                        echo ' on';
                    } ?>">
                        <a href="/broker/">专业人士<span class="caret"></span></a>
                        <div class="sub-menu">
                            <div class="item"><a href="/broker/list_1_0_0_1_.html">房产经纪人</a></div>
                            <div class="item"><a href="/broker/list_4_0_0_1_.html">品牌公寓</a></div>
                            <div class="item"><a href="/broker/list_3_0_0_1_.html">非中介机构</a></div>
                        </div>
                    </li>
                    <li<?php if ($currentColumn == 'news') {
                        echo ' class="on"';
                    } ?>><a href="//www.<?php echo $cfg['page']['basehost'];?>/news/" style="border-right:none;">资讯</a></li>
                    <li class="menu<?php if ($currentColumn == 'softzjb') {
                                                    echo ' on';
                                                } ?>">
                                                    <a href="/softzjb/">软件游戏<span class="caret"></span></a>
                                                    <div class="sub-menu">
                                                        <div class="item"><a href="/softmenu/soft_2_0_2_3_.html">手机电脑软件</a></div>
                                                        <div class="item"><a href="/gamemenu/soft_2_0_2_2_.html">桌游手游</a></div>

                                                    </div>
                                                </li>
                    <div class="clear"></div>
                </ul>
            </div>
            <?php
        }
        ?>
        <div id="head_right">
            <?php
            //会员链接地址
            if ($cityInfo['url_name']) {
                $member_url = '//' . $cityInfo['url_name'] . '.' . $cfg['page']['basehost'];
            } elseif ($position_city_info['url_name']) {
                $member_url = '//' . $position_city_info['url_name'] . '.' . $cfg['page']['basehost'];
            } else {
                $member_url = '';
            }
            if ($member_url) {
                if ($member_id){
                    //$user_name = empty($memberInfo['realname']) ? $memberInfo['username'] : $memberInfo['realname'];
                    ?>
                    <div class="header-right-member">
                        <div class="user-name"><a href="<?php echo $member_url;?>/member/"><?php echo $header_username;?><span class="caret"></span></a></div>
                        <div class="down-menu">
                            <div class="item"><a href="<?php echo $member_url;?>/member/">进入会员中心</a></div>
                            <div class="item"><a href="<?php echo $member_url;?>/member/house_new.php">发布新房</a></div>
                            <div class="item"><a href="<?php echo $member_url;?>/member/houseSale.php">发布出售</a></div>
                            <div class="item"><a href="<?php echo $member_url;?>/member/houseRent.php">发布出租</a></div>
                            <div class="item"><a href="<?php echo $member_url;?>/member/manage_house_new.php">管理新房</a></div>
                            <div class="item"><a href="<?php echo $member_url;?>/member/manageSale.php">管理出售</a></div>
                            <div class="item"><a href="<?php echo $member_url;?>/member/manageRent.php">管理出租</a></div>
                            <div class="item"><a href="<?php echo $member_url;?>/member/collect.php">我收藏的房源</a></div>
                            <div class="item"><a href="<?php echo $member_url;?>/member/member_vip.php">房源推广套餐</a><i class="flag-hot" style="right: 20px;">·</i></div>
                            <div class="item"><a href="<?php echo $member_url;?>/member/share.php">邀请好友赚积分</a><i class="flag-hot">·</i></div>
                            <div class="split-line"></div>
                            <div class="item"><a href="<?php echo $member_url;?>/member/brokerProfile.php">个人资料</a></div>
                            <?php
                            if ($memberInfo['user_type'] == 1) {
                            ?>
                            <div class="item"><a href="<?php echo $member_url;?>/member/brokerIdentity.php">实名认证</a></div>
                            <?php
                            }
                            ?>
                            <div class="item"><a href="<?php echo $member_url;?>/member/login.php?action=logout">安全退出</a></div>
                        </div>
                    </div>
                    <?php
                } else {
                    echo '<div class="header-login-box"><a href="' . $member_url . '/member/login.php" class="touxiang">登录</a><span>/</span><a href="' . $member_url . '/member/reg.php">注册</a></div>';
                }
            }
            ?>
        </div>
        <div class="clear"></div>
    </div>
</div>
<?php
if ($website_header_ad && $currentColumn != 'index') {
    echo '<div class="section-box" id="website-header-ad"><div class="ad-list">' . $website_header_ad . '</div></div>';
}
?>
<?php
if ($currentColumn != 'index' && $currentColumn != 'login_reg' && $disable_header_ad_count <= 3 && $show_header_ad == true) {
?>
<div class="vip-header-tips">
    <div class="tip">
        <div class="title">新年钜惠！即日起至2022年2月1日购买房源发布套餐买一个月送一个月，多买多送。限时特惠！<a href="javascript:void(0);" onclick="BuyVipButton();">点击购买</a></div>
        <div class="desc"></div>
    </div>
    <div class="close-btn"></div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('.vip-header-tips .close-btn').click(function (e) {
            $('.vip-header-tips').hide();
            e.stopPropagation();
            var date = new Date();
            date.setTime(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()) + 57600000);
            $.cookie('disable_header_ad_count', <?php echo $disable_header_ad_count;?>, {path: '/', expires: date});
        });
    });
</script>
<?php
}