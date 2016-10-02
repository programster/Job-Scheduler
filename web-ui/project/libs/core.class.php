<?php

/*
 * This is a library for the core functions that appear in pretty much all projects. Only these 
 * highly used functions are to be stored in the core.php library.
 * There is also the JsonResultBuilder which is a common object for sending json responses (apis)
 */

class Core
{    
    /**
     * Wrapping throw new Exception in a function so that can be used like 
     * 'or throwException(message)' in place of 'or die(message)'
     * 
     * @param message - optional message to be put in the exception.
     * 
     * @return void - throws an exception.
     */
    public static function throwException($message="") 
    { 
        global $globals;
        
        if (isset($globals['DEBUG']) && $globals['DEBUG'] == true)
        {
            ob_start();
            debug_print_backtrace();
            $stringBacktrace = '' . ob_get_clean();
            $message .= ' ' . $stringBacktrace;
        }
        
        if (!Core::isCli())
        {
            $message = nl2br($message);
        }
        
        throw new Exception($message); 
    }
    
    
    /**
     * Determines whether php is running as a CLI script or a website.
     * @param void
     * @return result (boolean) - true if CLI false if website.
     */
    public static function isCli()
    {
        $result = false;
        
        if (defined('STDIN') )
        {
            $result = true;
        }
        
        return $result;
    }
    
    
    /**
     * Function for outputting debug statments if debugging is switched on.
     * @param message - the message to be logged.
     * @return void - prints to the screen
    */
    public static function debugLog($message)
    {
        global $globals;
        
        if (isset($globals['DEBUG']) && $globals['DEBUG'] == true)
        {
            if (!Core::isCli())
            {
                $message .= '<br />';
            }
            
            echo $message . PHP_EOL;
        }
    }
    
    
    /**
     * Generates a unique id, which can be useful for javascript
     * @param prefix - optional - specify a prefix such as 'accordion' etc.
     * @return id - the 'unique' id.
     */
    public static function generateUniqueId($prefix="")
    {
        static $counter = 0;
        $counter++;
        $id = $prefix . $counter;
        return $id;
    }

    
    /**
     * Tiny helper function to help ensure that exit is always called after redirection and allows
     * the developer to only have to remember the location they want. (wont forget 'location:')
     * 
     * @param location - the location/address/url you want to redirect to.
     * 
     * @return void - redirects the user and quits.
     */
    public static function redirectUser($location)
    {
        header("location: " . $location);
        exit();
    }

    
    /**
     * Allows us to re-direct the user using javascript when headers have already been submitted.
     * 
     * @param url that we want to re-direct the user to.
     * @param numSeconds - optional integer specifying the number of seconds to delay.
     * @param message - optional message to display to the user whilst waiting to change page.
     * 
     * @return htmlString - the html to print out in order to redirect the user.
     */
    public static function javascriptRedirectUser($url, $numSeconds = 0, $message = '')
    {
        $htmlString = '';

        if ($message != '')
        {
            $htmlString .= $message . "<br><br>";
        }

        $htmlString .=
            "You are being redirected. <a href='" . $url . "'>Click here</a> If you are not " .
            "automatically redirected within " . $numSeconds . " seconds.";
            
        $htmlString .= 
            "<script type='text/javascript'>" .
                "var redirectTime=" . $numSeconds * 1000 . ";" . PHP_EOL .
                "var redirectURL='" . $url . "';" . PHP_EOL .
                'setTimeout("location.href = redirectURL;", redirectTime);' . PHP_EOL .
            "</script>";
            
        return $htmlString;
    }
    
    
    /**
     * Replaces the given string's <br> tags with newlines for textfields.
     * @param $input - the input string
     * @return output - the output string that has been converted.
     */
    public static function br2nl($input) 
    {
        //$output = preg_replace("/(\r\n|\n|\r)/", "", $input);
        $output = str_replace('<br />', PHP_EOL, $input);
        return $output;
    }
    
    
    /**
     * My own 'extended' version of nl2br which works in a lot of cases where the standard nl2br does
     * not
     * @param type $input - the input string to convert
     * @return $output - the newly converted string
     */
    public static function nl2br($input)
    {
        $output = str_replace(PHP_EOL, '<br />', $input);
        $output = str_replace('\r\n', '<br />', $output);
        return $output;
    }
    
