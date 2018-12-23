---

layout: post  
title: [OPS] ELK日志分析平台部署  
subtitle:   
author: Hiko  
category: tech
tags: ELK, Elasticsearch, Logstash, Kibana  
ctime: 2016-09-22 20:00:29  
lang: zh  

---

http://www.tuicool.com/articles/QFvARfr

Logstash 配置 Elasticsearch

> ./logstash agent -f hiko.logstash.conf

	input {
		stdin {}
	}
	output {
		elasticsearch {
			hosts => ["localhost"]
		}
		stdout {
			codec => rubydebug
		}
	}