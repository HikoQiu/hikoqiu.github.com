---

layout: post  
title: [Let's Go] 关于Slice的一些事
subtitle:   
author: Hiko  
category: tech  
tags: go,slice,array,value copy  
ctime: 2016-03-14 20:51:11  
lang: zh  

---

#### 概览

- 1\. Slice数据结构
- 2\. 使用Slice须知
	- 2.1 值传递下的Slice
	- 2.2 Slice截取和扩充
- 3\. Slice操作常用函数
- 4\. 其他
	- 4.1 关于string
	- 4.2 关于Slice nil

- - -

> 前言 默认你是对数组有一定的认识。

#### 1. Slice数据结构


首先，直接从源码`$YOUR_GO_DIR/src/runtime/slice.go`（其中`$YOUR_GO_DIR`指你自己go源代码的根目录）中找到定义的`slice`结构，如下：


	type slice struct {
	    array unsafe.Pointer // 任意类型指针(类似C语言中的 void* )， 指向真实存储slice数据的数组
	    len   int // length, 长度
	    cap   int // capacity, 容量
    }


从结构很好看出，通过make()函数（比如：make([]int, 10, 20)）创建出来的slice，其实就是由两部分组成：`slice的"描述"(上面的结构体)` + `存储数据的数组(指针指向的数组)`。

> 提示：后文将使用SliceHeader代替上面的结构体，SliceHeader是[Rob Pike](https://en.wikipedia.org/wiki/Rob_Pike)在[Golang/slices](https://blog.golang.org/slices)这篇博文中的暂用来指代的名词，这里我也借用一下。为了方便理解，把slice拆成 SliceHeader + 存储数据的数组 两部分。

#### 2. 使用Slice须知

#### 2.1 值传递下的Slice

我们知道Go的参数是值传递，那么这里有个问题需要考虑： **当把一个slice变量通过参数传递给某函数时，传的是SliceHeader、还是整个SliceHeader+数据（存储数据的数组）都被复制过去？**

比如，这样的代码：  

    s := make([]string, 10)  
    saveSlice(s)


当我们在项目中某个slice有10万元素，如果传参数直接复制`SliceHeader + 数据`，那么这是一定不能接受的。

Rob Pike有这样一句话定义Slice: `slice不是数组，它是对数组进行了描述(A slice is not an array. A slice describes a piece of an array)`。 实际上，在上面的代码片段中saveSlice(s)接收到的是变量`s`的一个副本（就是一个值跟`s`一样，但是是全新的变量），这个副本跟变量`s`一样，有一个指向同个数组的指针、len和cap相同值。

为什么？因为Go是值传递，简单试验就知道。

**实验 1**  

假设：如果slice变量参数传递，是复制了`数据`，那么在函数中操作"被复制过来的"数据，**不会**对原数据造成影响。


	// 代码片段
	data := make([]int, 10)
	fmt.Println("处理前数据: ", data)
	changeIndex0(data)
	fmt.Println("处理后数据: ", data)
	
函数 changeIndex0(data []int)

	// 替换第一个元素的值
	func changeIndex0(data []int)  {
		data[0] = 99
	}


实验结果

	处理前数据:  [0 0 0 0 0 0 0 0 0 0]
	处理后数据:  [99 0 0 0 0 0 0 0 0 0]

显然，从结果中看得出，原始数据被修改了，所以可以得出结论是：传递slice变量时，并不是复制真正存储数据的数组进行传递。

**实验 2**

如何证明传的是SliceHeader，并且函数所接受到的变量是一个副本？

	// 代码片段
	data := make([]int, 10)
	fmt.Println("处理前数据: ", data, len(data), cap(data))
	subSlice(data)
	fmt.Println("处理后数据: ", data, len(data), cap(data))

函数 subSlic(data []int)

	// 截取slice
	func subSlice(data []int)  {
		data[0] = 99
		data = data[0:8]
		fmt.Println("函数中数据: ", data, len(data), cap(data))
	}
	
实验结果

	处理前数据:  [0 0 0 0 0 0 0 0 0 0] 10 10
	函数中数据:  [99 0 0 0 0 0 0 0] 8 10
	处理后数据:  [99 0 0 0 0 0 0 0 0 0] 10 10

结果中看到函数中和处理后的slice的长度（len）不一样，显然不是同一个SliceHeader。其实也有个简单的办法，直接取两变量的地址，一看就知道。（这个实验根本就可以不用，因为Go参数传递本身就是值传递，自然会复制，不像Java的引用传递）。


所以，在实际项目中，直接传递slice变量与传递slice变量的指针，对内存的消耗区别并不是很大。一个SliceHeader的大小是24字节，而指针大小8字节。

> 备注： SliceHeader 24字节计算方式：8字节(指针) + 8字节(整型int, len) + 8字节(整型int, cap)，这是以我自己电脑为例， 我电脑是64位，指针大小8字节；整型int大小也跟编译器有关，但Golang中最少是32bit，我在本机使用unsafe.SizeOf()实测是8字节。

#### 2.2 Slice截取和扩充

说到底，slice由SliceHeader和数组构成。涉及到数组，避不开的问题就是`定长`，也就是一旦数组长度确定了就无法改变。如果非要改变长度，那只能一个办法：重新分配一个新的数组。

对于slice也一样，如果一个slice已经确定了容量(capacity)，那么如果要扩充该slice的容量，也必须重新分配一个存数据的数组。
> 备注：slice的容量在使用make([]byte, 10, 20)时，第三个参数已经确定；第三个参数就是容量(capacity)，如果不指定，默认跟第二个参数（长度len）一样。

##### 截取子Slice

当基于原Slice进行截取子Slice时，实际上操作的还是原Slice的元素。也就是对子Slice的元素进行修改，都会在原Slice中体现，从上面`实验2`中就可以看出。

也就是，在Slice的容量(capacity)范围内子Slice截取，都是直接使用了原Slice的数组，并没有为该子Slice分配新的数组。

如果我需要截取一个子Slice并且希望该子Slice有新的数组，该怎么操作？这是可以使用copy()函数。

	sub := make([]int, 2)
	copy(sub, data[3:5])

##### 扩充Slice

事实上，扩充Slice的操作就是：重新创建一个更大容量的Slice，然后把原Slice中的数据复制到新的Slice里面。

比如：常用操作`append()`

	fmt.Printf("append()前: len: %d, cap: %d \n", len(data), cap(data))
	data = append(data, 5)
	fmt.Printf("append()后: len: %d, cap: %d \n", len(data), cap(data))

结果

	append()前: len: 10, cap: 10 
	append()后: len: 11, cap: 20 

append()中的操作就是新建了一个容量为原来两倍的Slice，然后把原来的数据复制到新Slice并且把新的元素加上。


#### 3. Slice操作常用函数

3.1 Go提供了方便操作的语法糖，如： data[2:5]，以此来获取第二到第四（包括第四）个元素。

> 备注： ':' 左右都可以不指定值。右边的值不可以超过该Slice的容量大小，否则会Panic。

3.1 `copy()` 复制Slice的值到另外一个Slice，上面例子也用到了，这函数会自动参考len更小的那个Slice，不会发生爆出`slice bounds out of range`的异常。

3.2 `append()`给某个Slice添加元素，也是常用的，上面的例子也有体现。


#### 4. 其他


#### 4.1 关于string

从源码包`runtime`中`string.go`中可以看到字符串的struct。

	type stringStruct struct {
		str unsafe.Pointer
		len int
	}
	
也就是，string实际上就是**只读**的byte切片(Slice)，只是从Golang语言层面提供的语法支持而已。因为只能读，所以容量的存在与否都无济于事。

#### 4.2 关于Slice nil

我们知道`make()`方法专门用来新建Slice、map、chan，但是我们也可以用`new()`来建Slice，但是两者有区别。

	// 代码片段
	nilSlice := new([]int)
	fmt.Printf("nilSlice is nil: %v \n", *nilSlice == nil)
	emptySlice := make([]int, 0)
	fmt.Printf("emptySlice is nil: %v \n", emptySlice == nil)
	
结果打印
	
	nilSlice is nil: true 
	emptySlice is nil: false 

也就是用`new()`创建后的Slice变量是零值，而make()创建一个0长度的Slice并不是nil。

为什么？因为`new()` 和 `make()`做的事情不一样。

**`new()`做了两件事**

- 1. 为该类型分配内存
- 2. 置零值（不同类型的零值不一样，比如: bool是false，整型是0...等）

**`make()`也做了两件事**

- 1. 为该类型分配内存
- 2. 初始化

以Slice为例，`new([]int)`得到的SliceHeader是：

	sliceHeader {
    	array: nil,
    	len: 0,
    	cap: 0,
	}

而`make([]int, 0)`得到的SliceHeader应该是：

	sliceHeader {
    	array: 0x8201d0140, // 指向0个元素的数组
    	len: 0,
    	cap: 0,
	}

- - -

#### 结语

从Slice的实现、使用场景进行更加全面的了解，会对在项目中的使用有更大的帮助以及尽量避免因为不知道细节而错用。



参考：

1. [Golang/slices](https://blog.golang.org/slices)