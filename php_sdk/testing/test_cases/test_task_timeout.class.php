<?php


/**
 * This is a file to test the capabilities of the scheduler NOT the computing instances that run the 
 * tasks themselves. Here we are interested if we start getting disconnects and whether the scheduler
 * takes too long to respond when it has a load of tasks on its books already.
 * 
 * Unfortunately this 
 */


class TestTaskTimeout extends TestAbstract
{
    private $m_recieved_tasks = array(); # store the order in which the tasks came back, name only
    
    public function getErrorMessage() 
    {
        $message = 
            get_class() . " incorrectly ordered tasks based on priorities." . PHP_EOL .
            print_r($this->m_recieved_tasks, true) . PHP_EOL;
        
        return $message;
    }

    
    /**
     * The main logic of the test.
     * Note that the successful flag gets updated from within the fetch_expected_task function
     */
    public function test() 
    {
        global $globals;
        
        $scheduler = $this->getScheduler();
        
        $not_important_id = $scheduler->addTask($name="not_important", 
                                                $context=array(), 
                                                $dependencies=array(),
                                                $priority=1);
        
        $high_importance_id = $scheduler->addTask($name="high_importance", 
                                                  $context=array(), 
                                                  $dependencies=array(),
                                                  $priority=9); 
        
        # Now fetch the tasks and hope that they come in the correct order.
        $this->m_successful = true; # default to true and let the funcs below set to false if fail
        
        $this->fetch_expected_task($high_importance_id, 
                                   "high_importance", 
                                   $this->m_recieved_tasks);
        
        $sleepTime = $globals['MAX_LOCK_TIME'] + 2; // Has to be longer than the timeout time
        
        print "Sleeping for task timout period of: " . $sleepTime . ' seconds.' . 
              PHP_EOL;
        
        sleep($sleepTime);
        
        
        # Even though the important task should have exceeded its MAX_LOCK_TIME we should still 
        # only get the not_important task first as all available tasks should be completed before
        # looking for timeout tasks. This is is because we want to give them as much time as
        # possible and not waste CPU looping through tasks that are in progress.
        
        $this->fetch_and_complete_expected_task($not_important_id, 
                                                "not_important", 
                                                $this->m_recieved_tasks);
        
        $this->fetch_and_complete_expected_task($high_importance_id, 
                                                "high_importance", 
                                                $this->m_recieved_tasks);
    }
}