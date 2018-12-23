---

layout: post  
title: [Go细节] Go的runtime调度器实现(准备中)  
subtitle:   
author: Hiko  
category: tech
tags: Golang, runtime, goroutine, 调度器  
ctime: 2016-04-12 22:45:52  
lang: zh  

---

内容准备中.

`runtime/runtime.go`
`runtime/stack.go`
`runtime/proc.go`

#### 参考

- [-1] [Analysis of the Go runtime scheduler](http://www1.cs.columbia.edu/~aho/cs6998/reports/12-12-11_DeshpandeSponslerWeiss_GO.pdf)
- [0] [Scalable Go Scheduler Design Doc](https://docs.google.com/document/d/1TTj4T2JO42uD5ID9e89oa0sLKhJYD0Y_kqxDv3I3XMw/edit)
- [1] [The Go Scheduler](http://morsmachine.dk/go-scheduler)  
- [2] [golang的goroutine是如何实现的 ?](https://www.zhihu.com/question/20862617)
- [3] [What exactly does runtime gosched do ?](http://stackoverflow.com/questions/13107958/what-exactly-does-runtime-gosched-do)
- [4] [How goroutine work ?](http://blog.nindalf.com/how-goroutines-work/)
- [5] [Goroutine浅析](http://www.cnblogs.com/yjf512/archive/2012/07/19/2599304.html) 