    /**
     * Converts any newlines found to be the same as the systems newlines
     * @param $input - any string input
     * @return $output - the newly reformatted string
     */
    public static function convertNewlines($input)
    {
        $output = str_replace('\n', PHP_EOL, $input);
        $output = str_replace('\r\n', PHP_EOL, $input);
        return $output;
    }
        
    
    /**
     * Given a class name, this function will convert it to the relevant filename
     * This function could be improved to handle abstract classes later which do not follow the 
     * normal rule specified by zend. E.g. my_classAbstract.class.php compared to my_class.php
     * 
     * @param className - the specified class that we are going to convert to a filename.
     * 
     * @return filename - the name of the file that the class should be defined in.
     */
    public static function convertClassNameToFileName($className)
    {
        $appendClassSuffix = true;
        
        $chars = str_split($className);
        $fileName = '';

        foreach ($chars as $index => $char)
        {
            if (($index > 0) && (ctype_upper($char)))
            {
                $remainder = substr($className, $index);
                
                if ($remainder == 'Abstract' || $remainder == 'Interface')
                {
                    $fileName .= $remainder;
                    
                    if ($remainder == 'Interface')
                    {
                        $appendClassSuffix = false;
                    }
                    
                    break;
                }
                else
                {
                    $fileName .= '_';
                    $fileName .= strtolower($char);
                }
            }
            else
            {
                $fileName .= strtolower($char);
            }
        }

        if ($appendClassSuffix)
        {
            $fileName .= '.class';
        }
        
        $fileName .= '.php';
        
        return $fileName;
    }
    
    
    /**
     * Generates an textfield (not textarea) that can be submitted by hitting return/enter.
     * 
     * @param label - the display text to put next to the input field.
     * @param name - the name/id of the generated input field.
     * @param onSubmit - javascript string of what to run when the buttons onClick is activated.
     * @param value - an optional parameter of the default/current value to stick in the input box.
     * 
     * @return htmlString - the generated html to create this submittable row.
     */
    public static function generateSubmittableTextfield($fieldName,
                                                        $postfields  = array(),
                                                        $value       = "", 
                                                        $placeholder = '', 
                                                        $formId      = '')
    {    
        if ($formId != '')
        {
            $formId = ' id="' . $formId . '" ';
        }

        $htmlString = 
            '<form method="POST" action="" ' . $formId . '>' .
                Core::generateInputField($fieldName, 'text', $value, $placeholder) .
                Core::generateHiddenInputFields($postfields) .
                Core::generateSubmitButton('submit', $offscreen=true) .
            "</form>";

        return $htmlString;
    }
    
    
    /**
     * Generates what appears to be just a button but is actually a submittable form that will 
     * post itself or the specified postUrl. 
     * 
     * @param label      - the text to display on the button
     * @param postfields - name/value pairs of data to post when the form submits
     * @param postUrl    - optional address where the form should be posted.
     * 
     * @return html - the generated html for the button form.
     */
    public static function generateButtonForm($label, $postfields, $postUrl='')
    {
        $html = '<form method="POST" action="' . $postUrl . '">' .
                    Core::generateHiddenInputFields($postfields) .
                    Core::generateSubmitButton($label) .
                '</form>';
        
        return $html;
    }
    
    
    /**
     * Generates an textfield (not textarea) that can be submitted by hitting return/enter.
     * 
     * @param label - the display text to put next to the input field.
     * @param name - the name/id of the generated input field.
     * @param onSubmit - javascript string of what to run when the buttons onClick is activated.
     * @param value - an optional parameter of the default/current value to stick in the input box.
     * 
     * @return htmlString - the generated html to create this submittable row.
     */
    public static function generateAjaxTextfield($fieldName,
                                                 $staticData   = array(),
                                                 $currentValue = "", 
                                                 $placeholder  = '',
                                                 $offscreenSubmit = true)
    {    
        $html = 
            "<form action='' onsubmit='ajaxPostForm(this, \"" . $fieldName . "\")'>" .
                Core::generateHiddenInputFields($staticData) .
                Core::generateInputField($fieldName, 'text', $currentValue, $placeholder) .
                Core::generateSubmitButton('', $offscreenSubmit) .
            '</form>';
        
        return $html;
    }
    
    
    /**
     * Generates a button that triggers an ajax request. (using POST and expecting json response)
     * 
     * @param label     - label to appear on the ajax button. e.g. 'click me'
     * @param postData - associative array of name/value pairs to send in the ajax request.
     * @param updateButtonText - flag for whether the buttons text should change to reflect status
     * @param onSuccess - name of javascript function to run upon successful request
     * @param onError   - name of javascript function to run if there was an ajax comms error.
     * @param onAlways  - name of javascript function to run if success or error.
     * 
     * @return html - the generated html for the ajax button.
    */
    public static function generateAjaxButton($label, 
                                              $postData, 
                                              $updateButtonText=true,
                                              $onSuccess = '', 
                                              $onError   = '', 
                                              $onAlways  = '')
    {
        $ajaxParams = array('data'     => $postData,
                            'type'     => 'POST',
                            'dataType' => $postData);
        
        $callbacks = '';
        
        if ($updateButtonText)
        {  
            $callbacks .= 
                'var originalText = this.value;' . PHP_EOL .
                'this.value = "Updating...";' . PHP_EOL .
                'ajaxRequest.fail(function(){this.value="Error"});' . PHP_EOL;
                'ajaxRequest.done(function(){this.value="Complete"});' . PHP_EOL;
                'var timeoutFunc = function(){this.value=originalText};' . PHP_EOL .
                'ajaxRequest.done(function(){setTimeout(timeoutFunc, 2000)});' . PHP_EOL;
        }
        
        if ($onSuccess != '')
        {
            $callbacks .= 'ajaxRequest.done(' . $onSuccess . ');' . PHP_EOL;
        }
        
        if ($onError != '')
        {
            $callbacks .= 'ajaxRequest.fail(' . $onError . ');' . PHP_EOL;
        }
        
        if ($onAlways != '')
        {
            $callbacks .= 'ajaxRequest.always(' . $onAlways . ');' . PHP_EOL;
        }
        
        # Important that only double quotes appear within onclick and no single quotes.
        $onclick = 
            'var ajaxUrl     = "ajax_handler.php";' . PHP_EOL .
            'var ajaxParams  = ' . json_encode($ajaxParams) . ';' . PHP_EOL .
            'var ajaxRequest = $.ajax(ajaxUrl, ajaxParams);' .  PHP_EOL .
            $callbacks;
        
        # Have to use an 'input type=button' rather than button here because we want to change  
        # value property
        $html = "<input type='button' value='" . $label . "' onclick='" . $onclick . "' />";
        
        return $html;
    }
    
    
    /**
     * Generates the SET part of a mysql query with the provided name/value pairs provided
     * @param pairs - assoc array of name/value pairs to go in mysql
     * @return query - the generated query string that can be appended.
     */
    public static function generateMysqlPairs($pairs)
    {
        $query = '';
        
        foreach ($pairs as $name => $value)
        {
            $query .= "`" . $name . "`='" . $value . "', ";
        }
        
        $query = substr($query, 0, -2); # remove the last comma.
        return $query;
    }
    
    
    /**
     * Generates the Select as section for a mysql query (but does not include SELECT) directly.
     * example: $query = "SELECT " . generateSelectAs($my_columns) . ' WHERE 1';
     * @param type $columns - map of sql column names to the new names
     * @return string - the genereted query section
     */
    public static function generateSelectAsPairs($columns)
    {
        $query = '';
        
        foreach($columns as $column_name => $new_name)
        {
            $query .= '`' . $column_name . '` AS "' . $new_name . '", ';
        }
        
        $query = substr($query, 0, -2);
        
        return $query;
    }
    
    
    /**
    * Checks to see if the string in $haystack begins with $needle.
    * 
    * @param haystack         - the string to search in.
    * @param needle           - the string to look for
    * @param caseSensitive    - whether to enforce case sensitivity or not (default true)
    * @param ignoreWhiteSpace - whether to ignore white space at the ends of the inputs
    * functionfunction
    * @return result - true if the haystack begins with the provided string. False otherwise.
    */
    public static function startsWith($haystack, 
                                      $needle, 
                                      $caseSensitive = true, 
                                      $ignoreWhiteSpace = false)
    {       
        $result = false;
        
        if ($caseSensitive == false) //Reduce to lower case if required.
        {
            $haystack = strtolower($haystack);
            $needle = strtolower($needle);
        }
        
        if ($ignoreWhiteSpace)
        {
            $haystack = trim($haystack);
            $needle = trim($needle);
        }
        
        if (strpos($haystack, $needle) === 0)
        {
            $result = true;
        }
        
        return $result;
    }
    
    
    /**
    * Checks to see if the string in $haystack ends with $needle.
    * 
    * @param haystack         - the string to search in.
    * @param needle           - the string to look for
    * @param caseSensitive    - whether to enforce case sensitivity or not (default true)
    * @param ignoreWhiteSpace - whether to ignore white space at the ends of the inputs
    * 
    * @return true if haystack begins with the provided string.  False otherwise.
    */
    public static function endsWith($haystack, 
                                    $needle, 
                                    $caseSensitive = true, 
                                    $ignoreWhiteSpace = false)
    {
        //Reverse our input vars
        $revHaystack = strrev($haystack);
        $revNeedle = strrev($needle);
        
        return Core::startsWith($revHaystack, $revNeedle, $caseSensitive, $ignoreWhiteSpace);
    }
    
    
    /**
     * Generates the source link for the latest jquery source so that you dont have to remember 
     * it, or store it locally on your server and keep updating it.
     * @param void
     * @return html - the html for including jquery ui in your website.
     */
    public static function generateJqueryInclude()
    {
        # This does not fetch version 1 but the latest 1.x version of jquery.
        $html = '<script type="text/javascript" ' .
                    'src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js">' .
                '</script>';
        return $html;
    }
    
    
    /**
     * Generates the source link for the latest jquery ui source so that you dont have to remember 
     * it, or store it locally on your server and keep updating it.
     * @param void
     * @return html - the html for including jquery ui in your website.
     */
    public static function generateJqueryUiInclude()
    {
        $html = '<script type="text/javascript" ' .
                    'src="http://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js" >' .
                '</script>';
        return $html;
    }

    
    /**
     * Generates the html for a hidden input field. This allows us to easily POST variables rather 
     * than using GET everywhere.
     * 
     * @param name - the neame of the variable we are trying to send.
     * @param value - the value of the variable we are posting.
     * 
     * @return the generated html for a hidden input field.
     */
    public static function generateHiddenInputField($name, $value)
    {
        return "<input type='hidden' name='" . $name . "' value='" . $value . "' />";
    }
    
    
    
