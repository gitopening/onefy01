<!--{foreach from=$perHouseSellBest key=key item=item}-->
document.write('<tr>	<td class="tdleft tdw r4_c_b_list_td1"><a href="<!--{$cfg.url_sale}-->d-<!--{$item.id}-->.html" target="_blank" class="col_a"><!--{$item.borough_name}--></a></td>	<td class="tdright tdw1"><!--{$item.house_room}-->室<!--{$item.house_hall}-->厅<!--{$item.house_toilet}-->卫<!--{$item.house_veranda}-->阳</td>	<td class="tdright tdw2"><!--{$item.house_floor}-->F/<!--{$item.house_topfloor}-->F</td>	<td class="tdright tdw3"><!--{$item.house_totalarea}--></td>	<td class="tdright tdw4 se_1 v_font"><!--{$item.house_price}--></td></tr>');
<!--{/foreach}-->