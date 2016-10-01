
import java.io.BufferedReader;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.PrintWriter;
import java.net.Socket;

/*
 * Class to represent a single client's connection. The connection should persist whilst the 
 * client is working on any tasks. If the connection becomes lost, then we unlock any tasks
 * that were given to the client and were not marked as completed.
 */

public class SocketConnection 
{
    private boolean m_isClosed;
    private Socket m_socket;
    
    InputStream in;
    BufferedReader br;
    PrintWriter out;
    
    
    public SocketConnection(Socket socket)
    {
        m_socket = socket;
        m_isClosed = false;
        
        try
        {
            in  = m_socket.getInputStream();
            br  = new BufferedReader(new InputStreamReader(in));
            out = new PrintWriter(m_socket.getOutputStream());
            
            while (!br.ready())
            {
                try
                {
                    Thread.sleep(100);
                } 
                catch (InterruptedException ex) 
                {
                    // do nothing
                }
            }
        }
        catch (Exception e)
        {
            System.out.println("ERROR! HandlerLogic experienced IOException when handling socket");
        }
    }
    
    
    /**
     * Send a message on the socket connection.
     * @param responseString 
     */
    public void sendMessage(String responseString)
    {
        // We must use println instead of print becuase php (Normal mode not binary) 
        // requires responses to end in an endline to mark the end.
        out.println(responseString);
        out.flush();
    }
    
    
    /**
     * Read a message from the client
     * @return String - the message the client sent.
     */
    public String readMessage()
    {
        String message = "";
        
        try
        {
            message = br.readLine();
        }
        catch(Exception e)
        {
            System.out.println("Read message failed");
        }
        
        return message;
    }
    
    
    /**
     * Tells us whether there is a message waiting to be read from the buffered reader.
     * @return 
     */
    public boolean isMessageWaiting()
    {
        boolean isMessageWaiting = true;
        
        try
        {
            if (in.available() == 0)
            {
                isMessageWaiting = false;
            }
        }
        catch(Exception e)
        {
            System.out.println("isMessageWaiting failed");
        }
        
        return isMessageWaiting;
    }
    
    
    /**
     * Check if the client is still connected
     * http://stackoverflow.com/questions/1390024/how-do-i-check-if-a-socket-is-currently-connected-in-java
     * @return 
     */
    public boolean checkConnected()
    {
        boolean result = true;
        
        if (out.checkError())
        {
            result = false;
        }
        
        return result;
    }
    
    
    public synchronized void close()
    {
        System.out.println("Closing the socket");
        
        try
        {
            System.out.println("Closing socket");
            out.close();
            br.close();
            m_socket.close();
        }
        catch (Exception e)
        {
            System.out.println("Failed closing streams/socket.");
        }
        
        m_isClosed = true;
    }
    
    
    // Accessors
    public synchronized boolean isClosed() { return m_isClosed; }
}