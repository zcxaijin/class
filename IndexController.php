<?php

namespace controller;
use model\ArticleModel;
use model\LinkModel;
use model\SiteinfoModel;
use framework\Page;
class IndexController extends Controller
{
	//使用一个变量，用来承接model类的对象
	public $article;
	public $page;
	public $eliteCount;
	public $pageCount;
	public $link;
	public $siteinfo;
	function __construct()
	{
		parent::__construct();
		$this->article = new ArticleModel();
		// 精品贴总数
		$this->eliteCount = $this->article->blogCount('first=1 and elite=1');
		
		// 分页
		$this->page = new Page(3,$this->eliteCount);
		$this->link = new LinkModel();
		$this->siteinfo = new SiteinfoModel();
	}
	// 首页展示精品贴
	function index()
	{
		// var_dump($_SESSION);
		// 分页
		// 查询出来精品帖子的分页
		
		// var_dump($pageCount);
		// 返回所有分页
		$a = $this->page->allpage();
		$this->assign('a',$a);
		// var_dump($a);
		$limit = $this->page->limit();
		// var_dump($limit);
		// 查询精品贴展示在首页
		$result = $this->article->field('*')->table('bk_article as a,bk_user as u')->elite('a.first=1 and isdel=0 and a.elite=1 and u.uid=a.authorid ',$limit);
		// var_dump($result);
		// $title = $result[]['title']
		$this->assign('result',$result);
		//什么都用写，代表显示app/view/index(控制器)/index(方法名字).html
		// 右侧友情链接
		$friend = $this->link->select();
		// var_dump($friend);
		$this->assign('friend', $friend);
		$site = $this->siteinfo->select();
		// var_dump($site);
		$this->assign('site', $site);
		$this->display();
	}
	// 展示所有的帖子的页面
	function allarticle()
	{
		// 分页
		// 所有帖子的总数
		$this->pageCount = $this->article->blogCount('first=1');
		// 分页
		$this->page = new Page(3,$this->pageCount);

		$a = $this->page->allpage();
		$this->assign('a',$a);
		$limit = $this->page->limit();

		$result = $this->article->field('*')->table('bk_article as a,bk_user as u')->order('elite desc')->elite('first=1 and isdel=0 and u.uid=a.authorid',$limit);
		// var_dump($result);
		$this->assign('result',$result);
		// 右侧友情链接
		$friend = $this->link->select();
		// var_dump($friend);
		$this->assign('friend', $friend);
		$this->display();
	}

	function services()
	{
		$this->display();
	}
	// 特效文章展示
	function portfolio()
	{
		$result = $this->article->where('first=1 and isdel=0')->select();
		$this->assign('result', $result);
		// 右侧友情链接
		$friend = $this->link->select();
		// var_dump($friend);
		$this->assign('friend', $friend);
		$this->display();
	}
	function contact()
	{
		$this->display();
	}
	function search()
	{

		// 搜索
		if (empty($_POST['searchContent'])) {
			$this->notice('小伙子，没填写搜索内容啊','index.php');
			exit;
		}
		$mes = $_POST['searchContent'];
		// 分页
		$where = "isdel=0 and isdisplay=0 and(title like '%$mes%' or content like '%$mes%')";
		// 获取回复总数
		$pageCount = $this->article->blogCount($where);
		// var_dump($pageCount);
		$this->page = new Page(3,$pageCount);

		$a = $this->page->allpage();
		// var_dump($a);
		$this->assign('a',$a);
		$limit = $this->page->limit();
		$message = $this->article->field('*')->table('bk_article as a,bk_user as u')->elite("u.uid=a.authorid and isdel=0 and isdisplay=0 and(title like '%$mes%' or content like '%$mes%')",$limit);
		// var_dump($message);
		if(empty($message)){
			$this->notice('没有搜到想要的内容');
			exit;
		}
		$this->assign('message', $message);
		// 右侧友情链接
		$friend = $this->link->select();
		// var_dump($friend);
		$this->assign('friend', $friend);
		$this->display();
	}
}
