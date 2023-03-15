// JavaScript Document
var isMozilla = (typeof document.implementation != 'undefined') && (typeof document.implementation.createDocument != 'undefined') && (typeof HTMLDocument!='undefined');
var isIE = window.ActiveXObject ? true : false;
var isFirefox = (navigator.userAgent.toLowerCase().indexOf("firefox")!=-1);
var isSafari = (navigator.userAgent.toLowerCase().indexOf("safari")!=-1);
var isOpera = (navigator.userAgent.toLowerCase().indexOf("opera")!=-1);

String.prototype.htmlEncode = function() {
	return this.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

String.prototype.trim = function() {
	return this.replace(/^\s*|\s*$/g, "");
}


var Global = new Object();

Global.fixEvent = function(e) {
    var evt = (typeof e == "undefined") ? window.event : e;
    return evt;
}

Global.srcElement = function(e) {
    if (typeof e == "undefined") e = window.event;
    var src = document.all ? e.srcElement : e.target;

    return src;
}


Global.tableRowIndex = function (tr) {
    if (isIE) {
        return tr.rowIndex;
    } else {
      table = tr.parentNode.parentNode;

      for (i = 0; i < table.rows.length; i++) {
          if (table.rows[i] == tr) {
              return i;
              break;
          }
      }
    }
}

