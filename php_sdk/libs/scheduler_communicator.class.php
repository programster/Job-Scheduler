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

        $responseArray = $this->sendRequest($request);
        
        $addTaskResponse = new AddTaskResponse($responseArray);
        
        
        if (!$addTaskResponse->isOk())
        {
            throw new Exception('sending addjob request failed: ' . $addTaskResponse->getError());
        }
        
        return $addTaskResponse->getTaskId();
    }

    
    /**
     * Fetches a task from the scheduler.
     * @param void
     * @return Task an object representing the task to be done, null if no task is available
     * @throws Exception if recieve an unexpected response.
     */
    public function fetchTask()
    {        
        $task = null;
        
        $request = array();
        $request['action'] = 'get_task';
        
        $responseArray = $this->sendRequest($request);
        
        $getTaskResponse = new GetTaskResponse($responseArray);

        if ($getTaskResponse->isOk())
        {
            # Using an array is me being REALLY lazy. Please do not reproduce. 
            $task = $getTaskResponse->getTask();
        }
        else
        {
            # There are no available tasks is ok. return null
            if (strcmp($getTaskResponse->getError(), "There are no available tasks!") !== 0)
            {
                $err_msg = 'sending getjob request failed: [' . $getTaskResponse->getError() . ']';
                throw new Exception($err_msg);
            }
        }
        
        return $task;
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
        $baseResponse = new BaseResponse($response);
        
        if (!$baseResponse->isOk())
        {
            $err_msg = 'marking task completed failed: [' . $baseResponse->getError() . ']';
            throw new Exception($err_msg);
        }
    }


    /**
     * Reject a task that was given to us.
     * @param int $taskId
     * @param String $lock
     * @return void
     * @throws Exception if failed to reject the task.
     */
    public function rejectTask($taskId, $lock)
    {
        $request['action']  = 'reject_task';
        $request['task_id'] = $taskId;
        $request['lock']    = $lock;
        
        $request = array(
            'action'  => 'reject_task',
            'task_id' => $taskId,
            'lock'    => $lock
        );
        
        $responseArray = $this->sendRequest($request);
        
        $baseResponse = new BaseResponse($responseArray);
        
        if (!$baseResponse->isOk())
        {
            # There are no available tasks is ok. return null
            $err = 'rejecting task failed, message: [' . $baseResponse->getError() . ']';
            throw new Exception($err);
        }
    }


    /**
     * Fetches information about the schedulers "state". This includes information about 
     * all the tasks that are available, being-computed, or waiting for other tasks to finish.
     * Please note that the scheduler itself is not responsible for collecting any statistics as 
     * it is deliberately kept as light as possible.
     * @param void
     * @return GetInfoResponse
     */
    public function getInfo()
    {
        $request = array('action' => 'get_info');
        $responseArray = $this->sendRequest($request);
        $getInfoResponse = new GetInfoResponse($responseArray);
        return $getInfoResponse;
    }


    /**
     * Helper function that sends a request to the scheduler.
     * @param $request - name/value pairs to send to the scheduler
     * @return Array $response - the response from the scheduler in name/value pairs
     */
    private function sendRequest($request)
    {
        $response = self::sendTcpRequest($this->m_address, $this->m_port, $request);
        return $response;
    }


    /**
     * This is the socket "equivalent" to the sendApiRequest function. However unlike
     * that funciton it does not require the curl library to be installed, and will try to
     * send/recieve information over a direct socket connection.
     *
     * @param Array $request - map of name/value pairs to send.
     * @param string $host - the host wish to send the request to.
     * @param int $port - the port number to make the connection on.
     * @param int $bufferSize - optionally define the size (num chars/bytes) of the buffer. If this
     *                     is too small your information can get cut off, causing errors.
     *                     10485760 = 10 MiB
     * @param int $timeout - (optional, default 2) the number of seconds before connection attempt 
     *                       times out.
     * @param int $attempts_limit - (optional, default 5) the number of failed connection attempts to 
     *                         make before giving up.
     * @param String $ack - the acknowledgement to send back to state that message recieved.
     * @return Array - the response from the api in name/value pairs.
     */
    public static function sendTcpRequest($host, 
                                          $port, 
                                          $request,
                                          $bufferSize=10485760, 
                                          $timeout=2, 
                                          $attempts_limit=100,
                                          $ack = "ack")
    {
        # The PHP_EOL endline is so that the reciever knows that is the end of the message with
        # PHP_NORMAL_READ.
        $request_string = json_encode($request) . PHP_EOL;
        
        $protocol = getprotobyname('tcp');
        $socket = socket_create(AF_INET, SOCK_STREAM, $protocol);
        
        # stream_set_timeout DOES NOT work for sockets created with socket_create or socket_accept.
        # http://www.php.net/manual/en/function.stream-set-timeout.php
        $socket_timout_spec = array(
            'sec'  =>$timeout,
            'usec' => 0
        );
        
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, $socket_timout_spec);
        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, $socket_timout_spec);
        
        $attempts_made = 0;
        $socketErrors = array();
        $timeStart = time();
        
        do
        {
            $connected = socket_connect($socket, $host, $port);
            
            if (!$connected)
            {
                $socket_error_code   = socket_last_error($socket);
                $socket_error_string = socket_strerror($socket_error_code);
                $socketErrors[] = $socket_error_string;
                
                # socket_last_error does not clear the last error after having fetched it, have to 
                # do this manually
                socket_clear_error();
                
                if ($attempts_made == $attempts_limit)
                {
                    $errorMsg = 
                        "Failed to make socket connection " . PHP_EOL .
                        "host: [" . $host . "] " . PHP_EOL .
                        "total time waited: [" . time() - $timeStart . "]" . PHP_EOL .
                        "socket errors: " . PHP_EOL . 
                        print_r($socketErrors, true) . PHP_EOL;
                    
                    throw new Exception($errorMsg);
                }
                
                $attempts_made++;
                
                # The socket may just be "tied up", give it a bit of time before retrying.
                print "Failed to connect so sleeping...." . PHP_EOL;
                sleep(1);
            }
        } while (!$connected); # 110 = timeout error code
        
        /* @var $socket Socket */
        $wroteBytes = socket_write($socket, $request_string, strlen($request_string));
        
        if ($wroteBytes === false)
        {
            throw new Exception('Failed to write request to socket.');
        }
        
        # PHP_NORMAL_READ indicates end reading on newline
        $serverMessage = socket_read($socket, $bufferSize, PHP_NORMAL_READ);
        
        if ($ack != null && $ack != "")
        {
            print "writing ack to server" . PHP_EOL;
            $ack .= PHP_EOL;
            socket_write($socket, $ack, strlen($ack));
        }
        
        
        $response = json_decode($serverMessage, $arrayForm=true);
        socket_shutdown($socket, 2); # 0=shut read, 1=shut write, 2=both
        socket_close($socket);
        
        return $response;
    }
}