<div class="memberLeftNav">
  <ul class="navList">
    <li class="item">
      <ul>
        <a href="index.php" <!--{if $navmenu=='index'}--> class="hover"<!--{/if}-->>会员中心首页</a><br />
        <a href="houseRent.php" <!--{if $navmenu=='houseRent'}--> class="hover"<!--{/if}-->>发布出租</a><br />
        <a href="houseSale.php" <!--{if $navmenu=='houseSale'}--> class="hover"<!--{/if}-->>发布出售</a><br />
        <a href="manageRent.php" <!--{if $navmenu=='manageRent' || $navmenu=='manageRentTop' || $navmenu=='manageRentRecycle' || $navmenu=='manageRentDone'}--> class="hover"<!--{/if}-->>管理出租</a><br />
        <a href="manageSale.php" <!--{if $navmenu=='manageSale' || $navmenu=='manageSaleTop' || $navmenu=='manageSaleRecycle' || $navmenu=='manageSaleDone'}--> class="hover"<!--{/if}-->>管理出售</a><br />
        <a href="brokerProfile.php" <!--{if $navmenu=='brokerProfile'}--> class="hover"<!--{/if}-->>个人资料</a><br />
        <!--{if $user_type == 1}-->
        <a href="brokerIdentity.php" <!--{if $navmenu=='brokerIdentity'}--> class="hover"<!--{/if}-->>实名认证</a><br />
        <a href="msg.php" <!--{if $navmenu=='msg' || $navmenu=='showmsg'}--> class="hover"<!--{/if}-->>给我的留言</a><br />
        <a href="weituo.php" <!--{if $navmenu=='weituo' || $navmenu=='showweituo'}--> class="hover"<!--{/if}-->>给我的委托</a><br />
        <!--{/if}-->
        <a href="pwdEdit.php" <!--{if $navmenu=='pwdEdit'}--> class="hover"<!--{/if}-->>修改密码</a><br />
        <a href="login.php?action=logout">安全退出</a><br />
      </ul>
    </li>
  </ul>
</div>
