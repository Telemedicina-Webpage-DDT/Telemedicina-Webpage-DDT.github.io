<?php

/**
 *  A generic class that all the form's controls extend
 *
 *  @author     Stefan Gabos <ix@nivelzero.ro>
 *  @copyright  (c) 2006 - 2008 Stefan Gabos
 *  @package    HTMLForm_Controls
 */
class HTMLForm_control extends xss_clean
{

    /**
     *  By default, when a form is reloaded after a submission, all the controls will have thir respectivelly submitted values, while the
     *  default value will be ignored
     *
     *  If you set this property to TRUE, the control's default value will be used when reloading the form (although in the POST/GET
     *  superglobals you will still have the actual submitted value)
     *
     *  Default is FALSE
     *
     *  @var boolean
     */
    var $locked;
    
    /**
     *  Array of HTML attributes of the element
     *
     *  @var array
     *
     *  @access private
     */
     
    var $attributes;

    /**
     *  Array of HTML attributes that control's renderProperties() method should skip
     *
     *  @var array
     *
     *  @access private
     */
     
    var $privateAttributes;

    /**
     *  Array of validation rules set for the element
     *
     *  @var array
     *
     *  @access private
     */
     
    var $rules;
    
    /**
     *  Whether the control's value should be filtered for XSS injection or not
     *
     *  Default is FALSE
     *
     *  @var boolean
     *
     *  @access private
     */
    var $disable_xss_filter;

    /**
     *  Constructor of the class
     *
     *  @return void
     *
     *  @access private
     */
    function HTMLForm_control()
    {
    
        // Sets default values of the class' properties
        // We need to do it this way for the variables to have default values PHP 4
        
        $this->locked = FALSE;

        $this->attributes = array();

        $this->privateAttributes = array();

        $this->rules = array();
        
        $this->disable_xss_filter = FALSE;

    }
    
    /**
     *  Sets one or more HTML attributes of the control
     *
     *  <code>
     *  /*
     *  create a text field named "name"
     *  (notice the use of the "&" symbol -> it's how we make it work in PHP 4, too!)
     *  {@*}
     *
     *  $obj = & $form->add('text', 'name');
     *
     *  /*
     *  set some HTML attributes for the text control
     *  more specifically set it's "size" attribute to 2
     *  and it's "readonly" attribute
     *  {@*}
     *
     *  $obj->setAttributes(array('size' => 2, 'readonly' => 'readonly'));
     *  </code>
     *
     *  @param  array   $attributes     An associative array of type attributeName => attributeValue
     *
     *  @return void
     */
    function setAttributes($attributes)
    {

        // check if $attributes is given as an array
        if (is_array($attributes)) {
        
            // iterate through the given attributes array
            foreach ($attributes as $attribute => $value) {
            
                // add attribute to attributes array
                $this->attributes[$attribute] = $value;
                
            }
            
        }
        
    }
    
    /**
     *  Returns the values of requested HTML attributes of the control
     *
     *  <code>
     *  /*
     *  create a text field named "name"
     *  (notice the use of the "&" symbol -> it's how we make it work in PHP 4, too!)
     *  {@*}
     *
     *  $obj = & $form->add('text', 'name');
     *
     *  /*
     *  set some HTML attributes for the text control
     *  more specifically set it's "size" attribute to 2
     *  and it's "readonly" attribute
     *  {@*}
     *
     *  $obj->setAttributes(array('size' => 2, 'readonly' => 'readonly'));
     *
     *  /*
     *  now read the attributes
     *  {@*}
     *
     *  $attributes = $obj->getAttributes(array('size', 'readonly'));
     *
     *  /*
     *  The result will be an associative array
     *
     *  $attributes = Array(
     *      [size]      => 2,
     *      [readonly]  => "readonly"
     *  )
     *  {@*}
     *  </code>
     *
     *  @param  mixed   $attributes     The name of a single HTML attribute or an array of names representing HTML attributes
     *                                  of which values' to be returned
     *
     *  @return array   Returns an associative array of type attributeName => attributeValue where the array's keys are the requested
     *                  attributes' names and the array's values are each key's respective value
     */
    function getAttributes($attributes)
    {
    
        // initialize the array that will be returned
        $result = array();

        // if the argument is an array
        if (is_array($attributes)) {
        
            // iterate through the array
            foreach ($attributes as $attribute) {
            
                // if attribute exists
                if (array_key_exists($attribute, $this->attributes)) {
                
                    // populate the $result array
                    $result[$attribute] = $this->attributes[$attribute];
                    
                }
                
            }
            
        // if the argument is a string
        } else {
        
            // if attribute exists
            if (array_key_exists($attributes, $this->attributes)) {
            
                // populate the $result array
                $result[$attributes] = $this->attributes[$attributes];

            }
            
        }
        
        // return the results
        return $result;

    }
    
