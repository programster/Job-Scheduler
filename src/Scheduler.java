
/*
 * The scheduler used to contain all the logic, but now we support multiple queues and the logic 
 * was moved into the taskQueu object. The "scheduler" class is now just an interface to
 * a collection of queues.
 */

import java.util.concurrent.ConcurrentHashMap;



public class Scheduler 
{
    private static Scheduler s_instance;
    private ConcurrentHashMap<String, TaskQueue> m_queues = new ConcurrentHashMap<>();
    
    
    /**
     * Be careful to only initiate one of this object, but we do not NEED to implement a singleton
     */
    private Scheduler(){}
    
    
    /**
     * Fetches the instance of this class (singleton)
     * @return Scheduler $s_instance - the instance of this class.
     */
    public static Scheduler getInstance()
    {
        if (s_instance == null)
        {
            s_instance = new Scheduler();
        }
        
        return s_instance;
    }   
    
    
    /**
     * Fetch the queue that we wish to interface with by name.
     * @param queue_name - the name of the queue that we wish to work with.
     * @return TaskQueue
     */
    public TaskQueue getQueue(String queue_name)
    {
        TaskQueue queue;
        
        if (!this.m_queues.containsKey(queue_name))
        {
            queue = new TaskQueue(queue_name);
            this.m_queues.put(queue_name, queue);
        }
        else
        {
            queue = this.m_queues.get(queue_name);
        }
        
        return queue;
    }
    
    
    /**
     * Drop a queue and any tasks it contains.
     * @param queue_name - the name of the queue that we wish to drop.
     */
    public void dropQueue(String queue_name)
    {
        TaskQueue queue;
        
        if (!this.m_queues.containsKey(queue_name))
        {
            // do nothing. The queue dosent exist anyway.
            // Perhaps we should throw an exception...
        }
        else
        {
            this.m_queues.remove(queue_name);
        }        
    }
}