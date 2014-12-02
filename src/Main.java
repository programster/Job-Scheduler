

class Main
{
    public static void main (String[] args)
    {
        try
        {
            String path = Core.getJarPath();
            
            if (!Core.isAlreadyRunning(path, "lock.txt"))
            {
                SocketListener socketListener = new SocketListener();
                socketListener.start();
                System.out.println("started the socket listener");
            }
            else
            {
                System.out.println("Scheduler already running so quitting.");
            }
        }
        catch(Exception e)
        {
            System.out.println("Failed to get the JAR path");
        }
    }
}
