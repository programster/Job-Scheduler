/*
 * This is the socket listener. Its sole responsibility is to accept socket connections and pass
 * them on to something else to handle before moving onto the next connection.
 * Do NOT handle the logic of the request before listening to the next connection.
 */


import java.io.IOException;
import java.net.InetAddress;
import java.net.ServerSocket;
import java.net.Socket;
import java.util.ArrayList;
import java.util.concurrent.BlockingQueue;
import java.util.logging.Level;
import java.util.logging.Logger;

/*
 * Messages set to the socket listener need to consist EXACTLY of just one line json strings.
 */


public class SocketListener extends Thread
{
    private static SocketListener s_instance;
    private ServerSocket m_socket;
    
    // if we are using a thread pool, this will hold all the sockets that threads will pull from
    private static BlockingQueue<Socket> s_clientSockets;
    
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
                    s_clientSockets.add(clientSocket);
                }
                else
                {
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
     * 
     * @return 
     */
    public Socket getWaitingSocket()
    {
        try
        {
            return s_clientSockets.take();
        }
        catch(Exception e)
        {
            return getWaitingSocket();
        }
    }

}
