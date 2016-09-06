---

layout: post  
title: [Golang] Golang pb (Protobuf) 使用实例  
subtitle:   
author: Hiko  
category: tech
tags: Golang, pb, Protobuf, 例子  
ctime: 2016-09-06 14:19:26  
lang: zh  

---

#### 0.0 背景

> 把 HiMagpie 推送服务的各模块数据交互从 json 替换成 pb.


#### 1.0 安装 Protobuf 编译器 protoc

- 到 [https://github.com/google/protobuf/releases](https://github.com/google/protobuf/releases) 下载 protoc 源码
- 解压并进入源码目录
- ./configure --prefix=/usr/local/pb
- make
- sudo make install

> 把 /usr/local/pb/bin 加到 $PATH 环境变量  
>  - sudo vi /etc/profile  
>  - 添加 `export PATH=$PATH:/usr/local/pb/bin`
>  - 保存，并执行: source /etc/profile

#### 2.0 获取 Protobuf 编译器插件 protoc-gen-go，以支持生成 Golang 的 pb 源文件

-  新建一个 pb 的 Golang 项目  
- 进入 pb 项目， export GOPATH=`pwd`  
- 获取 `protoc-gen-go`: go get -u github.com/golang/protobuf/protoc-gen-go

> 在 pb 的 `bin` 目录中会生成 `protoc-gen-go` 二进制文件

- 把 `pb/src/bin` 的绝对路径添加到环境变量 `PATH`, 比如: 修改 `/etc/profile` 文件，添加 `export PATH=$PATH:/.../pb/src/bin`, 然后 source /etc/profile 使变量生效

- 获取 Golang 的 goprotobuf 提供的库，用于 Golang 项目实际开发中的编码 (marshal) 和 解码 (unmarshal) 等
- go get -u github.com/golang/protobuf


#### 3.0 测试


##### 3.1 新建 *.proto 文件
在刚刚新建的 pb 项目下，新建目录: src/app/protos, 然后在 `protos` 包目录下新建 `example.proto` 文件。

`example.proto` 内容:

	package protos;

	enum FOO {
		X = 17;
	};

	message Example {
		required string label = 1;
		optional int32 type = 2 [default=77];
		repeated int64 reps = 3;
		optional group OptionalGroup = 4 {
			required string RequiredField = 5;
		}
	}

##### 3.2 使用 protoc 解析 *.proto 文件，生成对应的 *.go 文件

进入 `src/app/protos` 目录, 编译 *.proto 

> protoc --go_out=. example.proto  
> 备注：也可以使用 `protoc --go_out=. *.proto` 编译所有 *.proto 文件

编译成功，会在当前目录看到 example.pb.go 的 Golang 文件，可用于开发使用。


#### 4.1 测试实例

在 `src/app` 目录下新建 `main.go` 文件， 内容:

	package main

	import (
		"app/protos"
		"github.com/golang/protobuf/proto"
		"log"
		"os"
		"fmt"
	)

	func main() {
		exam := &protos.Example{
			Label: proto.String("Hello, ProtoBuffer."),
			Type: proto.Int32(17),
			Optionalgroup:&protos.Example_OptionalGroup{
				RequiredField:proto.String("Opitonalgroup"),
			},
		}

		data, err := proto.Marshal(exam)
		if err != nil {
			log.Fatal("Marshaling pb err: ", err)
			os.Exit(-1)
		}

		newExam := &protos.Example{}
		err = proto.Unmarshal(data, newExam)
		if err != nil {
			log.Fatal("Unmarshling err: ", err)
			os.Exit(-1)
		}

		if exam.GetLabel() != newExam.GetLabel() {
			log.Fatalf("data mismatch %q != %q", exam.GetLabel(), newExam.GetLabel())
			os.Exit(-1)
		}

		fmt.Println("OK! ", newExam.GetLabel())
	}

- 编译: go install app
- 执行生成的 app 二进制文件，如: ./bin/app

输出结果:

> OK!  Hello, ProtoBuffer.