
import java.net.InetAddress;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

public class Settings 
{
    public static Boolean DEBUG() 
    {
        Boolean debug = false;
        
        if (System.getenv("DEBUG") != null) 
        {
            debug = Boolean.parseBoolean(System.getenv("DEBUG"));
        }
        
        return debug;
    }
    
    
    // specify whether you want to spawn one thread per connection (apache style) or want to use
    // a thread pool which removes the overhead of constantly spawning threads (nginx).
    public static Boolean USE_THREAD_POOL()
    {
        Boolean useThreadPool = false;
        
        if (System.getenv("USE_THREAD_POOL") != null) 
        {
            useThreadPool = Boolean.parseBoolean(System.getenv("USE_THREAD_POOL"));
        }
        
        return useThreadPool;
    }
    
    
    // If USE_THREAD_POOL is set to true, this specifies the size of the thread pool (how many 
    // threads will be handling connections).    
    public static int THREAD_POOL_SIZE()
    {
        int threadPoolSize = Core.GetNumCores();;
        
        if (System.getenv("USE_THREAD_POOL") != null) 
        {
            threadPoolSize = Integer.parseInt(System.getenv("USE_THREAD_POOL"));
        }
        
        return threadPoolSize;
    }
    
    
    // Specify the time in seconds that a task is allowed to be locked for before being considered
    // as having timed out and will be given to something else to take care of.
    // Atm this just results in the socket being closed, but in the future, not acking a message
    // will revert any changes made by the request.
    public static int MAX_ACK_WAIT()
    {
        int maxAckWait = 3;
        
        if (System.getenv("MAX_ACK_WAIT") != null) 
        {
            maxAckWait = Integer.parseInt(System.getenv("MAX_ACK_WAIT"));
        }
        
        return maxAckWait;
    }
    
    
    // Specify the time in seconds that a task is allowed to be locked for before being considered
    // as having timed out and will be given to something else to take care of.
    public static long MAX_LOCK_TIME()
    {
        long maxLockTime = 9;
        
        if (System.getenv("MAX_LOCK_TIME") != null) 
        {
            maxLockTime = Long.parseLong(System.getenv("MAX_LOCK_TIME"));
        }
        
        return maxLockTime;
    }
    
    
    public static int SOCKET_PORT()
    {
        int socketPort = 3901;
        
        if (System.getenv("SOCKET_PORT") != null) 
        {
            socketPort = Integer.parseInt(System.getenv("SOCKET_PORT"));
        }
        
        return socketPort;
    }
    
    
    // Optional - set this in order to specifically set the IP that this computer listens on. If not set
    // then will default to the pubic IP of this machine.
    // If using docker this needs to be 172.17.0.2 instead of the public ip of instance due to the way
    // the networking is set up.
    // WARNING - if you want this to be automatic use [null] instead of deleting it or 0 etc.
    //public static String ADDRESS = "172.17.0.2";
    public static String ADDRESS()
    {
        return System.getenv("ADDRESS");
    }
}