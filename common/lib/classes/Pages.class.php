<?php 
/**
 * 分页类
 *
 * @author 阿一 yandy@yanwee.com
 * @package 1.0
 * @version $Id$
 */


/**
 * Pages 分页类
 * @package Util
 */
class Pages {
	/**
	 * 使用该类的文件
	 * @var string
	 */
	public $fileName;
	/** 
	 * 每页行数
	 * @var int
	 */
	public $lines;
	/**
	 * 当前页
	 * @var int
	 */
	public $currentPage;
	/**
	 *总行数
	 *@var int
	 */
	public $lineCount;
	/**
	 * 总页数
	 * @var int
	 */
	public $pageCount;
	/**
	 * 模版文件名
	 * @var strint
	 */
	public $templateFile;
	/**
	 * 构析函数
	 * @param int $count
	 * @param string $templata_file
	 * @param int $lines
	 * @return void
	 */
	public function __construct($count, $lines=15, $template_file='pages.tpl') {
		$this->lineCount = intval($count);
		if (intval($_GET['lines'])>0) {
			$this->lines = intval($_GET['lines']);
		} else {
			$this->lines = $lines;
		}
		if ($this->lineCount<=0) {
			$this->pageCount = 1;
		} else {
			$this->pageCount = ceil($this->lineCount / $this->lines);
		}	
		$this->templateFile = $template_file;
		$uri = preg_replace('/&(pageno|lines)\=[^&]*/','', '&' . $_SERVER['QUERY_STRING']);
		$ext = '&';
		$uri = substr($uri . $ext,1);
		$this->fileName = $_SERVER['SCRIPT_NAME'] . '?' . $uri ;
		$currentPage = intval($_GET['pageno']);
		
		if ($currentPage<=0) {
			$this->currentPage = 1;
		} elseif ($currentPage>$this->pageCount) {
			$this->currentPage = $this->pageCount;
		} else {
			$this->currentPage = intval($_GET['pageno']);
		}
	}
	/**
	 * 生成sql分页
	 * @param string $sql
	 * @return string
	 */
	public function getLimit() {
		//$lineFrom = ($this->currentPage-1) * $this->lines + 1;
		$lineFrom = ($this->currentPage-1) * $this->lines;
		if ($lineFrom < 0){
			$lineFrom = 0;
		}
		$lineTo = $lineFrom + $this->lines - 1;
		return array('rowFrom'=>$lineFrom,'rowTo'=>$lineTo);
	}
	public function get_sql_limit($pageno) {
		$lineFrom = ($pageno - 1)  * $this->lines;
		$lineTo = $lineFrom + $this->lines - 1;
		return array('rowFrom'=>$lineFrom,'rowTo'=>$lineTo);
	}
	/**
	 * 生成控制条
	 * @return string
	 */
	public function showCtrlPanel()	{
		global $tpl;
		if ($this->templateFile=='wapPage.tpl') {
			$this->fileName = str_replace('&','&amp;',$this->fileName);
		}
		$vars = array(
			'lineCount'				=> $this->lineCount,	//总行数
			'pageCount'				=> $this->pageCount,	//总页数
			'lines'					=> $this->lines,	//每页行数
			'formName'				=> $this->formName,	//表单名称
			'lineFrom'				=> $lineFrom,	//从第几行开始
			'lineTo'				=> $lineTo,		//到第几行结束
			'currentPage'			=> $this->currentPage,	//当前页
			'is_disabled_cp'		=> (($this->pageCount<=1)?' disabled style="background-color:#EEEEEE"':''),
			'is_disabled_scroll'	=> (($this->currentPage<=1)?' disabled':''), //是否可用
			'is_disabled_left'		=> (($this->currentPage<=1)?' disabled':''), //是否可用
			'is_disabled_right'		=> (($this->currentPage>=$this->pageCount)?' disabled':''), //是否可用
			'pageName'				=> $this->fileName,
			'nextPage'				=> $this->currentPage+1,
			'prePage'				=> $this->currentPage-1,
			);
		$tpl->assign($vars);
		
		$pageList = array();
		$start_select_page = $this->currentPage - 10;
		if ($start_select_page < 1){
			$start_select_page = 1;
		}
		$end_select_page = $this->currentPage + 10;
		if ($end_select_page > $this->pageCount){
			$end_select_page = $this->pageCount;
		}
		
		for($i=$start_select_page; $i<=$end_select_page; $i++){
			// $p 存储页的数组
			if($i == $this->currentPage){
				$pageList[$i] = ' selected="selected"'; // 当前页时下拉菜单默认选中该页
			} else {
				$pageList[$i] = '';
			}
			
		}
		$tpl->assign('pageList',$pageList);
		
		// 输出模板
		return $tpl->fetch($this->templateFile);
	}
	/**
	 * 生成控制条 google 风格
	 * @return string
	 */
	public function showCtrlPanel_g($halfPer = 5, $show_total = false)	{
		
		$re = '<div class="multipage">';
		if($this->currentPage > $this->pageCount){
	$re .= '<a href="'.$this->fileName.'pageno='.($this->currentPage-1) .'"> <span class="prexpage">上一页</span></a>';			
			}else{
					$re .= '<span  class="nolink prexpage">上一页</span>';
			}	
		if($this->currentPage-$halfPer >1){
			$re .= '';
			$re .= '<a href="'.$this->fileName.'pageno=1"><span>1</span></a>';
			if($this->currentPage-$halfPer*2 >1){
				$re .= '<a href="'.$this->fileName.'pageno='.($this->currentPage-$halfPer*2).'"><span>...</span></a>';
			}else{
				$re .= '<a href="'.$this->fileName.'pageno=1"><span>...</span></a>';
			}
		}
		for ( $i = $this->currentPage - $halfPer,$i > 1 || $i = 1 , $j = $this->currentPage + $halfPer, $j < $this->pageCount || $j = $this->pageCount;$i <= $j ;$i++ )
		{
			$re .= $i ==  $this->currentPage 
				? '<span class="current">'.$i.'</span>'."\n"
				: '<a href="'.$this->fileName.'pageno='.$i.'"><span>'.$i.'</span></a>'."\n";
		}
		if($this->currentPage+$halfPer < $this->pageCount){
			if($this->currentPage+$halfPer*2 < $this->pageCount){
				$re .= '<a href="'.$this->fileName.'pageno='.($this->currentPage+$halfPer*2).'"><span>...</span></a>';
			}else{
				$re .= '<a href="'.$this->fileName.'pageno='.$this->pageCount.'"><span>...</span></a>';
			}
			$re .= '<a href="'.$this->fileName.'pageno='.$this->pageCount.'"><span>'.$this->pageCount.'</span></a>';
		}
			if($this->currentPage>=$this->pageCount){
				$re .= '<span class="nolink nextpage">下一页</span>';
			}else{
				$re .= '<a href="'.$this->fileName.'pageno='.($this->currentPage+1) .'"> <span class="nextpage">下一页</span></a>';	
			}
        if ($show_total) {
            $re = $re . '<span class="total-count">总计：' . $this->lineCount . ' 条</span>';
        }
		$re .= '</div>';

		return $re;
	}
	/**
	 * 生成控制条 google 风格
	 * @return string
	 */
	public function showCtrlPanel_h($halfPer = 5)	{
		$re = '<span class="pageMoreTop">
			<ul>';
		if($this->currentPage-$halfPer >1){
			$re .= '<li><a href="'.$this->fileName.'pageno=1"><span>1</span></a></li>';
			if($this->currentPage-$halfPer*2 >1){
				$re .= '<li><a href="'.$this->fileName.'pageno='.($this->currentPage-$halfPer*2).'"><span>...</span></a></li>';
			}else{
				$re .= '<li><a href="'.$this->fileName.'pageno=1"><span>...</span></a></li>';
			}
		}
		for ( $i = $this->currentPage - $halfPer,$i > 1 || $i = 1 , $j = $this->currentPage + $halfPer, $j < $this->pageCount || $j = $this->pageCount;$i <= $j ;$i++ )
		{
			$re .= $i ==  $this->currentPage 
				? '<li class="linkOn"><a href="'.$this->fileName.'pageno='.$i.'"><span>'.$i.'</span></a></li>'."\n"
				: '<li><a href="'.$this->fileName.'pageno='.$i.'"><span>'.$i.'</span></a></li>'."\n";
		}
		if($this->currentPage+$halfPer < $this->pageCount){
			if($this->currentPage+$halfPer*2 < $this->pageCount){
				$re .= '<li><a href="'.$this->fileName.'pageno='.($this->currentPage+$halfPer*2).'"><span>...</span></a></li>';
			}else{
				$re .= '<li><a href="'.$this->fileName.'pageno='.$this->pageCount.'"><span>...</span></a></li>';
			}
			$re .= '<li><a href="'.$this->fileName.'pageno='.$this->pageCount.'"><span>'.$this->pageCount.'</span></a></li>';
		}
				
		$re .= '	
			</ul>
			</span>';
		return $re;
	}
	/**
	 * 经典风格分页链接
	 * @return string
	 */
	public function showCtrlPanel_c($halfPer = 5, $urlpath = ''){
		global $cityInfo;
		$_GET['price'] = isset($_GET['price']) ? $_GET['price'] : '0-0';
		$_GET['q'] = urlencode($_GET['q']);
		if (!empty($urlpath)){
			if ($urlpath == 'broker'){
				$uri = 'list_'.intval($_GET['cityarea']).'_'.intval($_GET['cityarea2']).'_';
			}else{
				$uri = $urlpath.'/list_'.intval($_GET['id']).'_';
			}
		}else{
			$uri = 'list_'.intval($_GET['cityarea']).'_'.intval($_GET['cityarea2']).'_'.$_GET['price'].'_'.intval($_GET['room']).'_';
		}
		if ($urlpath == 'broker'){
			$uri = 'list_'.intval($_GET['cityarea']).'_'.intval($_GET['cityarea2']).'_';
		}elseif ($urlpath == 'company'){
			$uri = 'list_';
		}elseif ($urlpath == 'housenav'){
			$orderBy = isset($_GET['orderby'])? $_GET['orderby']: 'default';
			$orderWay = isset($_GET['orderway'])? $_GET['orderway']: 'd';
			$uri = 'list_'.$orderBy.'_'.$orderWay.'_';
		}elseif ($urlpath == 'news'){
			$uri = 'list_';
		}else{
			$uri = 'list_'.intval($_GET['cityarea']).'_'.intval($_GET['cityarea2']).'_'.$_GET['price'].'_'.intval($_GET['room']).'_';
		}
		$this->fileName = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
// 		if ($urlpath == 'housenav' || $urlpath == 'company' || $urlpath == 'qqgroup' || $urlpath == 'housenav' || $urlpath == 'news' ){
			$this->fileName = '/'.$cityInfo['url_name'].$this->fileName;
// 		}
		$this->fileName = str_replace('sale.php', '', $this->fileName);
		$this->fileName = str_replace('rent.php', '', $this->fileName);
		$this->fileName .= $uri;
		$re = '<ul>';
		if ($this->currentPage>1){
			$re .= '<li><a href="'.$this->fileName.($this->currentPage-1).'_'.$_GET['q'].'.html">上一页</a></li>';
		}else{
			$re .= '<li class="disabled">上一页</li>';
		}
		//分页链接数
		$linkCount=$halfPer * 2 +1;
		if ($this->pageCount <= $linkCount){
			$startPage=1;
			$endPage=$this->pageCount;
		}else{
			if ($this->currentPage<=$halfPer){
				$startPage = 1;
				$endPage = $linkCount;
			}else{
				if (($this->currentPage + $halfPer)>$this->pageCount){
					$startPage=$this->pageCount - $linkCount;
					$endPage=$this->pageCount;
				}else{
					$startPage = $this->currentPage - $halfPer;
					$endPage = $this->currentPage + $halfPer;
				}
			}
		}
		for ($i=$startPage;$i<=$endPage;$i++){
			if ($this->currentPage==$i){
				$re .= '<li class="thisclass"><a>'.$i.'</a></li>'."\n";
			}else{
				$re .= '<li><a href="'.$this->fileName.$i.'_'.$_GET['q'].'.html">'.$i.'</a></li>'."\n";
			}
		}
		if($this->currentPage>=$this->pageCount){
			$re .= '<li class="disabled">下一页</li>';
		}else{
			$re .= '<li><a href="'.$this->fileName.($this->currentPage+1).'_'.$_GET['q'].'.html">下一页</a></li>';
		}
		$re .= '</ul>';
		return $re;
	}
	
