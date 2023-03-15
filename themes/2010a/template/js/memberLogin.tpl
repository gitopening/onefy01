<!--{if $username}-->
document.write('您好，<!--{$username}-->！欢迎来到 <!--{$cfg.page.titlec}-->！<a href="<!--{$cfg.url}-->login/login.php?action=logout">退出</a> | <a href="<!--{$cfg.url}-->member/">我的<!--{$cfg.page.titlec}--></a> | <a href="<!--{$cfg.url}-->member/msgInbox.php" target="_blank">站内信</a>[<!--{$msgCount}-->]');
<!--{else}-->
document.write('您好！欢迎来到 <!--{$cfg.page.titlec}--> | 请先<a href="<!--{$cfg.url}-->member/">登录</a> 或 <a href="#">注册</a>');
<!--{/if}-->