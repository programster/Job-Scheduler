<?php

/*
 * Define the settings for this application.
 */

global $globals;
$globals = array();


# Define the IP where the scheduler is:
# E.g. 192.168.1.243
$globals['SCHEDULER_ADDRESS'] = "10.1.0.3";


# Define which port the scheduler is listening on.
$globals['SCHEDULER_PORT'] = 3901;


# This setting only needs to be set for the automated testing. It should match up with whatever
# is the max lock time on the scheduler.
# This does not change the SDK in any other way!
$globals['MAX_LOCK_TIME'] = 9;
