<?php

/* 
 * This test checks that having multiple tasks that all have the same dependency ID works as 
 * expected.
 */

class TestSharedDependencies extends TestAbstract
{
    private $m_recieved_tasks = array(); # store the order in which the tasks came back, name only
    
    
    public function getErrorMessage() 
    {
        $message = 
            "TestSharedDependencies: incorrectly ordered tasks based on dependencies." . PHP_EOL .
            print_r($this->m_recieved_tasks, true) . PHP_EOL;
        
        return $message;
    }

    
    /**
     * The main logic of the test.
     * Note that the successful flag gets updated from within the fetch_expected_task function
     */
    public function test() 
    {
        $num_secondary_tasks = 100;
        
        $scheduler = $this->getScheduler();
 
        
        $impediment_1_id = $scheduler->addTask($name="first_level", 
                                               $context=array(), 
                                               $dependencies=array());
        $secondary_task_ids = array();
        
        for ($s=0; $s<$num_secondary_tasks; $s++)
        {
            $secondary_task_ids[] = $scheduler->addTask($name="second_level", 
                                                        $context=array(), 
                                                        $dependencies=array($impediment_1_id),
                                                        $priority=7);
        }
        
        $third_task_id = $scheduler->addTask($name="third_level", 
                                             $context=array(), 
                                             $secondary_task_ids,
                                             $priority=10);

        
        
        # Now fetch the tasks and hope that they come in the correct order.
        $this->m_successful = true; #set to true and funcs within will set to false if fails
        $this->fetch_and_complete_expected_task($impediment_1_id, "first_level", $this->m_recieved_tasks);
        
        for ($s=0; $s<$num_secondary_tasks; $s++)
        {
            $this->fetch_and_complete_expected_task('', "second_level", $this->m_recieved_tasks);
        }
        
        $this->fetch_and_complete_expected_task($third_task_id, "third_level", $this->m_recieved_tasks);
    }   
}