	public function build_pager_url($url, $page){
		return str_replace('{pageno}', $page, $url); 
	}
	/**
	 * 房源列表分页链接
	 * @return string
	 */
	public function get_pager_nav($halfPer = 5, $curpage = 1, $urlpath = ''){
		$this->currentPage = $curpage;
		$this->fileName = $urlpath;
		$re = '<ul>';
		if ($this->currentPage>1){
			$re .= '<li><a href="'.$this->build_pager_url($this->fileName, ($this->currentPage - 1)).'">上一页</a></li>';
		}else{
			$re .= '<li class="disabled">上一页</li>';
		}
		//分页链接数
		$linkCount=$halfPer * 2 +1;
		if ($this->pageCount <= $linkCount){
			$startPage=1;
			$endPage=$this->pageCount;
		}else{
			if ($this->currentPage<=$halfPer){
				$startPage = 1;
				$endPage = $linkCount;
			}else{
				if (($this->currentPage + $halfPer)>$this->pageCount){
					$startPage=$this->pageCount - $linkCount;
					$endPage=$this->pageCount;
				}else{
					$startPage = $this->currentPage - $halfPer;
					$endPage = $this->currentPage + $halfPer;
				}
			}
		}
		for ($i=$startPage;$i<=$endPage;$i++){
			if ($this->currentPage==$i){
				$re .= '<li class="thisclass"><a>'.$i.'</a></li>'."\n";
			}else{
				$re .= '<li><a href="'.$this->build_pager_url($this->fileName, $i).'">'.$i.'</a></li>'."\n";
			}
		}
		if($this->currentPage>=$this->pageCount){
			$re .= '<li class="disabled">下一页</li>';
		}else{
			$re .= '<li><a href="'.$this->build_pager_url($this->fileName, ($this->currentPage+1)).'">下一页</a></li>';
		}
		$re .= '</ul>';
		return $re;
	}
}
