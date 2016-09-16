/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author stuart
 */
public class Debug 
{
    public static void println(String message)
    {
        if (Settings.DEBUG())
        {
            System.out.println(message);
        }
    }
}
