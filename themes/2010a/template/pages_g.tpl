<table border="0" cellspacing="5" cellpadding="1">
  <tr>
    <td class="align_c" >
		共<span class="orag"><!--{$lineCount}--></span>行　当前<!--{$currentPage}-->/<!--{$pageCount}-->页 　<!--{if $currentPage==1}--><span disabled="1">首页  上一页</span><!--{else}--><a href="<!--{$pageName}-->pageno=1">首页</a>  <img src="<!--{$cfg.path.images}-->Examination_67.gif" /><a href="<!--{$pageName}-->pageno=<!--{$prePage}-->">上一页</a><!--{/if}--><!--{if $currentPage==$pageCount}--> <span disabled="1">下一页  末页</span><!--{else}--> <a href="<!--{$pageName}-->pageno=<!--{$nextPage}-->">下一页</a><img src="<!--{$cfg.path.images}-->Examination_68.gif" />  <a href="<!--{$pageName}-->pageno=<!--{$pageCount}-->">末页</a><!--{/if}-->　 
		转到&nbsp;&nbsp;<select name="UIPageNoSelect" onChange="goToPage(this)" style="width:50px;">
		<!--{foreach from=$pageList item=item key=key}-->
			<option value="<!--{$pageName}-->pageno=<!--{$key}-->" <!--{$item}-->><!--{$key}--></option>
		<!--{/foreach}-->
		</select>
	</td>
  </tr>
</table> 
<script language="javascript">
function goToPage(obj) {
	$str = obj.options[obj.selectedIndex].value ;
	window.location = $str;
}
</script>

	<div class="pageMore">
		<ul>
			<li><span><!--{$lineCount}-->条</span></li>
			<li><span><!--{$currentPage}-->/<!--{$pageCount}-->页</span></li>
			<!--{if $currentPage-2 >1}-->
			<li><a href="<!--{$pageName}-->pageno=1"><span>1</span></a></li>
			<!--{/if}-->
			<!--{section name=loop loop=5}--> 
			<!--{if $currentPage+2 <$pageCount}-->
			<li><a href="<!--{$pageName}-->pageno=<!--{$pageCount}-->"><span><!--{$pageCount}--></span></a></li>
			<!--{/if}-->
		</ul>
	</div>