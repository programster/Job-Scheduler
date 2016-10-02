<?php


/**
 * This is a communicator (amazon calls it an SDK) that allows us to easily interface with the 
 * scheduler API without having to look details up.
 */

class SchedulerCommunicator
{
    private $m_address;
    private $m_port;

    /**
     * Construct the communicator so that we can interface with the scheduler.
     * @param String $address - the url of where the scheduler is
     * @param int $port - the port to connect on.
     */
    public function __construct($address, $port)
    {
        $this->m_address = $address;
        $this->m_port = $port;
    }
    
    
    /**
     * Adds a task to the scheduler
     * 
     * @param String $name - the name to give the task.
     * @param Array<int> $dependencies - an array of task ids that this task relies on being  
     *                                    completed before it is allowed to be executed.
     * @param Array $context - name/value pairs to give the executor an idea of what needs doing.
     * @param int $priority - optionally set a priority between 1-10
     * 
     * @return int - the ID that was assigned to the task that was just added.
     */
    public function addTask($name, Array $context=array(), $dependencies = array(), $priority='')
    {        
        $request = array();
        $request['action'] = 'add_task';
        
        if (!empty($dependencies))
        {
            $request['dependencies'] = $dependencies;
        }

        $request['task_name'] = $name;
        $request['extra_info'] = $context;
        
        if ($priority !== '')
        {
            $priority = intval($priority);
            $request['priority'] = $priority;
        }

        $response = $this->sendRequest($request);
        
        if ($response['result'] == 'success')
        {
            $taskId = $response['cargo']['task_id'];
        }
        else
        {
            var_dump($response);
            Core::throwException('sending addjob request failed: ' . $response['message']);
        }
        
        return $taskId;
    }

    
    /**
     * Fetches a task from the scheduler.
     * @param void
     * @return Array - the described task.
     */
    public function fetchTask()
    {        
        $taskArray = null;
        
        $request = array();
        $request['action'] = 'get_task';
        
        $response = $this->sendRequest($request);

        if ($response['result'] == 'success')
        {
            # Using an array is me being REALLY lazy. Please do not reproduce. 
            $taskArray = $response['cargo']['task'];
        }
        else
        {
            # There are no available tasks is ok. return null
            if (strcmp($response['message'],"There are no available tasks!") !== 0)
            {
                $err_msg = 'sending getjob request failed, message: [' . $response['message'] . ']';
                Core::throwException($err_msg);
            }
        }
        
        return $taskArray;
    }


    /**
     * Mark a task as completed so that other tasks that rely on it being finished can now be 
     * made available.
     * @param type $taskId
     * @param type $lock
     * @return type
     */
    public function markTaskCompleted($taskId, $lock)
    {
        $request['action']  = 'complete_task';
        $request['task_id'] = $taskId;
        $request['lock']    = $lock;
        
        $response = $this->sendRequest($request);

        
        if ($response['result'] == 'success')
        {
            # Using an array is me being REALLY lazy. Please do not reproduce. 
            $taskArray = $response['cargo'];
        }
        else
        {
            # There are no available tasks is ok. return null
            $err_msg = 'marking task completed failed, message: [' . $response['message'] . ']';
            Core::throwException($err_msg);
        }
        
        return $taskArray;
    }


    /**
     * Reject a task that was given to us.
     * @param int $taskId
     * @param String $lock
     * @return $taskArray - the fetched task in array form. 
     */
    public function rejectTask($taskId, $lock)
    {        
        $request['action'] = 'reject_task';
        $request['task_id'] = $taskId;
        $request['lock'] = $lock;
        
        $request = array(
            'action'  => 'reject_task',
            'task_id' => $taskId,
            'lock'    => $lock
        );
        
        $response = $this->sendRequest($request);
        
        if ($response['result'] == 'success')
        {
            # Using an array is me being REALLY lazy. Please do not reproduce. 
            $taskArray = $response['cargo'];
        }
        else
        {
            # There are no available tasks is ok. return null
            Core::throwException('rejecting task failed, message: [' . $response['message'] . ']');
        }
        
        return $taskArray;
    }


    /**
     * Fetches information about the schedulers "state". This includes information about 
     * all the tasks that are available, being-computed, or waiting for other tasks to finish.
     * Please note that the scheduler itself is not responsible for collecting any statistics as 
     * it is deliberately kept as light as possible.
     * @param void
     * @return Array $response - the response from the scheduler in name/value pairs
     */
    public function getInfo()
    {
        $request = array('action' => 'get_info');
        return $this->sendRequest($request);
    }


    /**
     * Helper function that sends a request to the scheduler.
     * @param $request - name/value pairs to send to the scheduler
     * @return Array $response - the response from the scheduler in name/value pairs
     */
    private function sendRequest($request)
    {
        $response = Core::sendTcpRequest($this->m_address, $this->m_port, $request);
        return $response;
    }
}