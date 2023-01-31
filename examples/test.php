<?php
require dirname(__DIR__).'/autoload.php';
use iry\cli\Cli;
function showEg($n, $code, $line)
{
    echo "\n--------------------[示例2]-------------------------------\n";
    Cli::output($code . "\n");
    Cli::stdin('回车查看效果');
    Cli::cursorMove('u', 1);
    Cli::output(["示例代码在第【",[$line+1,'warning'],'】行']);
    echo "\n";
}

class TestEg
{
    function test_isCli()
    {
        showEg(2, 'Cli::isCli()', __LINE__);
        if (!Cli::isCli()) {
            Cli::output("请在CLI下运行");
        } else {
            Cli::output(['CLI模式检测', ['OK', 'success']]);
        }
    }

    function test_output()
    {

        showEg(2, '输出: Cli::output(Str)', __LINE__);
        echo "\n";
        //----以下就是示例效果的代码
        Cli::output("\n这是一个测试");                    //直接打印
        Cli::output("\n这是一个测试", 'success');     //打印主题样式 success/warning/error/info/comment/question/highlight
        Cli::output("\n这是一个测试", 'error');
        Cli::output("\n这是一个测试", 'warning');
        Cli::output("\n这是一个测试", ['white', 'green']);
        Cli::output([
            "\n这是",
            ['一个', 'error'],//使用error样式
            ['测试', ['white', 'green']],//使用info样式
            "\n"
        ]);
    }

    function test_stdin()
    {
        showEg(4, '交互输入: Cli::stdin(Str,$validator,$processor)', __LINE__);
        echo "\n";
        //----以下就是示例效果的代码
        $content = Cli::stdin("请输入");
        Cli::output("您输入的内容是:", 'warning');
        Cli::output($content . "\n");

        $num = Cli::stdin("请输入数字", function ($v) {
            return preg_match('/\d+/', $v);
        });
        Cli::output("您输入的数字是:", 'warning');
        Cli::output($num . "\n");

        $emal = Cli::stdin("请输入邮箱", function ($v) {
            return preg_match('/\w+@[\w\-.]+\.[\w]+/', $v);
        }, 'trim');
        Cli::output("您的邮箱是:", 'warning');
        Cli::output($emal . "\n");
    }

    function test_select(){
        showEg(3, '菜单选择: Cli::select( "array|清单|必须", "<int|列数|可选|默认:1>", "<string|自定义文本|可选|默认:请选择>", "<bool|多选|可选|默认false>" )', __LINE__+2);

        //----以下就是示例效果的代码
        echo "\n常规示例";
        $v = Cli::select(['女士','先生','其他']);
        echo "你选择的值为:$v\n";

        echo "\n示例二，2列模式";
        $v = Cli::select(['A'=>'先生','B'=>'女士','C'=>'其他'],2);
        echo "你选择的值为:$v\n";

        echo "\n 示例3 多选模式, 自定义Msg文本";
        $v = Cli::select(["游泳","滑冰","滑雪","登山","徒步","篮球","足球","兵乓球","网球","其它"],3,"选择你的爱好",true);
        echo "你选择的值为:".implode(',',$v)."\n";

    }

    function test_confirm(){
        showEg(3, '确认对话: Cli::confirm( "string|消息文本" )', __LINE__+2);

        //----以下就是示例效果的代码
        echo "\n";
        $v = Cli::confirm("这是一个确认示例，你确定要执行吗");
        echo "结果为:";var_export($v);echo "\n";

    }

    function test_wait(){
        showEg(4, 'Cli::wait("int 时长", "string 自定义文本")', __LINE__+2);

        //----以下就是示例效果的代码
        echo "\nCli::wait(10,'10秒倒计时')\n";
        Cli::wait(10,'10秒倒计时');
        echo "\n计时结束\n";

        Cli::wait(5,'5秒等待中');
        echo "\n结束\n";
    }


    function test_progressBar(){
        showEg(5, '绘制进度条: Cli::progressBar(Str,$validator,$processor)', __LINE__);
        //----以下就是示例效果的代码
        echo "\n";
        for ($i=0;$i<10;$i++) {
            Cli::progressBar(10, $i,'自定义文字');
            sleep(1);
        }

        echo"\n";
        for ($i=0;$i<10;$i++) {
            Cli::progressBar(10, $i,'自定义文字',40);
            sleep(1);
        }
    }


    function test_loading()
    {
        showEg(2, 'Cli::loading(callable,"msg")', __LINE__+1);

        //----以下就是示例效果的代码
        echo "\n";
        $i = 0;
        Cli::loading( function()use(&$i){$i++;return $i > 300;}, '第一种风格/正在加载');

        echo "\n";
        $i = 0;
        Cli::loading( function()use(&$i){$i++;return $i > 1000;}, ' 降低动画帧率为(每秒2帧)/正在加载',['style'=>0,'fps'=>2]);//fps 每秒2帧

        echo "\n";
        $i = 0;
        Cli::loading( function()use(&$i){$i++;return $i > 300;}, '第二种风格/正在加载',['style'=>1]);

        echo "\n";
        $i = 0;
        Cli::loading( function()use(&$i){$i++;return $i > 300;}, '第三种风格/正在加载',['style'=>2]);

        echo "\n";
    }
}

if(!Cli::isCli()){
    exit("请在CLI下执行");
}
$eg = new TestEg();
$methods = get_class_methods($eg);
$egList = [];
foreach ($methods as $v){
    $egList[$v] = str_ireplace('test_','',$v);
}
$egList['all']='ALL / 全部';
$egList['exit']='Exit / 退出';
while (true) {
    $egName = Cli::select($egList, 3, '您需要查看哪个示例');
    if ($egName === 'all') {
        if(Cli::confirm("您选择的展示全部示例，是否要继续")) {
            foreach ($methods as $k => $v) {
                $eg->$v();
            }
        }
    }
    elseif($egName==='exit') {
        exit("已退出\n");
    }
    else {
        $eg->$egName();
    }
}