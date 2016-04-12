---

layout: post  
title: [Let's Go] 浅析goroutine和线程、进程的区别
subtitle:   
author: Hiko  
category: tech  
tags: go,goroutine,thread,process  
ctime: 2016-03-13 13:09:58  
lang: zh  

---

#### 概览

- 1\. 一个死循环的例子
	- 1.1 goroutine for死循环
	- 1.2 Java Thread for死循环
	- 1.3 C fork子进程 for死循环
- 2\. 调度方式
	- 2.1 抢占式
	- 2.2 协作式 
- 3\. 进程/线程开销
	- 进程栈的实现
	- 线程栈的实现 
- 4\. goroutine开销
	- 4.1 goroutine栈的实现
- 5\. 其他

#### 1. 一个死循环的例子

#### 1.1 goroutine for死循环

#### 1.2 Java Thread for死循环

#### 1.3 C fork子进程 for死循环



#### 参考

- [Why is a goroutines stack infinite ?](http://dave.cheney.net/2013/06/02/why-is-a-goroutines-stack-infinite)
- [How stacks are handled in go ?](https://blog.cloudflare.com/how-stacks-are-handled-in-go/)