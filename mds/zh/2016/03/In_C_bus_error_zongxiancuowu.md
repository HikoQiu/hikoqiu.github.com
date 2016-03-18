---

layout: post  
title: In C bus error/总线错误  
subtitle:   
author: Hiko  
category: tech  
tags: c,bus err,总线错误  
ctime: 2016-03-18 13:27:30  
lang: zh  

---

##### 触发`bus error`的代码片段  


````
	char s1[10] = "string";
	char s2[10];
	//	s2 = "string"; // cant't assign to char array
	s2[0] = 'b';

	char *s3 = "string";
	char *s4;
	s4 = "string"; // s4 points to a string literal, It's read only.

	s3[0] = 'S'; // Occur: bus err
	printf("1: %s, 2: %s, 3: %s, 4: %s \n", s1, s2, s3, s4);

````

#### 原因

Quote from stackoverflow:

````
Bus errors are rare nowadays on x86 and occur when your processor cannot even attempt the memory access requested, typically:

 - using a processor instruction with an address that does not satisfy its alignment requirements.
Segmentation faults occur when accessing memory which does not belong to your process, they are very common and are typically the result of:

 - using a pointer to something that was deallocated.
 - using an uninitialized hence bogus pointer.
 - using a null pointer.
 - overflowing a buffer.
PS: To be more precise this is not manipulating the pointer itself that will cause issues, it's accessing the memory it points to (dereferencing).

[What is a bus error?](http://stackoverflow.com/a/212585/2398826)
````

