<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="renderer" content="webkit" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><!--{$cfg.page.title}--> - 用户中心</title>
<!--{include file="inc/member_header.tpl"}-->
</head>
<body>
<!--{include file="inc/newhead.tpl"}-->
<div class="clear"></div>
<div class="main">
  <div style="margin-left:auto; margin-right:auto;">
    <div class="page">
      <!--{include file="inc/member_left.tpl"}-->
      <div class="memberBox" style="border:none;">
        <div class="infoTipBox">
          <div id="memberinfo">
              <div class="right-top-box">
                  <!--{if $today_sign_data}-->
                  <div class="sign-link signed-link"><a href="sign.php"><img src="/images/icon/icon-signed.png" />已签到</a></div>
                  <div class="sign-tips">明日签到+<!--{$sign_score}--> 积分</div>
                  <!--{else}-->
                  <div class="sign-link"><a href="sign.php"><img src="/images/icon/icon-sign.png" />签到</a></div>
                  <div class="sign-tips">今日签到+<!--{$sign_score}--> 积分</div>
                  <!--{/if}-->
              </div>
            <div class="memberface">
                <a href="brokerIdentity.php">
                    <!--{if $memberInfo.avatar}-->
                    <div class="img" style="background-image: url(<!--{$memberInfo.avatar}-->)"></div>
                    <!--{elseif $memberInfo.first_name}-->
                    <div class="img first-name-box"><div class="text"><!--{$memberInfo.first_name}--></div></div>
                    <!--{else}-->
                    <div class="img"></div>
                    <!--{/if}-->
                </a>
            </div>
            <div class="memberright">
                <div class="loginTip">
                    欢迎您，<!--{if $memberInfo.realname}--><!--{$memberInfo.realname}--><!--{else}--><!--{$memberInfo.username}--><!--{/if}-->
                    <a href="brokerProfile.php" title="点击切换会员类型">(<!--{if $memberInfo.user_type == 1}-->经纪人<!--{elseif $memberInfo.user_type == 2}-->个人<!--{elseif $memberInfo.user_type == 3}--><!--{if $memberInfo.user_type_sub == 1}-->
                        物业公司
                        <!--{elseif $memberInfo.user_type_sub == 2}-->
                        开发商
                        <!--{elseif $memberInfo.user_type_sub == 3}-->
                        拍卖机构
                        <!--{elseif $memberInfo.user_type_sub == 4}-->
                        其它公司
                        <!--{else}-->
                        非中介机构
                        <!--{/if}--><!--{elseif $memberInfo.user_type == 4}-->品牌公寓<!--{/if}-->)</a>
                    <span class="member-score"><i class="icon-score" title="积分"></i></span><span class="score"><!--{$memberInfo.score}--> 积分</span>
                    <a href="share.php" class="member-score-link" style="margin-left: 20px;">我要赚积分</a>
                </div>
              <ul id="rz">
                <li class="base-info">
                    <b><!--{if $memberInfo.mobile_checked == 1}--><a href="mobile_auth.php" class="mobile-auth-url"><!--{$memberInfo.mobile}--></a><!--{else}--><!--{$memberInfo.mobile}--><!--{/if}--></b>
                    <!--{if $memberInfo.mobile_checked == 1}--><a href="mobile_auth.php" class="icon-mobile-authenticated" title="手机已认证"></a><!--{else}--><a href="mobile_auth.php" class="icon-mobile-not-authenticated" title="手机未认证，点击认证"></a><!--{/if}-->
                    <!--{if $memberInfo.status == 1}-->
                    <a href="brokerIdentity.php" class="icon-user-authenticated" title="实名已认证"></a>
                    <!--{else}-->
                    <a href="brokerIdentity.php" class="icon-user-not-authenticated" title="实名未认证，点击认证"></a>
                    <!--{/if}-->
                    <!--{if $memberInfo.wechat_unionid == ''}-->
                    <a href="bind_account.php" class="icon-wechat-1" title="未绑定微信"></a>
                    <!--{else}-->
                    <a href="bind_account.php" class="icon-wechat-1-active" title="已绑定微信"></a>
                    <!--{/if}-->
                </li>
                <li class="memuniversityTip"><b>我的网上店铺</b> <a href="<!--{$http_type}--><!--{$member_city_info.url_name}-->.<!--{$cfg.page.basehost}-->/shop/<!--{$memberInfo.id}-->" class="underline" target="_blank"><!--{$http_type}--><!--{$member_city_info.url_name}-->.<!--{$cfg.page.basehost}-->/shop/<!--{$memberInfo.id}--></a></li>
              </ul>
            </div>
            <div class="clear"></div>
          </div>
          <div class="member-info-tips">
              <!--{if $memberInfo.user_level_id > 1 && ($memberInfo.user_level_expire_time + 604800) > $smarty.now}-->
              <div class="member-vip-type">
                  <b <!--{if ($memberInfo.user_level_expire_time - 604800) < $smarty.now}-->class="text-red"<!--{/if}-->>您的房源推广套餐“<!--{$opened_user_level_info.level_name}-->”<!--{if $memberInfo.user_level_expire_time > $smarty.now}-->有效期至：<span <!--{if ($memberInfo.user_level_expire_time - 604800) < $smarty.now}-->class="text-red"<!--{/if}-->><!--{$user_level_expire_time}--></span><!--{else}--><span class="text-red">已过期</span><!--{/if}-->
                  <!--{if ($memberInfo.user_level_expire_time - 604800) < $smarty.now}--><a href="member_vip.php" class="member-vip-btn">立即续费</a><!--{/if}--></b>
              </div>
              <!--{/if}-->
              <!--{if $quantity > 0}-->
              <div class="member-vip-type" id="get-gift-box">
                  <span class="get-gift">恭喜您“获得免费领取 <!--{$quantity}--> 支签字笔的资格”</span><span class="get-gift-btn">填写收件地址</span>
              </div>
              <!--{/if}-->
          </div>
          <div>
            <h4 class="member-index-title publish-title">我发布的房源<!--{if $house_used_count.count}--><span class="count"><!--{$house_used_count.count}--></span><!--{/if}--></h4>
            <ul class="houseTip">
                <li><a href="manage_house_new.php" class="underline familyAlpha size14px weightBold"><span class="caption">新房</span><span class="number"><!--{$house_used_count.new}--></span></a></li>
                <li><a href="manageSale.php" class="underline familyAlpha size14px weightBold"><span class="caption">出售房源</span><span class="number"><!--{$house_used_count.sale}--></span></a></li>
                <li><a href="manageRent.php" class="underline familyAlpha size14px weightBold"><span class="caption">出租房源</span><span class="number"><!--{$house_used_count.rent}--></span></a></li>
                <li><a href="manageQiugou.php" class="underline familyAlpha size14px weightBold"><span class="caption">求购信息</span><span class="number"><!--{$house_used_count.qiugou}--></span></a></li>
                <li><a href="manageQiuzu.php" class="underline familyAlpha size14px weightBold"><span class="caption">求租信息</span><span class="number"><!--{$house_used_count.qiuzu}--></span></a></li>
            </ul>
          </div>
            <h4 class="member-index-title">房源推广套餐对照</h4>
          <div class="member-vip-data-list vip-type-info">
            <table cellspacing="1">
                <tr>
                    <td class="td-title left-title" width="20%">功能权限</td>
                    <!--{foreach from=$user_level_list item=item key=key}-->
                    <td class="td-title" width="20%"><!--{$item.level_name}--></td>
                    <!--{/foreach}-->
                </tr>
                <tr style="display: none;">
                    <td class="left-title">发布/刷新/修改数量</td>
                    <!--{foreach from=$user_level_list item=item key=key}-->
                    <td><!--{$item.house_day_refresh}--> 条/每天</td>
                    <!--{/foreach}-->
                </tr>
                <tr>
                    <td class="left-title">库存房源数量</td>
                    <!--{foreach from=$user_level_list item=item key=key}-->
                    <td>库存 <!--{$item.house_stock}--> 条</td>
                    <!--{/foreach}-->
                </tr>
                <tr>
                    <td class="left-title">房源自动刷新</td>
                    <!--{foreach from=$user_level_list item=item key=key}-->
                    <td><!--{if $item.id == 1}-->无自动刷新<!--{else}-->每隔1小时刷新1次<!--{/if}--></td>
                    <!--{/foreach}-->
                </tr>
                <tr style="display: none;">
                    <td class="left-title">房源最大图片上传量</td>
                    <!--{foreach from=$user_level_list item=item key=key}-->
                    <td><!--{$item.house_pic_max}--> 张/每条房源</td>
                    <!--{/foreach}-->
                </tr>
                <tr>
                    <td class="left-title">价格</td>
                    <!--{foreach from=$user_level_list item=item key=key}-->
                    <td><!--{$item.month_price}--></td>
                    <!--{/foreach}-->
                </tr>
                <tr>
                    <td class="left-title">购买或升级套餐</td>
                    <!--{foreach from=$user_level_list item=item key=key}-->
                    <td><!--{if $item.id == 1}-->—<!--{else}--><a href="member_vip.php?level_id=<!--{$item.id}-->" class="buy-btn">前往购买<!--{/if}--></a></td>
                    <!--{/foreach}-->
                </tr>
            </table>
              <div class="refresh-tips">房源刷新时间：早7:00 — 晚23:00</div>
          </div>
        </div>
      </div>
      <div class="clear"></div>
    </div>
    <div class="clear"></div>
  </div>
  <div style="background-image:none;" class="reg_c"></div>
