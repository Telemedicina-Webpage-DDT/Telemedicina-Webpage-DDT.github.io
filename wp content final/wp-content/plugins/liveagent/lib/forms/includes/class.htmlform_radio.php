<?php

/**
 *  Class for radio button controls
 *
 *  @author     Stefan Gabos <ix@nivelzero.ro>
 *  @copyright  (c) 2006 - 2008 Stefan Gabos
 *  @package    HTMLForm_Controls
 */
class HTMLForm_radio extends HTMLForm_control
{

    /**
     *  Inserts an HTML <input type="radio"> control in the form
     *
     *  <b>Do not instantiate this class directly!</b>
     *
     *  Use {@link HTMLForm::add()} method instead!
     *
     *  <code>
     *  /*
     *  notice the use of the "&" symbol -> it's the way we can have a reference to the object in PHP4
     *  {@*}
     *
     *  $obj = & $form->add('radio', 'control_id', 'radio button value');
     *
     *  </code>
     *
     *  @param  string  $controlID      Unique name to identify the control in form
     *
     *                                  <i>Note that the</i> <b>name</b> <i>attribute of the control will have the value of $controlID,
     *                                  while the</i> <b>id</b> <i>attribute will be
     *
     *	 	 							str_replace(' ', '_', $controlID . '_' . $value)
     *
     *                                  So, if $controlID is "radio" and $value is "value 1", the control's actual ID will be "radio_value_1"</i>
     *
     *  @param  mixed   $value          Value of the radio button
     *
     *  @param  array   $attributes     (Optional) an array of user specified HTML attributes valid for a radio button control
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
    function HTMLForm_radio($controlID, $value, $attributes = '')
    {
    
        // call the constructor of the parent class
        parent::HTMLForm_control();
    
        // set the private attributes of this control
        // these attributes are private for this control and are for internal use only
        $this->privateAttributes = array(

            'nobr'

        );

        // set the default attributes for the radio button control
        $this->setAttributes(
        
            array(

		        'type'  =>  'radio',
                'id'    =>  str_replace(' ', '_', $controlID.'_'.$value),
                'name'  =>  $controlID,
                'value' =>  $value,
                'class' =>  'radio'

		    )

		);

        // sets user specified attributes for the table cell
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
