<?php
require dirname(__DIR__).'/autoload.php';
use iry\cli\Cli;
echo "\n--------------------[示例一]-------------------------------\n";
echo "Cli::isCli()\n";
if(!Cli::isCli()){
    Cli::output("请在CLI下运行");
}else{
    Cli::output(['CLI模式检测',['OK','success']]);
}

function showEg($n,$code,$line){
    echo "\n--------------------[示例2]-------------------------------\n";
    Cli::output($code."\n");
    Cli::stdin('回车查看效果');
    Cli::cursorMove('u',1);
    echo '示例代码在第【'.($line+1).'】行';
}

//--------------------[示例2]-------------------------------
showEg(2,'Cli::output(Str)',__LINE__);echo "\n";
//----以下就是示例效果的代码
Cli::output("\n这是一个测试");                    //直接打印
Cli::output("\n这是一个测试",'success');     //打印主题样式 success/warning/error/info/comment/question/highlight
Cli::output("\n这是一个测试",'error');
Cli::output("\n这是一个测试",'warning');
Cli::output("\n这是一个测试",['white','green']);
Cli::output([
    "\n这是",
    ['一个','error'],//使用error样式
    ['测试',['white','green']],//使用info样式
    "\n"
]);

//--------------------[示例3]-------------------------------
showEg(2,'Cli::stdin(Str,$validator,$processor)',__LINE__);echo "\n";
//----以下就是示例效果的代码
$content = Cli::stdin("请输入");
Cli::output("您输入的内容是:",'warning');Cli::output($content."\n");

$num = Cli::stdin("请输入数字",function ($v){return preg_match('/\d+/',$v);});
Cli::output("您输入的数字是:",'warning');Cli::output($num."\n");

$emal = Cli::stdin("请输入邮箱",function ($v){return preg_match('/\w+@[\w\-.]+\.[\w]+/',$v);},'trim');
Cli::output("您的邮箱是:",'warning');Cli::output($emal."\n");
