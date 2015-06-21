<?php

/**
 *  Class for hidden controls
 *
 *  @author     Stefan Gabos <ix@nivelzero.ro>
 *  @copyright  (c) 2006 - 2008 Stefan Gabos
 *  @package    HTMLForm_Controls
 */
class HTMLForm_hidden extends HTMLForm_control
{

    /**
     *  Inserts an HTML <input type="hidden"> control in the form
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
     *  $obj = & $form->add('hidden', 'control_id', 'hidden value');
     *
     *  </code>
     *
     *  @param  string  $controlID      Unique name to identify the control in form
     *
     *                                  <i>Note that control's ID attribute will be, by default, the same as the name attribute!
     *                                  This means that this will be the name you will be using to refer to the control in the POST/GET
     *                                  superglobals, after the form has been submitted</i>
     *
     *  @param  string  $defaultValue   (Optional) Default value to be stored in the hidden value
     *
     *  @return void
     */
    function HTMLForm_hidden($controlID, $defaultValue = '')
    {
    
        // call the constructor of the parent class
        parent::HTMLForm_control();
    
        // set the private attributes of this control
        // these attributes are private for this control and are for internal use only
        $this->privateAttributes = array(

            'nobr'

        );

        // set the default attributes for the hidden control
        $this->setAttributes(

            array(

                'type'  =>  'hidden',
                'id'    =>  $controlID,
                'name'  =>  $controlID,
                'value' =>  $defaultValue

            )

        );
        
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
