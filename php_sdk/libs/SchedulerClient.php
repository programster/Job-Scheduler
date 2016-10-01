<?php


/**
 * This is a communicator (amazon calls it an SDK) that allows us to easily interface with the 
 * scheduler API without having to look details up. This uses a persistent TCP socket to send 
 * all requests over and should be closed when finished.
 */

class SchedulerClient
{
    private static $ATTEMPTS_LIMIT = 100;
    private static $ATTEMPT_TIMEOUT = 2; // seconds to wait before connection attempt times out
    
    // buffer size of socket connection. A large number is required when tasks have lots of 
    // dependencies, or when fetching get_info.
    //10485760 = 10 MiB
    private static $BUFFER_SIZE = 10485760; 
    
    private $m_address;
    private $m_port;
    private $m_queueName;
    private $m_socket; # the socket connection
    
    private static $s_instance = null;
    
    /**
     * Construct the communicator so that we can interface with the scheduler.
     * @param String $address - the url of where the scheduler is
     * @param int $port - the port to connect on.
     * @param string $queueName - the name of the queue we will put tasks into etc.
     */
    private function __construct($address, $port, $queueName)
    {
        $this->m_address = $address;
        $this->m_port = $port;
        $this->m_queueName = $queueName;
        $this->m_socket = null;
    }
    
    
    /**
     * Accessor for the scheduler instance (singleton)
     * @param String $host - the url of where the scheduler is
     * @param int $port - the port to connect on.
     * @return SchedulerClient
     */
    public static function getInstance($host, $port, $queueName)
    {
        if (self::$s_instance == null)
        {
            self::$s_instance = new SchedulerClient($host, $port, $queueName);
        }
        
        return self::$s_instance;
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
        
        $request = array(
            'action' => 'get_task'
        );
        
        
        $responseArray = $this->sendRequest($request);
        $getTaskResponse = new GetTaskResponse($responseArray);
        
        if ($getTaskResponse->isOk())
        {
            $task = $getTaskResponse->getTask();
        }
        else
        {
            # There are no available tasks is ok. return null
            if (stripos($getTaskResponse->getError(), "There are no available tasks!") === FALSE)
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
        $request = array(
            'action'  => 'complete_task',
            'task_id' => $taskId,
            'lock'    => $lock
        );
        
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
     * Connect to the job scheduler with a TCP Socket.
     * @throws Exception
     */
    private function connect()
    {
        if ($this->m_socket != null)
        {
            $errMsg = "Attempting to connect to scheduler when there is already a connection";
            throw new Exception($errMsg);
        }
        
        $protocol = getprotobyname('tcp');
        $this->m_socket = socket_create(AF_INET, SOCK_STREAM, $protocol);

        # stream_set_timeout DOES NOT work for sockets created with socket_create or socket_accept.
        # http://www.php.net/manual/en/function.stream-set-timeout.php
        $socket_timout_spec = array(
            'sec'  => self::$ATTEMPT_TIMEOUT,
            'usec' => 0
        );

        socket_set_option($this->m_socket, SOL_SOCKET, SO_RCVTIMEO, $socket_timout_spec);
        socket_set_option($this->m_socket, SOL_SOCKET, SO_SNDTIMEO, $socket_timout_spec);

        $attempts_made = 0;
        $socketErrors = array();
        $timeStart = time();
        
        do
        {
            $connected = socket_connect($this->m_socket, $this->m_address, $this->m_port);
            
            if (!$connected)
            {
                $socket_error_code   = socket_last_error($this->m_socket);
                $socket_error_string = socket_strerror($socket_error_code);
                $socketErrors[] = $socket_error_string;

                # socket_last_error does not clear the last error after having fetched it, have to 
                # do this manually
                socket_clear_error();
                
                if ($attempts_made == self::$ATTEMPTS_LIMIT)
                {
                    $errorMsg = 
                        "Failed to make socket connection " . PHP_EOL .
                        "host: [" . $this->m_address . "] " . PHP_EOL .
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
    }
    
    
    /**
     * Close all connections to the scheduler.
     */
    public function close()
    {
        $request = array('action' => 'close');
        $this->sendRequest($request, $expectResponse=false);
        socket_shutdown($this->m_socket, 2); # 0=shut read, 1=shut write, 2=both
        socket_close($this->m_socket);
        $this->m_socket = null;
    }
    
    
    /**
     * Sends a request to the scheduler using our persistant socket. If the socket
     * doesn't exist yet, then we instantiate it.
     * @param Array $request - map of name/value pairs to send.
     * @param bool $expectResponse - manually set to false if you are not expecting a response
     * @return Array - the response from the api in name/value pairs.
     */
    private function sendRequest($request, $expectResponse=true)
    {
        $response = null;
        
        # The PHP_EOL endline is so that the reciever knows that is the end of the message with
        # PHP_NORMAL_READ.
        $request_string = json_encode($request) . PHP_EOL;
        
        ## connect
        if ($this->m_socket == null)
        {
            print "connecting to scheduler" . PHP_EOL;
            $this->connect();
        }
        
        /* @var $socket Socket */
        $wroteBytes = socket_write($this->m_socket, $request_string, strlen($request_string));
        
        if ($wroteBytes === false)
        {
            throw new Exception('Failed to write request to socket.');
        }
        
        if ($expectResponse)
        {
            # PHP_NORMAL_READ indicates end reading on newline
            $serverMessage = socket_read($this->m_socket, self::$BUFFER_SIZE, PHP_NORMAL_READ);
            $response = json_decode($serverMessage, $arrayForm=true);
        }
        
        return $response;
    }
}