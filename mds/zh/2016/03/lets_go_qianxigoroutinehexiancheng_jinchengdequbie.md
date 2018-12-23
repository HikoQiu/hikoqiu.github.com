---

layout: post  
title: [Go细节] 浅析goroutine和线程、进程的区别
subtitle:   
author: Hiko  
category: tech  
tags: go,goroutine,thread,process  
ctime: 2016-03-13 13:09:58  
lang: zh  

---

### 概览

- 1\. 一个死循环的例子
	- 1.1 goroutine 死循环 (Go版本 <= 1.5)
	- 1.2 goroutine 死循环 (Go版本 >= 1.6)
	- 1.3 Java Thread 死循环
	- 1.4 C fork子进程 死循环
	- 1.5 小总结
- 2\. 调度方式
	- 2.1 抢占式( 进程/线程 )
	- 2.2 协作式( goroutine ) 
- 3\. 相关开销
	- 3.1 内存(栈)
	- 3.2 上下文切换 
- 4\. 其他

- - -

### 1. 一个死循环的例子

例子描述

- 先起100个协程/线程/进程
- 接着 while(true) 在主线程中执行死循环  

观察 goroutine 、线程以及进程最终的执行结果。

#### 1.1 goroutine 死循环( Go 版本 <= 1.5)

i. 代码片段(1.1代码片段与1.2代码片段一样)

	// runtime.GOMAXPROCS(runtime.NumCPU()) // 1.5之后无需指定利用处理器个数
	for i:=0; i < 100; i++ {
		go func() {
			fmt.Println("当前 goroutine id : ", GetGid())
		}()
	}

	for {}
	
> 备注：关于如何获取goroutine的id，见另一篇文：[`《获取 goroutine id》`](/posts/zh/2016/04/huoqugoroutine_id_tech.html)

ii. 编译运行结果**卡在主协程的 for{} 死循环，其他子协程都无法被 Go runtime 调度器分配到处理器(P)去执行**。

	结果：死循环中，无输出。	
	

#### 1.2 goroutine 死循环( Go 版本 >= 1.6)

i. 代码片段(1.1代码片段与1.2代码片段一样)

	// runtime.GOMAXPROCS(runtime.NumCPU())
	for i:=0; i < 100; i++ {
		go func() {
			fmt.Println("当前 goroutine id : ", GetGid())
		}()
	}

	for {}

> 备注：关于如何获取goroutine的id，见另一篇文：[`获取 goroutine id`](/posts/zh/2016/04/huoqugoroutine_id_tech.html)

ii. 编译执行结果
	
	...
	
	当前 goroutine id :  22
	当前 goroutine id :  23
	当前 goroutine id :  24
	当前 goroutine id :  25
	当前 goroutine id :  26
	当前 goroutine id :  77
	当前 goroutine id :  51
	当前 goroutine id :  52
	
	...


#### 1.3 Java Thread 死循环

i. 创建100个子线程

    for(int i = 0; i < 100; i++) {   
        // 起一个Java线程
        PrintNumThread tr = new PrintNumThread();
        tr.start();    
    }
        
    while(true) {}
    

    
ii. 子线程中打印线程id

   
	public class PrintNumThread extends Thread {

    	@Override
    	public void run() {
        	super.run();
        
        	try {
            	Thread.currentThread().sleep(5);
        	} catch (InterruptedException e) {
            	// TODO Auto-generated catch block
            	e.printStackTrace();
        	}
        	System.out.println("current thread id : "+ Thread.currentThread().getId());
    	}
	}

iii. 编译运行结果(截取其中的一小片段)

	...
	
	current thread id : 8
	current thread id : 9
	current thread id : 11
	current thread id : 12
	current thread id : 10
	current thread id : 13
	current thread id : 14
	current thread id : 17
	current thread id : 21
	current thread id : 24
	
	...


#### 1.4 C fork子进程 死循环

i. 父进程中，fork 100个子进程，并且在子进程中打印子进程的进程id

	for(int i = 0; i < 100; i++) {
        pid_t pid = fork();

        if(pid == 0) { // 子进程
            printf("当前子进程ID: %d\n", getpid());
            return 0;
        }else if(pid == -1) {
            return -1;
        }
    }

    while (1) {}

ii. 编译运行结果(截取其中的一小片段)

	...
	
	当前进程ID: 2354
	当前进程ID: 2355
	当前进程ID: 2356
	当前进程ID: 2357
	当前进程ID: 2358
	当前进程ID: 2359
	当前进程ID: 2360
	当前进程ID: 2361
	
	...
	
#### 1.5 小总结

