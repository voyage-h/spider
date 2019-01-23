# Image Spider
an image spider created by docker
## Requirements
1. docker and docker-compose, click https://docs.docker.com/compose/install/ to install
2. php 7.* or later

## Usage
### Step 1
```
git clone git@github.com:voyage-h/spider.git
```
### Step 2
```
sbin/spider install
```
### Step 3
```
sbin/spider start
```
### Step 4
```
sbin/spider run someurl
```
## Problem
1. if you run spider with root, some problem is supposed to occur
```
<b>Warning</b>:  file_put_contents(/workspace/logs/spider-2019-01-23.log): failed to open stream: Permission denied...
```
To solve this problem, You may change the permission of images and logs folder with 0777

2. If it output something like "Spider is not running", you may try spider start again

3. Spider requires at least 2 GB memeries, if you can not start spider, set the etc/docker-compose.yml like this
```
KAFKA_HEAP_OPTS: -Xmx256M -Xms128M
```
