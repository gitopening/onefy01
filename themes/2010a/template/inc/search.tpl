        <div id="Search">
	        <form method="get" id="searchhouse" action="/sale/">
            	<label>
            		<select name="searchtype" onchange="setsearchaction(this.value);">
                    	<option value="1" selected="selected">出售</option>
                        <option value="2">出租</option>
                    </select>
				</label>
            	<input type="text" class="text01"  name="q"/>
	            <input type="submit" class="button02" value="搜索" />
                <div class="clear"></div>
	        </form>
          </div>
          <div class="f_color"><font style="color:#041a9b;">搜索</font>&nbsp;<font style="color:#999999">关键字、地址、房源名</font></div>
