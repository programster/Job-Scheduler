Job-Scheduler
=============
This project is a queuing system similar to [beanstalkd](https://kr.github.io/beanstalkd/)), but 
with some key improvements. It provides [DAG capabilities](https://www.youtube.com/watch?v=1Yh5S-S6wsI), 
with tasks possibly depending on other tasks. This queuing system will not allow a task to be taken 
unless all of its dependencies have been completed. All requests/responses are in JSON format. The 
application prioritizes tasks first based on priority, and then on how many other tasks require it 
to be completed before they can start. This prevents bottlenecks, but means that this is not a 
FIFO style queuing program, but one built for overall speed, but still be able to prioritize
things if required.


## Key Features
* Dependency Management and enforcement.
* Named Queues for isolation and simplicity.
* Task prioritization.
* Scheduling built for high parallelization.
* Ludicrously fast (all in-memory and multithreaded).
* JSON request/responses for easy integration.
* [SDK for PHP users](https://packagist.org/packages/irap/job-scheduler) to easily integrate.


## Getting Started / Deploying (Docker)

1. Copy the `.env.example` file to create a `.env` file and fill it in as appropriate to you.
2. Run `docker compose build` to build the image
3. Run `docker compose up` to spin up the service.


## Testing
This codebase has a folder for testing at `/testing` that contains some PHP code for testing. Deploy this service using
docker compose as outlined above, and then pull down the PHP packages with composer install, before then running:

```php
php test.php
```

If all goes well, then you should see some output like below:

```
Running TestTaskTimeout
Skipping TestTaskTimeout is pointless because MAX_LOCK_TIME is set to infinite
Running TestDependencies
Running TestSharedDependencies
Running TestBlockageRating
Running TestPriorities
Running TestMemoryUsage
Running TestPerformance
Time taken: 0.25075793266296
Congratulations! All tests succeeded.
```


## Planned Features
* change MAX_LOCK_TIME to DEFAULT_MAX_LOCK_TIME, with the ability for the lock time to be specified on a per-task basis
  as some tasks may expect to take longer than others.
* Update the testing area to use Docker as well, for a controlled PHP environment.
* UUID based identifiers
* [REST](https://www.boxuk.com/insight/creating-a-rest-api-quickly-using-pure-java/) or 
  [gRPC based](https://grpc.io/docs/languages/java/basics/) interfacing rather than direct 
  socket connections.
* Security/Authentication
* Separate Web UI service that integrates through for easy visual monitoring/metrics and control.
* Groups with the ability to "drop" a group of tasks


## Minimum Requirements
* 2 vCPUs
* 512MB of RAM



## Configuration/Settings
All of the settings can be configured through environment variables. If you are 
not using Docker, you can just edit the settings file and recompile the Java application with the script provided.


### DEBUG
Toggles debugging mode on/off. Debugging mode will result in a lot of extra console output.
* **Default:** off
* **Example Usage:** `DEBUG=true`


### USE_THREAD_POOL
Set this variable to true in order to use a pool of threads that get re-used to handle connections, instead of spawning 
1 new thread for each incoming connection.

* **Default:** false.
* **Example Usage:** `USE_THREAD_POOL=true`


### THREAD_POOL_SIZE
Specify the number of threads you want in your thread pool to handle requests. This does nothing if you haven't
specified `USE_THREAD_POOL=true`.

* **Default:** number of vCPUs on host.
* **Example Usage:** `THREAD_POOL_SIZE=5`


### MAX_LOCK_TIME
Specify the maximum amount of time (in seconds) that a worker can have a task "checked out" before it is considered 
lost and the task will be unlocked for other workers to grab. By default there is no timeout and the task will be 
considered locked until the either the socket connection is lost, or the worker comes back and says it has been 
completed.

* **Default:** `0` (infinite)

### SOCKET_PORT
Specify the socket you want the scheduler to listen on for requests.

* **Default:** `3901`

### ADDRESS
Optionally specify the IP address to listen on. If not set (default) then the program will default to listening on
*all* sockets/IPs. This is generally only really useful in non-docker environments if you only wanted to listen 
on `127.0.0.1` and not listen for outside connections etc.

* **Example Usage:** `ADDRESS=127.0.0.1`


## Warnings & Limitations
* This software is developed in a manner that intends to be deployed on a server that has at least 2+ vCPUs. 
* Although it may be able to cope with running on a single-core computer, that should never be a requirement of the 
* system that restricts the software development in any way.
* The software holds all tasks in memory, which causes a loss of data if the program exits unexpectedly, such as an 
* unexpected reboot.
  * In the future, there will be the ability to save/load tasks from a file that is synced to at a scheduled interval, 
  * and a shutdown request to gracefully stop the scheduler.

### Why Java?
Java provided the easiest way to program whilst achieving the desired performance with its multi-threading capabilities.

## Reason For Development
I needed a queuing system that would take care of scheduling tasks for me, based on their dependencies, whilst being 
able to handle a large number of simultaneous requests. I found many queuing systems on the Internet, but none that had 
a concept of dependencies. Initially this project was written in PHP for ease of development, but was moved to Java for 
the the performance and multi-threaded capabilities. Other languages could have been used, but Java has a similar syntax 
to PHP, and is fairly easy to build multi-threaded applications with.

## Testing And Contributing
Feel free to use the `testing` section to benchmark how well this application works on your hardware. Before running 
any tests, you will need to run a `composer update`. If you wish to contribute something new to this project, then 
please add an appropriate test for that feature with the merge request.

## Licensing Information
This project currently uses the Google-gson 2.2.4 library and has been fully included with licensing in the libs folder. 
That part of the project uses Apache 2.0 license which is why this project uses the GPL v3.0 license which is stated to 
be compatible. The reason I chose this license is on the understanding that it expects extensions/updates of the 
software to also be made open source. It appears that open source licenses have become extremely complicated, and dull 
the joys of developing open-source software for others to use.
