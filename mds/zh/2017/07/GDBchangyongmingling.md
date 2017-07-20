---

layout: post  
title: GDB常用命令  
subtitle:   
author: 向南  
category: tech
tags: GDB, 调试  
ctime: 2017-07-21 02:01:12  
lang: zh  

---

## 0x00 简介

GDB是GNU开源组织发布的一个强大的UNIX下的程序调试工具。

一般来说，GDB 主要有一下功能：    

1. 启动程序，按照期望运行程序
2. 程序断点调试
3. 查看程序运行时信息
4. 动态的改变程序的执行环境

## 0x01 启动程序

**1. 启动方式一**

````
$ gdb {BIN_NAME}
````

**2. 启动方式二**

````
$ gdb
(gdb) file {BIN_NAME}
(gdb) start/run
````
> **编译程序的时候要加入 `-g` 调试选项。**  
> 
> i. 关于 `run`
> 用 `run` 命令开始运行，程序会运行到设置了断点的位置后暂停运行。>
> 可使用 `run` 命令，在它后面可以跟随发给该程序的任何参数，包括标准输入和标准输出说明符(<和> )和 shell 通配符（*、？、[、]）在内。
> 
> `run` 可简写为 `r`。
> 
> ii. 关于 start
> 
> 运行至main()函数，停下来。

**3. 启动方式三： 调试正在运行的程序**

````
$ gdb {PID} // {PID} 为进程 ID
````

## 0x02 查看代码相关

### i. 列出指定区域代码
````
(gdb) list n1 n2
````
> `list` 可以简写为l，将会显示 n1 行和 n2 行之间的代码。
> 
> 如果没有 n1 和 n2 参数，那么就会默认显示当前行和之后的10行，再执行又下滚 10 行。
> 
> `list` 可以接函数名。

> 一般来说在 `list` 后面可以跟以下这们的参数：  
> 
> < linenum >   行号， 如： l 100  
> <+offset>   当前行号的正偏移量， 如：l +90  
> <-offset>   当前行号的负偏移量。  
> < filename:linenum >  指定文件文件的某一行， 如：l nginx.c:100  
> <function>  函数名，如： l main  
> < filename:function > 指定文件某函数， 如： l nginx.c:main  
> < *address >  程序运行时的语句在内存中的地址。 

## 0x03 执行代码相关

**1. 执行一行代码。**

````
(gdb) next
````
如果是函数也会跳过函数，可以简写为 n。

**2. 执行 N 次下一步**  

````
(gdb) n N
````

**3. 执行上次执行的命令**
````
(gdb) [Enter]
````

输入回车就会执行`上一次`的命令。

**4. 单步进入**

````
(gdb) step
````

`step` 也会执行一行代码，不过如果遇到函数的话就会进入函数的内部，再一行一行的执行。

> 备注：注意与 `next` 的区别。

**5. 执行完当前函数返回到调用它的函数**

````
(gdb) finish
````

运行程序直到当前函数运行完毕返回再停止。

例如进入的单步执行如果已经进入了某函数，而想退出该函数返回到调用该函数的代码位置。

**6. 指定程序直到退出当前循环体**

````
(gdb) until
````
把光标停止在循环的头部，输入 `until` 将自动执行完循环。

可简写为：u。

** 7. 跳转执行程序到第 N 行**

````
(gdb) jump N
````

跳转到第 N 行执行完毕之后，如果后面没有断点则继续执行。   
可以简写为 `j N`。

> 备注：跳转不会改变当前的堆栈内容，所以跳到别的函数中就会有奇怪的现象，因此最好跳转在一个函数内部进行，跳转的参数也可以是程序代码行的地址，函数名等等类似 `list`。

**8. 强制返回当前函数**

````
(gdb) return

````

将会忽略当前函数还没有执行完毕的语句，强制返回。

> 备注：`return` 后面可以接一个表达式，表达式的返回值就是函数的返回值。

**9. 强制调用函数 1**

````
(gdb) call <expr>
// 如： call main , main 是函数名
````
<expr> 可以是一个函数，返回函数的返回值，如果函数的返回类型是void 那么就不会打印函数的返回值。

**10. 强制调用函数 2**

````
(gdb) print <expr>
````
`print` 和 `call` 的功能类似。

不同的是：如果函数的返回值是 void, 那么 `call` 不会打印返回值，但是 `print` 还是会打印出函数的返回值并且存放到历史记录。

**11. 在当前的文件中某一行（假设为 6）设定断点**

