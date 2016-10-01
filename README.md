Job-Scheduler
=============
This project is a queuing system (like [beanstalkd](https://kr.github.io/beanstalkd/)) that focuses on dependencies between tasks. All responses are in JSON format. The application prioritizes tasks based on how many other tasks require it to be completed before they can start. This prevents bottlenecks, but means that this is not a FIFO style queuing program, but one built for overall speed.

### Why Java?
Java provided the easiest way to program whilst achieving the desired performance with its multi-threading capabilities.

## Quickstart
[Docker](https://www.docker.com/) users can run the command below to deploy their own job scheduler.

```
docker run -d \
-p 3901:3901 \
-e "ADDRESS=172.17.0.2" \
--name="scheduler" \
programster/job-scheduler
```

The following commands can be used to update and re-deploy
```
docker pull programster/job-scheduler
docker kill scheduler

docker run -d \
-p 3901:3901 \
-e "ADDRESS=172.17.0.2" \
--name="scheduler" \
programster/job-scheduler
```


## Build Your Own Docker Image
If you don't want to use the [publicly available image](https://hub.docker.com/r/programster/job-scheduler/), you can build your own from this project by following the steps below:
* Navigate to the "docker" folder at the top of the source tree and run `bash build.sh`.
* Start the container by executing `bash deploy.sh`

## Configuration/Settings
All of the settings can be configured through environment variables to make the project "docker friendly". If you are not using Docker, you can just edit the settings file and recompile the Java application with the script provided.

Environment variables for docker containers are specified with `-e` as shown below:

```
docker run \
-e "ADDRESS=172.17.0.2" \
-e "USE_THREAD_POOL=true" \
...
```

### DEBUG

Specify  to turn on debugging mode. Debugging mode will result in a lot of extra console output.
* **Default:** off
* **Example Usage:** `DEBUG=1`

### USE_THREAD_POOL
Specify in order to use a pool of threads that get re-used to handle connections, instead of spawning 1 new thread for each incomming connection.

* **Default:** false.
* **Example Usage:** `USE_THREAD_POOL=true`


### THREAD_POOL_SIZE
Specify the number of threads you want in your thread pool to handle requests. This does nothing if you haven't specified `USE_THREAD_POOL=true`.

* **Default:** number of vCPUs on host.
* **Example Usage:** `THREAD_POOL_SIZE=5`

### MAX_ACK_WAIT
Specify the amount of time to wait for an acknowledgement of a message before considering the message as having beeen lost. At the moment this just results in the socket being closed, but in the future, not acking a message will revert any changes made by the request.

* **Default**: `3`


### MAX_LOCK_TIME
Specify the maximum amount of time that a worker can have a task "checked out" before it is considered lost and the task will be unlocked for other workers to grab. By default there is no timeout and the task will be considered locked until the worker comes back and says it has been completed.

* **Default:** `null` (wait forever)

### SOCKET_PORT
Specify the socket you want the scheduler to listen on for requests.

* **Default:** `3901`

### ADDRESS
Specify the IP to listen on. If not set (default) then the program will default to the pubic IP of this machine.
If using docker this needs to be `172.17.0.2` instead of the public ip of instance due to the way the networking is set up. You may wish to set this to 127.0.0.1 for security if all your applications are on the same host.

* **Default:** The Public IP of the server (fetched from somewhere like icanhazip.com)
* **Example Usage:** `ADDRESS=127.0.0.1`


## Warnings & Limitations
* This software is developed in a manner that intends to be deployed on a server that has at least two cores or vCPUs. Although it may be able to cope with running on a single-core computer, that should never be a requirement of the system that restricts the software development in any way.
* The software holds all tasks in memory, which causes a loss of data if the program exits unexpectedly, such as an unexpected reboot.
  * In the future, there will be the ability to save/load tasks from a file that is synced to at a scheduled interval, and a shutdown request to gracefully stop the scheduler.

## Reason For Development
I needed a queuing system that would take care of scheduling tasks for me, based on their dependencies, whilst being able to handle a large number of simultaneous requests. I found many queuing systems on the Internet, but none that had a concept of dependencies. Initially this project was written in PHP for ease of development, but was moved to Java for the the performance and multi-threaded capabilities. Other languages could have been used, but Java has a similar syntax to PHP, and is fairly easy to build multi-threaded applications with.

## Licensing Information
This project currently uses the Google-gson 2.2.4 library and has been fully included with licensing in the libs folder. That part of the project uses Apache 2.0 license which is why this project uses the GPL v3.0 license which is stated to be compatible. The reason I chose this license is on the understanding that it expects extensions/updates of the software to also be made open source. It appears that open source licenses have become extremely complicated, and dull the joys of developing open-source software for others to use.
