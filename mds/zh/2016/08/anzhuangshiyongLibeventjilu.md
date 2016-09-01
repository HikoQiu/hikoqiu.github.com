---

layout: post  
title: [C] 安装使用 Libevent 记录  
subtitle:   
author: Hiko  
category: tech
tags: Libevent, 开发， 多路复用I/O 
ctime: 2016-08-31 11:06:48  
lang: zh  

---

> Libevent 官网：[http://libevent.org](http://libevent.org)

*注意： 前提条件需要已经安装: gcc/make等编译需要的软件。*


### 0. 下载源码


源码包下载: [libevent-2.0.22-stable.tar.gz](https://github.com/libevent/libevent/releases/download/release-2.0.22-stable/libevent-2.0.22-stable.tar.gz)

### 1. 编译安装

- 解压: tar zxvf libevent-2.0.22-stable.tar.gz
- 进入 libevent 源码目录, ./configure --prefix=/usr/local/libevent
- make
- sudo make install


△ 可能遇到错误：`fatal error: 'openssl/bio.h' file not found`

#### △ 解决方式：
- 查找 `bio.h` 所在位置: `find /  -name "bio.h"`
- 将 openssl 的的头文件目录软链到 libevent 源码的 include目录，例如：
> ln -s /usr/local/Cellar/openssl/1.0.2g/include/openssl  /usr/local/include/openssl


安装完毕，可以进入 `/usr/local/libevent` 目录查看相关头文件。


### 2. 测试Demo



