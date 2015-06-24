<?php

/**
 *  Class for arbitrary HTML
 *
 *  @author     Stefan Gabos <ix@nivelzero.ro>
 *  @copyright  (c) 2006 - 2008 Stefan Gabos
 *  @package    HTMLForm_Controls
 */
class HTMLForm_html extends HTMLForm_control
{

    /**
     *  Adds arbitrary HTML code (or plain text) to the form
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
     *  $obj = & $form->add('html', 'control_id', '<small>Notes about the control</small>');
     *
     *  </code>
     *
     *  @param  string  $controlID      Unique name to identify the control in form
     *
     *                                  <i>You only need to have unique IDs if you use user-defined templates so that you can refer to each
     *                                  control in the template. If you rather generate a form automatically, you don't need unique IDs and
     *                                  you can set all html controls the same id (i.e. 'html')</i>
     *
     *  @param  string  $content        HTML or plain text to be displayed
     *
     *  @param  array   $attributes     (Optional) an array of user specified HTML attributes valid for the HTML control (actually, there is
     *                                  only one valid attribute for the HTML control and that is the "nobr" attribute)
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
    function HTMLForm_html($controlID, $content, $attributes = '')
    {
    
        // call the constructor of the parent class
        parent::HTMLForm_control();
        
        // set the private attributes of this control
        // these attributes are private for this control and are for internal use only
        $this->privateAttributes = array(

            'content',
            'nobr',

        );

        
        // set the default attributes for the HTML control
        $this->setAttributes(
        
            array(
            
                'type'  	=>  'html',
                'id'    	=>  $controlID,
                'content'   =>  $content,
                
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
    
        $attributes = $this->getAttributes('content');
    
        return $attributes['content'];
    
    }
    
}

?>
