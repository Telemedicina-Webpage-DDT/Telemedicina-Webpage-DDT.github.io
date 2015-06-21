<?php

/**
 *  Class for button controls
 *
 *  @author     Stefan Gabos <ix@nivelzero.ro>
 *  @copyright  (c) 2006 - 2008 Stefan Gabos
 *  @package    HTMLForm_Controls
 */
class HTMLForm_button extends HTMLForm_control
{

    /**
     *  Inserts an HTML <input type="button"> control in the form
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
     *  $obj = & $form->add('button', 'control_id', 'Click me!');
     *
     *  </code>
     *
     *  @param  string  $controlID      Unique name to identify the control in form
     *
     *                                  <i>Note that control's ID attribute will be, by default, the same as the name attribute!
     *                                  This means that this will be the name you will be using to refer to the control in the POST/GET
     *                                  superglobals, after the form has been submitted</i>
     *
     *  @param  string  $caption        Caption of the button control
     *
     *  @param  array   $attributes     (Optional) an array of user specified HTML attributes valid for an <input type="button>" control
     *
     *                                  Must be specified as an associative array of type attributeName => attributeValue
     *
     *                                  See {@link HTMLForm_control::setAttributes()} method to see how to set HTML attributes,
     *                                  other than through the class' constructor
     *
     *                                  <i>Note that the following properties are automatically set and should not
     *                                  be altered manually:</i>
     *
     *                                  <b>type</b>, <b>id</b>, <b>name</b>, <b>value</b>, <b>class</b>
     *
     *                                  <i>If you don't use templates for the form's output but rather let the script automatically generate
     *                                  it for you, you may use a special attribute called "nobr" (used like "nobr" => true) which will
     *                                  instruct the script to not start a new line after the control having this attribute set, but place
     *                                  the next control on the same line</i>
     *
     *  @return void
     */
    function HTMLForm_button($controlID, $caption, $attributes = '')
    {
    
        // call the constructor of the parent class
        parent::HTMLForm_control();
        
        // set the private attributes of this control
        // these attributes are private for this control and are for internal use only
        $this->privateAttributes = array(

            'nobr'

        );

        // set the default attributes for the button control
        $this->setAttributes(

            array(
                'type'  =>  'button',
                'id'    =>  $controlID,
                'name'  =>  $controlID,
                'value' =>  $caption,
                'class' =>  'button'
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
    
        return '<input ' . $this->renderAttributes() . '>';
    
    }
    
}

?>
