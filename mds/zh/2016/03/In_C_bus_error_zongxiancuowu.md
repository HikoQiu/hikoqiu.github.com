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

... 待补充 ...
