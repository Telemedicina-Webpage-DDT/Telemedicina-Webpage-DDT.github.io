<?php

/**
 * XSS Clean
 *
 * This class is taken from the Code Igniter PHP Framework
 * with permission.
 *
 *  The {@link clean()} method is automatically run for each
 *  of the form's controls whenever the form is validated
 *  (unless disabled by calling {@link disable_xss_filters()})
 *
 * Sanitizes data so that Cross Site Scripting Hacks can be
 * prevented. This function does a fair amount of work but
 * it is extremely thorough, designed to prevent even the
 * most obscure XSS attempts. Nothing is ever 100% foolproof,
 * of course, but I haven't been able to get anything passed
 * the filter.
 *
 * Note: This function should only be used to deal with data
 * upon submission. It's not something that should
 * be used for general runtime processing.
 *
 * This function was based in part on some code and ideas I
 * got from Bitflux: http://blog.bitflux.ch/wiki/XSS_Prevention
 *
 * To help develop this script I used this great list of
 * vulnerabilities along with a few other hacks I've
 * harvested from examining vulnerabilities in other programs:
 * http://ha.ckers.org/xss.html
 *
 * @package		XSS_Clean
 * @author		Rick Ellis
 * @copyright	Copyright (c) 2006, EllisLab, Inc.
 * @license		http://www.codeignitor.com/user_guide/license.html
 * @link		http://www.codeigniter.com
 */
class xss_clean
{

