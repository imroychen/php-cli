[中文] /  [English](./README-EN.md)
#简介

iry/Cli 可以简单实现 输出、输入、倒计时、绘制进度条、绘制表格、移动光标、清除屏幕、确认对话 等功能

可以轻松实现类似表单等输入功能：

如（input: _stdin_, 开关:_confirm_, select:select，radio:_select单选模式_, checkbox:_select多选模式_）
# 安装
```
composer require iry/cli
```
# 使用方法
# 环境控制 【可选】
```
use ir\cli\Cli;
Cli::disableStyle(true|false)//是否禁用样式 默认false;
Cli::charset('gbk')//'utf-8', 'gbk', 'gb2312'//默认utf-8
```


## 示例

## 标准输入 
stdin($msg,$validator,$processor);//等待并获取用户输入的数据

|  参数    |类型| 说明  |
|  ----    |----| ----  |
|$msg      |string|消息：等待用户输入前，输一条提示消息消息 |
|$validator|false/callable|验证器:用于验证用户输入的信息是否有效的方法|
|$processor|false/callable|处理器:对用户输入的数据进行处理的方法|

```php
use ir\cli\Cli;
$numeric = Cli::stdin('请输入');//等待并获取用户输入的数据
$numeric = Cli::stdin('请输入数字',function(){return preg_match('/^[0-9]+$/');},'trim');
$numeric = Cli::stdin('请输入数字',function(){return preg_match('/^[0-9]+$/');},'trim');
```
## 标准输出 output($msg,$styleType)
打印到屏幕 支持彩色文字

|参数    ||说明  |
|  ----    |----| ----  |
|$msg      |string/array|消息：等待用户输入前，输一条提示消息消息 |
|$styleType |string/array|error,info,comment,question,highlight,warning,[前景色,背景色]|
$msg数组模式可以设置复杂的彩色文字如下示例
### 示例
```php
Cli::output('这是一个测试','success') 
Cli::output('这是一个测试','error')
Cli::output('这是一个测试',['purple','yellow'])
Cli::output([
    '这是',
    ['一个','error'],//使用error样式
    ['测试','yellow'],//使用info样式
    //string || ['$msg','$styleType']
]);
```
### 示例大致效果如下
<div style="background:#1e1d1d;padding: 10px 20px">
<span style="color:green">这是一个测试：</span><br />
<span style="color:#fff;background:#f54444">这是一个测试：</span><br />
<span style="color:purple;background:yellow">这是一个测试：</span><br />
这是<span style="color:#fff;background:#f54444">一个</span><span style="color: yellow">测试</span><br />
</div>

### 确认对话
confirm($msg,$validator,$processor);//Cli中 确认对话

|参数    ||说明  |
|  ----    |----| ----  |
|$msg      |string|消息：等待用户输入前，输一条提示消息消息 |
|return |bool| true/false|

$msg
```php
use ir\cli\Cli;
$confirm = Cli::confirm('你确认要删除吗?');

//效果如下
//你确认要删除吗?[y/n] _
```

### 列表选择
select($list,$colQty,$msg,$mul);

|参数    |||说明  |
|  ----    |----|----| ----  |
|$list |array|_必须_|可选清单 |
|$colQty|int|可选|列数，展示可选清单时，展示几列。默认：1|
|$msg|string|可选|默认：请输入|
|$mul|bool|可选|是否允许多选，默认：false|

### 移动光标
cursorMove(方位,移动量)
方位：l:left r:ring u:up, d:down
```php
use ir\cli\Cli;
$confirm = Cli::cursorMove('u',3); //光标上移三行
$confirm = Cli::cursorMove('l',4); //光标左移4个字符
```

### 光标定位 _(移动光标到指定位置)_
cursorPosition(x,y) y行x列
```php
use ir\cli\Cli;
$confirm = Cli::cursorPosition(3,5);//光标定位到5行第三个字符后面
``` 

### clear
clear();//清除屏幕

### wait (等待/倒计时/模拟loading 等场景)
wait(int $s 秒,string $msg 消息);

### progressBar (进度条绘制)
progressBar(int $total 总量,int $current 当前量, string $msg 文本信息)