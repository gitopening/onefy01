<div id="conter_h">
    <div id="nav">
    <ul>
      <li><a href="/" <!--{if $type!='sale' && $type!='rent'}-->class="on"<!--{/if}-->>首页</a></li>
      <li><a href="/sale/" <!--{if $type=='sale'}-->class="on"<!--{/if}-->>个人二手房</a></li>
      <li><a href="/rent/" <!--{if $type=='rent'}-->class="on"<!--{/if}-->>个人出租房</a></li>
      <div class="clear"></div>
    </ul>
  </div>
    <div id="Search">
    <form method="get" id="searchhouse" action="/sale/">
        <label>
            <select name="searchtype" onchange="setsearchaction(this.value);">
                <option value="1" selected="selected">二手房</option>
                <option value="2">出租房</option>
            </select>
        </label>
        <input type="text" class="text01"  name="q"/>
        <input type="submit" class="button02" value="搜索" />
        <div class="clear"></div>
    </form>
  <div class="f_color"><font style="color:#041a9b;">搜索</font>&nbsp;<font style="color:#999999">关键字、地址、小区楼盘名</font></div>
  </div>
  <div class="clear"></div>
</div>