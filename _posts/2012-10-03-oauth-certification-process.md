---
layout: post
title: OAUTH认证授权流程
categories:
- 思想举重
tags: []
status: publish
type: post
published: true
meta:
  _edit_last: '1'
---
OAUTH认证授权流程

在弄清楚了OAUTH的术语后，我们可以对OAUTH认证授权的流程进行初步认识。其实，简单的来说，OAUTH认证授权就三个步骤，三句话可以概括：

1. 获取未授权的Request Token

2. 获取用户授权的Request Token

3. 用授权的Request Token换取Access Token

当应用拿到Access Token后，就可以有权访问用户授权的资源了。大家肯能看出来了，这三个步骤不就是对应OAUTH的三个URL服务地址嘛。一点没错，上面的三个步骤中，每个步骤分别请求一个URL，并且收到相关信息，并且拿到上步的相关信息去请求接下来的URL直到拿到Access Token。具体的步骤如下图所示：

<img src="http://p.blog.csdn.net/images/p_blog_csdn_net/hereweare2009/EntryImages/20090308/1-2.jpg" alt="" width="740" height="576" /> 

具体每步执行信息如下：

A. 使用者（第三方软件）向OAUTH服务提供商请求未授权的Request Token。向Request Token URL发起请求，请求需要带上的参数见上图。

B. OAUTH服务提供商同意使用者的请求，并向其颁发未经用户授权的oauth_token与对应的oauth_token_secret，并返回给使用者。

C. 使用者向OAUTH服务提供商请求用户授权的Request Token。向User Authorization URL发起请求，请求带上上步拿到的未授权的token与其密钥。

D. OAUTH服务提供商将引导用户授权。该过程可能会提示用户，你想将哪些受保护的资源授权给该应用。此步可能会返回授权的Request Token也可能不返回。如Yahoo OAUTH就不会返回任何信息给使用者。

E. Request Token 授权后，使用者将向Access Token URL发起请求，将上步授权的Request Token换取成Access Token。请求的参数见上图，这个比第一步A多了一个参数就是Request Token。

F. OAUTH服务提供商同意使用者的请求，并向其颁发Access Token与对应的密钥，并返回给使用者。

G. 使用者以后就可以使用上步返回的Access Token访问用户授权的资源。

从上面的步骤可以看出，用户始终没有将其用户名与密码等信息提供给使用者（第三方软件），从而更安全。

可以参考：http://wenku.baidu.com/view/d932434f767f5acfa1c7cd23.html