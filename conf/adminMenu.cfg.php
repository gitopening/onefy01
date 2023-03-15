<?php
return array(
    'index' => array(
        'caption' => '首页',
        'icon' => 'admin/icon_15.gif',
        'sub' => array(
            'webconfig' => array('caption' => '网站设置', 'url' => 'webConfig/webConfig.php'),
            'about' => array('caption' => '关于我们', 'url' => 'webConfig/about.php'),
            'suggest' => array('caption' => '投诉与建议', 'url' => 'webConfig/suggest.php'),
            'editpwd' => array('caption' => '修改密码', 'url' => 'user/editpwd.php'),
            'clearcache' => array('caption' => '清理缓存', 'url' => 'webConfig/clearcache.php'),
        ),
    ),
    'house' => array(
        'caption' => '房源管理',
        'icon' => 'admin/icon_15.gif',
        'sub' => array(
            'MemberHouseSale' => array('caption' => '本站房源二手房', 'url' => 'house/membersell.php?check=0'),
            'MemberHouseRent' => array('caption' => '本站房源租房', 'url' => 'house/memberrent.php?check=0'),
            'CheckHouseSalePic' => array('caption' => '本站图片二手房', 'url' => 'house/checksalepic.php'),
            'CheckHouseRentPic' => array('caption' => '本站图片租房', 'url' => 'house/checkrentpic.php'),
        ),
    ),
    'member' => array(
        'caption' => '会员管理',
        'icon' => 'admin/icon_15.gif',
        'sub' => array(
            'brokerList' => array('caption' => '网站会员管理', 'url' => 'member/index.php'),
            'memberIdentity' => array('caption' => '经纪人身份审核', 'url' => 'member/identity.php?status=1'),
            'memberAptitude' => array('caption' => '经纪人资质审核', 'url' => 'member/aptitude.php?status=1'),
            'MemberLeave' => array('caption' => '给经纪人的留言', 'url' => 'member/leave.php'),
            'MemberWeituo' => array('caption' => '给经纪人的委托', 'url' => 'member/weituo.php'),
        ),
    ),
);
