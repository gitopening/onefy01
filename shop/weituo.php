<?php
if (!in_array($dataInfo['user_type'], array(1, 3, 4))) {
    exit();
}
?>
<div class="rightweituo">
    <form method="post" id="form1" name="form1" action="addmsg.php" onsubmit="return checkweituo();">
          <input type="hidden" id="action" name="action" value="addweituo">
          <input type="hidden" id="uid" name="id" value="<?php echo $dataInfo['id'];?>">
          <table width="100%" cellspacing="0" cellpadding="0" border="0">
              <tbody><tr>
                <td valign="bottom" height="30"><h3>我有房产需要委托<?php echo $dataInfo['realname'];?></h3></td>
              </tr>
              <tr>
                <td valign="bottom" height="30"><table width="100%" border="0" cellspacing="5" cellpadding="0">
                    <tbody><tr>
                      <td><strong>房产地址</strong><span class="red">*</span>&nbsp;</td>
                    </tr>
                </tbody></table></td>
              </tr>
              <tr>
                <td valign="bottom" height="30"><table width="100%" border="0" cellspacing="5" cellpadding="0">
                    <tbody><tr>
                      <td><label><select onchange="getarea(this.value);" style="font-size: 9pt" id="bigzone" name="bigzone">
                          <option value="0" selected="selected">选择区域</option>
                          <?php 
                          foreach ($cityarea_option as $item){
                          ?>
                          <option value="<?php echo $item['region_id'];?>"><?php echo $item['region_name'];?></option>
                          <?php 
                          }
                          ?>
                          </select>
                          </label> &nbsp; 
                        <label>
                        <select id="smallzone" name="smallzone">
                          <option value="0" selected="selected">选择地点</option>
                        </select></label>                      </td>
                    </tr>
                </tbody></table></td>
              </tr>
              <tr>
                <td><table width="100%" cellspacing="0" cellpadding="0" border="0">
                    <tbody>
                      <tr>
                        <td width="100%" height="30" valign="bottom"><table width="100%" border="0" cellspacing="5" cellpadding="0">
                            <tbody><tr>
                              <td><strong>户型</strong><span class="red">*</span></td>
                              <td><strong>面积</strong><span class="red">*</span></td>
                              <td><strong>楼层</strong><span class="red">*</span></td>
                            </tr>
                            <tr>
                              <td><input type="text" maxlength="2" style="width:40px;border:#ccc solid 1px" id="huxing" name="huxing">
                              室</td>
                              <td><input type="text" maxlength="4" style="width:40px;border:#ccc solid 1px" id="symj" name="symj">
                              ㎡</td>
                              <td>第
                                <input type="text" maxlength="2" style="width:30px;border:#ccc solid 1px" name="louceng" id="louceng">
                                共
                              <input type="text" maxlength="2" style="width:30px;border:#ccc solid 1px" name="endlouceng" id="endlouceng"></td>
                            </tr>
                        </tbody></table></td>
                      </tr>
                    </tbody>
                  </table></td>
              </tr>
              <tr>
                <td valign="bottom" height="30"><table width="100%" border="0" cellspacing="5" cellpadding="0">
                    <tbody><tr>
                      <td><strong>您的姓名</strong><span class="red">*</span></td>
                    </tr>
                    <tr>
                      <td><input type="text" style="width:244px;border:#ccc solid 1px" id="nikename" name="uname"></td>
                    </tr>
                </tbody></table></td>
              </tr>
              <tr>
                <td valign="bottom" height="30"><table width="100%" border="0" cellspacing="5" cellpadding="0">
                    <tbody><tr>
                      <td><strong>您的电话</strong><span class="red">*</span></td>
                    </tr>
                    <tr>
                      <td><input type="text" style="width:244px;border:1px solid #CCC;" id="weituotel" name="tel"></td>
                    </tr>
                </tbody></table></td>
              </tr>
              <tr>
                <td valign="bottom" height="30"><table width="100%" border="0" cellspacing="5" cellpadding="0">
                    <tbody><tr>
                      <td><strong>留言</strong></td>
                    </tr>
                    <tr>
                      <td><textarea name="note" rows="4" style="width:95%;font-size:12px;" id="weituonote"></textarea></td>
                    </tr>
                </tbody></table></td>
              </tr>
              <tr>
                <td valign="bottom" height="30"><table width="100%" border="0" cellspacing="5" cellpadding="0">
                    <tbody><tr>
                      <td colspan="2"><strong>验证码</strong><span class="red">*</span></td>
                    </tr>
                    <tr>
                      <td width="5%"><input name="valid" type="text" id="weituovalid" style="border:#ccc solid 1px" size="6" maxlength="4" /></td>
                      <td width="95%"><img src="/valid.php" name="valid_pic" height="20" id="valid_pic" style="cursor:pointer;" onclick="this.src='/valid.php?' + Math.random();" /></td>
                    </tr>
                </tbody></table></td>
              </tr>
              <tr>
                <td height="50" style=" padding-left:4px;"><input type="image" src="/images/zxss.png" name="submitweituo"></td>
              </tr>
          </tbody></table>
        </form>
    </div>