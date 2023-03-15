        <h1><?php echo $dataInfo['realname'] ? $dataInfo['realname'] . '的主页' : '我的主页';?></h1>
        <div class="detial clear-fix">
        	<div class="photo">
                <?php
                if ($dataInfo['avatar']) {
                    ?>
                    <img src="<?php echo GetBrokerFace($dataInfo['avatar']);?>" />
                    <?php
                } elseif ($dataInfo['realname']) {
                    ?>
                    <div class="first-name-box"><div class="text"><?php echo mb_substr($dataInfo['realname'], 0, 1, 'utf-8');?></div></div>
                    <?php
                } else {
                    ?>
                    <img src="/images/demoPhoto.jpg" />
                    <?php
                }
                ?>
            </div>
            <div class="info clear-fix">
            	<dl>
                    <dt>姓　　名：</dt>
                    <dd><?php echo empty($dataInfo['realname']) ? '　' : $dataInfo['realname'];?><?php
                        if ($dataInfo['mobile_checked'] == 1){
                            echo '<img src="/images/icon/icon-mobile-authenticated.png" class="icon-right" title="手机已认证" />';
                        }
                        if ($dataInfo['status'] == 1){
                            echo '<img src="/images/icon/icon-user-authenticated.png" class="icon-right" title="实名已认证" />';
                        }
                        if (!empty($dataInfo['wechat_unionid'])) {
                            echo '<img src="/images/icon/icon-wechat-active.png" class="icon-right" title="微信已绑定" />';
                        }
                        /*if ($dataInfo['email_checked'] == 1){
                            echo '<img src="/images/icon/icon-email-authenticated.png" class="icon-right" title="邮箱已认证" />';
                        }*/
                        ?></dd>
                    <div class="clear"></div>
                </dl>
                <?php
                if (in_array($dataInfo['user_type'], array(1, 3, 4))) {
                ?>
                <dl>
                    <dt>公司门店：</dt>
                    <dd><?php echo $dataInfo['company'];?> - <?php echo $dataInfo['outlet'];?></dd>
                    <div class="clear"></div>
                </dl>
                    <?php
                }
                if ($region) {
                ?>
                    <dl>
                        <dt>所在区域：</dt>
                        <dd><?php echo $region;?></dd>
                        <div class="clear"></div>
                    </dl>
                <?php
                }
                if ($dataInfo['address']) {
                    ?>
                    <dl>
                        <dt>门店地址：</dt>
                        <dd><?php echo $dataInfo['address']; ?></dd>
                        <div class="clear"></div>
                    </dl>
                    <?php
                }
                    ?>
                <?php
                if (in_array($dataInfo['user_type'], array(1, 3, 4))) {
                    ?>
                <dl>
                    <dt>服务区域：</dt>
                    <dd><?php echo str_replace('|', '　', $dataInfo['servicearea']);?></dd>
                    <div class="clear"></div>
                </dl>
                    <?php
                }
                if ($dataInfo['mobile_format']) {
                    ?>
                <div class="phone-box clear-fix">
                    <div class="common-submit-btn"><?php echo $dataInfo['mobile_format'];?><span class="phone-tips">微信扫码拨号</span></div>
                    <div class="qrcode">
                        <img src="/upfile/qrcode.jpg?data=<?php echo urlencode($http_type . 'm.' . $page->basehost . '/' . $broker_city_info['url_name'] . '/shop/' . $dataInfo['id'] . '?call=1');?>">
                    </div>
                </div>
                <?php
                }
                ?>
            </div>
            <div class="shop-qrcode">
                <img src="/upfile/qrcode.jpg?data=<?php echo urlencode($http_type . $broker_city_info['url_name'] . '.' . $page->basehost . '/shop/' . $dataInfo['id']);?>" />
                <div class="tips">扫码分享我的店铺</div>
            </div>
        </div>
        <div class="m_tab">
            <ul>
                <li<?php echo $page->name == 'index' ? ' class="on"' : '';?>><a href="<?php echo $cfg['url_shop'].$id;?>">首页</a></li>
                <li<?php echo $page->name == 'sale' ? ' class="on"' : '';?>><a href="/shop/sale/list_<?php echo $id;?>_1_.html">出售房源</a></li>
                <li<?php echo $page->name == 'rent' ? ' class="on"' : '';?>><a href="/shop/rent/list_<?php echo $id;?>_1_.html">出租房源</a></li>
                <?php
                if (in_array($dataInfo['user_type'], array(1, 3, 4))) {
                ?>
                <li<?php echo $page->name == 'contact' ? ' class="on"' : '';?>><a href="/shop/contact_<?php echo $id;?>.html">个人介绍</a></li>
                <?php
                }
                ?>
            </ul>
        </div>