    /**
     *  Filters a string for XSS injection attempts and removes any suspicious data
     *
     *  @param  string  $str    String to be filtered
     *
     *  @return string          Returns filtered string
     */
	function sanitize($str)
	{
	
		/*
		 * Remove Null Characters
		 *
		 * This prevents sandwiching null characters
		 * between ascii characters, like Java\0script.
         *
		 */
		$str = preg_replace('/\0+/', '', $str);

        $str = preg_replace('/(\\\\0)+/', '', $str);

		/*
		 * Validate standard character entities
		 *
		 * Add a semicolon if missing.  We do this to enable
		 * the conversion of entities to ASCII later.
         *
		 */
		$str = preg_replace('#(&\#?[0-9a-z]+)[\x00-\x20]*;?#i', "\\1;", $str);

		/*
		 * Validate UTF16 two byte encoding (x00)
		 *
		 * Just as above, adds a semicolon if missing.
         *
		 */
		$str = preg_replace('#(&\#x?)([0-9A-F]+);?#i',"\\1\\2;",$str);

		/*
		 * URL Decode
		 *
		 * Just in case stuff like this is submitted:
		 *
		 * <a href="http://%77%77%77%2E%67%6F%6F%67%6C%65%2E%63%6F%6D">Google</a>
		 *
		 * Note: Normally urldecode() would be easier but it removes plus signs
		 *
		 */
		$str = preg_replace("/(%20)+/", '9u3iovBnRThju941s89rKozm', $str);

        $str = preg_replace("/%u0([a-z0-9]{3})/i", "&#x\\1;", $str);

        $str = preg_replace("/%([a-z0-9]{2})/i", "&#x\\1;", $str);

        $str = str_replace('9u3iovBnRThju941s89rKozm', "%20", $str);

		/*
		 * Convert character entities to ASCII
		 *
		 * This permits our tests below to work reliably.
		 * We only convert entities that are within tags since
		 * these are the ones that will pose security problems.
		 *
		 */
		$str = preg_replace_callback("/[a-z]+=([\'\"]).*?\\1/si", array($this, '_attribute_conversion'), $str);

		$str = preg_replace_callback("/<([\w]+)[^>]*>/si", array($this, '_html_entity_decode_callback'), $str);

		/*
		 * Convert all tabs to spaces
		 *
		 * This prevents strings like this: ja	vascript
		 * NOTE: we deal with spaces between characters later.
		 * NOTE: preg_replace was found to be amazingly slow here on large blocks of data,
		 * so we use str_replace.
		 *
		 */
		$str = str_replace("\t", " ", $str);

		/*
		 * Not Allowed Under Any Conditions
		 */
		$bad = array(
		
    		'document.cookie'	=> '[removed]',
    		'document.write'	=> '[removed]',
    		'.parentNode'		=> '[removed]',
    		'.innerHTML'		=> '[removed]',
    		'window.location'	=> '[removed]',
    		'-moz-binding'		=> '[removed]',
    		'<!--'				=> '&lt;!--',
    		'-->'				=> '--&gt;',
    		'<!CDATA['			=> '&lt;![CDATA['
    		
		);

		foreach ($bad as $key => $val) {
		
			$str = str_replace($key, $val, $str);
		}

		$bad = array(
		
			"javascript\s*:"	=> '[removed]',
			"expression\s*\("	=> '[removed]', // CSS and IE
			"Redirect\s+302"	=> '[removed]'

		);

		foreach ($bad as $key => $val) {
		
			$str = preg_replace("#".$key."#i", $val, $str);

        }

		/*
		 * Makes PHP tags safe
		 *
		 *  Note: XML tags are inadvertently replaced too:
		 *
		 *	<?xml
		 *
		 * But it doesn't seem to pose a problem.
		 *
		 */
		$str = str_replace(array('<?php', '<?PHP', '<?', '?'.'>'),  array('&lt;?php', '&lt;?PHP', '&lt;?', '?&gt;'), $str);

		/*
		 * Compact any exploded words
		 *
		 * This corrects words like:  j a v a s c r i p t
		 * These words are compacted back to their correct state.
		 *
		 */
		$words = array('javascript', 'expression', 'vbscript', 'script', 'applet', 'alert', 'document', 'write', 'cookie', 'window');

        foreach ($words as $word) {
        
			$temp = '';
			
			for ($i = 0; $i < strlen($word); $i++) {
			
				$temp .= substr($word, $i, 1)."\s*";

            }

			// We only want to do this when it is followed by a non-word character
			// That way valid stuff like "dealer to" does not become "dealerto"
			$str = preg_replace('#('.substr($temp, 0, -3).')(\W)#ise', "preg_replace('/\s+/s', '', '\\1').'\\2'", $str);
			
		}

		/*
		 * Remove disallowed Javascript in links or img tags
		 */
		do
		{
			$original = $str;

			if (

                (version_compare(PHP_VERSION, '5.0', '>=') === TRUE && stripos($str, '</a>') !== FALSE) ||
                
				 preg_match("/<\/a>/i", $str)

            ) {
            
				$str = preg_replace_callback("#<a.*?</a>#si", array($this, '_js_link_removal'), $str);
				
			}

			if (

                (version_compare(PHP_VERSION, '5.0', '>=') === TRUE && stripos($str, '<img') !== FALSE) ||
                
				 preg_match("/img/i", $str)

            ) {
            
				$str = preg_replace_callback("#<img.*?".">#si", array($this, '_js_img_removal'), $str);
				
			}

			if (

                (

                    version_compare(PHP_VERSION, '5.0', '>=') === TRUE &&

                    (

                        stripos($str, 'script') !== FALSE ||

                        stripos($str, 'xss') !== FALSE

                    )

                ) ||
                
				preg_match("/(script|xss)/i", $str)

            ) {
            
				$str = preg_replace("#</*(script|xss).*?\>#si", "", $str);

            }
            
		}
		
		while ($original != $str);

		unset($original);

		/*
		 * Remove JavaScript Event Handlers
		 *
		 * Note: This code is a little blunt.  It removes
		 * the event handler and anything up to the closing >,
		 * but it's unlikely to be a problem.
		 *
		 */
		$event_handlers = array(
            'onblur',
            'onchange',
            'onclick',
            'onfocus',
            'onload',
            'onmouseover',
            'onmouseup',
            'onmousedown',
            'onselect',
            'onsubmit',
            'onunload',
            'onkeypress',
            'onkeydown',
            'onkeyup',
            'onresize',
            'xmlns'
        );

		$str = preg_replace("#<([^>]+)(".implode('|', $event_handlers).")([^>]*)>#iU", "&lt;\\1\\2\\3&gt;", $str);

		/*
		 * Sanitize naughty HTML elements
		 *
		 * If a tag containing any of the words in the list
		 * below is found, the tag gets converted to entities.
		 *
		 * So this: <blink>
		 * Becomes: &lt;blink&gt;
		 *
		 */
		$str = preg_replace('#<(/*\s*)(alert|applet|basefont|base|behavior|bgsound|blink|body|embed|expression|form|frameset|frame|head|html|ilayer|iframe|input|layer|link|meta|object|plaintext|style|script|textarea|title|xml|xss)([^>]*)>#is', "&lt;\\1\\2\\3&gt;", $str);

		/*
		 * Sanitize naughty scripting elements
		 *
		 * Similar to above, only instead of looking for
		 * tags it looks for PHP and JavaScript commands
		 * that are disallowed.  Rather than removing the
		 * code, it simply converts the parenthesis to entities
		 * rendering the code un-executable.
		 *
		 * For example:	eval('some code')
		 * Becomes:		eval&#40;'some code'&#41;
		 *
		 */
		$str = preg_replace('#(alert|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si', "\\1\\2&#40;\\3&#41;", $str);

		/*
		 * Final clean up
		 *
		 * This adds a bit of extra precaution in case
		 * something got through the above filters
		 *
		 */
		$bad = array(
		
			'document.cookie'	=> '[removed]',
			'document.write'	=> '[removed]',
			'.parentNode'		=> '[removed]',
			'.innerHTML'		=> '[removed]',
			'window.location'	=> '[removed]',
			'-moz-binding'		=> '[removed]',
			'<!--'				=> '&lt;!--',
			'-->'				=> '--&gt;',
			'<!CDATA['			=> '&lt;![CDATA['
			
		);

		foreach ($bad as $key => $val) {
		
			$str = str_replace($key, $val, $str);
			
		}

		$bad = array(
		
			"javascript\s*:"	=> '[removed]',
			"expression\s*\("	=> '[removed]', // CSS and IE
			"Redirect\s+302"	=> '[removed]'

		);

		foreach ($bad as $key => $val) {
		
			$str = preg_replace("#".$key."#i", $val, $str);
			
		}

		return $str;
		
	}

