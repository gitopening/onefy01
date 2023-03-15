//提取查找字符串前面所有的字符
function getFront(mainStr,searchStr){
    foundOffset=mainStr.indexOf(searchStr);
    if(foundOffset==-1){
       return null;
    }
    return mainStr.substring(0,foundOffset);
}
//提取查找字符串后面的所有字符
function getEnd(mainStr,searchStr){
    foundOffset=mainStr.indexOf(searchStr);
    if(foundOffset==-1){
       return null;
    }
    return mainStr.substring(foundOffset+searchStr.length,mainStr.length);
}
//在字符串 searchStr 前面插入字符串 insertStr 
function insertString(mainStr,searchStr,insertStr){
    var front=getFront(mainStr,searchStr);
    var end=getEnd(mainStr,searchStr);
    if(front!=null && end!=null){
       return front+insertStr+searchStr+end;
    }
    return null;
}
//删除字符串 deleteStr
function deleteString(mainStr,deleteStr){
    return replaceString(mainStr,deleteStr,"");
}
//将字符串 searchStr 修改为 replaceStr
function replaceString(mainStr,searchStr,replaceStr){
    var front=getFront(mainStr,searchStr);
    var end=getEnd(mainStr,searchStr);
    if(front!=null && end!=null){
       return front+replaceStr+end;
    }
    return null;
}