</div>
<div class="clear"></div>
<!--{include file="inc/newfoot.tpl"}-->
<!--{if $order_info}-->
<div id="get-gift-popup">
    <div class="get-gift-box" id="get-gift-box">
        <div class="form-title">填写收件人信息</div>
        <form action="/order/return_url.php" name="gift_form" id="gift-form" method="post">
            <input type="hidden" name="action" id="action" value="get_gift" />
            <input type="hidden" name="order_id" id="order_id" value="<!--{$order_info.id}-->" />
            <div class="item clearfix">
                <div class="caption">收件人姓名</div>
                <div class="control-wrap">
                    <label>
                        <input type="text" name="consignee" id="consignee" value="<!--{if $memberInfo.realname}--><!--{$memberInfo.realname}--><!--{else}--><!--{$memberInfo.username}--><!--{/if}-->" style="width: 150px;" datatype="s2-10" sucmsg=" " nullmsg="请填写收件人姓名" errormsg="请填写收件人姓名" />
                    </label>
                </div>
            </div>
            <div class="item clearfix">
                <div class="caption">联系电话</div>
                <div class="control-wrap">
                    <label>
                        <input type="text" name="mobile" id="mobile" value="<!--{$memberInfo.mobile}-->" style="width: 150px;" datatype="m" sucmsg=" " nullmsg="请填写您的手机号" errormsg="手机号码错误" />
                    </label>
                </div>
            </div>
            <div class="item clearfix">
                <div class="caption">所在地区</div>
                <div class="control-wrap">
                    <select id="province_id" name="province_id" datatype="n" sucmsg=" " nullmsg="请选择所在地区" errormsg="请选择所在地区">
                        <option value="">请选择</option>
                        <!--{foreach from=$province_option item=item key=key}-->
                        <!--{if $memberInfo.province_id == $item.region_id}-->
                        <option value="<!--{$item.region_id}-->" selected="selected"><!--{$item.region_name}--></option>
                        <!--{else}-->
                        <option value="<!--{$item.region_id}-->"><!--{$item.region_name}--></option>
                        <!--{/if}-->
                        <!--{/foreach}-->
                    </select>
                    <select id="city_id" name="city_id" datatype="n" sucmsg=" " nullmsg="请选择所在地区" errormsg="请选择所在地区">
                        <option value="">请选择</option>
                        <!--{foreach from=$city_option item=item key=key}-->
                        <!--{if $memberInfo.city_id == $item.region_id}-->
                        <option value="<!--{$item.region_id}-->" selected="selected"><!--{$item.region_name}--></option>
                        <!--{else}-->
                        <option value="<!--{$item.region_id}-->"><!--{$item.region_name}--></option>
                        <!--{/if}-->
                        <!--{/foreach}-->
                    </select>
                    <select id="region_id" name="region_id" datatype="n" sucmsg=" " nullmsg="请选择所在地区" errormsg="请选择所在地区">
                        <option value="">请选择</option>
                        <!--{foreach from=$cityarea_option item=item key=key}-->
                        <!--{if $memberInfo.cityarea_id == $item.region_id}-->
                        <option value="<!--{$item.region_id}-->" selected="selected"><!--{$item.region_name}--></option>
                        <!--{else}-->
                        <option value="<!--{$item.region_id}-->"><!--{$item.region_name}--></option>
                        <!--{/if}-->
                        <!--{/foreach}-->
                    </select>
                </div>
            </div>
            <div class="item clearfix">
                <div class="caption">收件地址</div>
                <div class="control-wrap">
                    <label>
                        <input type="text" name="address" id="address" value="" datatype="s3-250" sucmsg=" " nullmsg="请填写收件地址" errormsg="请填写收件地址" />
                    </label>
                </div>
            </div>
            <div class="item">
                <input type="submit" class="common-submit-btn submit-btn" value="确认提交" />
            </div>
        </form>
        <script type="text/javascript">
            $(function () {
                var validForm = $("#gift-form").Validform({
                    tiptype: 3,
                    showAllError: true,
                    ajaxPost: true,
                    callback: function (data) {
                        layer.closeAll();
                        $('#Validform_msg').remove();
                        if (data.error == 0) {
                            layer.alert(data.msg, {icon: 1});
                            $('#get-gift-box').remove();
                            return true;
                        } else {
                            layer.alert(data.msg, {icon: 2});
                            return false;
                        }
                    }
                });
            });
        </script>
    </div>
