<?php


/**
 * This is a file to test the capabilities of the scheduler NOT the computing instances that run the 
 * tasks themselves. Here we are interested if we start getting disconnects and whether the scheduler
 * takes too long to respond when it has a load of tasks on its books already.
 * 
 * Unfortunately this 
 */


class TestPriorities extends TestAbstract
{
    private $m_recieved_tasks = array(); # store the order in which the tasks came back, name only
    
    public function getErrorMessage() 
    {
        $message = 
            "TestPriorities: incorrectly ordered tasks based on priorities." . PHP_EOL .
            print_r($this->m_recieved_tasks, true) . PHP_EOL;
        
        return $message;
    }

    
    /**
     * The main logic of the test.
     * Note that the successful flag gets updated from within the fetch_expected_task function
     */
    public function test() 
    {
        $scheduler = $this->getScheduler();
        
        $not_important_id = $scheduler->addTask($name="not_important", 
                                                $context=array(), 
                                                $dependencies=array(),
                                                $priority=1);
        
        $high_importance_id = $scheduler->addTask($name="high_importance", 
                                                  $context=array(), 
                                                  $dependencies=array(),
                                                  $priority=9);
        
        $low_importance_id = $scheduler->addTask($name="low_importance", 
                                                 $context=array(), 
                                                 $dependencies=array(),
                                                 $priority=3);
        
        # This brings up an important point, should a task with lower importance be scheduled
        # ahead of other tasks if the task waiting on it is high priority? ATM the expected
        # behaviour is no, ordered by AVAILABLE tasks importance, then dependeencies etc.
        $dependent_high_import_id = $scheduler->addTask($name="highest_importance", 
                                                        $context=array(), 
                                                        $dependencies=array($not_important_id),
                                                        $priority=10);        
        
        # Now fetch the tasks and hope that they come in the correct order.
        $this->m_successful = true; # default to true and let the funcs below set to false if fail
        $this->fetch_and_complete_expected_task($high_importance_id, 
                                                "high_importance", 
                                                $this->m_recieved_tasks);
        
        $this->fetch_and_complete_expected_task($low_importance_id, 
                                                "low_importance", 
                                                $this->m_recieved_tasks);
        
        $this->fetch_and_complete_expected_task($not_important_id, 
                                                "not_important", 
                                                $this->m_recieved_tasks);
        
        $this->fetch_and_complete_expected_task($dependent_high_import_id, 
                                                "highest_importance", 
                                                $this->m_recieved_tasks);
    }
}