	/**
	 * JS Link Removal
	 *
	 * Callback function for xss_clean() to sanitize links
	 * This limits the PCRE backtracks, making it more performance friendly
	 * and prevents PREG_BACKTRACK_LIMIT_ERROR from being triggered in
	 * PHP 5.2+ on link-heavy strings
	 *
	 * @access	private
	 * @param	array
	 * @return	string
	 */
	function _js_link_removal($match)
	{
	
		return preg_replace("#<a.+?href=.*?(alert\(|alert&\#40;|javascript\:|window\.|document\.|\.cookie|<script|<xss).*?\>.*?</a>#si", "", $match[0]);

    }

	/**
	 * JS Image Removal
	 *
	 * Callback function for xss_clean() to sanitize image tags
	 * This limits the PCRE backtracks, making it more performance friendly
	 * and prevents PREG_BACKTRACK_LIMIT_ERROR from being triggered in
	 * PHP 5.2+ on image tag heavy strings
	 *
	 * @access	private
	 * @param	array
	 * @return	string
	 */
	function _js_img_removal($match)
	{

    	return preg_replace("#<img.+?src=.*?(alert\(|alert&\#40;|javascript\:|window\.|document\.|\.cookie|<script|<xss).*?\>#si", "", $match[0]);

    }

	/**
	 * Attribute Conversion
	 *
	 * Used as a callback for XSS Clean
	 *
	 * @access	private
	 * @param	array
	 * @return	string
	 */
	function _attribute_conversion($match)
	{

		return str_replace('>', '&lt;', $match[0]);

	}

	/**
	 * HTML Entity Decode Callback
	 *
	 * Used as a callback for XSS Clean
	 *
	 * @access	private
	 * @param	array
	 * @return	string
	 */
	function _html_entity_decode_callback($match)
	{

		return $this->_html_entity_decode($match[0]);

	}

	/**
	 * HTML Entities Decode
	 *
	 * This function is a replacement for html_entity_decode()
	 *
	 * In some versions of PHP the native function does not work
	 * when UTF-8 is the specified character set, so this gives us
	 * a work-around.  More info here:
	 * http://bugs.php.net/bug.php?id=25670
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	/* -------------------------------------------------
	/*  Replacement for html_entity_decode()
	/* -------------------------------------------------*/

	/*
	NOTE: html_entity_decode() has a bug in some PHP versions when UTF-8 is the
	character set, and the PHP developers said they were not back porting the
	fix to versions other than PHP 5.x.
	*/
	function _html_entity_decode($str, $charset='UTF-8')
	{

		if (stristr($str, '&') === FALSE) {

            return $str;
            
        }

		// The reason we are not using html_entity_decode() by itself is because
		// while it is not technically correct to leave out the semicolon
		// at the end of an entity most browsers will still interpret the entity
		// correctly.  html_entity_decode() does not convert entities without
		// semicolons, so we are left with our own little solution here. Bummer.

		if (

            function_exists('html_entity_decode') &&

            (

                strtolower($charset) != 'utf-8' ||

                version_compare(phpversion(), '5.0.0', '>=')

            )

        ) {
        
			$str = html_entity_decode($str, ENT_COMPAT, $charset);

			$str = preg_replace('~&#x([0-9a-f]{2,5})~ei', 'chr(hexdec("\\1"))', $str);

			return preg_replace('~&#([0-9]{2,4})~e', 'chr(\\1)', $str);

		}

		// Numeric Entities
		$str = preg_replace('~&#x([0-9a-f]{2,5});{0,1}~ei', 'chr(hexdec("\\1"))', $str);

		$str = preg_replace('~&#([0-9]{2,4});{0,1}~e', 'chr(\\1)', $str);

		// Literal Entities - Slightly slow so we do another check
		if (stristr($str, '&') === FALSE) {

			$str = strtr($str, array_flip(get_html_translation_table(HTML_ENTITIES)));

		}

		return $str;
		
	}

}

?>
