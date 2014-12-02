<?php

/* 
 * 
 */

class BaseResponse
{
    protected $m_responseArray;
    
    
    /**
     * Create a base response object from the array response from the Scheduler API
     * @param Array $responseArray - assoc array of name value pairs recieved from the scheduler
     */
    public function __construct($responseArray)
    {
        $this->m_responseArray = $responseArray;
    }
    
    
    /**
     * Check whether there was 
     * @return boolean
     */
    public function isOk()
    {
        $result = false;
        
        if ($this->m_responseArray['result'] == 'success')
        {
            $result = true;
        }
        
        return $result;
    }
    
    
    /**
     * Retrieve the error message from the response if there is one.
     * @return type
     */
    public function getError()
    {
        $errorMessage = null;
        
        if (!$this->isOk())
        {
            $errorMessage = $this->m_responseArray['message'];
        }
        else
        {
            throw new Exception('Calling getError when there was no error');
        }
        
        return $errorMessage;
    }
    
    
    
    /**
     * Get the main body of the request. Everything other than within this is overhead to let you
     * know if everything went ok etc.
     * @return Array
     * @throws Exception if the response came back with an error message.
     */
    protected function getCargo()
    {
        if ($this->isOk())
        {
            return $this->m_responseArray['cargo'];
        }
        else
        {
            throw new Exception($this->getError());
        }
    }
}