从 1.1 ~ 1.4 的试验中，很明显能看出（或感受到） go 的 runtime 调度器对 goroutine 的调度与操作系统对进程和线程的调度方式是有区别的。

> 在我写这篇文章之前的一段时间里，我的本地环境Go版本是1.5，当时试验1.1代码片段，观察到程序在 for{} 处死循环，并且 go 调度器没有调度其他 goroutine 进行执行。而最近1.6发布后，我升级了版本之后整理这篇文的时候，发现这个问题已经解决，所以有了1.2代码片段的试验(1.1代码片段一样)。  

> 而对于1.1代码片段(在1.5版本上编译执行)，要让其正常打印结果，可以在 for{} 中手动触发 Go 调度器进行调度：`runtime.GoSched()`。

### 2. 调度方式



Processes are managed by kernel.
Goroutines and coroutines are managed by processes themself, and they are more lightweight than processes.


#### 2.1 抢占式( 进程/线程 )

#### 2.2 协作式( goroutine )

### 3. 相关开销

#### 3.1 内存(栈)

##### 3.1.1 进程

##### 3.1.2 线程

##### 3.1.3 goroutine

At least with the gc toolchain, a goroutine really just 
has two values: a stack pointer and a pointer to the g structure. 
Switching to a new goroutine is just a matter of a few instructions. 


> How is it possible Go supports 100k+ concurrent goroutines on a single CPU, 
> doing context switching between all of them without large overhead? 

If all 100k goroutines are actively doing things in parallel, the Go 
code will tend to have significant overhead.  In a normal Go program, 
though, each goroutine will be waiting for network input. 


> We all know it is a bad idea for a (C, Java) program to create thousands of 
> OS threads (e.g. one thread per request). This is mainly because: 
> 
> 1. Each thread uses fixed amount of memory for its stack 
>     (while in Go, the stack grows and shrinks as needed) 
> 
> 2. With many threads, the overhead of context switching becomes significant 
>     (isn't this true in Go as well?) 

The context of a goroutine is much smaller and easier to change than 
the context of an OS thread.  If nothing else an OS thread has a 
signal mask.  At least with the gc toolchain, a goroutine really just 
has two values: a stack pointer and a pointer to the g structure. 
Switching to a new goroutine is just a matter of a few instructions. 

Also goroutines do cooperative scheduling and threads do not. 


> Even if Go context-switched only on IO and channel access, doesn't it still 
> have to save and restore the state of variables for each goroutine? How is 
> it possible it is so efficient compared to an OS scheduler? 

It does not have to save and restore each variable.  All goroutines 
see the same global variables, and local variables are always accessed 
via the stack pointer. 

[Context switch](https://groups.google.com/forum/#!msg/golang-nuts/j51G7ieoKh4/wxNaKkFEfvcJ)

#### 3.2 上下文切换

https://groups.google.com/forum/#!topic/golang-nuts/0Szdmmy22pk

[Performance without the event loop](http://dave.cheney.net/2015/08/08/performance-without-the-event-loop)

##### 3.2.1 进程

##### 3.2.2 线程

##### 3.2.3 goroutine

Goroutines

They're called goroutines because the existing terms—threads, coroutines, processes, and so on—convey inaccurate connotations. A goroutine has a simple model: it is a function executing concurrently with other goroutines in the same address space. It is lightweight, costing little more than the allocation of stack space. And the stacks start small, so they are cheap, and grow by allocating (and freeing) heap storage as required.

Goroutines are multiplexed onto multiple OS threads so if one should block, such as while waiting for I/O, others continue to run. Their design hides many of the complexities of thread creation and management.


### 4. 其他

### 参考

- [Concurrency, Goroutines and GOMAXPROCS](https://www.goinggo.net/2014/01/concurrency-goroutines-and-gomaxprocs.html)
- [How do goroutine work or goroutines and os threads relation](http://stackoverflow.com/questions/24599645/how-do-goroutines-work-or-goroutines-and-os-threads-relation)
- [Why 1000 goroutine generate 1000 os threads?](https://groups.google.com/forum/#!topic/golang-nuts/2IdA34yR8gQ)
- [Goroutine vs OS threads](https://groups.google.com/forum/#!msg/golang-nuts/j51G7ieoKh4/wxNaKkFEfvcJ)
- [goroutines](https://golang.org/doc/effective_go.html#goroutines)
- [Why is a goroutines stack infinite ?](http://dave.cheney.net/2013/06/02/why-is-a-goroutines-stack-infinite)
- [How stacks are handled in go ?](https://blog.cloudflare.com/how-stacks-are-handled-in-go/)