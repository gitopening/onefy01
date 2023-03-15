<!--{if $website_all_top_ad}-->
<div class="site-top-ad"><!--{$website_all_top_ad}--></div>
<!--{/if}-->
<div class="section-wrap">
    <div id="head">
        <div id="logo"><a href="/"><img src="/images/logo.png" alt="<!--{$cityInfo.city_name}--><!--{$cfg.page.titlec}-->" /></a></div>
        <div id="select-city">
            <div id="current-city"><a href="//www.<!--{$cfg.page.basehost}-->/index.htm"><img src="/images/icon/icon-position.png" /><!--{$cityInfo.city_name}--></a></div>
        </div>
        <div id="nav">
            <ul>
                <li><a href="/">首页</a></li>
                <li class="menu<!--{if ($currentColumn == 'new')}--> on<!--{/if}-->">
                    <a href="/new/">新房</a>
                </li>
                <li class="menu<!--{if ($currentColumn == 'sale')}--> on<!--{/if}-->">
                    <a href="/sale/">二手房<span class="caret"></span></a>
                    <div class="sub-menu">
                        <div class="item"><a href="/sale/list_2.html">个人房源</a></div>
                        <div class="item"><a href="/sale/list_1.html">经纪人房源</a></div>
                        <div class="item"><a href="/sale/list_3.html">非中介机构房源</a></div>
                        <div class="item"><a href="/qiugou/">求购信息</a></div>
                    </div>
                </li>
                <li class="menu<!--{if ($currentColumn == 'rent' && $smarty.get.mtype != 5)}--> on<!--{/if}-->">
                    <a href="/rent/">租房<span class="caret"></span></a>
                    <div class="sub-menu">
                        <div class="item"><a href="/rent/list_2.html">个人房源</a></div>
                        <div class="item"><a href="/rent/list_1.html">经纪人房源</a></div>
                        <div class="item"><a href="/rent/list_3.html">经纪人房源</a></div>
                        <div class="item"><a href="/rent/list_5.html">品牌公寓</a></div>
                        <div class="item"><a href="/qiuzu/">求租信息</a></div>
                    </div>
                </li>
                <li class="<!--{if ($currentColumn == 'rent' && $mtype == 5)}-->on<!--{/if}-->"><a href="/rent/list_5.html" style="border-right:none;">品牌公寓</a></li>
                <li class="menu<!--{if ($currentColumn == 'xzl')}--> on<!--{/if}-->">
                    <a href="/xzlcz/">写字楼<span class="caret"></span></a>
                    <div class="sub-menu">
                        <div class="item"><a href="/xzlcz/">写字楼出租</a></div>
                        <div class="item"><a href="/xzlqz/">写字楼求租</a></div>
                        <div class="item"><a href="/xzlcs/">写字楼出售</a></div>
                        <div class="item"><a href="/xzlqg/">写字楼求购</a></div>
                    </div>
                </li>
                <li class="menu<!--{if ($currentColumn == 'sp')}--> on<!--{/if}-->">
                    <a href="/spcz/">商铺<span class="caret"></span></a>
                    <div class="sub-menu">
                        <div class="item"><a href="/spcz/">商铺出租</a></div>
                        <div class="item"><a href="/spqz/">商铺求租</a></div>
                        <div class="item"><a href="/spcs/">商铺出售</a></div>
                        <div class="item"><a href="/spqg/">商铺求购</a></div>
                    </div>
                </li>
                <li class="menu<!--{if ($currentColumn == 'cw')}--> on<!--{/if}-->">
                    <a href="/cwcz/">车位<span class="caret"></span></a>
                    <div class="sub-menu">
                        <div class="item"><a href="/cwcz/">车位出租</a></div>
                        <div class="item"><a href="/cwqz/">车位求租</a></div>
                        <div class="item"><a href="/cwzr/">车位转让</a></div>
                        <div class="item"><a href="/cwcs/">车位出售</a></div>
                        <div class="item"><a href="/cwqg/">车位求购</a></div>
                    </div>
                </li>
                <li class="menu<!--{if ($currentColumn == 'cf' || $currentColumn == 'ck')}--> on<!--{/if}-->">
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
                <li class="menu<!--{if ($currentColumn == 'broker')}--> on<!--{/if}-->">
                    <a href="/broker/">专业人士<span class="caret"></span></a>
                    <div class="sub-menu">
                        <div class="item"><a href="/broker/list_1_0_0_1_.html">房产经纪人</a></div>
                        <div class="item"><a href="/broker/list_4_0_0_1_.html">品牌公寓</a></div>
                        <div class="item"><a href="/broker/list_3_0_0_1_.html">非中介机构</a></div>
                    </div>
                </li>
                <li class="<!--{if ($currentColumn == 'news')}-->on<!--{/if}-->"><a href="/news/" style="border-right:none;">资讯</a></li>
                <li class="popup-nav">
                    <div class="text">下载APP</div>
                    <div class="popup-box">
                        <div class="logo"><img src="/images/qrcode/qrcode_logo.png" /></div>
                        <div class="name">第一房源</div>
                        <div class="desc">租房买房上第一房源</div>
                        <div class="qrcode">
                            <img src="/images/app/app.png" alt="<?php echo $page->titlec;?>">
                        </div>
                    </div>
                </li>
                <div class="clear"></div>
            </ul>
        </div>
        <div id="head_right">
                <!--{if $member_id}-->
            <div class="header-right-member">
                <div class="user-name"><a href="/member/"><!--{$header_username}--><span class="caret"></span></a></div>
                <div class="down-menu">
                    <div class="item"><a href="/member/">进入会员中心</a></div>
                    <div class="item"><a href="/member/house_new.php">发布新房</a></div>
                    <div class="item"><a href="/member/houseSale.php">发布出售</a></div>
                    <div class="item"><a href="/member/houseRent.php">发布出租</a></div>
                    <div class="item"><a href="/member/manage_house_new.php">管理新房</a></div>
                    <div class="item"><a href="/member/manageSale.php">管理出售</a></div>
                    <div class="item"><a href="/member/manageRent.php">管理出租</a></div>
                    <div class="item"><a href="/member/collect.php">我收藏的房源</a></div>
                    <div class="item"><a href="/member/member_vip.php">房源推广套餐</a><i class="flag-hot" style="right: 20px;">·</i></div>
                    <div class="item"><a href="/member/share.php">邀请好友赚积分</a><i class="flag-hot">·</i></div>
                    <div class="split-line"></div>
                    <div class="item"><a href="/member/brokerProfile.php">个人资料</a></div>
                    <!--{if $user_type == 1}-->
                    <div class="item"><a href="/member/brokerIdentity.php">实名认证</a></div>
                    <!--{/if}-->
                    <div class="item"><a href="/member/login.php?action=logout">安全退出</a></div>
                </div>
            </div>
                <!--{else}-->
            <p><a href="/member/login.php" class="touxiang">登录</a><span>/</span><a href="/member/reg.php">注册</a></p>
                <!--{/if}-->
        </div>
        <div class="clear"></div>
    </div>
</div>
<!--{if $disable_header_ad_count <= 3 && $show_header_ad == true}-->
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
            $.cookie('disable_header_ad_count', <!--{$disable_pop_dialog_count}-->, {path: '/', expires: date});
        });
    });
</script>
<!--{/if}-->