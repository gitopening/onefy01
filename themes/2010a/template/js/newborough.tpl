<!--{foreach from=$commendbroker key=key item=item}-->
document.write(' <LI><A class=col_a href="<!--{$cfg.url_newHouse}-->d-<!--{$item.id}-->.html"  target=_blank><!--{$item.borough_name}--></A></LI>');
<!--{/foreach}-->