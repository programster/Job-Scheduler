
import com.google.gson.JsonObject;
import com.google.gson.JsonPrimitive;
import java.util.ArrayList;
import java.util.HashMap;



public class Task implements Comparable<Task>
{
    public static final int DEFAULT_PRIORITY = 5;
    
    // UID of this task
    private final int m_taskId;
    
    // Fast lookup list of task ids still remaining that this task is waiting to be completed.
    // value is always 1
    private HashMap<Integer, Integer> m_remainingDependencies = new HashMap<>();
    
    // Namd of this task e.g. 'Smoothen Roads'
    private final String m_name;
    
    // Json string of data to be stored about the task
    private final String m_extraInfo;
    
    // Time this Task was created.
    private final long m_creationTime; 
    
    // Time this task was locked (started processing)
    private long m_lockTime;
    
    // string of lock given to this task (must be given to unlock it)
    private String m_lock;
    
    private final int m_priority;
    
    private final String m_group;
    
    
    /**
     * Constructor for a Task to be executed.
     * @param taskId - the unique id of this task
     * @param name - a human readable name for this task
     * @param remainingDependencies - array of task ids that this task relies on having finished
     * @param extraInfo - any information to attach to the task to feed back to executors
     *                            this will help them know what they need to do (context)
     * @param priority - integer between 1 and 10 which effects ordering in queue
     * @param group - string representing a group the task belongs to. Completely optional and may
     *                 be null
     * @param extraInfo - json string of any information to go with the task. (context)
     */
    public Task(int taskId, 
                String name, 
                HashMap<Integer, Integer> remainingDependencies, 
                String extraInfo, 
                int priority,
                String group)
    {
        m_taskId                = taskId;
        m_name                  = name;
        m_creationTime          = System.currentTimeMillis() / 1000L;
        m_remainingDependencies = remainingDependencies;
        m_priority              = priority;
        m_extraInfo             = extraInfo;
        m_group                 = group;
    }
    
    
    /**
     * Mark this job as being processed. Once locked other processes shouldn't be able to touch it.
     */
    public void lock() throws Exception
    {
        if (m_lock != null)
        {
            throw new Exception("Task [" + m_taskId + "] is already locked!");
        }
        
        m_lock = Core.generateRandomString(32);
        m_lockTime = Core.time(true); //true = to the millisecond
    }
    
    
    /**
     * Tries to unlock the task with the specified lock. If successful then the lock is removed
     * and returns true, otherwise the lock remains and returns false.
     * @param String lock - a string that should be the same as this tasks lock.
     * @return $unlocked - flag indicating if this task is now unlocked
     */
    public boolean unlock(String lock)
    {
        boolean unlocked = false;
        
        if (m_lock.equalsIgnoreCase(lock))
        {
            m_lock     = null;
            unlocked   = true;
            m_lockTime = 0;
        }
        
        return unlocked;
    }
    
    
    /**
     * Removes a job from this jobs list of dependencies because it has completed.
     * @param jobId - the id of the job/task that has been completed and does not have to be waited upon
     */
    public void removeDependency(int jobId)
    {
        this.m_remainingDependencies.remove(jobId);
    }
    
    
    /**
     * Returns a flag indicating whether this job is ready to start work (all dependencies met)
     * @return boolean.
     */
    public boolean isReady()
    {
        return this.m_remainingDependencies.isEmpty();
    }
    
    
    /**
     * Convert this object into an array format that can be placed into JSON format and 
     * transferred/stored
     * @return array map of attributes/values representing this task.
     */
    public JsonObject jsonSerialize()
    {    
        JsonObject jsonForm = new JsonObject();
        
        System.out.println("name: " + m_name);
        
        jsonForm.add("id",            new JsonPrimitive(m_taskId));
        jsonForm.add("name",          new JsonPrimitive(m_name));
        jsonForm.add("creation_time", new JsonPrimitive(m_creationTime));
        jsonForm.add("priority",      new JsonPrimitive(m_priority));
        jsonForm.add("lock",          new JsonPrimitive(m_lock));
        
        if (m_group != null)
        {
            jsonForm.add("group", new JsonPrimitive(m_group));
        }
        
        jsonForm.add("extra_info",    new JsonPrimitive(m_extraInfo));
        
        return jsonForm;
    }
    
    
    
