<?php


/**
 * This is a file to test the capabilities of the scheduler NOT the computing instances that run the 
 * tasks themselves. Here we are interested if we start getting disconnects and whether the scheduler
 * takes too long to respond when it has a load of tasks on its books already.
 * 
 * Unfortunately this 
 */


class TestBlockageRating extends TestAbstract
{
    private $m_recieved_tasks = array(); # store the order in which the tasks came back, name only
    
    
    public function getErrorMessage() 
    {
        $message = 
            "TestBlockageRating: incorrectly ordered tasks based on dependencies." . PHP_EOL .
            "recieved tasks: " . PHP_EOL .
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
        
        $not_important_id = $scheduler->addTask($name="block_rating_not_important", 
                                                $context=array(), 
                                                $dependencies=array());
        
        $imepediment_task_id = $scheduler->addTask($name="block_rating_impediment", 
                                                   $context=array(), 
                                                   $dependencies=array());
        
        $dependent_task = $scheduler->addTask($name="block_rating_dependent", 
                                              $context=array(), 
                                              $dependencies=array($imepediment_task_id),
                                              $priority=10);
        
        
        # Now fetch the tasks and hope that they come in the correct order.
        $this->m_successful = true; #set to true and funcs within will set to false if fails
        $this->fetch_and_complete_expected_task($imepediment_task_id, "block_rating_impediment", $this->m_recieved_tasks);
        $this->fetch_and_complete_expected_task($dependent_task, "block_rating_dependent", $this->m_recieved_tasks);
        $this->fetch_and_complete_expected_task($not_important_id, "block_rating_not_important", $this->m_recieved_tasks);
    }   
}