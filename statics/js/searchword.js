function stopEvent(e){
    e = e || window.event;
    if(e.preventDefault) {
        e.preventDefault();
        e.stopPropagation();
    }else{
        e.returnValue = false;
        e.cancelBubble = true;
    }
}
function clearSelectedOptBgColor(target){
    if (target.seletedIndex >= 0){
        $(".ui-menu li a").removeClass("ui-state-focus");
        var inputval=$("#li-u-"+target.seletedIndex).text();
        inputval = inputval.replace(/<[^>].*?>/g,"");
        $("#searchfield").val(inputval);
    }
}

function setSelectedOptBgColor(target){
    $("#li-u-"+target.seletedIndex).addClass("ui-state-focus");
    var inputval=$("#li-u-"+target.seletedIndex).text();
    inputval = inputval.replace(/<[^>].*?>/g,"");
    $("#searchfield").val(inputval);
}
function initSelect(){
    var upKeyCode = 38;
    var downKeyCode = 40;
    var enterKeyCode = 13;
    oInput = document.getElementById("searchfield");
    oInput.options = $(".ui-menu li");
    oInput.seletedIndex = -1;
    oInput.onkeyup = function(event){
        if (event == undefined){
            event = window.event;
        }

        switch (event.keyCode){
            case upKeyCode:
                clearSelectedOptBgColor(this);
                this.seletedIndex--;
                if (this.seletedIndex < 0)
                    this.seletedIndex = this.options.length - 1;
                setSelectedOptBgColor(this);
                break;

            case downKeyCode:
                clearSelectedOptBgColor(this);
                this.seletedIndex++;
                if (this.seletedIndex >= this.options.length)
                    this.seletedIndex = -1;
                setSelectedOptBgColor(this);
                break;

            case enterKeyCode:
                break;
            default:
                genshortresult();
                clearSelectedOptBgColor(this);
                break;

        }

    };
    oInput.onblur = function(){
        clearSelectedOptBgColor(this);
        this.seletedIndex = -1;
        clearHover();
    };
}
$(".ui-menu li").live('click',function(){
    var inputval=$(this).text();
    inputval = inputval.replace(/<[^>].*?>/g,"");
    $("#searchfield").val(inputval);

});
$(".ui-menu li a").live('hover',function(){
    $(".ui-menu li a").removeClass("ui-state-focus");
    $(this).addClass("ui-state-focus");
    var inputval=$(this).text();
    inputval = inputval.replace(/<[^>].*?>/g,"");
    $("#searchfield").val(inputval);
});
$(function() {
    $(document).click(hideshortresult);
});
function hideshortresult() {
    $(".ui-menu").hide();
}

$(function() {
    var returnword = '';

    var url="/index.php?m=content&c=index&a=get_searchkey&pc_hash=WrCDxe";
        $.ajax({
            type: "GET",
            url: url,
            dataType:'json',
            cache : false,
            async : false,
            success: function(data){
                console.log(data.data);
                returnword=data.data;

            },error: function(){

            }
        });
        console.log(returnword);
    var searchword = returnword;
    $("#searchfield").val(searchword);
    $("#searchfield").focus(function(){
        if($(this).val()==searchword){
            $(this).val("");
        }
        $(this).addClass('bkeyword');
    }).blur(function(){
        $(this).removeClass('bkeyword');
        if($(this).val()==""){
            $("#searchfield").val(searchword);
        }
    });
    $("#topForm").attr("accept-charset","UTF-8");
});

