<?php
/**
 * User: roy
 * Date: 2016/12/6
 */

namespace iry\cli;
/**
 * Class Cli
 * @package iry\cli
 * @method static setCharset ($charset); //$charset:'utf-8', 'gbk', 'gb2312'
 * @method static setLang ($lang); //$lang: zh/en
 * @method static setTypewriterMode($p);//$p bool
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

     static private $_theme = [
         'error' => ['white', 'red'],
         'success'=> ['green',null],
         'warning' => ['purple', 'yellow'],

         'info' => ['green', null],
         'comment' => ['yellow', null],
         'question' => ['black', 'cyan'],
         'highlight' => ['red', null],
     ];

     static private $_lang = [
         'zh'=>[
             'Please select'=>'请选择',
             'Please continue to select.'=>'请继续选择[#结束，<回退，*清空，%全选]',
             'Currently selected'=>'当前已选择',
             'Error,please enter again.'=>'输入不正确！请重新输入。'
         ],
         'en'=>[
             'Please select'=>'Please select',
             'Please continue to select.'=>'Please continue to select[#complete，<go back，*reset,%select all]',
             'Currently selected'=>'Currently selected',
             'Error,please enter again.'=>'Invalid input! please enter again.'
         ]
     ];
     private static $_typewriterMode=false;

     static function __callStatic($name, $arguments)
     {
         $name = $name==='charset'?'setCharset':$name;
         $lowerName = strtolower($name);
         if($lowerName ==='settypewritermode'){
             self::$_typewriterMode = !!$arguments[0];
         }
         if(strpos($name,'set')===0){
             $fn = substr($name,3);
             $fn = preg_replace('/([A-Z])/', '_$1', $fn);
             $fn = strtolower(ltrim($fn, '_'));

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
         return null;
     }
     static function versionId(){
         return 10002;
     }

     /**
      * @return bool
      */
     static function isCli(){
         return (bool)(preg_match("/cli/i", php_sapi_name()));
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
         $str = strtolower(trim($str));

         if(!isset(self::$_lang[$type]['_init_'])){
             $new = ['_init_'=>true];
            foreach (self::$_lang[$type] as $k=>$v){
                $_k = strtolower(trim($k));
                if($k!=$_k){
                    unset(self::$_lang[$type][$k]);
                    $new[$_k]=$v;
                }
            }
             self::$_lang[$type] = array_merge(self::$_lang[$type],$new);
         }

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

     static private function _typingAnim($text){
         $totalLen = mb_strlen($text);
         $start = 0;
         while (true){
             $len = 1;
             echo mb_substr($text,$start,$len);
             usleep(1000 * 100);
             if($start>$totalLen){
                 return ;
             }
             $start=$start+$len;
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

         $widths = [0=>0];
         $arr = [];
         $keys = [];
         $i=1;
         foreach ($list as $k=>$v){
             $arr[$i] = $v;
             $keys[$i]=$k;
             if($colQty>0) $widths[$i] = mb_strwidth($v);
             $i++;
         }
         $valMaxWidth = max($widths);

         $keyMaxWidth = strlen(strval(count($arr)));
         //- - - - - - - - - - - - - - -

         $rowWidth = max($colQty * ($valMaxWidth + $keyMaxWidth + 5 +3) ,10);
         self::stdout("\n". str_pad('',$rowWidth,'-')."\n");
         $rowLine = str_pad('',$rowWidth,'- ');
         foreach ($arr as $k=>$v){
             //$color = ($row%2)===0? 'while' :'light_gray';
             $color = 'while';
             self::stdout(' ' . str_pad(strval($k), $keyMaxWidth, ' ', STR_PAD_LEFT) . ". ", 'highlight');
             self::stdout($v, [$color, null]);
             echo $colQty > 1 ? str_pad('', max($valMaxWidth - $widths[$k], 0), ' ') : '';
             if ($n < $colQty) {
                 $n++;
                 self::stdout(" | ", ['light_gray', null]);
             } else {
                 $n = 1;
                 $row++;
                 self::stdout("\n$rowLine", ['light_gray', null]);
                 self::stdout("\n");
             }
         }
         self::stdout("\n". str_pad('',$rowWidth,'-')."\n");


         $r = [];
         $msg = trim($msg)===''?self::_l('Please select'):$msg;
         while (true){
             $tmpRes = self::stdin($msg.":\t",function($v) use ($arr,$mul){
                 return (is_numeric($v) && isset($arr[$v])) || ($mul && in_array($v,['#','<','*','%']));
             },function ($v){return trim($v);});

             if(!$mul){
                 return $keys[$tmpRes];
             }else {
                 if ($tmpRes === '%') {
                     return $keys;
                 } elseif ($tmpRes === '#') {
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
				 self::stdout(self::_l('Currently selected').':[' . implode(',', $r) . ']', 'comment');
				 $msg = self::_l("Please continue to select.");
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
      * @return mixed
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
             self::stdout(self::_l('Error,please enter again.'),'comment');
             self::stdout("\n");
             $content = self::stdin($msg, $validator,$processor,$limitLen);
         }
         return $content;
     }

     /**
      * 等待输入  标准输入的别名
      * @param string $label
      * @param bool $validator
      * @param bool $processor
      * @param int $limitLen
      * @return mixed
      */

     static function input($label,$validator=false,$processor = false,$limitLen=100000){
         return self::stdin($label,$validator,$processor,$limitLen);
     }

     /**
      * 标准输出 暂时仅支持打印到屏幕
      * @param $str
      * @param bool $type error,info,comment,question,highlight,warning
      * @param bool $return
      * @return string
      */

     static public function stdout($str,$type = false,$return = false){
         return self::output($str,$type,$return);
     }

     /**
      * table
      * @param array $header 键值对。键需要和$data中的键对应
      * @param array $data
      * @param string $align 'l/c/r/left/center/rignt'
      * @param bool $autoRender default true ,if false you need "echo $returnResult->render()";
      * @return cmp\Table | null
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
             echo $tab->render();
             return null;
         }

         return $tab;
     }

     /**
      * output
      * @param string|array $str  array:['this',['is','green'],'test'];
      * @param false|string $type success,warning,error,info,comment,question,highlight|false
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
                     $r .= self::output($text, $style, $return);
                 }else{
                     $r .= self::output((string)$item, $type, $return);
                 }
             }
             if ($return) {
                 return $r;
             } else {
                 echo $r;
             }
         }else {
             if (is_array($type)) {
                 $color = $type;
                 $color[0] = isset($color[0]) ? $color[0] : null;
                 $color[1] = isset($color[1]) ? $color[1] : null;
             } else {
                 $type = $type ? trim($type, '[ ]') : $type;
                 $color = ($type && isset(self::$_theme[$type])) ? self::$_theme[$type] : false;
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
             }
             elseif(self::$_typewriterMode) {
                self::_typingAnim($type ? self::getColoredString($str, $color[0], $color[1]) : $str);
             }
             else{
                 echo $type ? self::getColoredString($str, $color[0], $color[1]) : $str;
             }
         }
         return '';
     }

     /**
      * 移动光标
      * @param string $position l:left r:right u:up, d:down
      * @param $n
      */

     static public function cursorMove($position,$n=1){
         $position = strtolower($position[0]);
         $list = ['u'=>'A','d'=>'B','l'=>'D','r'=>'C'];
         echo "\033[$n".$list[$position];
     }

     /**
      * 设置关光标位置
      * @param $x
      * @param $y
      */

     static public function cursorPosition($x,$y){
        echo "\033[$y;$x".'H';
     }

     /**
      * 清屏
      */
     static public function clear(){
         echo "\033[2J";
     }

     /**
      * Wait
      * @param int $s 秒数
      * @param string $msg 消息
      */
     static public function wait($s,$msg=''){
         $totalLen = 20;
         $disabledColor = self::$_cfg['disable_style'];
         $colorBar = !($disabledColor || PHP_OS === 'WINNT');
         $msg = empty($msg)? "[$s 秒倒计时]":$msg;
         for ($i=$s;$i>0;$i--){
             echo "\r";
             self::_bar($s,$i,20,false);
             self::output($msg);
             sleep(1);
         }

         echo "\r";
         self::_bar($s,0,20,false);
         self::output($msg);
     }

     static private function _bar($total,$current,$totalLen,$moveCursor=true){
         $disabledColor = self::$_cfg['disable_style'];
         $colorBar = !($disabledColor || PHP_OS === 'WINNT');
         $solidLen = floor($current*$totalLen/$total);
         //$hollowLen = $totalLen-$solidLen;


         if($moveCursor) echo "\r";
         if($colorBar ) {
             $str = str_pad($current.'/'.$total,$totalLen," ",STR_PAD_BOTH);
             $solidStr = substr($str,0,$solidLen);
             $hollowStr = substr($str,$solidLen);
             self::output(['[',[$solidStr, ['white', 'cyan']], [$hollowStr, ['black', 'light_gray']], '] ']);

         }else{

             $solidStr = str_pad('',$solidLen, '=' );
             $hollowStr = str_pad('', $totalLen-$solidLen,'-');
             self::output('['.$solidStr.$hollowStr."] $current/$total.");
         }
     }

     /**
      * progressBar
      * @param $total
      * @param $current
      * @param string $msg
      * @param int $len 进度条的总宽度
      */

     static public function progressBar($total,$current,$msg='',$len=60){
         self::_bar($total,$current+1,$len,true);
         self::output("$current/$total $msg     ");
     }

     /**
      * @param callable $callback
      * @param string $msg
      * @param array $options [
      *     'style'=>'int 样式 0-2 default(0)',
      *     'refresh_rate'=>'int 刷新频率 default(60)',
      *     'fps'=>'int 帧率 0:Auto|1-30 default(0)'
      * ]
      */

     static public function loading($callback,$msg='',$options=[]){

         $options = array_merge(['style'=>0,'refresh_rate'=>100,'fps'=>0],$options);
         $loading = [
             ['⣷', '⣯', '⣟', '⡿', '⢿', '⣻', '⣽','⣾'],
             ["◐", "◓", "◑", "◒"],
             ['   ','.  ', '.. ','...'],
         ];
         $loading = isset($loading[$options['style']])?$loading[$options['style']]:$loading[0];
         $len = count($loading);
         $after = $options['style']===2;

         $switchSpeed = $options['fps']<1? ($options['refresh_rate']/$len/2): $options['refresh_rate']/min($options['fps'],30);
         $i = 0;

         while (true) {
             if(call_user_func($callback)) return;

             $loadingStr = self::getColoredString($loading[intval($i/$switchSpeed) % $len].' ','green');
             echo $after ? ("\r" .$msg.$loadingStr): ("\r" .$loadingStr.$msg);

             if($i>=$options['refresh_rate']*$len) $i=0;
             else $i++;
             usleep(1000000/$options['refresh_rate']);
         }
     }
}