/*
 * WEIP - Web-based Enterprise Information Platform
 * Copyright (C) 2005 WEIP Group (http://www.weip.cn/)
 * All rights reserved.
 * 未经许可，禁止用于商业用途！
 * 
 * 版本(Version):  0.1
 * 最后修改时间(Modified): 0000-00-00 00:00:00
 * 
 * 文件作者(File Authors):
 *      魏永增 (catorwei@163.com)
 */
/**
 * XML解释器
 *
 * @package weip.lib.xml
 */
 
function WEIPXml(_url) {
	/** XML文件的URL */
	this.url = _url;
	/** XML文档对象 */
	this.oDocument = null;
	/** XMLHTTP对象 */
	this.oXmlHttp = null;

	/** 浏览器类型 */
	this.isNS = isNetscape;
	this.isIE = isIE;

	/**
	 * 获取XmlHttp对象
	 */
	this.getXmlHttp = function() {
		if (this.isIE) {
			try {
				this.oXmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
			} catch (e) {
				try {
					this.oXmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
				} catch (e) {
					// nothing
				}
			}
		} else if (window.XMLHttpRequest) {
			this.oXmlHttp = new XMLHttpRequest();
			//if (this.oXmlHttp.overrideMimeType) {
			//	this.oXmlHttp.overrideMimeType("text/xml");
			//}
		}
	}
	
	/**
	 * 装载XML文件
	 */
	this.load = function(_content, _isAsynchronous, _mothed, _xmlUrl) {
		_isAsynchronous = (!_isAsynchronous) ? false : true;
		if (!this.oXmlHttp) {
			this.getXmlHttp();
		}
		if (_xmlUrl) {
			this.url = _xmlUrl;
		}
		if (!_mothed) {
			_mothed = 'POST';
		}
		this.oXmlHttp.open(_mothed, this.url, _isAsynchronous);
		if (_mothed == 'POST') {
			this.oXmlHttp.setRequestHeader('Content-Type', 
										   'application/x-www-form-urlencoded');
		}
		if (_content) {
			this.oXmlHttp.send(_content);
		} else {
			this.oXmlHttp.send(null);
		}
		if (!_isAsynchronous) {
			if (this.oXmlHttp.status == 200) {
				this.oDocument = this.oXmlHttp.responseXML;
				return true;
			} else {
				alert('XML 请求错误: ' + this.oXmlHttp.statusText 
						+ ' (' + this.oXmlHttp.status + ')');
			}
		} else {
			this.oXmlHttp.onreadystatechange = function() {
				switch (this.oXmlHttp.readyState) {
				case 0 :	// 未初始化
					this.onUninitialized(); break;
				case 1 :	// 装载中
					this.onLoading(); break;
				case 2 :	// 装载完毕
					this.onLoaded(); break;
				case 3 :	// 交互中
					this.onInteractive(); break;
				case 4 :	// 完成
					if (this.oXmlHttp.status == 200) {
						this.oDocument = this.oXmlHttp.responseXML;
						this.onComplete();
					} else {
						alert('XML 请求错误: ' + this.oXmlHttp.statusText 
										+ ' (' + this.oXmlHttp.status + ')');
					}
					break;
				}
			}.bind(this);
		}
		return false;
	}
	
	this.onUninitialized = function() {};	// 未初始化
	this.onLoading		 = function() {};	// 装载中
	this.onLoaded		 = function() {};	// 装载完毕
	this.onInteractive	 = function() {};	// 交互中
	this.onComplete		 = function() {};	// 完成

	/**
	 * 提取节点数组
	 */
	this.selectNodes = function(xpath) {
		if (this.isIE) {
			return this.oDocument.selectNodes(xpath);
		} else {
			var aNodeArray = new Array();
			var xPathResult = this.oDocument.evaluate(xpath, this.oDocument, 
					this.oDocument.createNSResolver(this.oDocument.documentElement), 
					XPathResult.ORDERED_NODE_ITERATOR_TYPE, null);
			if (xPathResult) {
				var oNode = xPathResult.iterateNext();
				while (oNode) {
					aNodeArray[aNodeArray.length] = oNode;
					oNode = xPathResult.iterateNext();
				}
			}
			return aNodeArray;
		}
	}

	/**
	 * 提取单个节点
	 */
	this.selectSingleNode = function(xpath) {
		if (this.isIE) {
			return this.oDocument.selectSingleNode(xpath);
		} else {
			var xPathResult = this.oDocument.evaluate(xpath, this.oDocument,
					this.oDocument.createNSResolver(this.oDocument.documentElement), 9, null);
			if ( xPathResult && xPathResult.singleNodeValue ) {
				return xPathResult.singleNodeValue;
			} else {
				return null;
			}
		}
	}

	/**
	 * 获取节点text值
	 */
	this.getText = function(xpath) {
		var oNode = (typeof(xpath) == "string") ? this.selectSingleNode(xpath) : xpath;
		if (oNode) {
			return (isIE) ? oNode.text : oNode.textContent;
		} else {
			return this.oXmlHttp.responseText;
		}
	}

	/**
	 * 获取节点属性值
	 */
	this.getAttribute = function(xpath, attributeName) {
		var oNode = (typeof(xpath) == "string") ? this.selectSingleNode(xpath) : xpath;
		if (oNode) {
			var oAttribute = oNode.attributes.getNamedItem(attributeName);
			return (oAttribute) ? oAttribute.value : null;
		} else {
			return null;
		}
	}
}

Function.prototype.bind = function() {
	var __method = this, args = $A(arguments), object = args.shift();
	return function() {
		return __method.apply(object, args.concat($A(arguments)));
	}
}

var $A = Array.from = function(iterable) {
	if (!iterable) return [];
	if (iterable.toArray) {
		return iterable.toArray();
	} else {
		var results = [];
		for (var i = 0; i < iterable.length; i++) {
			results.push(iterable[i]);
		}
		return results;
	}
}