    /**
     *  Converts the array with control's attributes to valid HTML markup interpreted by the {@link toHTML()} method
     *
     *  <i>Note that this method skips {@link $privateCellAttributes}</i>
     *
     *  @return array   Returns an associative array with a single item where the key is the word "attributes"
     *                  and the value is the resulted string
     *
     *  @access private
     */
    function renderAttributes()
    {
    
        // the string to be returned
        $attributes = '';
        
        // iterates through the control's attributes
        foreach ($this->attributes as $attribute => $value) {
        
            if (

                // if there are no private attributes set for the class
                !isset($this->privateAttributes) ||

                // or attribute not in array of private attributes
                !@in_array($attribute, $this->privateAttributes)

            ) {
        
                // add attribute => value pair to the return string
                $attributes .= ($attributes != '' ? ' ' : '') . $attribute . '="' . preg_replace('/\"/', '&quot;', $value) . '"';
                
            }
        
        }
        
        // returns string
        return $attributes;
    
    }

    /**
     *  Sets validation rules for the control
     *
     *  Here are the available rules:
     *
     *      -   <b>mandatory</b>
     *
     *          Specified as 'mandatory' => array($errorMessageContainerBlock, $errorMessage)
     *
     *          Validates only if the control has a value
     *
     *          Available for {@link HTMLForm_checkbox}, {@link HTMLForm_password}, {@link HTMLForm_radio}, {@link HTMLForm_select},
     *          {@link HTMLForm_text}, {@link HTMLForm_textarea}
     *
     *          <code>
     *          $obj->setRule('mandatory' => array('errorBlock1', 'This field is required'));
     *          </code>
     *
     *      -   <b>minlength</b>
     *
     *          Specified as 'minlength' => array($minimumLength, $errorMessageContainerBlock, $errorMessage)
     *
     *          Validates only if entered text's length is greater than $minimumLength
     *
     *          Available for {@link HTMLForm_password}, {@link HTMLForm_text}, {@link HTMLForm_textarea}
     *
     *          <code>
     *          $obj->setRule('minlength' => array('6', 'errorBlock1', '6 characters is minimum!'));
     *          </code>
     *
     *      -   <b>maxlength</b>
     *
     *          Specified as 'maxlength' => array($maximumLength, $errorMessageContainerBlock, $errorMessage)
     *
     *          Validates only if entered text's length is shorter than $maximumLength
     *
     *          Available for {@link HTMLForm_password}, {@link HTMLForm_text}, {@link HTMLForm_textarea}
     *
     *          <code>
     *          $obj->setRule('maxlength' => array('12', 'errorBlock1', '12 Characters is maximum', ));
     *          </code>
     *
     *      -   <b>email</b>
     *
     *          Specified as 'email' => array($errorMessageContainerBlock, $errorMessage)
     *
     *          Validates only if entered text is a valid email address
     *
     *          Available for {@link HTMLForm_password}, {@link HTMLForm_text}, {@link HTMLForm_textarea}
     *
     *          <code>
     *          $obj->setRule('email' => array('errorBlock1', 'Not a valid email address!'));
     *          </code>
     *
     *      -   <b>emails</b>
     *
     *          Specified as 'emails' => array($errorMessageContainerBlock, $errorMessage)
     *
     *          Validates only if entered text is a valid list of comma separated email addresses
     *
     *          Available for {@link HTMLForm_password}, {@link HTMLForm_text}, {@link HTMLForm_textarea}
     *
     *          <code>
     *          $obj->setRule('emails' => array('errorBlock1', 'Invalid email address/addresses!'));
     *          </code>
     *
     *      -   <b>digitsonly</b>
     *
     *          Specified as 'digitsonly' => array($errorMessageContainerBlock, $errorMessage)
     *
     *          Validates only if entered characters are all digits
     *
     *          Available for {@link HTMLForm_password}, {@link HTMLForm_text}, {@link HTMLForm_textarea}
     *
     *          <code>
     *          $obj->setRule('digitsonly' => array('errorBlock1', 'Only numbers allowed!'));
     *          </code>
     *
     *      -   <b>compare</b>
     *
     *          Specified as 'compare' => array($controlIDToCompareWith, $errorMessageContainerBlock, $errorMessage)
     *
     *          Validates only if control's value is equal with the value of the control indicated by $controlIDToCompareWith
     *
     *          Useful for when you want to check password confirmation
     *
     *          Available for {@link HTMLForm_password}, {@link HTMLForm_text}, {@link HTMLForm_textarea}
     *
     *          <code>
     *          $obj->setRule('compare' => array('password', 'errorBlock1', 'Password not confirmed correctly!'));
     *          </code>
     *
     *      -   <b>captcha</b>
     *
     *          Specified as 'captcha' => array($errorMessageContainerBlock, $errorMessage)
     *
     *          Validates only if control's value is the same as the characters seen in the {@link HTMLForm_captcha} control on the form
     *
     *          <i>...therefore, you must also have a {@link HTMLForm_captcha} control on your form!</i>
     *
     *
     *          Available for {@link HTMLForm_text}
     *
     *          <code>
     *          $obj->setRule('captcha' => array('errorBlock1', 'Password not confirmed correctly!'));
     *          </code>
     *
     *      -   <b>custom</b>
     *
     *          This rule allows you to define custom rules.
     *
     *          Custom rules allow you to do customized validations within the $form->validate() method (as opposed to doing your custom
     *          validations after the $form->validate() method and thus giving feedback to the user only after validating for standard
     *          rules which would be awkward for the user because it will look like after passing all validation rules he will get error
     *          messages that did not showed up earlier)
     *
     *          It must be specified as
     *          'custom' => array($callbackFunctionName, [optional arguments to be passed to the function], $errorMessageContainerBlock, $errorMessage)
     *
     *          <b>Note that the custom function's first parameter will ALWAYS be the control's submitted value and the optional arguments
     *          to be passed to the function will start as of second argument!</b>
     *
     *          <b>Also note that the custom validation function MUST return TRUE on success or FALSE on failure!</b>
     *
     *          <b>Note that multiple custom rules can also be set:</b>
     *
     *          It must be specified as
     *
     *          'custom' => array(
     *
     *              array($callbackFunctionName1, [optional arguments to be passed to the function], $errorMessageContainerBlock, $errorMessage),
     *              array($callbackFunctionName2, [optional arguments to be passed to the function], $errorMessageContainerBlock, $errorMessage)
     *
     *          )
     *
     *          <code>
     *          /*
     *              custom function that checks if a control's value is equal
     *              to a defined value
     *          {@*}
     *          function textIs($controlsSubmittedValue, $valueToCompareTo)
     *          {
     *              if ($controlsSubmittedValue != $valueToCompareTo) {
     *                  return false;
     *              }
     *              return true;
     *          }
     *
     *          /*
     *              add a text box control to the form
     *          {@*}
     *          $obj = & $form->add('text', 'control_id');
     *
     *          /*
     *              set the custom rule which will compare weather the text box control's submitted value
     *              is 'admin' and display the error message in specified error block if is not
     *          {@*}
     *          $obj->setRule('custom' => array('textIs', 'admin', 'errorBlock', 'Text must be 'admin'!'));
     *
     *          </code>
     *
     *  @param  array   $rules  An associative array
     *
     *                          See above how it needs to be specified for each rule
     *
     *  @return void
     */
    function setRule($rules)
    {

        // continue only if argument is an array
        if (is_array($rules)) {
        
            // iterate through the given rules
            foreach ($rules as $ruleName => $ruleProperties) {

                // append the rule and rule's properties to the rules array
                $this->rules[] = array($ruleName => $ruleProperties);

                // we have some special rules for which we do some additional stuff
                switch (strtolower($ruleName)) {

                    case 'custom':

                        // if multiple custom rules have been specified
                        if (is_array(array_pop(array_values($ruleProperties)))) {

                            // get the custom rules
                            $custom_rules = $this->rules[count($this->rules) - 1]['custom'];

                            // delete the last entry in the rules table (which, in this case, is the custom property)
                            array_pop($this->rules);

                            // iterate through the custom rules
                            foreach ($custom_rules as $rule) {

                                // and re-assign them properly this time
                                $this->rules[] = array('custom' => $rule);

                            }

                        }

                        break;

                    case 'digitsonly':

                        $this->setAttributes(array('onkeypress' => 'return digitsOnly(event)'));
                        
                        break;
                        
                    case 'maxlength':
                    
                        $this->setAttributes(array('maxlength' => $ruleProperties[0]));
                        
                        break;
                        
                }
                
            }

        }
        
    }
    
