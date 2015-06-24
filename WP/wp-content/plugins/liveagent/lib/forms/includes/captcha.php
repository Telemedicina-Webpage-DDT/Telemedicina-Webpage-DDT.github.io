<?php

    /**
     *  Creates a CAPTCHA image
     *
     *  @author     Stefan Gabos <ix@nivelzero.ro>
     *  @copyright  (c) 2006 - 2008 Stefan Gabos
     */

    // as this file actually generates an image we set the headers accordingly
    //header('Content-type:image/jpeg');
    
    // the number of characters to be used in the generated image
    $charactersNumber = 5;

    // spacing between characters (this can also be a negative number)
    // you should leave this as it is (unless you want to increase it)
    // decreasing this value may result in characters overlapping and being hardly distinguishable
    $characterSpacing = -2;
    
    // each character's size will be randomly selected from this range
    $fontSizeVariation = array(20, 40);

    // each character's angle will be randomly selected from this range
    // (remember to also change the character spacing if you change these to avoid character overlapping)
    $fontAngleVariation = array(-10, 10);

    // the number of horizontal lines to be drawn on the generated image
    $linesNumber = 15;

    // if you changed anything above, you should probably change this too si that all the characters fit into the image
    // and there's not too much of blank space
    $imageWidth = 140;

    // if you changed anything above, you should probably change this too si that all the characters fit into the image
    // and there's not too much of blank space
    $imageHeight = 50;
    
    // the quality, in percents, of the generated image
    $imageQuality = 70;

    // list of characters from which to choose
    // (notice that characters that can be (in some circumstances) confused with others, are missing)
    // you should not alter this setting
    $charList = 'bcdkmpsx345678';
    
    $captcha = array();
    
    $resultString = '';
    
    $totalWidth = 0;
    
    // this is the used font
    $font = 'babelsans-bold.ttf';

    // first we figure out how much space the character would take
    for ($i = 0; $i < $charactersNumber; $i++) {

        // get a random character
        $char = $charList[rand(0, strlen($charList) - 1)];
        
        $resultString .= $char;

        // get a random size for the character
        $charSize = rand($fontSizeVariation[0], $fontSizeVariation[1]);
        
        // get a random angle for the character
        $charAngle = rand($fontAngleVariation[0], $fontAngleVariation[1]);

        // get the bounding box of the character
        $bbox = imagettfbbox($charSize, $charAngle, $font, $char);

        // resolve the returned measurements
        $bbox['left'] = abs(min($bbox[0], $bbox[2], $bbox[4], $bbox[6]));

		$bbox['top'] = abs(min($bbox[1], $bbox[3], $bbox[5], $bbox[7]));

		$bbox['width'] = max($bbox[0], $bbox[2], $bbox[4], $bbox[6]) -  min($bbox[0], $bbox[2], $bbox[4], $bbox[6]);

		$bbox['height'] = max($bbox[1], $bbox[3], $bbox[5], $bbox[7]) - min($bbox[1], $bbox[3], $bbox[5], $bbox[7]);
        
        // this will be the total width of the random generated word
        $totalWidth += $bbox['width'] + $characterSpacing;

        // save info about the current character
        $captcha[] = array(

            'char'  =>  $char,
            'size'  =>  $charSize,
            'angle' =>  $charAngle,
            'box'   =>  $bbox

        );
        
    }

    // sets a cookie that will later be read by the form generator and used to see if user entered the correct characters
    setcookie('captcha', md5(md5(md5($resultString))), time() + 3600, '/');
    
    // create the image
    $img = imagecreatetruecolor($imageWidth, $imageHeight);

    // allocate some colors
    $white = imagecolorallocate($img, 255, 255, 255);

    $black = imagecolorallocate($img, 0, 0, 0);

    // fill the canvas to white
    imagefilledrectangle($img, 0, 0, $imageWidth, $imageHeight, $white);

    // draw a bounding rectangle
    imagerectangle($img, 0, 0, $imageWidth - 1, $imageHeight - 1, $black);

    // this is to keep the word centered in the box
    $left = (($imageWidth - $totalWidth) / 2);

    // iterate through the chosen characters
    foreach ($captcha as $values) {
    
        // print each character
        imagettftext($img, $values['size'], $values['angle'], $left , ($imageHeight + $values['box']['height']) / 2 , $black, $font, $values['char']);

        // compute the position of the next character
        $left += $values['box']['width'] + $characterSpacing;
        
    }
    
    $lines = array();

    // start drawing the number of lines specified in $linesNumber
    for ($i = 0; $i < $linesNumber; $i++) {
    
        // get a random position for the line (inside the box so that we don't write on the margins)
        $line = rand(2, $imageHeight - 2);

        $counter = 0;

        // if the line is not allowed at that position
        // and we've not reached $imageHeight iterations (this is to ensure we don't get infinite loops)
        while (in_array($line, $lines) && ++$counter < $imageHeight) {

            // pick a new random position
            $line = rand(2, $imageHeight - 2);
        
        }
        
        // save the position of the line
        $lines[] = $line;

		// and also save adjacent positions so that lines are not drawn near each other but having at least one pixel between

		$lines[] = $line - 1;

		$lines[] = $line + 1;

        // draw the line using white color
        imageline($img, 1, $line, $imageWidth - 2, $line, $white);

    }
    
    // and finally output the image at the specified quality
    imagejpeg($img, '', $imageQuality);
    
    // free memory
    imagedestroy($img);
    
?>
