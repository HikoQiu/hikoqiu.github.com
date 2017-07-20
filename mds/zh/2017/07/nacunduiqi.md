---

layout: post  
title: 内存对齐  
subtitle:   
author: Hiko  
category: tech
tags: 内存对齐  
ctime: 2017-07-20 11:09:01  
lang: zh  

---

## 前言

在缺省情况下，编译器为每一个变量或是数据单元按其自然对齐条件分配空间。

在结构中，编译器为结构的每个成员按其自然对界（alignment）条件分配空间。各个成员按照它们被声明的顺序在内存中顺序存储（成员之间可能有插入的空字节），第一个成员的地址和整个结构的地址相同。

## 0x00 影响因素

关系到内存对齐的两个重要因素：

1. 数据成员对齐和结构整体对齐
2. \#pragma pack(n) 设置对齐条件 // n 表示整数，n = 1, 2, 4, 8, ...

## 0x01 因素解释

### i. 数据成员对齐和结构整体对齐

- 编译器缺省的`结构成员`自然对齐条件为“N字节对齐”，N即该成员数据类型的长度。

````
    如：int型成员的自然对界条件为4字节对齐，而double类型的结构成员的自然对界条件为8字节对齐。
    若该成员的起始偏移不位于该成员的“默认自然对界条件”上，则在前一个节面后面添加适当个数的空字节。
````
- 编译器缺省的`结构整体`的自然对齐条件为：该结构所有成员中要求的**最大**自然对齐条件。
````
	若结构体各成员长度之和不为“结构整体自然对齐条件的整数倍，则在最后一个成员后填充空字节。
````

### ii. \#pragma pack(n)

改变默认的对齐条件。

````
- 使用伪指令#pragma pack (n)，编译器将按照 n 个字节对齐。
- 使用伪指令#pragma pack ()，取消自定义字节对齐方式。

````
备注：

- 当 #pragma pack(n) 的 n 值等于或超过所有数据成员长度的时候，这个 n 值的大小将不产生任何效果。
- 对齐的原则是 min(sizeof(TYPE), n), TYPE 表示类型， 比如： int、long 等。

## 0x02 情况一： 默认的对齐条件（不设置 pack（n)）


````
	#include <iostream>
	
	// 不设置统一的对齐边界
	// #pragma pack(4)
	
	using namespace std;
	
	typedef struct {
	    char a;
	    char a1;
	    long b;  // 8 bytes
	    char c;
	} S1; // 24 bytes = 1(a) + 1(a1) + [6个对齐字节] + 8(b) + 1(c) + [7个对齐字节]
	
	typedef struct {
	    char a;
	    S1 s1;
	    char b;
	    char c;
	} S2; // 40 bytes = 1(a) + [7个对齐字节] + 24(s1) + 1（b） + 1（c) + [6个对齐字节] 
	
	typedef struct {
	    int e;  // 4 bytes
	    char f;
	    short int a;  // 2 bytes
	    char b;
	} S3; // 12 bytes = 4(e) + 1(f) + [1个对齐字节] + 2(a) + 1(b) + [3个对齐字节]
	
	typedef struct {
	    int e;  // 4 bytes
	    char f;
	    int a;  // 4 bytes
	    char b;
	} S4; // 16 bytes = 4(e) + 1(f) + [3个对齐字节] + 4(a) + 1(b) + [3个对齐字节]
	
	int main(int argc, char* argv[]) {
	
	    cout << "sizeof int: " << sizeof(int) << endl;
	
	    cout << "size of S1: " << sizeof(S1) << endl;
	
	    cout << "size of S2: " << sizeof(S2) << endl;
	
	    cout << "sizeof S3: " << sizeof(S3) << endl;
	
	    cout << "sizeof S4: " << sizeof(S4) << endl;
	
	    return 0;
	}

````

````
结果如下： 
sizeof int: 4
size of S1: 24
size of S2: 40
sizeof S3: 12
sizeof S4: 16

````


## 0x03 情况而： 设置对齐条件 pack（n)


````
	#include <iostream>
	
	// 设置统一的对齐条件为 4 
	#pragma pack(4)
	
	using namespace std;
	
	typedef struct {
	    char a;
	    char a1;
	    long b;  // 8 bytes
	    char c;
	} S1; // 16 bytes = 1(a) + 1(a1) + [2个对齐字节] + 8(b) + 1(c) + [3个对齐字节]
	
	typedef struct {
	    char a;
	    S1 s1;
	    char b;
	    char c;
	} S2; // 24 bytes = 1(a) + [3个对齐字节] + 16(s1) + 1（b） + 1（c) + [2个对齐字节] 
	
	typedef struct {
	    int e;  // 4 bytes
	    char f;
	    short int a;  // 2 bytes
	    char b;
	} S3; // 12 bytes = 4(e) + 1(f) + [1个对齐字节] + 2(a) + 1(b) + [3个对齐字节]
	
	typedef struct {
	    int e;  // 4 bytes
	    char f;
	    int a;  // 4 bytes
	    char b;
	} S4; // 16 bytes = 4(e) + 1(f) + [3个对齐字节] + 4(a) + 1(b) + [3个对齐字节]
	
	int main(int argc, char* argv[]) {
	
	    cout << "sizeof int: " << sizeof(int) << endl;
	
	    cout << "size of S1: " << sizeof(S1) << endl;
	
	    cout << "size of S2: " << sizeof(S2) << endl;
	
	    cout << "sizeof S3: " << sizeof(S3) << endl;
	
	    cout << "sizeof S4: " << sizeof(S4) << endl;
	
	    return 0;
	}

````

````
结果如下： 
sizeof int: 4
size of S1: 16
size of S2: 24
sizeof S3: 12
sizeof S4: 16
````

**从 S3 的对齐结果大小看： 当 pack(n) 的 n 值大于类型的大小时， 对齐大小参考类型的大小，也就是： 对齐条件 = min(sizeof(TYPE), n).**
