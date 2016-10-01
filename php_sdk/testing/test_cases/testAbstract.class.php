<?php


/**
 * This is the 'base' framework for the scheduler tests to extend.
 * Any functionality that all the tests share or "workflow" should be defined here.
 */


abstract class TestAbstract
{
    private $m_scheduler = null;
    protected $m_successful = false;
    
    
    /**
     * Fetches a SchedulerClient by creating it if it does not exist yet.
     * @return SchedulerClient
     */
    protected function getScheduler()
    {
        global $globals;
        
        if ($this->m_scheduler == null)
        {
            $this->m_scheduler = SchedulerClient::getInstance(
                $globals['SCHEDULER_ADDRESS'], 
                $globals['SCHEDULER_PORT'],
                $globals['SCHEDULER_QUEUE']
            );
        }
        
        return $this->m_scheduler;
    }
    
    
    # Define the message to display if the test was not successful.
    public abstract function getErrorMessage();
    
    
    # This is for the extended class to define the logic for exectuting the test
    protected abstract function test();
    
    
    /**
     * This is the entry point to the test for it to be executed.
     * @return boolean - flag indicating if was successful.
     */
    public function run()
    {
        $this->clear_scheduler();
        static::test();
        $this->m_scheduler->close();
        return $this->m_successful;
    }
    
    
    /**
     * Fetches a task from the scheduler and checks if it was the expected one. If not, then this
     * marks the test as a failure.
     * @param int $expectedId - the id of the task we are expecting. 
     *                       May be set to '' if dont want to compare
     * @param string $expectedName - the name of the task we are expecting.
     * @param Array $name_store - optionally specify an array to append the name of task to.
     * @return boolean - flag inididcating whether successful or not.
     */
    protected function fetch_and_complete_expected_task($expectedId, 
                                                        $expectedName, 
                                                        &$name_store=array())
    {        
        $result = true;
        $scheduler = $this->getScheduler();
        $task = $scheduler->fetchTask();
        
        if ($task === null)
        {
            print get_class($this) . " error: was expecting a task but recieved a null task" . PHP_EOL;
            $result = false;
        }
        else
        {
            $name_store[] = $task->getName();
        
            if ((!empty($expectedId) && $task->getId() !== $expectedId) ||
                strcasecmp($expectedName, $task->getName()) !== 0)
            {
                print "either the id did not match " . $expectedId . " " . $task->getId() . 
                      "or the names didn't: " . $expectedName . " " . $task->getName() . PHP_EOL;
                $result = false;
            }
        }
        
        if ($result === false)
        {
            $this->m_successful = false;
        }
        else 
        {
            $scheduler->markTaskCompleted($task->getId(), $task->getLock());
        }
        
        return $result;
    }
    
    
    /**
     * Fetches a task from the scheduler and checks if it was the expected one. If not, then this
     * marks the test as a failure.
     * @param int $expectedId - the id of the task we are expecting. 
     *                          This can be set to '' if don't want to compare
     * @param string $expectedName - the name of the task we are expecting.
     * @param Array $name_store - optionally specify an array to append the name of task to.
     * @return boolean - flag inididcating whether successful or not.
     */
    protected function fetch_expected_task($expectedId, $expectedName, &$name_store=array())
    {
        $result = true;
        $scheduler = $this->getScheduler();
        $task = $scheduler->fetchTask();
        
        if ($task === null)
        {
            print get_class($this) . " error: wwas expecting a task but recieved a null task" . PHP_EOL;
            $result = false;
        }
        else
        {
            $name_store[] = $task->getName();
        
            if ((!empty($expectedId) && $task->getId() !== $expectedId) ||
                strcasecmp($expectedName, $task->getName()) !== 0)
            {
                print "either the id did not match " . $expectedId . " " . $task->getId() . 
                        "or the names didnt: " . $expectedName . " " . $task->getName() . PHP_EOL;
                $result = false;
            }
        }
        
        if ($result === false)
        {
            $this->m_successful = false;
        }
        
        return $result;
    }
    
    
    /**
     * This function ensures there are no tasks in the scheduler.
     * These tests need to be independent, such that if one fails, it does not affect the other
     * tests. Thus it is a good idea if the scheduler is made sure to be "clear" before each test
     * @param void
     * @return void
     */
    protected function clear_scheduler()
    {
        $scheduler = $this->getScheduler();
             
        while (($task = $scheduler->fetchTask()) !== null)
        {
            $scheduler->markTaskCompleted($task->getId(), $task->getLock());
        }
    }
}
