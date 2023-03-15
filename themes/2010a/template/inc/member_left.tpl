<div class="memberLeftNav">
    <!--{if $member_page_name != 'index'}-->
    <div class="back-member-home"><a href="/member/index.php">返回会员中心首页</a></div>
    <!--{/if}-->
    <ul class="navList">
		<li style="display: none;"><a href="index.php" <!--{if $navmenu=='index'}--> class="hover"<!--{/if}-->><span>会员中心首页</span></a></li>
		<li>
			<div class="item-caption"><span>发布房源</span></div>
			<div class="sub-item">
				<a href="houseRent.php" <!--{if $navmenu=='houseRent'}--> class="hover"<!--{/if}-->><span>发布出租</span></a>
				<a href="houseSale.php" <!--{if $navmenu=='houseSale'}--> class="hover"<!--{/if}-->><span>发布出售</span></a>
				<a href="house_new.php" <!--{if $navmenu=='house_new'}--> class="hover"<!--{/if}-->><span>发布新房</span></a>
				<a href="houseQiugou.php" <!--{if $navmenu=='houseQiugou'}--> class="hover"<!--{/if}-->><span>发布求购</span></a>
				<a href="houseQiuzu.php" <!--{if $navmenu=='houseQiuzu'}--> class="hover"<!--{/if}-->><span>发布求租</span></a>
				<a href="news.php" <!--{if $navmenu=='news'}--> class="hover"<!--{/if}-->><span>发表博文资讯</span></a>
				<div class="clear"></div>
			</div>
		</li>
		<li>
			<div class="item-caption"><span>管理房源</span><!--{if $house_used_count.count}--><span class="count"><!--{$house_used_count.count}--></span><!--{/if}--></div>
            <div class="sub-item">
				<a href="manageRent.php" <!--{if $navmenu=='manageRent'}--> class="hover"<!--{/if}-->><span>管理出租</span><!--{if $house_used_count.rent}--><span class="count"><!--{$house_used_count.rent}--></span><!--{/if}--></a>
				<a href="manageSale.php" <!--{if $navmenu=='manageSale'}--> class="hover"<!--{/if}-->><span>管理出售</span><!--{if $house_used_count.sale}--><span class="count"><!--{$house_used_count.sale}--></span><!--{/if}--></a>
				<a href="manage_house_new.php" <!--{if $navmenu=='manage_house_new'}--> class="hover"<!--{/if}-->><span>管理新房</span><!--{if $house_used_count.new}--><span class="count"><!--{$house_used_count.new}--></span><!--{/if}--></a>
				<a href="manageQiugou.php" <!--{if $navmenu=='manageQiugou'}--> class="hover"<!--{/if}-->><span>管理求购</span><!--{if $house_used_count.qiugou}--><span class="count"><!--{$house_used_count.qiugou}--></span><!--{/if}--></a>
				<a href="manageQiuzu.php" <!--{if $navmenu=='manageQiuzu'}--> class="hover"<!--{/if}-->><span>管理求租</span><!--{if $house_used_count.qiuzu}--><span class="count"><!--{$house_used_count.qiuzu}--></span><!--{/if}--></a>
				<a href="news_list.php" <!--{if $navmenu=='news_list'}--> class="hover"<!--{/if}-->><span>管理博文资讯</span></a>
            </div>
		</li>
		<li>
			<div class="item-caption"><span>账户管理与设置</span></div>
			<div class="sub-item">
				<a href="member_vip.php" <!--{if $navmenu=='member_vip'}--> class="hover"<!--{/if}-->><span>房源推广套餐</span><i class="flag-hot"></i></a>
				<!--{if $user_type == 5}-->
				<a href="news.php" <!--{if $navmenu=='news'}--> class="hover"<!--{/if}-->><span>发布新闻资讯</span></a>
				<a href="news_list.php" <!--{if $navmenu=='news_list'}--> class="hover"<!--{/if}-->><span>管理新闻资讯</span></a>
				<!--{/if}-->
				<a href="share.php" <!--{if $navmenu=='share'}--> class="hover"<!--{/if}-->><span>邀请好友，赚取<!--{$web_config.score_invite_new_user}-->积分</span><i class="flag-new"></i></a>
				<a href="subscribe_leader.php" style="display: none;" <!--{if $navmenu=='subscribe_leader'}--> class="hover"<!--{/if}-->><span>房源订阅</span><i class="flag-new" style="right:25px;"></i></a>
				<a href="brokerProfile.php" <!--{if $navmenu=='brokerProfile'}--> class="hover"<!--{/if}-->><span>个人资料</span></a>
				<a href="brokerIdentity.php" <!--{if $navmenu=='brokerIdentity'}--> class="hover"<!--{/if}-->><span>实名认证</span></a>
				<a href="collect.php" <!--{if $navmenu=='collect'}--> class="hover"<!--{/if}-->><span>我收藏的房源</span></a>
                <a href="order.php" <!--{if $navmenu=='order'}--> class="hover"<!--{/if}-->><span>订单管理</span></a>
                <a href="score.php" <!--{if $navmenu=='score'}--> class="hover"<!--{/if}-->><span>我的积分</span></a>
                <a href="pwdEdit.php" <!--{if $navmenu=='pwdEdit'}--> class="hover"<!--{/if}-->><span>设置密码</span></a>
                <a href="unsubscribe.php" <!--{if $navmenu=='unsubscribe'}--> class="hover"<!--{/if}-->><span>注销账户</span></a>
                <a href="login.php?action=logout"><span>安全退出</span></a>
            </div>
		</li>
	</ul>
</div>
<script type="text/javascript">
	/*$(document).ready(function () {
		$('.navList .item-caption').click(function () {
			$(this).next('.sub-item').toggle();
            return false;
		});
	});*/
</script>
