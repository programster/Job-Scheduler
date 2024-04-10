/*
 * This is the socket listener. Its sole responsibility is to accept socket connections and pass
 * them on to something else to handle before moving onto the next connection.
 * Do NOT handle the logic of the request before listening to the next connection.
 *
 * Messages sent to the socket listener need to consist EXACTLY of just one line JSON strings.
 */


import java.io.IOException;
import java.net.InetAddress;
import java.net.ServerSocket;
import java.net.Socket;
import java.util.ArrayList;
import java.util.Iterator;
import java.util.logging.Level;
import java.util.logging.Logger;


public class SocketListener extends Thread
{
    private ServerSocket m_socket;
    
    // if we are using a thread pool, this is what will hold them.
    private static ArrayList<Thread> s_threadHandlers;
    
    
    public SocketListener()
    {
        try
        {
            int max_waiting_connections = 100;
            
            if (Settings.ADDRESS() != null)
            {
                InetAddress bindAddr = InetAddress.getByName(Settings.ADDRESS());
                m_socket = new ServerSocket(Settings.SOCKET_PORT(), max_waiting_connections, bindAddr);
            }
            else
            {
                m_socket = new ServerSocket(Settings.SOCKET_PORT(), max_waiting_connections);
            }
        }
        catch (Exception e)
        {
            System.out.println("Failed to bind server socket");
            System.exit(1);
        }
        
        if (Settings.USE_THREAD_POOL())
        {
            s_threadHandlers = new ArrayList<>();
            
            for (int s=0; s<Settings.THREAD_POOL_SIZE(); s++)
            {
                Thread pool_thread = new SocketThreadPoolHandler(this);
                s_threadHandlers.add(pool_thread);
                pool_thread.start();
            }
        }
    }
    
    
    @Override
    public void run()
    {
        while (true)
        {
            try 
            {
                Debug.println("Waiting for a connection on port " + Settings.SOCKET_PORT() + "...");
                Socket clientSocket = m_socket.accept();
                Debug.println("Accepted a new connection.");
                
                if (Settings.USE_THREAD_POOL())
                {
                    pushSocketToThreadPoolHandler(clientSocket);
                }
                else
                {
                    // No thread pool, so one thread per socket connection
                    Thread threadHandler = new SocketConnectionHandler(clientSocket);
                    threadHandler.start();
                }
            } 
            catch (IOException ex) 
            {
                Logger.getLogger(SocketListener.class.getName()).log(Level.SEVERE, null, ex);
            }
        }
    }
    
    
    /**
     * Pushes the provided socket onto the handler in the thread pool handler that is handling the
     * least number of sockets.
     */
    private void pushSocketToThreadPoolHandler(Socket clientSocket)
    {
        int minimum = 999999999;
        boolean assignedSocket = false;
        SocketThreadPoolHandler minimumHandler = (SocketThreadPoolHandler)s_threadHandlers.get(0);
        Iterator iterator = s_threadHandlers.iterator();
        
        while (iterator.hasNext())
        {
            SocketThreadPoolHandler handler = (SocketThreadPoolHandler)iterator.next();
            
            if (handler.getSocketCount() == 0)
            {
                // Quick escape since any thread that has 0 tasks can automatically be assigned
                assignedSocket=true;
                handler.addSocket(clientSocket);
                break;
            }
            else
            {
                if (handler.getSocketCount() < minimum)
                {
                    minimum = handler.getSocketCount();
                    minimumHandler = handler;
                }
            }
        }
        
        // Check that we haven't already assigned the socket to a thread that had no work.
        if (!assignedSocket)
        {
            try
            {
                minimumHandler.addSocket(clientSocket);
            }
            catch(Exception e)
            {
                System.out.println("Somehow minimumHandler never got defined.");
            }
        }
    }
}
