<?php

/**
 *  Class for date controls
 *
 *  @author     Stefan Gabos <ix@nivelzero.ro>
 *  @copyright  (c) 2006 - 2008 Stefan Gabos
 *  @package    HTMLForm_Controls
 */
class HTMLForm_date extends HTMLForm_control
{

    /**
     *  Inserts a date picker control in the form
     *
     *  <b>Before being able to use a date picker control you must set the form's {@link HTMLForm::$datePickerPath} property!</b>
     *
     *  <i>Note that this class creates a reference to the Zebra PHP Components Framework Date Picker Class named</i> "<b>datePicker</b>" <i>through
     *  which you can alter all the properties of the date picker</i>
     *
     *  <i>Also note that the output of this control will be a text box control and an icon to the right of it, as set in the stylesheet
     *  of the {@link $template} file by the class named "date-icon"</i>
     *
     *  <b>Do not instantiate this class directly!</b>
     *
     *  Use {@link HTMLForm::add()} method instead!
     *
     *  <code>
     *  /*
     *  notice the use of the "&" symbol -> it's the way we can have a reference to the object in PHP4
     *  {@*}
     *  $obj = & $form->add('date', 'control_id');
     *
     *  /*
     *  by using the datePicker reference of the control, we preselect a date in the date picker
     *  read the documentation of the Date Picker class to find out about what properties are available
     *  {@*}
     *  $obj->datePicker->preselectedDate = 1220511903;
     *
     *  </code>
     *
     *  @param  string  $controlID      Unique name to identify the control in form
     *
     *                                  <i>Note that control's ID attribute will be, by default, the same as the name attribute!
     *                                  This means that this will be the name you will be using to refer to the control in the POST/GET
     *                                  superglobals, after the form has been submitted</i>
     *
     *  @param  string  $defaultValue   (Optional) String to be shown by default in the text box

     *  @param  array   $attributes     (Optional) an array of user specified HTML attributes valid for an <input type="text" control
     *
     *                                  Must be specified as an associative array of type attributeName => attributeValue
     *
     *                                  See {@link HTMLForm_control::setAttributes()} method to see how to set HTML attributes,
     *                                  other than through the class' constructor
     *
     *                                  <i>Note that the following properties are automatically set and should not
     *                                  be altered manually:</i>
     *
     *                                  <b>type</b>, <b>id</b>, <b>name</b>, <b>value</b>, <b>class</b>, <b>onfocus</b>, <b>onblur</b>,
     *                                  <b>readonly</b>
     *
     *                                  <i>If you don't use templates for the form's output but rather let the script automatically generate
     *                                  it for you, you may use a special attribute called "nobr" (used like "nobr" => true) which will
     *                                  instruct the script to not start a new line after the control having this attribute set, but place
     *                                  the next control on the same line</i>
     *
     *  @return void
     */
    function HTMLForm_date($controlID, $defaultValue = '', $attributes = '')
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
            
                'type'      =>  'text',
                'id'        =>  $controlID,
                'name'      =>  $controlID,
                'value'     =>  $defaultValue,
                'readonly'  =>  'readonly',
                'class'     =>  'date',
                'onfocus'   =>  'this.className = \'date-focus\'',
                'onblur'    =>  'this.className = \'date\''
            )
            
        );
        
        // sets user specified attributes for the control
        $this->setAttributes($attributes);
        
        // instantiate the date picker class
        $this->datePicker = new datepicker();

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

        // get some attributes of the control
        $attributes = $this->getAttributes(array('name'));
        
        return '<input '.$this->renderAttributes().'><a href=\'#\' onclick="'.$this->datePicker->show($attributes['name']).';"><div class=\'date-icon\'></div></a><div style="clear:both"></div>';

    }

}

?>
