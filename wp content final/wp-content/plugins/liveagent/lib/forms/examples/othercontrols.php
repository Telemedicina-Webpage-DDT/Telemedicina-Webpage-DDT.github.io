<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html>

    <head>

        <title>PHP HTML Form Processor Class - Other Controls</title>
        
        <style type="text/css"> body {font-family: tahoma, arial, verdana, sans; font-size: 12px; } </style>

    </head>

    <body>

    <h2>For this example to work you need the PHP Date Picker Class!</h2>

    <p><strong>Quick tip: </strong>Notice the '&'s in code - this is there in order for $obj to really point to the newly added control in PHP4</p>

    <?php

        // create a custom function that compares a control's
        // submitted value with another value
        function valueCompare($controlValue, $valueToCompareTo)
        {

            // if values are not the same
            if ($controlValue != $valueToCompareTo) {

                // return false
                return false;

            }

            // return true if everything is ok
            return true;

        }

        // require the htmlform class
        require '../class.htmlform.php';

        // instantiate the class
        $form = new HTMLForm('form', 'post');
        
        // specify the date to the datepicker class (a member of the Zebra PHP Framework)
        // if class is not found the execution will break when you try to instantiate a date control
        $form->datePickerPath = '../../datepicker/class.datepicker.php';

        // add a label to the 'admin' control - used in the template file as {controls.label_admin}
        $form->add('label', 'label_admin', 'name', 'Enter \'admin\' here');

        // add a text control named "admin" - used in the template file as {controls.admin}
        $obj = & $form->add('text', 'admin');

        // set the field as mandatory
        $obj->setRule(array('mandatory' => array('e1', 'The above field is mandatory!')));
        
        // add a custom validation rule
        $obj->setRule(array('custom' => array('valueCompare', 'admin', 'e1', 'You must enter \'admin\'!')));

        // add a label to the 'select' control - used in the template file as {controls.label_select}
        $form->add('label', 'label_select', 'select', 'Select a country');

        // add a select control named "select" - used in the template file as {controls.select}
        $obj = & $form->add('select', 'select');

        // add some options to the select control
        // NOTE THAT, IF THE "MULTIPLE" ATTRIBUTE IS NOT SET, THE FIRST OPTION WILL BE ALWAYS CONSIDERED AS THE
        // "NOTHING IS SELECTED" STATE OF THE CONTROL!
        $obj->addOptions(array('- select -', 'ro' => 'Romania', 'us' => 'United States', 'br' => 'Brazil', 'de' => 'Germany'));

        // set the field as mandatory
        $obj->setRule(array('mandatory' => array('e2', 'Select a country!')));

        // add a label to the 'option' control - used in the template file as {controls.label_option}
        $form->add('label', 'label_option', 'option', 'Select an option');

        // add a select control, having the name attribute set to "option[]" and the id attributes set to "option",
        // no default value, and some HTML attributes set like "multiple", "size" and "style"
        // note the '[]' at the end of the name in order to be able to submit selected options as an array
        // also note that the id attribute will be name of the control with the '[]' stripped and therefore
        // in the template file, you'll be able to call this control's output by using {controls.option}
        $obj = & $form->add('select', 'option[]', '', array('multiple' => 'multiple', 'size' => 3, 'style' => 'height:60px'));

        // add some options to the control
        $obj->addOptions(array('I like this PHP class', 'I don\'t like this PHP class', 'What\'s PHP?'));

        // set the field as mandatory
        $obj->setRule(array('mandatory' => array('e3', 'Select at least one option!')));

        // add a label to the 'date' control - used in the template file as {controls.label_date}
        $form->add('label', 'label_date', 'date', 'What date is today?');

        // add a select control named "date" - used in the template file as {controls.date}
        $obj = & $form->add('date', 'date');

        // set some properties of the date picker
        $obj->datePicker->preselectedDate = mktime(0, 0, 0, date('m'), date('d'), date('Y'));

        $obj->datePicker->dateFormat = 'd M Y';

        // set the field as mandatory
        $obj->setRule(array('mandatory' => array('e4', 'Please select a date!')));

        $obj = & $form->add('file', 'file');

        $obj->setRule(array('mandatory' => array('e5', 'Pick a file!')));

        // place a submit button
        $form->add('submit', 'submit', 'Submit');
        
        // validate the form
        if ($form->validate()) {
        
            // code if form is valid
            print_r('<p>Form is valid. Do your thing (write to db, send emails, whatever) and redirect.</p>');
            
            die();

        }
        
        // display the form with the specified template
        $form->render('forms/othercontrols.xtpl');
        
        // IF YOU DO NOT NEED ANY SPECIAL FORMATTINGS IN YOUR FORM YOU CAN ALSO CALL THE RENDER METHOD WITHOUT SPECIFYING A TEMPLATE NAME
        // AND THUS LETTING THE SCRIPT TO AUTOMATICALLY GENERATE OUTPUT AND SAVING YOU OF EXTRA WORK PUT INTO DESIGNING THE FORM'S LAYOUT
        // COMMENT THE LINE ABOVE AND UNCOMMENT THE ONE BELOW TO SEE IT IN ACTION

        //$form->render();

    ?>
    </body>
</html>
