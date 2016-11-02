---

layout: post  
title: [TOOL] 技术轮子集合  
subtitle:   
author: Hiko  
category: tech
tags: 技术, 工具, 轮子, 常用包/类  
ctime: 2016-09-14 10:21:22  
lang: zh  

---

## Distributed Systems

- [Zookeeper](http://zookeeper.apache.org/)  - an effort to develop and maintain an open-source server which enables highly reliable distributed coordination.

> ZooKeeper is a centralized service for maintaining configuration information, naming, providing distributed synchronization, and providing group services. 


- [Kafka](http://kafka.apache.org/) - Apache Kafka is publish-subscribe messaging rethought as a distributed commit log.


## Programming

- ### C/C++

- [Libevent](http://libevent.org) - an event notification library.

> The libevent API provides a mechanism to execute a callback function when a specific event occurs on a file descriptor or after a timeout has been reached. Furthermore, libevent also support callbacks due to signals or regular timeouts.

- [Protocol-buffers](https://developers.google.com/protocol-buffers/) - Protocol buffers are a flexible, efficient, automated mechanism for serializing structured data – think XML, but smaller, faster, and simpler. 

## DB

- [influxdb](https://github.com/influxdata/influxdb) - An Open-Source Time Series Database.

> InfluxDB is an open source time series database with no external dependencies. It's useful for recording metrics, events, and performing analytics.


## Network Tools

- ### HTTP benchmarking

- [wrk](https://github.com/wg/wrk)  - a HTTP benchmarking tool
> `wrk` is a modern HTTP benchmarking tool capable of generating significant load when run on a single multi-core CPU. It combines a multithreaded design with scalable event notification systems such as epoll and kqueue.

- [ab](http://httpd.apache.org/docs/2.0/programs/ab.html) - Apache HTTP server benchmarking tool
> `ab` is a tool for benchmarking your Apache Hypertext Transfer Protocol (HTTP) server.

## OPS

- ELK ([Elasticsearch](https://www.elastic.co/products/elasticsearch) / [Logstash](https://www.elastic.co/products/logstash) / [Kibana](https://www.elastic.co/products/kibana)) - Real-time log analysis platform.

> *Logstash* - Collect, Enrich & Transport Data.
> *Elasticsearch* - Search & Analyze Data in Real Time
> *Kibana* - Explore & Visualize Your Data

- [Ansible](https://www.ansible.com/) - sinple IT automation.
> Deploy apps. Manage systems. Crush complexity.
Ansible helps you build a strong foundation for DevOps.

````
eg:

ansible -i ./hosts test -u {user_name} -s -m shell -a 'wget http://hostname/wonder.sh -O -|bash'

````

- [SaltStack](https://saltstack.com/)
> Automation for enterprise IT ops, event-driven data center orchestration and the most flexible configuration management for DevOps at scale.

- [Open-Falcon](http://open-falcon.org/) - 互联网企业级监控系统解决方案。

- [Supervisor](http://www.supervisord.org/) - A Process Control System
> Supervisor is a client/server system that allows its users to monitor and control a number of processes on UNIX-like operating systems.


