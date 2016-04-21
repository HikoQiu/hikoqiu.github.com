---

layout: post  
title: [Go细节] Golang map的实现  
subtitle:   
author: Hiko  
category: tech
tags: Golang, maps实现  
ctime: 2016-04-12 22:39:48  
lang: zh  

---

### 概览

- 1\. map的数据结构
- 2\. 冲突解决
- 3\. 容量不足解决
- 4\. 并发安全问题
- 5\. 其他


### 参考

- [Macro View of Map Internals In Go](https://www.goinggo.net/2013/12/macro-view-of-map-internals-in-go.html)