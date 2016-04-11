---

layout: post  
title: 源码安装Go1 6  
subtitle:   
author: Hiko  
category: tech
tags: Golang源码安装, Go1.6, Go1.4  
ctime: 2016-04-11 13:02:55  
lang: zh  

---

#### 步骤概览

1. 下载Go1.4源码并编译
2. 下载Go1.6源码并通过Go1.4编译安装Go1.6
3. IntelliJ IDEA编辑器配置（可选）

#### 1 下载并编译Go1.4.*源码

 - 为什么需要安装Go1.4？

> Golang在版本1.5之后(包括1.5)实现了自举(bootstrapping)，指的是：用要编译的目标编程语言编写其编译器（或汇编器）。  
参考：[Go 1.5 Bootstrap Plan](https://docs.google.com/document/d/1OaatvGhEAq7VseQ9kkavxKNAfepWy2yhPUBs96FGV28/edit)

##### 1.1 下载

先从Go的官网下载1.4版本的源代码，地址见: [源码列表](https://golang.org/dl/)。  

Go官网需要需要梯子才能访问，如果你没有梯子，那么也可以直接从[Github/Golang](https://github.com/golang/go)仓库下载对应版本的Go源码（只需选择对应的分支进行下载就行，比如：`release-brach.go.1.4`分支）。

为了日后更方便管理源码和已经安装的软件，建议在/usr/local下见建一个go目录（路径: `/usr/local/go`），然后把刚下载的Go1.4源码解压到`/usr/local/go/go1.4`目录。


##### 1.2 编译

进入`/usr/local/go/go1.4/src`执行`./all.bash`。
一切顺利的话，将会在`/usr/local/go/go1.4/bin`看到两个二进制文件 `go` 和 `gofmt`。

#### 2 下载Go1.6源码并通过Go1.4编译安装Go1.6

##### 2.1 下载

同样从[源码列表](https://golang.org/dl/)或者[Github/Golang](https://github.com/golang/go)仓库下载下载1.6版本的源代码。

接着，解压到`/usr/local/go`目录（刚刚Go1.4是解压到/usr/local/go/1.4，其实这也不是强求的，只是个人的文件管理习惯而已）。

进入`/usr/local/go/src/`目录，修改`all.bash`文件，在顶部修改`GOROOT_BOOTSTRAP`环境变量，应该默认该变量的的值是: ~/go1.4，但是这里我是把Go1.4放到/usr/local/go/go1.4。 

> export GOROOT_BOOTSTRAP=/usr/local/go/go1.4

##### 2.2 安装

进入`/usr/local/go/src`执行`./all.bash`。
一切顺利的话，将会在`/usr/local/go/bin`看到两个二进制文件 `go` 和 `gofmt`。

最后，在自己的~/.profile加上，方便使用go命令:  

> export PATH=$PATH:/usr/local/go/bin

执行以下`. ~/.profile`, 然后运行`go version`将会看到：

> go version go1.6 darwin/amd64

#### 3 配置IntelliJ IDEA编辑器（可选）

个人使用Intellij IDEA进行Go开发，下载一个Go插件，接着配置Go的SDK，代码提示、包的自动导入都很方便。

##### 3.1 下载IntelliJ IDEA

从编辑器的官网下载：[IntelliJ IDEA](https://www.jetbrains.com/idea/#chooseYourEdition)，它有提供社区版，也够用了。

安装完编辑器后装上[Go插件](https://github.com/go-lang-plugin-org)，可以直接从编辑器中查找并安装，装完插件后重启编辑器。

##### 3.2 配置Go SDK

新建Go项目，配置SDK路径，SDK路径：`/usr/local/go`。
如果新建Go项目时没配SDK，也可以在打开项目之后，在`File -> Project Structure...`中配置。

至此，就可以开始开发Go项目。


#### 参考

1. [Installing Go from source](https://golang.org/doc/install/source)

2. [Install Go1.6 from Source on CentOS 7 in China](https://github.com/northbright/Notes/blob/master/Golang/china/install-go1.6-from-source-on-centos7-in-china.md)