Job-Scheduler
=============
This project is a queuing system (like [beanstalkd](https://kr.github.io/beanstalkd/)) that focuses on dependencies between tasks. written in Java that uses JSON responses. Optimized for multi-threaded handling of many simultaneous requests. This application aims to categorize tasks based on what other tasks each task is dependent on having finished before they can be made available. It aims to prioritize tasks that unlock the most other tasks first in order to prevent bottlenecks. Thus this is not a FIFO style queuing program, but one built for overall speed.#

Installation and Setup (Docker)
================
This project makes use of docker to make deployment incredibly simple and easy. This should be able to be deployed to any docker compatible host.
* Navigate to the "docker" folder at the top of the source tree and run `bash build.sh`.
* Start the container by executing `bash start-container.sh` which will start the program on port 3901, but obviously you can use your own docker startup command to have the service listen to and report on any port that you desire.

Warnings & Limitations
======================
* This software is developed in a manner that intends to be deployed on a server that has at least two cores or vCPUs. Although it may be able to cope with running on a single-core computer, that should never be a requirement of the system that restricts the software development in any way.
* The software holds all tasks in memory, which causes a loss of data if the program exits unexpectedly, such as a due to an unexpected reboot.
  * In the future, there will be the ability to sync tasks to/from a file at a scheduled interval, and a shutdown request to gracefully stop the scheduler.

Reason For Development
======================
I needed an queuing system that would take care of scheduling tasks for me, based on their dependencies, whilst being able to handle a large number of simultaneous requests. I found many queing systems on the internet, but none that had a concept of dependencies. Initially this project was written in PHP for ease of development, but was moved to Java for the the performance and multi-threaded capabilities. Other languages could have been used, but Java has a similar syntax to PHP, and is fairly easy to build multi-threaded applications with.

Licensing Information
=====================
This project currently uses the Google-gson 2.2.4 library and has been fully included with licensing in the libs folder. That part of the project uses Apache 2.0 license which is why this project uses the GPL v3.0 license which is stated to be compatible. The reason I chose this license is on the understanding that it expects extensions/updates of the software to also be made open source. It appears that open source licenses have become extremely complicated, and dull the joys of developing opensource software for others to use.
