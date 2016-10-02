<?php

/**
 * This singleton class automatically includes classes based on the name of the class
 * It alleviates the need of having a require_once() statement every time you want to 
 * include a certain class
 * 
 * WARNING: Autoloading is not available if using PHP in CLI INTERACTIVE mode, however using on CLI
 * scripts is fine.
 */

class Autoloader
{
    # array of all the directories where we can automatically load classes. 
    # Good idea if none of the classes in these directories shared the same class name.
    # This is populated inside the constructor.
    private static $classDirs;
    
    # Instance of this singleton class
    private static $s_instance = null; 
        
    
    /**
     * The constructor for this class. It is private because this is a singleton that should only
     * be instatiated once by itself.
     * @param void
     * @return void
    */
    private function __construct()
    {        
        # specify your model/utility/library folders here
        self::$classDirs = array(
            dirname(__FILE__)
        ); 
        
        // Specify extensions that may be loaded
        spl_autoload_extensions('.php, .class.php');
        spl_autoload_register('Autoloader::loaderCallback');
    }


    /**
     * Callback function that is passed to the spl_autoload_register. This function is run whenever
     * php is trying to find a class to load. This needs to be public for the spl_auto_loader
     * but is not meant to be called from the outside by the programmers.
     * 
     * @param className - the name of the class that we are trying to automatically load.
     * 
     * @return result - boolean indicator whether we successfully included the file or not.
     * @throws exception if we found two possible places where the class can be loaded.
     */
    public static function loaderCallback($className)
    {
        $result = false;
        
        $filename = Core::convertClassNameToFileName($className);

        foreach (self::$classDirs as $potentialFolder)
        {
            $absoluteFilePath = $potentialFolder . "/" . $filename;
            
            if (file_exists($absoluteFilePath))
            {
                # Check that we havent already managed to find a match, in which case throw an error
                if ($result)
                {
                    $errorMessage = 'Auto loader found two classes with the same name. ' .
                                    'Please manually specify, rather than rely on auto loader';
                    Core::throwException($errorMessage);
                }
                
                require_once($absoluteFilePath);
                $result = true;
                
                # do NOT break here as we want to check for and prevent potential 'class collisions'
            }
        }
        
        return $result;
    }
    
    
    /**
     * Instantiator of this singleton class. Can be called an infinite number of times without
     * wasting memory etc, but needs to be at least called once.
     * @param void
     * @return void
     */
    public static function initiate()
    {
        if (self::$s_instance == null)
        {
            self::$s_instance = new Autoloader();
        }
    }
}

Autoloader::initiate();