    /**
     *  Gets submitted value of the control and makes this value the "preselected" value of the control
     *
     *  @return void
     *
     *  @access private
     */
    function getSubmittedValue()
    {
    
        // get the form's name submission method
        global $HTMLForm_method, $HTMLForm_name;
        
        // if control's value is not locked to the default value
        if (!$this->locked) {
        
            // get some attributes of the control
            $attribute = $this->getAttributes(array('name', 'type', 'value'));

            // as some controls (like the 'label') don't have the 'name' attribute
            if (isset($attribute['name'])) {

                // strip any [] from the control's name (usually used in conjunction with multi-select select boxes and checkboxes)
                $attribute['name'] = preg_replace('/\[\]/', '', $attribute['name']);

            }
            
            $method = '_' . $HTMLForm_method;

            global $$method;
            
            if (

                // if form was submitted
                isset(${$method}['HTMLForm_formname']) &&

                ${$method}['HTMLForm_formname'] == $HTMLForm_name

            ) {
            
                if (

                    // if control was submitted
                    // note that we have @ in front of it as some controls have no "name" attribute (labels)
                    @isset(${$method}[$attribute['name']])

                ) {

                    // create the submittedValue property for the control and assign to it the submitted value of the control
                    $this->submittedValue = ${$method}[$attribute['name']];
                    
                    // if submitted value is an array
                    if (is_array($this->submittedValue)) {
                    
                        // iterate throught the submitted values
                        foreach ($this->submittedValue as $key => $value) {

                            // and also, if magic_quotes_gpc is on (meaning that both single and double quotes are escaped)
                            if (get_magic_quotes_gpc()) {

                                // strip those slashes
                                $this->submittedValue[$key] = stripslashes($value);

                            }

                        }

                    // if submitted value is not an array
                    } else {

                        // and also, if magic_quotes_gpc is on (meaning that both single and double quotes are escaped)
                        if (get_magic_quotes_gpc()) {

                            // strip those slashes
                            $this->submittedValue = stripslashes($this->submittedValue);

                        }

                    }

                    // since 1.1
                    if (

                        // if XSS filtering is not disabled
                        $this->disable_xss_filter !== TRUE


                    ) {

                        // if submitted value is an array
                        if (is_array($this->submittedValue)) {

                            // iterate throught the submitted values
                            foreach ($this->submittedValue as $key => $value) {
                            
                                // filter the control's value for XSS injection
                                $this->submittedValue[$key] = $this->sanitize($value);

                            }
                            
                        } else {

                            // filter the control's value for XSS injection
                            $this->submittedValue = $this->sanitize($this->submittedValue);
                            
                        }

                        // set the respective $_POST/$_GET value to the filtered value
                        ${$method}[$attribute['name']] = $this->submittedValue;

                    }

                } else {

                    // we set this for those controls that are not submitted even when the form they reside in is
                    // (i.e. unchecked checkboxes) so that we know that they were indeed submitted but they just don't have a value
                    $this->submittedValue = false;

                }

            }

            // if control was submitted
            if (isset($attribute['type']) && isset($this->submittedValue)) {

                // the assignment of the submitted value is type dependant
                switch ($attribute['type']) {

                    // if control is a checkbox
                    case 'checkbox':

                        if (

                            (

	                            // if is submitted value is an array
								is_array($this->submittedValue) &&

	                            // and the checkbox's value is in the array
	                            in_array($attribute['value'], $this->submittedValue)

							// OR
							) ||

                            // assume submitted value is not an array and the
                            // checkbox's value is the same as the submitted value
                            $attribute['value'] == $this->submittedValue

                        ) {

                            // set the "checked" attribute of the control
                            $this->setAttributes(array('checked' => 'checked'));

                        // if checkbox was "submitted" as not checked
                        } else {

                            // and if control's default state is checked
                            if (isset($this->attributes['checked'])) {

                                // uncheck it
                                unset($this->attributes['checked']);

                            }

                        }

                        break;

                    // if control is a radio button
                    case 'radio':


                        if (

                            // if the radio button's value is the same as the submitted value
                            ($attribute['value'] == $this->submittedValue)

                        ) {

                            // set the "checked" attribute of the control
                            $this->setAttributes(array('checked' => 'checked'));

                        }

                        break;

                    // if control is a select box
                    case 'select':

                        // set the "value" private attribute of the control
                        // the attribute will be handled by the HTMLForm_select::renderAttributes() method
                        $this->setAttributes(array('value' => $this->submittedValue));

                        break;

                    // if control is a file upload control, a hidden control, a password field, a text field or a textarea control
                    case 'file':
                    case 'hidden':
                    case 'password':
                    case 'text':
                    case 'textarea':
                    
                        // set the "value" standard HTML attribute of the control
                        $this->setAttributes(array('value' => $this->submittedValue));

                        break;

                }

            }
            
        }
            
    }

    /**
     *  Disables XSS filtering for the value of the control
     *
     *  By default, all values are filtered for XSS (Cross Site Scripting) injections (that is, the script removes event handlers, some
     *  javascript code, etc). While in 99% that's the right thing to do, it might happen that sometimes you don't want that - i.e. if
     *  you are building a fancy CMS and your prefered WYSIWYG editor also inserts some javascript, etc.
     *
     *  @return void
     */
    function disable_xss_filters()
    {

        $this->disable_xss_filter = TRUE;

    }

}

?>
