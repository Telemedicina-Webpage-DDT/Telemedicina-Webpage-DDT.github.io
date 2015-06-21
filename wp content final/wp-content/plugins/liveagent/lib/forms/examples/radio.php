<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html>

    <head>

        <title>PHP HTML Form Processor Class - Radio controls, Checkboxes, and shorthand for custom error messages</title>

        <style type="text/css"> body {font-family: tahoma, arial, verdana, sans; font-size: 12px; } </style>

    </head>

    <body>

    <?php
    
        // require the htmlform class
        require '../class.htmlform.php';

        // instantiate the class
        $form = new HTMLForm('form', 'post');
        
        // we're adding a label whose id is label_options but we're not linking it to any control (the third argument is blank)
        $form->add('label', 'label_options', '', 'Select an option:');

        // we're adding a radio control whose name is options and whose value is o1
        $form->add('radio', 'options', 'o1');
        
        // we're attaching a label to the previously added control
        // notice how we're doing that: we're using the name of the control + underscore + the value of the control!
        $form->add('label', 'label_o1', 'options_o1', 'option 1');

        // we're adding a radio control whose name is options and whose value is o1
        $form->add('radio', 'options', 'o2');

        // we're attaching a label to the previously added control
        // notice how we're doing that: we're using the name of the control + underscore + the value of the control!
        $form->add('label', 'label_o2', 'options_o2', 'option 2');
        
        // we're adding a radio control whose name is options and whose value is c1
        $form->add('checkbox', 'checkbox[]', 'c1');

        // we're attaching a label to the previously added control
        // notice how we're doing that: we're using the name of the control (without the []) + underscore + the value of the control!
        $form->add('label', 'label_c1', 'checkbox_c1', 'option 1');

        // we're adding a radio control whose name is options and whose value is o1
        $form->add('checkbox', 'checkbox[]', 'c2');

        // we're attaching a label to the previously added control
        // notice how we're doing that: we're using the name of the control (without the []) + underscore + the value of the control!
        $form->add('label', 'label_c2', 'checkbox_c2', 'option 2');

        // add the submit button
        $form->add('submit', 'submit', 'Submit');

        // validate the form
        if ($form->validate()) {
        
            // quick custom validation
            if (!isset($_POST['checkbox']) || !in_array('c2', $_POST['checkbox'])) {

                // notice that we bind the error message to the last label - we do this so that we won't trigger an error when choosing
                // to let the script to automatically generate our form's output
                $form->addError('e1', 'You MUST select checkbox option 2!', 'label_c2');

            } else {
        
                // code if form is valid
                print_r('Form is valid. Do your thing (write to db, send emails, whatever) and redirect.');

                die();
                
            }
            
        }
        
        // display the form with the specified template
        $form->render('forms/radio.xtpl');

        // IF YOU DO NOT NEED ANY SPECIAL FORMATTINGS IN YOUR FORM YOU CAN ALSO CALL THE RENDER METHOD WITHOUT SPECIFYING A TEMPLATE NAME
        // AND THUS LETTING THE SCRIPT TO AUTOMATICALLY GENERATE OUTPUT AND SAVING YOU OF EXTRA WORK PUT INTO DESIGNING THE FORM'S LAYOUT
        // COMMENT THE LINE ABOVE AND UNCOMMENT THE ONE BELOW TO SEE IT IN ACTION

        //$form->render();

    ?>
    </body>
</html>
