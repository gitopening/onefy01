<!--{foreach from=$houseRentBest key=key item=item}-->
document.write('<tr>	<td class="tdw5 tdleft r4_l_b_td_1"><a href="<!--{$cfg.url_rent}-->d-<!--{$item.id}-->.html" target="_blank" class="col_a"><!--{$item.borough_name}--></a></td>	<td class="tdw6 tdright"><!--{$item.house_room}-->室<!--{$item.house_hall}-->厅</td>	<td class="tdw7 tdright"><!--{$item.house_totalarea}--></td>	<td class="tdw8 tdright se_1 v_font"><!--{$item.house_price}--></td></tr>');
<!--{/foreach}-->