    /**
     * Give the task a 'blockage' rating. This is a float that represents how much of an impediment
     * this task is. E.g. how many other tasks will be freed up (or helped freed up) by completing
     * this task.
     * Having a higher blockage rating means that it is more important to complete the task as soon
     * as possible in order to free up other tasks to be executable.
     * @return float $blockageRating - a weight for how much of an impediment this task is to others
     * @throws Exception if Scheduler was unable to find this task in its task list 
     * (should never happen)
     */
    public float getBlockageRating()
    {
        float blockageRating = 0; // 0 means this task does not help free up any other tasks.
        
        ArrayList<Task> dependentTasks = this.getDependents();
                        
        for (Task task : dependentTasks)
        {
            // if the task has lots of dependencies, even though this task helps free it up, it
            // helps by an insignificant amount because that task has lots of dependencies
            blockageRating += (1.0 / (1 + (task.m_remainingDependencies.size()))); 
        }
        
        return blockageRating;
    }
    
    
    
    /**
     * Fetches an array list of task IDs that are dependent on this task being completed before
     * they can be run
     * @return array list of tasks dependent on this task being completed
     * @throws Exception if scheduler was unable to find this task in its list of tasks
     */
    public ArrayList<Task> getDependents()
    {
        Scheduler scheduler = Scheduler.getInstance();
        ArrayList<Integer> dependent_task_ids = scheduler.getDependencyList(m_taskId);
        
        ArrayList<Task>dependent_tasks = new ArrayList();

        //foreach (dependent_task_ids as dependent_task_id)
        for (Integer dependent_task_id : dependent_task_ids)
        {
            Task depenedentTask = null;
            
            try
            {
                depenedentTask = scheduler.getTaskFromId(dependent_task_id);
            }
            catch (Exception e)
            {
                System.out.println("Fatal exception: scheduler could not find dependent task");
                System.exit(1);
            }
            
            dependent_tasks.add(depenedentTask);
        }
        
        return dependent_tasks;
    }
    
    
    // Accessor functions
    public int      getId()           { return m_taskId; }
    public String   getName()         { return m_name; }
    public String   getLock()         { return m_lock; }
    public int      getPriority()     { return m_priority; }
    public long     getCreationTime() { return m_creationTime; }
    public long     getLockTime()     { return m_lockTime; }
    public String   getGroup()        { return m_group; }

    
    /**
     * This is the
     * @param o - the other task we want to compare to.
     * @return 
     */
    @Override
    public int compareTo(Task o) 
    {
        int result = 0;
                
        Task a = this;
        Task b = (Task)o;
                
        if (a.getPriority() > b.getPriority())
        {
            result = -1;
        }
        else if(a.getPriority() < b.getPriority())
        {
            result = 1;
        }
        else
        {
            // if priority is the same then sort by the number of tasks they will help unlock.
            if (a.getBlockageRating() > b.getBlockageRating())
            {
                result = -1;
            }
            else if (a.getBlockageRating() < b.getBlockageRating())
            {
                result = 1;
            }
            else
            {                
                // Finally sort by age as a last resort.
                // When creation time is smaller, the task is 'Older' and should go towards the head
                if (a.getCreationTime() < b.getCreationTime())
                {
                    result = -1;
                }
                else if (a.getCreationTime() > b.getCreationTime())
                {
                    result = 1;
                }
            }
        }
        
        return result;
    }
    
    
    @Override 
    public boolean equals(Object other) 
    {
        System.out.println("Running match against other object");
        boolean isEqual = false;
        
        if (other != null)
        {
            if (other == this)
            {
                isEqual = true;
            }
        }
                
        return isEqual;
    }
}