````
(gdb) break 6
````

可简写： `b 6`。
> 备注，`break` 也支持以下方式设置断点。
> 
> b 函数名，如：`b main`  > b 行号，如：`b 100`  > b 文件名:行号，如：`b nigix.c:100`  > b 文件名:函数名，如：`b nginx.c:main`  > b +偏移量  > b -偏移量  > b *地址
>

**12. 设置条件断点**

````
(gdb) break 46 if test_size==100
````

如果 test_size==100 就在 46 行处断点。

**13. 检测表达式变化则停住**

````
(gdb) watch i != 10
````

`i != 10` 这个表达式一旦变化，则停住。

> watch <expr> 为`表达式`或`变量` (expr) 设置一个观察点。一旦表达式值有变化时，马上停住程序 (也是一种断点)。
> 
> 备注： 除了 `watch` 还有 `awatch` （被访问和修改时）和 `rwatch` （被访问时）。

**14.删除断点**

````
(gdb) delete N
````

可简写为 `d N` 。

````
// 删除所有断点有两种方式

(gdb) delete

或

(gdb) d breakpoints

// 清除行N上面的所有断点：

(gdb) clear N
````

**15. 查看断点**

````
(gdb) info b
````

**16. 继续运行程序**

````
(gdb) continue
````

可简写： `c` 。

调试时，可以使用 c 命令继续运行程序。 程序会在遇到断点后再次暂停运行，如果没有遇到断点，就会一直运行到结束。

## 0x04 查看/设置运行时信息

**1. 显示当前调用函数栈中的函数**

````
(gdb) backtrace
````
命令产生一张列表，包含着从最近的过程开始的所有有效过程和调用这些过程的参数。同时，也显示出当前运行到的文件和行。

> bt 显示所有函数  > bt N 显示开头 N 个函数  > bt -N 显示最后 N 个函数    > bt full 显示调用函数和函数中局部变量  

**2. 查看当前调试程序的语言环境**

````
(gdb) show language
````
如果 gdb 不能识别你所调试的程序，那么默认是c语言。

**3. 查看当前函数的程序语言**

````
(gdb) info frame
````

**4. 显示当前的调试源文件**

````
(gdb) info source
````

显示当前所在的源代码文件信息，例如：文件名称，程序语言等。


**5. print 显示变量值**

````
(gdb) print var
````
可简写为 `p`。

> 备注： `print` 是 GDB 的一个功能很强的命令，利用它可以显示被调试的语言中任何有效的表达式。表达式除了包含你程序中的变量外，还可以包含函数调用，复杂数据结构(以及里面的元素，如：`s->data`)和历史等等。

**`print` 支持指定不同格式进行打印**

````
(gdb) print /x var
````
`print` 可以指定显示的格式，这里用 `/x` 表示 16 进制的格式。

> 可以支持的变量显示格式有：  
> x  按十六进制格式显示变量。    
> d  按十进制格式显示变量。  
> u  按十六进制格式显示无符号整型。  
> o  按八进制格式显示变量。  
> t  按二进制格式显示变量。  
> a  按十六进制格式显示变量。  
> c  按字符格式显示变量。  
> f  按浮点数格式显示变量。  

**6. 查看变量类型**

````
(gdb) ptype 变量名
````

`ptype` 会打印变量详细的数据结构（比如：struct）。

如果只想知道类型不需要详细结构，可以使用 `whatis 变量名`。

**7. 设置变量的值**

````
(gdb) set variable 变量名=值
````
可简写为：`set var 变量名=值`

**8. info 查看栈、内存等等信息**

查看方式： `info 信息名称`，如： info stack

````
(gdb) info 
address                    display                    macros                     sharedlibrary              tracepoints
all-registers              extensions                 mem                        signals                    tvariables
args                       files                      os                         skip                       type-printers
auto-load                  float                      pretty-printer             source                     types
auxv                       frame                      probes                     sources                    variables
bookmarks                  frame-filter               proc                       stack                      vector
breakpoints                functions                  program                    static-tracepoint-markers  vtbl
checkpoints                handle                     record                     symbol                     warranty
classes                    inferiors                  registers                  target                     watchpoints
common                     line                       scope                      tasks                      win
copying                    locals                     selectors                  terminal                   
dcache                     macro                      set                        threads 

````
## 0x05 其他操作

**1. 终止一个正在调试的程序**

````
(gdb) kill
````
输入 `kill` 就会终止正在调试的程序。

> 备注： 也可以是 `quit`（简写 `q`）。
