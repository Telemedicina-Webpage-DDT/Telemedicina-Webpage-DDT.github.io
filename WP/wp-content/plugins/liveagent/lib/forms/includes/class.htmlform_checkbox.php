<?php

/**
 *  Class for checkbox controls
 *
 *  @author     Stefan Gabos <ix@nivelzero.ro>
 *  @copyright  (c) 2006 - 2008 Stefan Gabos
 *  @package    HTMLForm_Controls
 */
class HTMLForm_checkbox extends HTMLForm_control
{

    /**
     *  Inserts an HTML <input type="checkbox"> control in the form
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
     *  $obj = & $form->add('checkbox', 'control_id', 'checkbox value');
     *
     *  </code>
     *
     *  @param  string  $controlID      Unique name to identify the control in form
     *
     *                                  <i>If you plan to have more checkboxes with the same ID (but different values) remember to put
     *                                  brackets after the ID (i.e. control_id[]) or only a single option will be available after
     *                                  submitting the form!</i>
     *
     *                                  <i>Note that the</i> <b>name</b> <i>attribute of the control will have the value of $controlID,
     *                                  while the</i> <b>id</b> <i>attribute will be
     *
     *                                  str_replace(array(' ', '[', ']'), array('_', ''), $controlID . '_' . $value)
     *
     *                                  So, if $controlID is "checkbox" and $value is "value 1", the control's actual ID will be "checkbox_value_1"</i>
     *
     *  @param  mixed   $value          Value of the checkbox
     *
     *  @param  array   $attributes     (Optional) an array of user specified HTML attributes valid for an <input type="checkbox>" control
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
    function HTMLForm_checkbox($controlID, $value, $attributes = '')
    {
    
        // call the constructor of the parent class
        parent::HTMLForm_control();
    
        // set the private attributes of this control
        // these attributes are private for this control and are for internal use only
        $this->privateAttributes = array(

            'nobr'

        );

        // set the default attributes for the checkbox control
        $this->setAttributes(
        
            array(
            
                'type'  =>  'checkbox',
                'id'    =>  str_replace(array(' ', '[', ']'), array('_', ''), $controlID . '_' . $value),
                'name'  =>  $controlID,
                'value' =>  $value,
                'class' =>  'checkbox'
                
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
