<?php

/**
 *  Class for labels
 *
 *  @author     Stefan Gabos <ix@nivelzero.ro>
 *  @copyright  (c) 2006 - 2008 Stefan Gabos
 *  @package    HTMLForm_Controls
 */
class HTMLForm_label extends HTMLForm_control
{

    /**
     *  Inserts an HTML <label> control in the form
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
     *  $obj = & $form->add('label', 'control_id_to_assign_to', 'label_id', 'caption');
     *
     *  </code>
     *
     *  @param  string  $controlID      Unique name to identify the control in form
     *
     *                                  <i>You only need to have unique IDs if you use user-defined templates so that you can refer to each
     *                                  control in the template. If you rather generate a form automatically, you don't need unique IDs and
     *                                  you can set all controls the same id (i.e. 'label')</i>
     *
     *  @param  string  $forControlID   <b>ID</b> of the control to link the label to.
     *
     *                                  <i>Notice that this must be the ID attribute of the control you are attaching the label to and not
     *                                  the name attribute!
     *
     *                                  This is important as while most of the controls have their ID attribute set by default to the same
     *                                  value as their name attribute, the {@link HTMLForm_checkbox} and {@link HTMLForm_radio} behave
     *                                  differently</i>
     *
     *  @param  mixed   $caption        Caption of the label
     *
     *  @param  array   $attributes     (Optional) an array of user specified HTML attributes valid for a label
     *
     *                                  Must be specified as an associative array of type attributeName => attributeValue
     *
     *                                  See {@link HTMLForm_control::setAttributes()} method to see how to set HTML attributes,
     *                                  other than through the class' constructor
     *
     *                                  <i>Note that the following properties are automatically set and should not
     *                                  be altered manually:</i>
     *
     *                                  <b>name</b>, <b>id</b>, <b>for</b>, <b>class</b>
     *
     *                                  <i>If you don't use templates for the form's output but rather let the script automatically generate
     *                                  it for you, you may use a special attribute called "nobr" (used like "nobr" => true) which will
     *                                  instruct the script to not start a new line after the control having this attribute set, but place
     *                                  the next control on the same line</i>
     *
     *  @return void
     */
    function HTMLForm_label($controlID, $forControlID, $caption, $attributes = '')
    {

        // call the constructor of the parent class
        parent::HTMLForm_control();

        // set the private attributes of this control
        // these attributes are private for this control and are for internal use only
        // set the private attributes of this control
        // these attributes are private for this control and are for internal use only
        $this->privateAttributes = array(

            'label',
            'nobr'

        );


        // set the default attributes for the label
        $this->setAttributes(

			array(

			    'id'    =>  $controlID,
                'for'   =>  $forControlID,
                'label' =>  $caption,

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

        // get private attributes
        $privateAttributes = $this->getAttributes('label');

        return '<label ' . $this->renderAttributes() . '>' . $privateAttributes['label'] . '</label>';

    }

}

?>