</div>
<script type="text/javascript">
    var getGiftBoxHTML = $('#get-gift-popup').html();
    $('#get-gift-popup').remove();
    $(document).ready(function () {
        $('.get-gift-btn').click(function () {
            //判断是否已领取过
            layer.load(2);
            $.post('/order/return_url.php', {
                action: 'check_is_get_gift',
                order_id: <!--{$order_info.id}-->,
            t: new Date().getTime()
        }, function (data) {
                layer.closeAll();
                if (data.error == 0) {
                    layer.open({
                        type: 1,
                        area:  ['600px', ''],
                        title: '',
                        content: getGiftBoxHTML,
                        success: function (layero, index) {
                            $('#province_id').change(function () {
                                SetSubRegion('city_id', $(this).val());
                                $('#city_id').html('<option value="">请选择</option>');
                                $('#region_id').html('<option value="">请选择</option>');
                            });
                            $('#city_id').change(function () {
                                SetSubRegion('region_id', $(this).val());
                            });
                        }
                    });
                } else {
                    layer.alert(data.msg, {icon: 2});
                }
            }, 'json');
        });
    });
</script>
<!--{/if}-->
<!--{if $show_foter_ad == true && $disable_pop_dialog_count <= 1}-->
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
            $.cookie('disable_pop_dialog_count', <!--{$disable_pop_dialog_count}-->, {path: '/', expires: date});
        });

        $('.invite-btn').click(function (e) {
            window.location.href = '/member/member_vip.php';
        });
    });
</script>
<!--{/if}-->
</body>
</html>