---

layout: post  
title: [Kafka]初探 Kafka  
subtitle:   
author: Hiko  
category: tech
tags:   
ctime: 2016-05-17 09:10:04  
lang: zh  

---

> 由于需要参与到 QBus 的相关业务开发，而 QBus 是基于 Kafka 封装的消息队列，自然而然需要先对Kafka进行一些了解。

### 概览

- 0x00 资料
- 0x01 Kafka 功能和应用场景
- 0x02 Kafka 原理概念
- 0x03 快速开始 Demo

### 0x00 资料

[***`Kafka 官网`***](http://kafka.apache.org)作为入门的不二之选。

### 0x01 Kafka 功能和应用场景

#### 功能

官方定义： Apache Kafka is publish-subscribe messaging rethought as a distributed commit log.

Kafka 是一个基于 `发布-订阅` 的分布式消息系统，有一下四个特点：

- 快速（高效）：单个 Kafka broker 每秒能应付成千上万个客户端百兆以上的读写。
- 可扩展性：Kafka is designed to allow a single cluster to serve as the central data backbone for a large organization. 能够不需要暂停服务并弹性、透明地进行扩展。数据流会被分区到不同集群的不同机器上。
- 持久化 & 高可用：Kafka的消息是持久化到磁盘并可以选择复制到多个节点（需要指定复制节点的数量）来防止数据丢失。
- 分布式设计

#### 应用场景

Kafka 是一个消息队列，那么一切可以跟队列挂上关系的都（勉强）适用，比如从官网中有如下几种：

1. **Messaging** (消息队列), 其他消息队列: ActiveMQ、RabbitMQ、ZeroMQ等。
2. **Website Activity Tracking** (网站行为跟踪), The original use case for Kafka was to be able to rebuild a user activity tracking pipeline as a set of real-time publish-subscribe feeds. 
3. **Metrics** (做过监控的同学对这个名词应该不陌生), Kafka is often used for operational monitoring data. 
4. **Log Aggregation** (日志聚合), Log aggregation typically collects physical log files off servers and puts them in a central place (a file server or HDFS perhaps) for processing. 与该功能相似的有: Scribe、Flume.
5. **Stream Processing** (流处理/分阶段的处理), doing stage-wise processing of data where data is consumed from topics of raw data and then aggregated, enriched, or otherwise transformed into new Kafka topics for further consumption.
6. **Event Sourcing** (事件追踪、事件溯源)，Event sourcing is a style of application design where state changes are logged as a time-ordered sequence of records. 
7. **Commit Log** (提交日志, 类似 MySQL binlog 的功能), Kafka can serve as a kind of external commit-log for a distributed system. The log helps replicate data between nodes and acts as a re-syncing mechanism for failed nodes to restore their data. 

### 0x02 原理概念

#### i. Kafka 拓扑图

 ![Kafka 拓扑结构](http://cdn1.infoqstatic.com/statics_s1_20160510-0242/resource/articles/kafka-analysis-part-1/zh/resources/0310020.png)
 
 > 备注：图片来自[infoq.com](http://www.infoq.com/cn/articles/kafka-analysis-part-1)
 
 Kafka 中有如下的角色，每个角色有不同的功能：

 - **Producer**
负责发布消息到Kafka broker

 - **Consumer**
消息消费者，向Kafka broker读取消息的客户端。

 - **Consumer Group**  
每个Consumer属于一个特定的Consumer Group（可为每个Consumer指定group name，若不指定group name则属于默认的group）。

 - **Broker**  
Kafka集群包含一个或多个服务器，这种服务器被称为broker

 - **Topic** 
每条发布到Kafka集群的消息都有一个类别，这个类别被称为Topic。（物理上不同Topic的消息分开存储，逻辑上一个Topic的消息虽然保存于一个或多个broker上但用户只需指定消息的Topic即可生产或消费数据而不必关心数据存于何处）

 - **Partition**  
Parition是物理上的概念，每个Topic包含一个或多个Partition.

### 0x03 快速开始Demo

This tutorial assumes you are starting fresh and have no existing Kafka or ZooKeeper data.

#### 1: 下载代码

[下载](https://www.apache.org/dyn/closer.cgi?path=/kafka/0.9.0.0/kafka_2.11-0.9.0.0.tgz) 最新版本(当前)[0.9.0.0 release](https://www.apache.org/dyn/closer.cgi?path=/kafka/0.9.0.0/kafka_2.11-0.9.0.0.tgz) 并解压。
> tar -xzf kafka_2.11-0.9.0.0.tgz
> cd kafka_2.11-0.9.0.0


#### 2: 启动 Zookeeper 和 Kafka Server

Kafaka 使用了Zookeeper，所以在启动Kafka之前需要先启动Zookeeper服务。Kafka的压缩包中有一个单点的Zookeeper实例供我们实验。

#### 2.1 启动 Zookeeper
> bin/zookeeper-server-start.sh config/zookeeper.properties

[2013-04-22 15:01:37,495] INFO Reading configuration from: config/zookeeper.properties (org.apache.zookeeper.server.quorum.QuorumPeerConfig)
...

#### 2.2 启动 Kafka Server
> bin/kafka-server-start.sh config/server.properties

[2013-04-22 15:01:47,028] INFO Verifying properties (kafka.utils.VerifiableProperties)
[2013-04-22 15:01:47,051] INFO Property socket.send.buffer.bytes is overridden to 1048576 (kafka.utils.VerifiableProperties)
...

备注：之前我在启动 Kafka Server 的时候报 GetClientHostname 错误，因为自己的主机名是 cloud2.hiko.com并且没有绑定 host,所以 Kafka Server无法解析真实的主机的IP。解决方式是在 `/etc/hosts` 下绑定 
> 127.0.0.1 cloud2.hiko.com


#### 3: 创建 Topic

创建一个只有一个分区并且复制因子为1的Topic（我们当前只有一台Broker）
> bin/kafka-topics.sh --create --zookeeper localhost:2181 --replication-factor 1 --partitions 1 --topic test

查看 Topic 列表

> bin/kafka-topics.sh --list --zookeeper localhost:2181
test

备注：我们也可以设置成当往一个Topic写消息的时，如果该Topic不存在，则自动创建Topic.

#### 4: 测试发消息

使用命令行客户端进行发消息，每行一条消息。

> bin/kafka-console-producer.sh --broker-list localhost:9092 --topic test
This is a message
This is another message

#### 5: 启动一个 Consumer

使用命令行客户端进行消费消息，消息会自动以标准处输出打印在命令行。

> bin/kafka-console-consumer.sh --zookeeper localhost:2181 --topic test --from-beginning
This is a message
This is another message


#### 6: 设置多个 Broker 的集群

启动耽搁 Broker 就是一个只有一个只有一个节点的集群。配置多个节点的集群也不费劲，以下是在单台主机下、启用多个端口进行模拟多个 Borker 的集群。

##### 6.1 从之前的配置中复制多两份并修改端口和日志路径
> cp config/server.properties config/server-1.properties
> cp config/server.properties config/server-2.properties

修改配置 (因为现在是在单台服务器，端口不能冲突、日志路径不要相同导致不同的 Broker 覆盖了其他 Broker的内容)
config/server-1.properties:
    broker.id=1
    port=9093
    log.dir=/tmp/kafka-logs-1

config/server-2.properties:
    broker.id=2
    port=9094
    log.dir=/tmp/kafka-logs-2
    
The broker.id property is the unique and permanent name of each node in the cluster.
备注: broker.id 字段是该 broker 在该集群的唯一标记以及名字。


#### 6.2 启动两台新的 Kafka Server
> bin/kafka-server-start.sh config/server-1.properties &
...
> bin/kafka-server-start.sh config/server-2.properties &
...

##### 6.3 创建一个复制因子为 3 的Topic (高可用)
> bin/kafka-topics.sh --create --zookeeper localhost:2181 --replication-factor 3 --partitions 1 --topic my-replicated-topic

我们现在已经启动了三台Broker Server形成一个小集群，可以通过以下命令查看各 Broker 的职责:  

> bin/kafka-topics.sh --describe --zookeeper localhost:2181 --topic my-replicated-topic

`Topic:my-replicated-topic` 	`PartitionCount:1`	`ReplicationFactor:3`	`Configs:`
	`Topic: my-replicated-topic`	`Partition: 0`	`Leader: 1`	`Replicas: 1,2,0`	`Isr: 1,2,0`

The first line gives a summary of all the partitions, each additional line gives information about one partition. Since we have only one partition for this topic there is only one line.  
"**leader**" is the node responsible for all reads and writes for the given partition. Each node will be the leader for a randomly selected portion of the partitions.    
  
"**replicas**" is the list of nodes that replicate the log for this partition regardless of whether they are the leader or even if they are currently alive.  

"**isr**" is the set of "in-sync" replicas. This is the subset of the replicas list that is currently alive and caught-up to the leader.  

Note that in my example node 1 is the leader for the only partition of the topic.
We can run the same command on the original topic we created to see where it is:

> bin/kafka-topics.sh --describe --zookeeper localhost:2181 --topic test
`Topic:test`	`PartitionCount:1`	`ReplicationFactor:1`	`Configs:`
	`Topic: test`	`Partition: 0`	`Leader: 0`	`Replicas: 0`	`Isr: 0`
	
So there is no surprise there—the original topic has no replicas and is on server 0, the only server in our cluster when we created it.
Let's publish a few messages to our new topic:

> bin/kafka-console-producer.sh --broker-list localhost:9092 --topic my-replicated-topic  

...    
my test message 1  
my test message 2  
^C  

Now let's consume these messages:  

> bin/kafka-console-consumer.sh --zookeeper localhost:2181 --from-beginning --topic my-replicated-topic

...  
my test message 1  
my test message 2  
^C  

Now let's test out fault-tolerance. Broker 1 was acting as the leader so let's kill it:  
> ps | grep server-1.properties  

7564 ttys002    0:15.91 /System/Library/Frameworks/JavaVM.framework/Versions/1.6/Home/bin/java...

> kill -9 7564

Leadership has switched to one of the slaves and node 1 is no longer in the in-sync replica set:

> bin/kafka-topics.sh --describe --zookeeper localhost:2181 --topic my-replicated-topic

`Topic:my-replicated-topic	PartitionCount:1	ReplicationFactor:3	Configs:
	Topic: my-replicated-topic	Partition: 0	Leader: 2	Replicas: 1,2,0	Isr: 2,0`  

But the messages are still be available for consumption even though the leader that took the writes originally is down:  

> bin/kafka-console-consumer.sh --zookeeper localhost:2181 --from-beginning --topic my-replicated-topic   

...  
my test message 1  
my test message 2  
^C  

#### 7: 使用 Kafka 进行数据的导入/导出 

Writing data from the console and writing it back to the console is a convenient place to start, but you'll probably want to use data from other sources or export data from Kafka to other systems. For many systems, instead of writing custom integration code you can use Kafka Connect to import or export data. Kafka Connect is a tool included with Kafka that imports and exports data to Kafka. It is an extensible tool that runs connectors, which implement the custom logic for interacting with an external system. In this quickstart we'll see how to run Kafka Connect with simple connectors that import data from a file to a Kafka topic and export data from a Kafka topic to a file. First, we'll start by creating some seed data to test with:  

> echo -e "foo\nbar" > test.txt  

Next, we'll start two connectors running in standalone mode, which means they run in a single, local, dedicated process. We provide three configuration files as parameters. The first is always the configuration for the Kafka Connect process, containing common configuration such as the Kafka brokers to connect to and the serialization format for data. The remaining configuration files each specify a connector to create. These files include a unique connector name, the connector class to instantiate, and any other configuration required by the connector.
  
> bin/connect-standalone.sh config/connect-standalone.properties config/connect-file-source.properties config/connect-file-sink.properties

  

These sample configuration files, included with Kafka, use the default local cluster configuration you started earlier and create two connectors: the first is a source connector that reads lines from an input file and produces each to a Kafka topic and the second is a sink connector that reads messages from a Kafka topic and produces each as a line in an output file. During startup you'll see a number of log messages, including some indicating that the connectors are being instantiated. Once the Kafka Connect process has started, the source connector should start reading lines from test.txt and producing them to the topic connect-test, and the sink connector should start reading messages from the topic connect-test and write them to the file test.sink.txt. We can verify the data has been delivered through the entire pipeline by examining the contents of the output file:    

> cat test.sink.txt

foo
bar
Note that the data is being stored in the Kafka topic connect-test, so we can also run a console consumer to see the data in the topic (or use custom consumer code to process it):  

> bin/kafka-console-consumer.sh --zookeeper localhost:2181 --topic connect-test --from-beginning



{"schema":{"type":"string","optional":false},"payload":"foo"}  
{"schema":{"type":"string","optional":false},"payload":"bar"}  
...  


The connectors continue to process data, so we can add data to the file and see it move through the pipeline:  

> echo "Another line" >> test.txt


You should see the line appear in the console consumer output and in the sink file.



