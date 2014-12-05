
import java.net.InetAddress;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

public class Settings 
{
    // specify whether you want to spawn one thread per connection (apache style) or want to use
    // a thread pool which removes the overhead of constantly spawning threads.
    public static Boolean USE_THREAD_POOL = false;
    
    // If USE_THREAD_POOL is set to true, this specifies the size of the thread pool (how many 
    // threads will be handling connections).
    public static int THREAD_POOL_SIZE = 1;
    
    
    // Specify the time in seconds that a task is allowed to be locked for before being considered
    // as having timed out and will be given to something else to take care of.
    // Atm this just results in the socket being closed, but in the future, not acking a message
    // will revert any changes made by the request.
    public static int MAX_ACK_WAIT = 3;
    
    
    // Specify the time in seconds that a task is allowed to be locked for before being considered
    // as having timed out and will be given to something else to take care of.
    public static long MAX_LOCK_TIME = 9;
    
    
    public static int SOCKET_PORT = 3901;
    
    // Optional - set this in order to specifically set the IP that this computer listens on. If not set
    // then will default to the pubic IP of this machine.
    // If using docker this needs to be 172.17.0.2 instead of the public ip of instance due to the way
    // the networking is set up.
    // WARNING - if you want this to be automatic use [null] instead of deleting it or 0 etc.
    //public static String ADDRESS = "172.17.0.2";
    public static String ADDRESS = null;
}