<?php

/**
 *  Class for CAPTCHA controls
 *
 *  @author     Stefan Gabos <ix@nivelzero.ro>
 *  @copyright  (c) 2006 - 2008 Stefan Gabos
 *  @package    HTMLForm_Controls
 */

class HTMLForm_captcha extends HTMLForm_control
{

    /**
     *  The class needs to know the path of the package in order to be able to include captcha.php
     *
     *  @access private
     */
    var $relativePath = '';

    /**
     *  Generates a CAPTCHA image
     *
     *  <b>You can alter the properties of this image in file includes/captcha.php</b>
     *
     *  <i>You must also place a {@link HTMLForm_text} control and set the</i> <b>captcha</b><i> rule to it (using {@link setRule})</i>
     *
     *  <b>Do not instantiate this class directly!</b>
     *
     *  Use {@link HTMLForm::add()} method instead!
     *
     *  <code>
     *  /*
     *  note the use of the "&" symbol -> it's the way we can have a reference to the object in PHP4
     *  {@*}
     *
     *  $obj = & $form->add('captcha', 'control_id');
     *
     *  </code>
     *
     *  @param  string  $controlID      Unique name to identify the control in form
     *
     *  @param  array   $attributes     (Optional) an array of user specified HTML attributes valid for an <img> tag
     *
     *                                  Must be specified as an associative array of type attributeName => attributeValue
     *
     *                                  See {@link HTMLForm_control::setAttributes()} method to see how to set HTML attributes,
     *                                  other than through the class' constructor
     *
     *                                  <i>If you don't use templates for the form's output but rather let the script automatically generate
     *                                  it for you, you may use a special attribute called "nobr" (used like "nobr" => true) which will
     *                                  instruct the script to not start a new line after the control having this attribute set, but place
     *                                  the next control on the same line</i>
     *
     *  @return void
     */
    function HTMLForm_captcha($controlID, $attributes = '')
    {

        // call the constructor of the parent class
        parent::HTMLForm_control();
        
        // set the private attributes of this control
        // these attributes are private for this control and are for internal use only
        $this->privateAttributes = array(

            'nobr'

        );

        // set the default attributes for the text control
        $this->setAttributes(
        
            array(
            
                'type'      =>  'captcha',
                'id'        =>  $controlID,
                'name'      =>  $controlID,
                
            )
            
        );

        // sets user specified attributes for the control
        $this->setAttributes($attributes);

    }

    /**
     *  Returns the HTML code of the control
     *
     *  @return string  Resulted HTML code
     *
     *  @access private
     */
    function toHTML()
    {
    
        return '<img src="' . $this->relativePath . '/includes/captcha.php?nocache=' . md5(mktime()) . '" alt="">';
    
    }
    
}

?>
