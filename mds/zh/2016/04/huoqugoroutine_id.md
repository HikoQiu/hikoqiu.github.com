---

layout: post  
title: [Go细节] 获取goroutine id  
subtitle:   
author: Hiko  
category: tech
tags: Golang, goroutine, id  
ctime: 2016-04-13 21:37:06  
lang: zh  

---

### 概览

1. Why goroutine id?
2. 方式一: 修改源码 (Go1.4 / Go1.6)
3. 方式二: 通过Stack解析
4. 方式三: 生成唯一id

- - -

### 1. Why goroutine id?

在准备[`《浅析goroutine和线程、进程的区别》`](/posts/zh/2016/03/lets_go_qianxigoroutinehexiancheng_jinchengdequbie_tech.html)这篇文的过程中，想用 goroutine 跟 Java 的 Thread 进行简单对比——打印goroutine id / 线程id。

潜意识认为，goroutine 是被 runtime 管理的，所以使用 runtime 包应该是能获取到 goroutine 的 id。试了几个潜意识中的 runtime.Get*Id() 的函数都未果，转为查找资料。

**为什么获取 goroutine id 还那么麻烦？**

在 google 的讨论组 [`golang-nuts`](https://groups.google.com/forum/#!forum/golang-nuts) 中， *minux* 给出的解释，如下：

> Please don't use goroutine local storage. It's highly discouraged. In fact, IIRC, we used to expose Goid, but it is hidden since we don't want people to do this.
>
> Potential problems include:  
> 1. when goroutine goes away, its goroutine local storage won't be GCed. (you can get goid for the current goroutine, but you can't get a list of all running goroutines)  
> 
> 2\. what if handler spawns goroutine itself?   
> the new goroutine suddenly loses access to your goroutine local storage. You can guarantee that your own code won't spawn other goroutines, but in general you can't make sure the standard library or any 3rd party code won't do that.

> thread local storage is invented to help reuse bad/legacy code that assumes global state, Go doesn't have legacy code like that, and you really should design your code so that state is passed explicitly and not as global (e.g. resort to goroutine local storage)

> Tip: [Ref from minux's reply.](https://groups.google.com/forum/#!msg/golang-nuts/Nt0hVV_nqHE/GABH0-ctYqAJ)


### 2. 方式一: 修改源码

#### 2.1 为什么 Go1.4 和 1.5以上版本 的获取方式不一样？

Golang在版本1.4之前还是使用 C 来实现，到Go1.5才实现自举（Go 编译自己）。


#### 2.2 Go1.4 实现方式
在Go1.4的 [`runtime.h`](https://github.com/golang/go/blob/release-branch.go1.4/src/runtime/runtime.h#L298) 中定义 `G` (goroutine的描述) 如下：

	struct	G
    {
        ...
        
        void*	param;		// passed parameter on wakeup
        uint32	atomicstatus;
        int64	goid;
        int64	waitsince;	// approx time when the G become blocked
        String	waitreason;	// if status==Gwaiting
        
        ...
    };

为了方便看到重点，上面的结构体省略去其他大部分字段。从 `G` 中看到 Go 为每个 goroutine 分配一个唯一 id ( goid ) 。只是没有暴露接口而已。

这样一来，可以直接修改源码，把 `goid` 暴露出来，比如在 `runtime.c` 文件中添加一个获取 goid 的函数： `GetGoId()`。

#### 2.2.1 Go1.4 修改方式

i. $GOPATH/src/pkg/runtime/runtime.c，在底部添加一个方法。

	void
	runtime·GetGoId(int32 ret)
	{
	    ret = g->goid;
        USED(&ret);
	}
	
ii. $GOPATH/src/pkg/runtime/extern.go 导出函数。

	func GetGoId() int

iii. 重新编译源代码，可参考：[`《源码安装Go1.6》`](/posts/zh/2016/04/yuanmaanzhuangGo1_6_tech.html)。
 

#### 2.2.2 Go1.6 修改方式

在 Go1.6 源码 [`runtime2.go`](https://github.com/golang/go/blob/master/src/runtime/runtime2.go#L331) 中仍然有 goid，只是没有暴露出来。

现在给源码添加一个 `GetGoid()` 的函数。

i. 在 [`extern.go`](https://github.com/golang/go/blob/master/src/runtime/extern.go) 中给 `runtime`包添加一个新的函数。
 
 	// 手工添加
	func GetGoid() int64 {
		return getg().goid
	}

ii. 在 [`api/go1.txt`](https://github.com/golang/go/blob/master/api/go1.txt) （ api 是跟源码目录 src 同级）中添加 `GetGoid()` 函数的*声明*。

	pkg runtime, func GetGoid() int64

如果不加，在编译源码过程中最后执行测试的时候会报错。

iii. 使用方式

因为直接给源码加了一个函数，所以用起来会很简单，直接在需要用到的地方调用：

 	runtime.GetGoid()


> 备注：不推荐以修改源码的方式去获取 goroutine 的id，这不利于后期维护。 

### 3. 方式二: 通过Stack解析

直接将获取 goroutine id 的代码封装成一个函数，方便使用，代码如下：

	func GoID() int {
		var buf [64]byte
		n := runtime.Stack(buf[:], false)
		idField := strings.Fields(strings.TrimPrefix(string(buf[:n]), "goroutine "))[0]
		id, err := strconv.Atoi(idField)
		if err != nil {
			panic(fmt.Sprintf("cannot get goroutine id: %v", err))
		}
		return id
	}
	
`runtime.Stack()` 会把当前调用的 goroutine 的栈追踪信息(Stack trace)保存写到所传的参数 `buf` 中。上面的方式是直接处理字符解析，获取到 goroutine 的id。

> 备注：上面代码在Go1.5 和 Go1.6 测试。

### 4. 方式三: 生成唯一id

上面提到的几种方式，总得来说能解决问题，但是不够优雅，用了一些 hack 的手段。如果只是想唯一标记一个 goroutine，其实也可以自己给 goroutine 生成唯一id。

Go 提供了并发安全的 `sync` 库 (了解更多可以查看[`《Go并发安全之sync库》`](/posts/zh/2016/04/Gobingfaanquanzhisyncku_tech.html))，其中有提供原子操作 `atomic`。

**解决方式：**

i. 定义*全局*变量 GoroutineId。
	
	var GoroutineId int64
	 
ii. 在每个需要需要唯一标记的 goroutine 中使用原子操作进行加1，分配唯一的id。

	atomic.AddInt64(&GoroutineId, 1)
	
以此方式来标记唯一的 goroutine 也能达到效果。


### 参考

- [获取 goroutine 的 id](http://wendal.net/2013/0205.html)
- [如何得到 goroutine 的 id?](http://colobu.com/2016/04/01/how-to-get-goroutine-id/)