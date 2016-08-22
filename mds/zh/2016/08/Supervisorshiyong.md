---

layout: post  
title: [OPS] Supervisor使用  
subtitle:   
author: Hiko  
category: tech
tags: 运维, Supervisor  
ctime: 2016-08-22 11:32:44  
lang: zh  

---

Supervisor是一个进程监控程序。

####需求一

有一个进程需要每时每刻不断的跑，但是这个进程又有可能由于各种原因有可能中断。当进程中断的时候希望能自动重新启动它，此时，就需要使用到 `Supervisor`.

两个命令: 

	supervisord : supervisor的服务器端部分，启动supervisor就是运行这个命令   

	supervisorctl：启动supervisor的命令行窗口。


####需求二

`redis-server` 这个进程是运行 `redis` 的服务。我们要求这个服务能在意外停止后自动重启,安装()Centos下).

> yum install Python-setuptools 
> easy_install supervisor 

测试是否安装成功： 
> echo_supervisord_conf 

创建配置文件： 
> sudo su #切换到root用户，不然提示无权限    
>echo_supervisord_conf > /etc/supervisord.conf 

修改配置文件, 在supervisord.conf最后增加：

	[program:redis]
	command = redis-server   //需要执行的命令  
	autostart=true    //supervisor启动的时候是否随着同时启动  
	autorestart=true   //当程序跑出exit的时候，这个program会自动重启  
	startsecs=3  //程序重启时候停留在runing状态的秒数  

	（更多配置说明请参考：http://supervisord.org/configuration.html）

运行命令： 
> supervisord //启动supervisor  
> supervisorctl//打开命令行 

结果： 
redis RUNNING pid 24068, uptime 3:41:55

ctl中： help //查看命令
ctl中： status //查看状态

遇到的问题：

	Q: redis出现的不是running而是FATAL 状态 
	A: 应该要去查看log，log在/tmp/supervisord.log

	Q: 日志中显示：gave up: redis entered FATAL state, too many start retries too quickly 
	A: 修改redis.conf的daemonize为no 
	具体说明：http://xingqiba.sinaapp.com/?p=240 
	事实证明webdis也有这个问题，webdis要修改的是webdis.json这个配置文件


完成验证：

	ps aux | grep redis 
	[root@cloud.com~]# ps aux | grep redis 
	root 30582 0.0 0.0 9668 1584 ? S 14:12 0:00 redis-server

	kill 30582

	[root@cloud.com ~]# ps aux | grep redis 
	root 30846 0.0 0.0 9668 1552 ? S 15:19 0:00 redis-server
