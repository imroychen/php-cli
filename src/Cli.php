<?php
/**
 * User: roy
 * Date: 2016/12/6
 */

namespace iry\cli;
/**
 * Class Cli
 * @package iry\cli
 * @method setCharset ($charset); //$charset:'utf-8', 'gbk', 'gb2312'
 * @method setLang ($lang); //$lang: zh/en
 */

 class Cli
 {
	 private static $_cfg=['disable_style'=>false,'charset'=>'utf-8','lang'=>'zh'];

 	 private static $_staticCache = [];

     static private $_fgColor = [
         'black'=>'0;30',
         'dark_gray'=>'1;30',
         'blue'=>'0;34',
         'light_blue'=>'1;34',
         'green'=>'0;32',
         'light_green'=>'1;32',
         'cyan'=>'0;36',
         'light_cyan'=>'1;36',
         'red'=>'0;31',
         'light_red'=>'1;31',
         'purple'=>'0;35',
         'light_purple'=>'1;35',
         'brown'=>'0;33',
         'yellow'=>'1;33',
         'light_gray'=>'0;37',
         'white'=>'1;37',
     ];
     static private $_bgColor = [
         'black' => '40',
         'red' => '41',
         'green' => '42',
         'yellow' => '43',
         'blue' => '44',
         'magenta' => '45',
         'cyan' => '46',
         'light_gray' => '47',
     ];

     static private $_lang = [
         'zh'=>[
             '请选择'=>'请选择',
             '请继续选择[#结束,<回退,*清空]'=>'请继续选择[#结束，<回退，*清空]',
             '当前已选择'=>'当前已选择',
             '输入不正确!请重新输入.'=>'输入不正确！请重新输入。'
         ],
         'en'=>[
             '请选择'=>'Please select',
             '请继续选择[#结束,<回退,*清空]'=>'Please continue to select[#complete，<go back，*reset]',
             '当前已选择'=>'Currently selected',
             '输入不正确!请重新输入.'=>'Invalid input! please enter again.'
         ]
     ];

     static function __callStatic($name, $arguments)
     {
         $name = $name==='charset'?'setCharset':$name;
         if(strpos($name,'set')===0){
             $fn = substr($name,3);
             if(isset(self::$_cfg[$fn])){
                 self::$_cfg[$fn] = $arguments[0];
             }
         }else {
             //兼容老代码
             $_name = strtolower($name);
             $alias = [
                 'stdselect' => 'select',
                 'stdconfirm' => 'confirm'
             ];
             if (isset($alias[$_name])) {
                 return call_user_func_array([__CLASS__, $alias[$_name]], $arguments);
             } else {
                 self::stdout(__CLASS__ . '::' . $name . " not found;", 'error');
                 exit;
             }
         }

     }
     static function versionId(){
         return 10002;
     }

     /**
      * windows cmd 不兼容
      * @return mixed
      */
     static private function _getScreenSize(){
     	if(!isset(self::$_staticCache['screen_size'])){
			$t = trim(exec('stty size'));
			$size = explode(' ',$t);
			self::$_staticCache['screen_size'] = ['w'=>$size[1],'h'=>$size[0]];
		}
		 return self::$_staticCache['screen_size'];
	 }

     private static function _l($str){
         $type = self::$_cfg['lang'];
        return isset(self::$_lang[$type][$str])?self::$_lang[$type][$str]:$str;
     }

	 /**
	  * 禁用样式
	  * @param bool $status
	  */
	 static public function disableStyle($status){
         self::$_cfg['disable_style'] = (bool)$status;
	 }


     // Returns colored string
     static public function getColoredString($string, $foreground_color = null, $background_color = null)
     {
         if(self::$_cfg['disable_style']){
             return $string;
         }else {
             $colored_string = "";
             // Check if given foreground color found
             if ($foreground_color && isset(self::$_fgColor[$foreground_color])) {
                 $colored_string .= "\033[" . self::$_fgColor[$foreground_color] . "m";
             }
             // Check if given background color found
             if ($background_color && isset(self::$_bgColor[$background_color])) {
                 $colored_string .= "\033[" . self::$_bgColor[$background_color] . "m";
             }
             // Add string and end coloring
             $colored_string .= $string . "\033[0m";
             return $colored_string;
         }
     }

     /**
      * 选择对话
      * @param $list
      * @param int $colQty
      * @param string $msg
      * @param bool $mul 多选
      * @return mixed|string|void
      */

     static function select($list,$colQty=1,$msg='',$mul=false){
         $n = 1;
         $row = 0;
         if(empty($list)){echo (self::_l("选项列表有误"));return ;}
         self::stdout("\n---------------------------\n");
         $arr = array_values($list);
         $keys = array_keys($list);
         foreach ($arr as $k=>$v){
             //$color = ($row%2)===0? 'while' :'light_gray';
             $color = 'while';
             self::stdout(" $k. ",'highlight');
             self::stdout($v, [$color, null]);
             if($n<$colQty){
                 $n++;
                 self::stdout( "\t\t");
             }else{
                 $n=1;
                 $row++;
                 self::stdout( "\n- - - - - - - - - - - - - - -",['light_gray',null]);
                 self::stdout( "\n");
             }
         }
         self::stdout( "\n---------------------------\n");


         $r = [];
         $msg = trim($msg)===''?self::_l('请选择'):$msg;
         while (true){
             $tmpRes = self::stdin($msg.":\t",function($v) use ($arr,$mul){
                 return (is_numeric($v) && isset($arr[$v])) || ($mul && in_array($v,['#','<','*']));
             },function ($v){return trim($v);});

             if(!$mul){
                 return $keys[$tmpRes];
             }else {
				 if ($tmpRes === '#') {
					 break;
				 } elseif ($tmpRes === '<') {
					 array_pop($r);
				 } elseif ($tmpRes === '*') {
					 $r = [];
				 } else {
					 $r[] = $tmpRes;
					 $r = array_unique($r);
				 }
				 echo "\033[1A\033[K";
				 self::stdout(self::_l('当前已选择').':[' . implode(',', $r) . ']', 'comment');
				 $msg = self::_l("请继续选择[#结束,<回退,*清空]");
			 }
         }
         if(!empty($r)){
             foreach ($r as $_k=>$_v){
                 $r[$_k] = $keys[$_v];
             }
         }
         return $r;
     }

     /**
      * 确认对话
      * @param string $msg
      * @return bool
      */

     static function confirm($msg){
         $r = self::stdin($msg." [y/n] ",function($v){return ($v==='y'||$v==='n');},function ($v){return strtolower(trim($v));});
         return $r==='y'? true: false;
     }

     /**
      * 标准输入
      * @param string $msg
      * @param bool $validator
      * @param bool $processor
      * @param int $limitLen
      * @return string
      */

     static function stdin($msg='',$validator=false,$processor = false,$limitLen=100000){
         if($msg) {
             if (strpos($msg, '[span]')) {
                 self::stdout($msg . ':');
             } else {
                 self::stdout($msg . ':');
             }
         }
         $stdin=fopen('php://stdin','r');
         $content=trim(fgets($stdin,$limitLen));
         fclose($stdin);

         if(is_callable($processor)){
             $content = call_user_func($processor,$content);
         }
         if(is_callable($validator) && !call_user_func($validator,$content) ){
             //if($callback && !$callback($content)){
             self::stdout("[error]",'highlight');
             self::stdout(self::_l('输入不正确!请重新输入.'),'comment');
             self::stdout("\n");
             $content = self::stdin($msg, $validator,$processor,$limitLen);
         }
         return $content;
     }

     /**
      * 标准输出
      * @param $str
      * @param bool $type error,info,comment,question,highlight,warning
      * @param bool $return
      * @return string
      */

     static public function stdout($str,$type = false,$return = false){
         return self::output($str,$type,$return);
     }

     /**
      * @param array $header
      * @param array $data
      * @param string $align 'l/c/r/left/center/rignt'
      * @return cmp\Table
      */
     static public function table($header=[],$data=[],$align='l',$autoRender=true){

         $tab =  new cmp\Table();
         if(!empty($header)){
             $alignMap = ['l'=>1,'left'=>1,'r'=>0,'right'=>0,'c'=>2,'center'=>2];
             $tab->setHeader($header,isset($alignMap[$align])?$alignMap[$align]:1);
         }

         $len = count($data);
         if($len>0 && is_array($data)){
             $i = 0;
             foreach ($data as $d) {
                 $i++;
                 $tab->addRow($d);
                 if($i<$len) {
                     $tab->addRow('-');
                 }
             }
         }
         if($autoRender){
             $tab->render();
         }

         return $tab;
     }

     /**
      * 文本输出
      * @param string|array $str  array:['this',['is','green'],'test'];
      * @param bool $type error,info,comment,question,highlight,warning
      * @param bool $return
      * @return string
      */

     static public function output($str,$type = false,$return = false){

         if(is_array($str)){
             $r = '';
             foreach ($str as $item){
                 if(is_array($item)){
                     $style = isset($item['style'])?$item['style']:(isset($item[1])?$item[1]:$type);
                     $text = (string)( isset($item['text'])?$item['text']:(isset($item[0])?$item[0]:'') );
                     $r .= self::stdout($text, $style, $return);
                 }else{
                     $r .= self::stdout((string)$item, $type, $return);
                 }
             }
             if ($return) {
                 return $r;
             } else {
                 echo $r;
             }
         }else {
             $typeList = [
                 'error' => ['white', 'red'],
                 'info' => ['green', null],
                 'comment' => ['yellow', null],
                 'question' => ['black', 'cyan'],
                 'highlight' => ['red', null],
                 'warning' => ['black', 'yellow'],
             ];
             if (is_array($type)) {
                 $color = $type;
                 $color[0] = isset($color[0]) ? $color[0] : null;
                 $color[1] = isset($color[1]) ? $color[1] : null;
             } else {
                 $type = $type ? trim($type, '[ ]') : $type;
                 $color = ($type && isset($typeList[$type])) ? $typeList[$type] : false;
             }
             if (PHP_OS === 'WINNT') {
                 $str = str_replace("\r\n", "\n", $str);
                 $str = str_replace("\n", "\r\n", $str);
                 //$str = iconv('utf-8', 'gbk', $str);
             }
             if (self::$_cfg['charset'] != 'utf-8') {
                 $str = iconv('utf-8', self::$_cfg['charset'], $str);
             }

             if ($return) {
                 return $type ? self::getColoredString($str, $color[0], $color[1]) : $str;
             } else {
                 echo $type ? self::getColoredString($str, $color[0], $color[1]) : $str;
                 return '';
             }
         }
         return '';
     }

     /**
      * 移动光标
      * @param string $position l:left r:ring u:up, d:down
      * @param $n
      */

     static public function cursorMove($position,$n=1){
         $position = strtolower($position[0]);
         $list = ['u'=>'A','d'=>'B','l'=>'D','r'=>'C'];
         echo '\033['.$n.$list[$position];
     }

     /**
      * 设置关光标位置
      * @param $x
      * @param $y
      */

     static public function cursorPosition($x,$y){
        echo '\033['.$y.';'.$x.'H';
     }

     /**
      * 清屏
      */
     static public function clear(){
         echo "\033[2J";
     }

     /**
      *
      * @param int $s 秒数
      * @param string $msg 消息
      */
     static public function wait($s,$msg=''){
         $totalLen = 20;
		 $disabledColor = self::$_cfg['disable_style'];
		 $colorBar = !($disabledColor || PHP_OS === 'WINNT');
         for ($i=$s;$i>0;$i--){
			 sleep(1);

             $okLeng = floor(($s-$i+1)/$s*$totalLen);

			 if($colorBar) {
				 $str = str_pad($i.'/'.$s,$totalLen," ",STR_PAD_BOTH);

				 $waitStr = substr($str,0,$totalLen-$okLeng);//str_pad(,$totalLen-$okLeng, " " );
				 $okStr = substr($str,$totalLen-$okLeng);//str_pad(,$okLeng," ");
				 self::stdout(["[$s]秒倒计时 [", [$waitStr, ['black', 'cyan']], [$okStr, [null, 'dark_gray']], '] ']);

			 }else{

				 $waitStr = str_pad('',$totalLen-$okLeng, '=' );
				 $okStr = str_pad('', $okLeng,'-');
				 self::stdout("[$s]秒倒计时 [$waitStr$okStr] $i/$s.");
			 }

             self::stdout($msg);

             echo "\r";

             //echo "\r\033[K";
         }
     }

	 /**
	  * @param int $total
	  * @param int $current
	  * @param string $msg
	  */
     static public function progressBar($total,$current,$msg=''){
		//
		 $len = 60;
		 $c = floor($len/$total*$current);
		 $bar = str_pad('',$c,'=').str_pad('',$len-$c,'-');
		 self::stdout("[$bar] $current/$total $msg     \r");
		 //self::stdout("[$s]秒倒计时 [".$okStr.$waitStr.']'.$i.'/'.$s.'.');
	 }
}