    /**
     * Given an array of name/value pairs, this will generate all the hidden input fields for them
     * to be inserted into a form.
     * @param pairs - assoc array of name/value pairs to post
     * @return html - the generated html.
     */
    public static function generateHiddenInputFields($pairs)
    {
        $html = '';
        
        foreach ($pairs as $name => $value)
        {
            $html .= Core::generateHiddenInputField($name, $value);
        }
        
        return $html;
    }
    
    
    /**
    * Generates a html input field
    *
    * @param name - the name of the input field so we can retrieve the value with GET or POST
    * @param type - the type of the input field. e.g. text, password, checkbox, radio
     *              should not be 'submit' or 'button', use other funcs for those
    * @param currentValue - optional, if the textfield has a value already use that
    * @param placeholder - optional - specify the placeholder text
    * 
    * @return html - the generated html.
    */
    public static function generateInputField($name, $type, $currentValue="", $placeholder="")
    {
        $type = strtolower($type);
        
        if ($type === 'button')
        {
            Core::throwException('Developer error: please use the generateButton function ' . 
                                 'instead to create buttons.');
        }
        
        if ($type == 'submit')
        {
            Core::throwException('Developer error: please use the generateSubmitButton function ' . 
                                 'instead to create submit buttons.');
        }
                
        $html = '<input ' .
                    'type="' . $type . '" ' .
                    'name="' . $name .'" ' . 
                    'placeholder="' . $placeholder . '" ' . 
                    'value="' . $currentValue . '" >';

        return $html;
    }
    
    
    /**
     * Generates a textaread form element
     * 
     * @param name          - the name of the textarea ( for reitrieving from POST)
     * @param currentValue  - any text that should appear in the texatrea
     * @param placeholder   - any text that should show in the textarea if there is no value
     * @param class         - the class to specify for the textarea (stylesheet)
     * @param rows          - the number of rows the textarea should have
     * @param cols          - the number of columns (width) the textarea should have
     * @param id            - if set the id will be set to this.
     * 
     * @return html - the generated html for the textarea. 
     */
    public static function generateTextArea($name, 
                                            $currentValue = "", 
                                            $placeholder  = "", 
                                            $class        = "",
                                            $rows         = "",
                                            $cols         = "",
                                            $id           = "",
                                            $disabled     = false)
    {
        $idAttribute = '';
        $rowAttribute = '';
        $colsAttribute = '';
        $disabledAttribute = '';
        
        if ($rows != "")
        {
            $rowAttribute = ' rows="' . $rows . '" ';
        }
        
        if ($cols != "")
        {
            $colsAttribute = ' cols="' . $cols . '" ';
        }
        
        
        if ($id != "")
        {
            $idAttribute = ' id="' . $id . '" ';
        }
        
        if ($disabled)
        {
            $disabledAttribute = ' disabled ';
        }
        
        $html = '<textarea ' .
                    'name="' . $name . '" ' .
                    'class="' . $class . '" ' .
                    'placeholder="' . $placeholder . '" ' .
                    $idAttribute .
                    $rowAttribute .
                    $colsAttribute .
                    $disabledAttribute .
                '>'  . 
                    $currentValue . 
                '</textarea>';
        
        return $html;
    }

    
    /**
     * Generates a submit button for a form.
     * 
     * @param label - The text that will be displayed over the button
     * @param offscreen - render the submit button offscreen so that it does not appear within the
     *                    form, but allows the form to be submitted by hitting enter. Setting
     *                    display:none would work in FF but not chrome
     * 
     * @return html - The html code for the button
     */
    public static function generateSubmitButton($label="Submit", $offscreen=false)
    {
        $styleAttribute = '';
        
        if ($offscreen)
        {
            $styleAttribute = ' style="position: absolute; left: -9999px" ';
        }

        $html = '<input ' .
                    'type="submit" ' .
                    'value="' . $label . '" ' .
                     $styleAttribute . 
                '/>'; 
        
        return $html;
    }
    
    
    /**
     * Generates the html for a button which runs the provided javascript functionName when clicked.
     * This is a button element e.g. <button> and NOT an input type='button'
     * There are subtle differences, but the main one is that the text for a button is NOT changed
     * by changingt the .value but the .textContent attribute, and input type buttons are supposed
     * to be inside a form and will submit data with the form. Both can have an onclick.
     * 
     * @param name         - label to stick on the button, (what the user can see).
     * @param functionName - callback function to run when the button is clicked.
     * @param parameters  - an array of parameters to pass to the callback function.
     * @param confirm      - whether the user needs to confirm that they meant to click the button.
     * @param confMessage  - if confirm set to true, the confirmation message as it will appear.
     * 
     * @return htmlString - the html for the button.
     */
    public static function generateButton($label, 
                                          $functionName, 
                                          $parameters  = array(), 
                                          $confirm     = false, 
                                          $confMessage = "")
    {
        $parameterString = "";

        if (count($parameters) > 0)
        {
            foreach ($parameters as $parameter)
            {
                $literals = array('this', 'true', 'false');

                $lowerCaseParam = strtolower($parameter);

                # Handle special case where we want to pass the `this`, 'true' or 'false'.
                if (in_array($lowerCaseParam, $literals))
                {
                    $parameterString .= $parameter . ", ";
                }
                else
                {
                    $parameterString .= "'" . $parameter . "', ";
                }

            } 

            // Remove the last character which should be an excess ,
            $parameterString = substr($parameterString, 0, -2);
        }

        $onclick = $functionName . "(" . $parameterString . ")";

        if ($confirm)
        {
            $onclick = "if (confirm('" . $confMessage . "')){" . $onclick . "}";
        }

        $onclick = '"' . $onclick . '"';
        $htmlString = '<button onclick=' . $onclick . '>' . $label . '</button>';
        return $htmlString;
    }
   
   
   
