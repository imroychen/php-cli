[中文](./README.md) /  [English]
#About <u>iry/cli</u>
# Introduction
iry/Cli can simply implement standard input, output, countdown, draw progress bar, 
draw table, move cursor, clear screen, confirm dialogue and other functions
        
The input function similar to the form can be easily realized:
E.g:（input: _stdin_, 开关:_confirm_, select:select，radio:_select[Single selection mode]_, checkbox:_select[Multiple selection mode]_）
# Installation
```
composer require iry/cli
```
# How to use?
# Env control 【Optional】
```
use ir\cli\Cli;
Cli::disableStyle(true|false)//Disable style. Default false;
Cli::charset('gbk')//'utf-8', 'gbk', 'gb2312'// Default utf-8
```


## Example
E.g:
## Standard input (stdin)
stdin($msg,$validator,$processor);//Wait and get the data entered by the user

| Parameters | Type | Description |
|  ----    |----| ----  |
|$msg      |string|Message：Enter a prompt message before waiting for user input |
|$validator|false/callable|Validator: a method used to verify whether the information entered by the user is valid|
|$processor|false/callable|Processor: a method of processing data entered by the user|

```php
use ir\cli\Cli;
$numeric = Cli::stdin('Pls Enter');//Wait and get the data entered by the user
$numeric = Cli::stdin('Pls enter the quantity',function(){return preg_match('/^[0-9]+$/');},'trim');
$numeric = Cli::stdin('Pls enter the quantity',function(){return preg_match('/^[0-9]+$/');},'trim');
```
## Standard output output($msg,$styleType)
Only print to the screen, support colored text

| Parameters | Type | Description |
|  ----    |----| ----  |
|$msg      |string/array|Message：Enter a prompt message before waiting for user input |
|$styleType |string/array|error,info,comment,question,highlight,warning,[Foreground color, background color]|
$msg: Use an array to set up complex colored text. E.g:

```php
[
    'This',
    ['is','error'],//use error style
    ['test','info']//use info style
    //string || ['$msg','$styleType']
];
```
### confirm
confirm($msg,$validator,$processor);//Cli中 Confirmation dialog

| Parameters | Type | Description |
|  ----    |----| ----  |
|$msg      |string/array|Message：Enter a prompt message before waiting for user input |
|return |bool| true/false|

$msg
```php
use ir\cli\Cli;
$confirm = Cli::confirm('Are you sure you want to delete?');

//The effect is as follows:
//Are you sure you want to delete? [y/n] _
```

### List selection
select($list,$colQty,$msg,$mul);

|参数    |||说明  |
|  ----    |----|----| ----  |
|$list |array|_Required_|Selection list |
|$colQty|int|Optional|The number of columns displayed when the selection list is displayed. Default : 1|
|$msg|string|Optional|Default：please enter|
|$mul|bool|Optional|Allow multiple selection，Default：false|

### Move the cursor
cursorMove(方位,移动量)
方位：l:left r:right u:up, d:down
```php
use ir\cli\Cli;
$confirm = Cli::cursorMove('u',3); //光标上移三行
$confirm = Cli::cursorMove('l',4); //光标左移4个字符
```

### Cursor positioning _(Move the cursor to the specified position)_
cursorPosition(x,y) y行x列
```php
use ir\cli\Cli;
$confirm = Cli::cursorPosition(3,5);//The cursor is positioned after the third character in line 5
``` 

### clear
clear();//Clear screen

### wait (Waiting/counting down/simulating loading and other scenes)
wait(int $s second,string $msg message);

### progressBar (Progress bar drawing)
progressBar(int $total total,int $current current, string $msg string)