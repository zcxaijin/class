<?php
namespace framework;
//定义一个文件上传类
class Upload
{
	//路径
	protected $path = './';
	//准许的mime类型
	protected $allowMime = array('image/png','image/jpeg','image/gif','image/wbmp');
	//准许的文件后缀名
	protected $allowSub = array('jpeg','png','gif','wbmp','pjpeg','jpg');
	//文件准许的大小
	protected $allowSize = 200000000;
	//文件的错误号
	protected $errorNum;
	//文件的错误信息
	protected $errorInfo;
	//文件的大小
	protected $size;
	//文件新名字
	protected $newName;
	//文件原名字
	protected $orgName;
	//是否启用随机文件名
	protected $isRandName = true;
	//临时文件名
	protected $tmpName;
	//文件前缀
	protected $preFix;
	//文件后缀
	protected $subFix;
	//文件的mime类型
	protected $type;
	
	public function __construct($array = array())  //path=>upload
	{	
		foreach ($array as $key => $val) {
			$keys = strtolower($key);
			
			if (!in_array($keys , get_class_vars(get_class($this)))) {
				continue;
			}
			//通过setOption函数去批量设置成员属性
			$this->setOptions($keys , $val);
		}
	}
	
	//上传的方法
	public function up($fields)  //就是input 表达提交过来的name的值
	{
		//var_dump($_FILES);
		if (!$this->checkPath()) {
			exit('没有上传文件');
		}
		
		//把传过来的图片的信息赋值给予临时变量 供一个函数使用？
		
		$name = $_FILES[$fields]['name'];
		$tmpName = $_FILES[$fields]['tmp_name'];
		$type = $_FILES[$fields]['type'];
		$error = $_FILES[$fields]['error'];
		$size = $_FILES[$fields]['size'];
		
		
		
		if ($this->setFiles($name , $tmpName , $type , $error , $size)) {
			
			//判断你是否启用随机的文件名
			$this->newName = $this->createName();
			
			//判断mime 判断 大小 判断 后缀
			if ($this->checkMime() && $this->checkSize() && $this->checkSub()) {
				
				//如果都成功了才让你进行移动文件
				if ($this->move()) {
					//echo '';
					
					return $this->path;
				} else {
					return false;
				}
				
			} else{
				return false;
			}
			
			
			
		}
	}
	protected function checkSize()
	{
		if ($this->size > $this->allowSize) {
			$this->setOptions('errorNum' , -5);
			return false;
		} else {
			return true;
		}
	}
	
	//移动文件
	public function move()
	{	
		if (is_uploaded_file($this->tmpName)) {
			
			$this->path = rtrim($this->path , '/') . '/' . $this->newName;


			if (move_uploaded_file($this->tmpName , $this->path)) {
			 	return true;
			} else {
				$this->setOptions('errorNum' , -7);
				
				return false;
			}
			
			
		} else {
			
			$this->setOption('errorNum' , -6);
			return false;
		}
	}
	
	//检测文件的后缀
	protected function checkSub()
	{
		if (in_array($this->subFix , $this->allowSub)) {
			
			return true;
		} else {
			$this->setOptions('errorNum' , -4);
			
			return false;
		}
	}
	//检测mime类型
	
	protected function checkMime()
	{
		if (in_array($this->type , $this->allowMime)) {
			
			return true;
		} else {
			$this->setOptions('errorNum' , -3);
			
			return false;
		}
	}
	
	
	//创建文件的新名字
	public function createName()
	{
		if ($this->isRandName) {
			//这是随机的区间
			return $this->preFix . $this->randName();  //??
		} else {
			//不随机的区间
			return $this->preFix . $this->orgName;
		}
	}
	//随机文件名
	public function randName()
	{
		return uniqid() . '.' . $this->subFix;
	}
	
	//给成员属性赋值
	public function setFiles($name , $tmpName , $type , $error , $size)
	{
		$this->orgName = $name;
		$this->tmpName = $tmpName;
		$this->size = $size;
		$this->type = $type;
		//怎么获取文件的后缀名
		$arr = explode('.' , $name); //fengjie.a.b.c.jpg
		
		$this->subFix = array_pop($arr);
		
		return true;
	}
	
	
	
	//检测路径的方法
	
	public function checkPath()
	{
		if (empty($this->path)) {
			$this->setOptions('errorNum' , -1);
			return false;
		} else {
			$this->path = rtrim($this->path , '/') . '/';
			
			if (file_exists($this->path) && is_writeable($this->path)) {
				return true;
			} else {
				if (mkdir($this->path , 0777 , true)) {
					return true;
				} else {
					$this->setOptions('errorNum' , -2);
					return false;
				}
			}
			


		}
	}
	
	
	//给成员属性赋值的函数
	public function setOptions($keys , $val)
	{
		$this->$keys = $val;
	}
	
	//获取错误信息
	public function getErrorInfo()
	{
		$str = '';
		switch ($this->errorNum) {
			case -1:
				$str = '没有上传文件';
				break;
			case -2:
				$str = '文件创建失败';
				break;
			case -3:
				$str = '不准许的mime';
				break;
			case -4:
				$str = '不准许的后缀';
				break;
			case -5:
				$str = '不准许的大小';
				break;
			case -6:
				$str = '不是上传文件';
				break;
			case -7:
				$str = '文件移动失败';
				break;
			case 1:
				$str = '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值。';
				break;
			case 2:
				$str = '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值';
				break;
			case 3:
				$str = '文件只有部分被上传';
				break;
			case 4:
				$str = '没有文件被上传';
				break;
			case 6:
				$str = '找不到临时文件夹';
				break;
			case 7:
				$str = '文件写入失败';
				break;
		}
		return $str;
	}
}