   /**
    * Effectively generates a normal link, but rendered as a button. e.g <a href=''>; The main 
    * advantage is that you can specify a confirm message or create a better/different look.
    * Note that this will be a form submit button and not a javascript button.
    * 
    * @param label       - the label to appear on the button.
    * @param location    - where you want the link to go
    * @param confirm     - set to true if you want a confirm dialogue to confirm.
    * @param confMessage - if confirm set to true, this will be the message that is displayed.
    * 
    * @return html - the generated html for the button.
    */
    public static function createButtonLink($label, $location, $confirm=false, $confMessage="")
    {
        $confirmAttribute = "";

        if ($confirm)
        {
            $onclick = "return confirm('" . $confMessage . "')";
            $confirmAttribute = 'onsubmit="' . $onclick . '"';
        }

        $html = 
            '<form method="post" action="' . $location . '" ' . $confirmAttribute . '>' .
                self::generateSubmitButton($label) .
            '</form>';

        return $html;
    }
    
    
    /**
     * Generates an input field row with a label beside it (making placeholder usage pointless).
     * This is useful for when you are displaying input fields with existing values. When this is 
     * the case, placeholders would not be visible, thus useless, but the user still needs to know 
     * what the fields represent.
     * 
     * @param name  - the name of the fild (name we use to get value from GET / POST)
     * @param type  - text, password, submit
     * @param label - the human readable name to display next to the input field.
     * @param value - the current value of the input field.
     * 
     * @return html - the generated html
     */
    public static function generateInputFieldRow($name, $type, $label, $value="")
    {
        $html =
            "<div class ='row'>" .
                "<div class='label'>" . $label . "</div>" .
                "<div class='inputs'>" .
                    Core::generateInputField($name, $type, $value) .
                "</div>" .
            "</div>";

        return $html;
    }

    
    /**
     * Returns true or false based on whether the provided array is an associative array or not.
     * note that this will return true if it is integer based but they index does not start at 0.
     * 
     * @param arr - the array to check
     * 
     * @return true if the array is associative, false otherwise.
     */
    public static function isAssoc($arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
    
  
    /**
     * Generates an html drop down menu for forms. If the array of drop down options passed in is
     * an array, then the value posted will be the key, and the display label for the option will be
     * the value.
     *
     * @param name           - name to assign to the input field (the lookup name when retrieving 
     *                          POST)
     * @param currentValue   - the current/default/selected value of that attribute.
     * @param options        - array of all the possible options/values that the user can pick
     *                          if this is an associative array, the key will be used as the value.
     * @param rowSize        - manually set the number of rows to show
     * @param multipleSelect - optional - set true if user should be able to select multiple values.
     * @param onChange       - specify javascript that should run when the dropdown changes. Note 
     *                          that this should not contain the text onchange= and if quotes are 
     *                          used (for js function parameters, then these should be encapsulated
     *                          in double quotes.
     * @param id             - (optional) set an id for the dropdown menu.
     *
     * @return htmlString - the generated html to be put on the page.
     */
    public static function generateDropDownMenu($name, 
                                                $currentValue, 
                                                $options, 
                                                $rowSize        = 1,
                                                $multipleSelect = false,
                                                $onChange       = "", 
                                                $id             = "")
    {
        $isAssoc = self::isAssoc($options);

        $optionsHtml = "";

        foreach ($options as $key => $option)
        {   
            $optionValue = $option;
            $optionLabel = $option;

            if ($isAssoc)
            {
                $optionValue = $key;
            }

            $selectedAttribute = "";

            if ($optionValue == $currentValue)
            {
                $selectedAttribute = " selected='true' ";
            }

            $optionsHtml .= "<option " . $selectedAttribute . 
                                'value="' . $optionValue . '"' .
                            ">" . 
                                $optionLabel . 
                            "</option>" . PHP_EOL;
        }

        $nameAttribute      = " name='" . $name . "' ";
        $idAttribute = "";
        
        if ($id != "")
        {
            $idAttribute = " id='" . $name . "' ";
        }
        
        $sizeAttribute      = " size='" . $rowSize . "' ";
        $onChangeAttribute  = "";

        if ($onChange != "")
        {
            $onChangeAttribute = " onchange='" . $onChange . "' ";
        }

        $multipleAttribute  = "";

        if ($multipleSelect)
        {   
            $multipleAttribute = " multiple ";
        }

        $htmlString = 
            "<select" .
                $idAttribute . 
                $nameAttribute . 
                $sizeAttribute . 
                $onChangeAttribute . 
                $multipleAttribute . 
            ">" . 
                $optionsHtml . 
            "</select>";

        return $htmlString;
    }

    
    # Thse are here because they 'belong' to the function below
    const PASSWORD_DISABLE_LOWER_CASE    = 2;
    const PASSWORD_DISABLE_UPPER_CASE    = 4;
    const PASSWORD_DISABLE_NUMBERS       = 8;
    const PASSWORD_DISABLE_SPECIAL_CHARS = 16;

    /**
     * Generates a random string. This can be useful for password generation or to create a 
     * single-use token for the user to do something (e.g. click an email link to register).
     * those settings and copying it to the users clipboard as well as returning it.
     * 
     * @param numberOfChars - how many characters long the string should be
     * @param char_options - any optional bitwise parameters to disable default behaviour:
     *          PASSWORD_DISABLE_LOWER_CASE
     *          PASSWORD_DISABLE_UPPER_CASE
     *          PASSWORD_DISABLE_NUMBERS
     *          PASSWORD_DISABLE_SPECIAL_CHARS
     *
     * @return token - the generated string
     */
    public static function generateRandomString($numberOfChars, $char_options=0)
    {
        $userLowerCase   = !($char_options & self::PASSWORD_DISABLE_LOWER_CASE);
        $useUppercase    = !($char_options & self::PASSWORD_DISABLE_UPPER_CASE); 
        $useNumbers      = !($char_options & self::PASSWORD_DISABLE_NUMBERS);
        $useSpecialChars = !($char_options & self::PASSWORD_DISABLE_SPECIAL_CHARS);

        $lowerCase = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q',
                           'r','s','t','u','v','w','x','y','z');

        $numbers = array('0', '1','2','3','4','5','6','7','8','9');

        $capitalLetters = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q',
                                'R','S','T','U','V','W','X','Y','Z');

