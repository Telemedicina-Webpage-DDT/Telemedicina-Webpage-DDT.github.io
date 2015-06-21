<?php

/**
 *  Class for select box controls
 *
 *  @author     Stefan Gabos <ix@nivelzero.ro>
 *  @copyright  (c) 2006 - 2008 Stefan Gabos
 *  @package    HTMLForm_Controls
 */
class HTMLForm_select extends HTMLForm_control
{

    /**
     *  Inserts an HTML <select> control in the form
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
     *  $obj = & $form->add('select', 'control_id', '2');
     *
     *  /*
     *  "Spiders" will be selected by default as we specified that in the constructor
     *  {@*}
     *  $obj->addOptions(array(' - select - ', 'Monsters', 'Spiders', 'Snakes'));
     *
     *  </code>
     *
     *  @param  string  $controlID      Unique name to identify the control in form
     *
     *                                  <i>Note that control's ID attribute will be, by default, the same as the name attribute (with '['
     *                                  and ']' trimmed, if found)!</i>
     *                                  This means that this will be the name you will be using to refer to the control in the POST/GET
     *                                  superglobals, after the form has been submitted</i>
     *
     *  @param  mixed   $defaultValue   (Optional) Default selected option
     *
     *                                  <i>This argument can also be an array in case you want to have multiple selected options
     *                                  (that, of course, if the select control has the "multiple" attribute set)</i>
     *
     *  @param  array   $attributes     (Optional) an array of user specified HTML attributes valid for a select box control
     *
     *                                  Must be specified as an associative array of type attributeName => attributeValue
     *
     *                                  See {@link HTMLForm_control::setAttributes()} method to see how to set HTML attributes,
     *                                  other than through the class' constructor
     *
     *                                  <i>Note that the following properties are automatically set and should not
     *                                  be altered manually:</i>
     *
     *                                  <b>id</b>, <b>name</b>, <b>class</b>
     *
     *                                  <i>If you don't use templates for the form's output but rather let the script automatically generate
     *                                  it for you, you may use a special attribute called "nobr" (used like "nobr" => true) which will
     *                                  instruct the script to not start a new line after the control having this attribute set, but place
     *                                  the next control on the same line</i>
     *
     *  @return void
     */
    function HTMLForm_select($controlID, $defaultValue = '', $attributes = '')
    {
    
        // call the constructor of the parent class
        parent::HTMLForm_control();
    
        // set the private attributes of this control
        // these attributes are private for this control and are for internal use only
        $this->privateAttributes = array(
        
            'type',
            'value',
            'options',
            'nobr',

		);

        // set the default attributes for the textarea control
        $this->setAttributes(

			array(

			    'type'      =>  'select',
                'id'        =>  str_replace(array('[', ']'), '', $controlID),
                'name'      =>  $controlID,
                'value'     =>  $defaultValue,
                'options'   =>  array()

			)

		);
        
        // sets user specified attributes for the table cell
        $this->setAttributes($attributes);
        
    }
    
    /**
     *  Adds options to the select box control
     *
     *  <b>note that, if the "multiple" attribute is not set, the first option will be always considered as the
     *  "nothing is selected" state of the control!</b>
     *
     *  <code>
     *      // option groups can be created like this:
     *      $form->addOptions(array('group' => array(' - select - ', 'option 1', 'option 2')));
     *  </code>
     *
     *  @param  array   $options    An associative array of options where the key is the value of the option and the value is
     *                              the actual text to be displayed for the option
     *
     *  @return void
     */
    function addOptions($options)
    {

        // continue only if parameter is an array
        if (is_array($options)) {

            // get the options of the select control
            $attribute = $this->getAttributes('options');

            $optionsArray = $attribute['options'];

            // if we're adding the options as part of an option group
            if (is_array(array_shift(array_values($options)))) {
            
                $optGroup = array_shift(array_keys($options));

                // iterate through the given options
                foreach ($options as $key => $value) {

                    // and append them
                    $optionsArray[$optGroup][$key] = $value;

                }

            } else {

                // iterate through the given options
                foreach ($options as $key => $value) {

                    // and append them
                    $optionsArray[$key] = $value;

                }

            }

            // set the options attribute of the control
            $this->setAttributes(

				array(

				    'options'   =>  $optionsArray

				)

			);

        }
        
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

        // get the options of the select control
        $privateAttributes = $this->getAttributes(array('options', 'value'));

        $optContent = '';

        // if options have been set
        if (!empty($privateAttributes['options'])) {

            // iterate through the options and get the value and the content of each
            foreach ($privateAttributes['options'] as $value => $content) {
            
                // if part of an option group
                if (is_array($content)) {
                
                    // create the option group
                    $optContent .= '\n<optgroup label="' . $value . '">';
                    
                    // iterate through the options and get the value and the content of each
                    foreach ($content[$value] as $optgroupValue => $optgroupContent) {

                        // create the option list
                        $optContent .= '\n

                            <option value="' . $optgroupValue . '" ' .

                            (

								$privateAttributes['value'] != '' &&
								
                            	(

									(is_array($privateAttributes['value']) && in_array($optgroupValue, $privateAttributes['value'])) ||
									
                            		(!is_array($privateAttributes['value']) && $optgroupValue == $privateAttributes['value'])

								) ? 'selected="selected"' : '') . '>' .

                            $optgroupContent . '</option>';

                    }

                    $optContent .= '\n</optgroup>';

                // if not part of an option group
                } else {

                    // create the option list
                    $optContent .= '\n

                        <option value="' . $value . '" ' .

                        (

							$privateAttributes['value'] != '' &&
                        
                        	(

								(is_array($privateAttributes['value']) && in_array($value, $privateAttributes['value'])) ||
                        
                        		(!is_array($privateAttributes['value']) && $value == $privateAttributes['value'])

							) ? 'selected="selected"' : '') . '>' .

                        $content . '</option>';
                        
                }

            }


        }

        return '<select '. $this->renderAttributes() . '>' . $optContent . '\n</select>';

    }

}

?>
