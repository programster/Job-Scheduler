Job-Scheduler
=============
A task scheduling program written in Java that uses JSON responses. Optimized for multi-threaded handling of many simultaneous requests. This application aims to categorize tasks based on what other tasks each task is dependent on having finished before they can be made available. It aims to prioritize tasks that unlock the most other tasks first in order to prevent bottlenecks. Thus this is not a FIFO style queing program, but one built for overall speed.

WARNINGS
=======
* This software is developed in a manner that intends to be deployed on a computer/server that has at least two cores, or a single core with hyperthreading. Although it may be able to cope with running on a single-core computer, that should never be a requirement of the system that restricts the software development in any way.
* Everything is held in memory rather than using any databases for performance reasons. This makes installation easy, but causes loss of data if the program exits unexpectedly, such as a due to a reboot.

Reason For Development
======================
I needed an application that would be able to gracefully handle many simulteous requests for work. Initially this project was written in PHP for ease of development, but was moved to Java for the the performance and multi-threaded capabilities. Other languages could have been used, but Java has a similar syntax to PHP, and is fairly easy to build multi-threaded applications with. 