        $specialChars = array('!','@','#','$','%','^','&','*','(',')','{','}','[',']','+','-','/',
                              '_');


        $possibleChars = array();

        if ($userLowerCase)
        {
            $possibleChars = array_merge($possibleChars, $lowerCase);
            $requirements['lower_case'] = $lowerCase;
        }

        if ($useUppercase)
        {
            $possibleChars = array_merge($possibleChars, $capitalLetters);
            $requirements['capitals'] = $capitalLetters;
        }

        if ($useNumbers)
        {
            $possibleChars = array_merge($possibleChars, $numbers);
            $requirements['numbers'] = $numbers;
        }

        if ($useSpecialChars)
        {
            $possibleChars = array_merge($possibleChars, $specialChars);
            $requirements['special_characters'] = $specialChars;
        }

        $acceptableToken = false;

        while (!$acceptableToken)
        {
            $outstandingRequirements = $requirements; #copy the array
            $token = '';
            $acceptableToken = true;
            $maxPossibleCharIndex = count($possibleChars) - 1;
            
            for ($s=0; $s<$numberOfChars; $s++)
            {
                $token .= $possibleChars[rand(0, $maxPossibleCharIndex)];
            }

            $stringArray = str_split($token);
            
            foreach ($stringArray as $character)
            {
                if (count($outstandingRequirements) > 0) # must recalculate each time.
                {
                    foreach ($outstandingRequirements as $name => $arrayOfChars)
                    {
                        if (array_search($character, $arrayOfChars) !== FALSE)
                        {
                            unset($outstandingRequirements[$name]);
                            break;
                        }
                    }           
                }
                else
                {
                    # Stop parsing the token as soon as all required chars found
                    break;
                }
            }

            if (count($outstandingRequirements) != 0)
            {
                $acceptableToken = false;
            }
        }

