<?php
	/**
	 * Tokenizes a given string.
	 *
	 * @param string $str The string to tokenize.
	 * @param array $reg Array of token types and regexps.
	 * @return array
	 */
	function Tokenize($str, $reg)
	{
		$tokens = array();
		while(strlen($str) > 0)
		{
			foreach($reg as $key => $val)
			{
				if(preg_match($val, $str, $matches))
				{
					$str = substr($str, strlen($matches[0]));
					$tokens[] = array($key, $matches[1]);
					break;
				}
			}
		}
		return $tokens;
	}

	// Example:

	$tokens = array('Number' => '/^([0-9.]+)/', 'String' => '/^([a-zA-Z]+)/'); // Array of tokens and their matching regexps.
	$arr = Tokenize('bleh156FOO', $tokens); // Tokenize the string.
	var_dump($arr); // Dump the result.
?>