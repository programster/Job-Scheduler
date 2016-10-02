Purpose
========
This is a self-contained "project" within the scheduler for building client applications in PHP that interface with the Scheduler. Whenever the API is updated, updates to the SDK should take place as necessary so that the SDK is always in sync.
e.g. revision 301 of the scheduler should work with revision 301 of the sdk found within it.

Testing
========
This SDK is also used for automatically testing that the Scheduler works as expected.

## Why Not A Separate Repo?
Having this in a separate repo could cause confusion and people may struggle to find the sdk that matches a specific previous revision of the scheduler should we wish to roll back.