        return $token;
    }
        

    /**
     * Sends an api request through the use of CURL
     * 
     * @param url - the url where the api is located. e.g. technostu.com/api
     * @param parameters - associative array of name value pairs for sending to the api server.
     * 
     * @return ret - array formed from decoding json message retrieved from xml api
     */
    public static function sendApiRequest($url, $parameters)
    {
        global $globals;
      
        $query_string = http_build_query($parameters, '', '&');
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        
        $jsondata = curl_exec($ch);
        if (curl_error($ch))
        {
            self::throwException("Connection Error: " . curl_errno($ch) . ' - ' . curl_error($ch));
        }
        
        curl_close($ch);
        $ret = json_decode($jsondata); # Decode JSON String
        
        if ($ret == null)
        {
            Core::throwException('Recieved a non json response from API: ' . $jsondata);
        }
        
        return $ret;
    }



    /**
     * This is the socket "equivalent" to the sendApiRequest function. However unlike
     * that funciton it does not require the curl library to be installed, and will try to
     * send/recieve information over a direct socket connection.
     *
     * @param Array $request - map of name/value pairs to send.
     * @param string $host - the host wish to send the request to.
     * @param int $port - the port number to make the connection on.
     * @param int $bufferSize - optionally define the size (num chars/bytes) of the buffer. If this
     *                     is too small your information can get cut off, causing errors.
     *                     10485760 = 10 MiB
     * @param int $timeout - (optional, default 2) the number of seconds before connection attempt 
     *                       times out.
     * @param int $attempts_limit - (optional, default 5) the number of failed connection attempts to 
     *                         make before giving up.
     * @return Array - the response from the api in name/value pairs.
     */
    public static function sendTcpRequest($host, 
                                          $port, 
                                          $request, 
                                          $bufferSize=10485760, 
                                          $timeout=2, 
                                          $attempts_limit=5)
    {
        $request_string = json_encode($request) . PHP_EOL;
        
        $protocol = getprotobyname('tcp');
        $socket = socket_create(AF_INET, SOCK_STREAM, $protocol);
        
        # stream_set_timeout DOES NOT work for sockets created with socket_create or socket_accept.
        # http://www.php.net/manual/en/function.stream-set-timeout.php
        $socket_timout_spec = array(
            'sec'  => $timeout,
            'usec' => 0
        );
        
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, $socket_timout_spec);
        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, $socket_timout_spec);
                
        $attempts_made = 0;
        
        do
        {
            $connected = socket_connect($socket, $host, $port);
            $socket_error_code = socket_last_error($socket);
            $socket_error_string = socket_strerror($socket_error_code);
            
            if (!$connected && $attempts_made == $attempts_limit)
            {
                $errorMsg = "Failed to make socket connection to [" . $host . ":" . $port . "]: " . PHP_EOL .
                            "Socket error: [" . $socket_error_string . ']';
                
                Core::throwException($errorMsg);
            }
            
            # socket_last_error does not clear the last error after having fetched it, have to do 
            # this manually
            socket_clear_error();
            $attempts_made++;
        } while (!$connected); # 110 = timeout error code

        
        /* @var $socket Socket */
        $wroteBytes = socket_write($socket, $request_string, strlen($request_string));
        
        if ($wroteBytes === false)
        {
            Core::throwException('Failed to write request to socket.');
        }
        
        # PHP_NORMAL_READ indicates end reading on newline
        $serverMessage = socket_read($socket, $bufferSize, PHP_NORMAL_READ);
        $response = json_decode($serverMessage, $arrayForm=true);
        
        return $response;
    }


    
    /**
     * Fetches the specified list of arguments from $_REQUEST. This will return false if any 
     * parameters could not be found.
     * 
     * @param args - array of all the argument names.
     * 
     * @return result - false if any parameters could not be found.
     */
    public static function fetchRequiredArguments($args)
    {
        $values = self::fetchRequiredArgumentsFromArray($args, $_REQUEST);
        return $values;
    }
    
    
    /**
     * Fetches the specified list of arguments from $_REQUEST. This will return false if any 
     * parameters could not be found.
     * 
     * @param array $args - array of all the argument names.
     * @param array $inputArray - array from which we are pulling the required args.
     * 
     * @return result - false if any parameters could not be found.
     */
    public static function fetchRequiredArgumentsFromArray($args, $inputArray)
    {
        $values = array();

        foreach ($args as $arg)
        {
            if (isset($inputArray[$arg]))
            {
                $values[$arg] = mysql_escape_string($inputArray[$arg]);
            }
            else
            {
                self::throwException("Required parameter: " . $arg . " not specified");
                break;
            }
        }

        return $values;
    }
    
    
    /**
     * Fetches as many of the specified list of arguments from $_REQUEST that it can retrieve.
     * This will NOT throw an exception or return false if it fails to find one.
     * @param args - array of all the argument names.
     * @return values - array of retrieved values
     */
    public static function fetchOptionalArguments($args)
    {
        $values = self::fetchOptionalArgumentsFromArray($args, $_REQUEST);
        return $values;
    }
    
    
    /**
     * Fetches as many of the specified list of arguments from $_REQUEST that it can retrieve.
     * This will NOT throw an exception or return false if it fails to find one.
     * 
     * @param array $args - array of all the argument names.
     * @param array $inputArray - array from which we are pulling the optional args.
     * 
     * @return values - array of retrieved values
     */
    public static function fetchOptionalArgumentsFromArray($args, $inputArray)
    {
        $values = array();

        foreach ($args as $arg)
        {
            if (isset($inputArray[$arg]))
            {
                $values[$arg] = mysql_escape_string($inputArray[$arg]);
            }
        }

        return $values;
    }
    
    
    /**
     * Retrieves the specified arguments from REQUEST. This will throw an exception if a required
     * argument is not present, but not if an optional argument is not.
     * 
     * @param reqArgs - array list of required arguments that must exist
     * @param optionalArgs - array list of arguments that should be retrieved if present.
     * 
     * @return values - map of argument name/value pairs retrieved.
     */
    public static function fetchArguments($reqArgs, $optionalArgs)
    {
        $values = self::fetchRequiredArguments($reqArgs);
        $values = array_merge($values, self::fetchOptionalArguments($optionalArgs));
        return $values;
    }
    
    
    
    /**
     * Builds url of the current page, excluding any ?=&stuff,   
     * @param void
     * @return pageURL - full page url of the current page e.g. https://www.google.com/some-page
     */
    public static function getCurrentUrl() 
    {
        $pageURL = 'http';
        
        if (isset($_SERVER["HTTPS"])) 
        {
            $pageURL .= "s";
        }
        
        $pageURL .= "://";

        if ($_SERVER["SERVER_PORT"] != "80") 
        {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . 
                        $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        } 
        else 
        {
            $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        }

        return $pageURL;
    }
    
    
    /**
     * Ensures that a given value is within the given range and if not, moves it to the boundary.
     * Note that this can work for objects if you install the following extension:
     * http://pecl.php.net/package/operator
     * 
     * @param mixed $value - the variable to make sure is within range.
     * @param mixed $max   - the max allowed value.
     * @param mixed $min   - the minimum allowed value
     * 
     * @return $value - the clamped input.
     */
    public static function clampValue($value, $max, $min)
    {        
        if ($value > $max)
        {
            $value = $max;
        }
        elseif ($value < $min)
        {
            $value = $min;
        }

        return $value;
    }
    
    
    /**
     * Safely retrieve variables from POST or GET. This needs to protect from injection attacks etc. 
     * 
     * @param varName - the name of the variable that was posted.
     * @param extraParams - extra parameters such as whether to 
     * 
     * @return variable - the safely retrieved value
     */
    public static function safelyGet($varName, $extraParams=array())
    {
        if (!isset($_REQUEST[$varName]))
        {
            self::throwException("Could not get variable:" . $varName);
        }

        $variable = $_REQUEST[$varName];

        if (isset($extraParams['urldecode']) && $extraParams['urldecode'] == true)
        {
            $variable = urldecode($variable);
        }

        $variable = stripslashes($variable);
        $variable = strip_tags($variable);
        $variable = mysql_escape_string($variable);
        
        return $variable;
    }
    
    
    /**
     * Script function (not for websits) Fetches the password from the shell without it being 
     * displayed whilst being typed. Only works on *nix systems and requires shell_exec and stty.
     * 
     * @param stars - (optional) set to false to stop outputting stars as user types password. This 
     *                prevents onlookers seeing the password length but does make more difficult.
     * 
     * @return string - the password that was typed in. (any text entered before hitting return)
     */
    public static function getPasswordFromUserInput($stars = true)
    {
        // Get current style
        $oldStyle = shell_exec('stty -g');

        if ($stars === false) 
        {
            shell_exec('stty -echo');
            $password = rtrim(fgets(STDIN), "\n");
        } 
        else 
        {
            shell_exec('stty -icanon -echo min 1 time 0');

            $password = '';
            while (true) 
            {
                $char = fgetc(STDIN);

                if ($char === "\n") 
                {
                    break;
                } 
                else if (ord($char) === 127) 
                {
                    if (strlen($password) > 0) 
                    {
                        fwrite(STDOUT, "\x08 \x08");
                        $password = substr($password, 0, -1);
                    }
                } 
                else 
                {
                    fwrite(STDOUT, "*");
                    $password .= $char;
                }
            }
        }

        // Reset old style
        shell_exec('stty ' . $oldStyle);
        print PHP_EOL;

        // Return the password
        return $password;
    }
    
    
    /**
     * Calculates the hostname including the starting http:// or https:// at the beginning. This is 
     * useful for linking items by relative source and getting around htaccess url rewrites. I 
     * believe php 5.3 has gethostname() function but our server is centos php 5.2
     * 
     * @param void
     * 
     * @return hostname - sting of url e.g. 'http://www.technostu.com'
    */
    public static function getHostname()
    {
        $hostname = $_SERVER['HTTP_HOST']; 

        if (isset($_SERVER['HTTPS']))
        {
            $hostname = 'https://' . $hostname;
        }
        else
        {
            $hostname = 'http://' . $hostname;
        }

        return $hostname;
    }

    
    /**
     * Retrieves an array list of files/folders within the specified directory.
     * Consider using the following instead:
     * $directory = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::CURRENT_AS_SELF);
     * $iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::CHILD_FIRST);
     * 
     * @param directoryPath - the path to the directory you wisht to find the contents of.
     * @param recursive     - whether we go through into each subfolder and retrieve its contents.
     * @param includePath   - whether we output the path to the entry such as '/folder/text.txt' 
     *                        instead of just 'text.txt'
     * @param onlyFiles     - whether we include the directory itself in the returned list
     * 
     * @return fileNames - the names of all the files/folders within the directory.
     */
    public static function getDirectoryContents($dir, 
                                                $recursive   = true, 
                                                $includePath = true, 
                                                $onlyFiles   = true)
    {
        $fileNames = array();
        $fpath     = realpath($dir);
        $handle    = opendir($dir);

        if ($handle)
        {
            while (false !== ($fileName = readdir($handle)))
            {
                if (strcmp($fileName,"..")!=0 && strcmp($fileName,".")!=0)
                {
                    if (is_dir($fpath . "/" . $fileName))
                    {
                        if (!$onlyFiles)
                        {
                            if ($includePath)
                            {
                                $fileNames[] = $fpath . "/" . $fileName;
                            }
                            else
                            {
                                $fileNames[] = $fileName;
                            }
                        }

                        if ($recursive)
                        {
                            $subFiles = self::getDirectoryContents($fpath . "/" . $fileName, 
                                                                   $recursive, 
                                                                   $includePath, 
                                                                   $onlyFiles);

                            $fileNames = array_merge($fileNames, $subFiles);
                        }
                    }
                    else
                    {
                        if ($includePath)
                        {
                            $fileNames[] = $fpath . "/" . $fileName;
                        }
                        else
                        {
                            $fileNames[] = $fileName;
                        }
                    }
                }
            }

            closedir($handle);
        }

        return $fileNames;
    }


    /**
     * Generates a string of yes or no based on the input variable.
     * Note that this will consider string 0 or a 0 integer as 'false' values.
     * @param input - the input variable to decide whether to output yes/no on.
     * @return result - string of 'Yes' or 'No'
     */
    public static function generateYesNoString($input)
    {
        $result = 'Yes';
        
        if ($input == "0" || $input == 0 || $input == false)
        {
            $result = 'No';
        }
        
        return $result;
    }
    
    
    /**
     * Generates a string 'True' or 'False' based on whether the value passed in.
     * Note that this will consider string 0 or a 0 integer as 'false' values.
     * @param input - the input variable to decide whether to output true or false on.
     * @return 
     */
    public static function generateTrueFalseString($input)
    {
        $result = 'True';
        
        if ($input == "0" || $input == 0 || $input == false)
        {
            $result = 'False';
        }
        
        return $result;
    }
    
    
    /**
     * Sets a variable to the sepcified default if it is not set within the $_REQUEST superglobal.
     * You can think of this as overriding the default if it is set in the $_REQUEST superglobal. 
     * 
     * @param variableName - the name of the variable if it would appear within the $_REQUEST
     * @param defaultValue - the value to set if the var is not set within $_REQUEST
     * 
     * @return returnVar - the calculated resulting value. (default value if not set) 
     */
    public static function overrideIfSet($variableName, $defaultValue)
    {
        $returnVar = $defaultValue;
        
        if (isset($_REQUEST[$variableName]))
        {
            $returnVar = $_REQUEST[$variableName];
        }
        
        return $returnVar;
    }
    
    
    /**
     * Implement a version guard. This will throw an exception if we do not have the required
     * version of PHP that is specified.
     * @param String $requiredVersion - required version of php, e.g '5.4.0'
     * @return void - throws an exception if we do not meet the required php version.
     */
    public static function versionGuard($requiredVersion, $errorMessage='')
    {
        if (version_compare(PHP_VERSION, $requiredVersion) == -1) 
        {
            if ($errorMessage == '')
            {
                $errorMessage = 'Required PHP version: ' . $requiredVersion . 
                                ', current Version: ' . PHP_VERSION;    
            }
            
            die($errorMessage); 
        }
    }

    
    /**
     * Deletes a directory even if it is not already empty. This resolves the issue with
     * trying to use unlink on a non-empty dir.
     * @param String $dir - the path to the directory you wish to delete
     * @return void - changes your filesystem
     */
    public static function deleteNonEmptyDir($dir) 
    {
        if (is_dir($dir)) 
        {
            $objects = scandir($dir);
        
            foreach ($objects as $object) 
            {
                if ($object != "." && $object != "..") 
                {
                    if (filetype($dir . "/" . $object) == "dir")
                    {
                        self::deleteNonEmptyDir($dir . "/" . $object); 
                    }
                    else
                    {
                        unlink($dir . "/" . $object);
                    }
                }
            }
        
            reset($objects);
            rmdir($dir);
        }
    }
    
    
    /**
     * Fetches what this computers IP address is. Please note that you may wish to run getPublicIp 
     * instead which may return a different IP address depending on your network.
     * @param string $interface - the network interface that we want the IP of, defaults to eth0
     * @return string - The ip of this machine on that interface. Will be empty if there is no IP.
     */
    public static function getIp($interface = 'eth0')
    {
        $command = 
            'ifconfig ' . $interface . ' | ' .
            'grep "inet addr" |  ' .
            'awk \'{print $2;}\' | ' .
            'cut -d : -f 2';
        
        $result = shell_exec($command);
        return $result;
    }
    
    
    /**
     * Determines what this computers public IP address is (this is not necessarily the IP address 
     * of the computer, and you may need to setup port forwarding.
     * This is a very quick and dirty method that relies on icanhazip.com remaining the same so use 
     * with  caution.
     * @param void
     * @return string $ip - the public ip address of this computer.
     */
    public static function getPublicIp()
    {
        $ip = file_get_contents('http://icanhazip.com/');
        $ip = trim($ip);
        return $ip;
    }
    
    
    /**
     * Checks to see if the specified port is open.
     * @param int $portNumber - the number of the port to check
     * @param $host - optional - the host to check against. Good for testing not just our outbound
     *                           but their inbound. If not specified just checking our own public IP
     * @return $isOpen - true if port is open, false if not.
     */
    public static function isPortOpen($portNumber, $protocol, $host='')
    {
        $protocol = strtolower($protocol);
        
        if ($protocol != 'tcp' && $protocol != 'udp')
        {
            $errMsg = 'Unrecognized protocol [' . $protocol . '] please specify [tcp] or [udp]';
            Core::throwException($errMsg);
        }
        
        if (empty($host))
        {
            $host = self::getPublicIp();
        }

        foreach ($ports as $port)
        {
            $connection = @fsockopen($host, $port);

            if (is_resource($connection))
            {
                $isOpen = true;
                fclose($connection);
            }

            else
            {
                $isOpen = false;
            }
        }
        
        return $isOpen;
    }
}

