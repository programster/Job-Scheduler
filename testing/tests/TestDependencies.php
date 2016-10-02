<?php


/**
 * This is a very simple test to check that dependencies of dependencies act as expected..
 */


class TestDependencies extends TestAbstract
{
    private $m_recieved_tasks = array(); # store the order in which the tasks came back, name only
    
    
    public function getErrorMessage() 
    {
        $message = 
            "TestDependencies: incorrectly ordered tasks based on dependencies." . PHP_EOL .
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
 
        
        $impediment_1_id = $scheduler->addTask($name="impediment", 
                                               $context=array(), 
                                               $dependencies=array());
        
        $impediment_2_id = $scheduler->addTask($name="dependent", 
                                              $context=array(), 
                                              $dependencies=array($impediment_1_id));
        
        # Priority 10 means that this task will be executed first if there is a bug and this is 
        # considered available before impediment 2 is finished.
        $final_task_id = $scheduler->addTask($name="final_task", 
                                             $context=array(), 
                                             $dependencies=array($impediment_2_id),
                                             $priority=10);
        
        # Now fetch the tasks and hope that they come in the correct order.
        $this->m_successful = true; #set to true and funcs within will set to false if fails
        $this->fetch_and_complete_expected_task($impediment_1_id, "impediment", $this->m_recieved_tasks);
        $this->fetch_and_complete_expected_task($impediment_2_id, "dependent", $this->m_recieved_tasks);
        $this->fetch_and_complete_expected_task($final_task_id, "final_task", $this->m_recieved_tasks);
    }   
}