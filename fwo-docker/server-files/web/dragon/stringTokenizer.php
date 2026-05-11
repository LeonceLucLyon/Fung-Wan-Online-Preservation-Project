<?php
// {{{ Header

/*
 * -File     $Id: StringTokenizer.php,v 1.8 2003/04/09 15:58:11 thyrell Exp $
 * -License    LGPL (http://www.gnu.org/copyleft/lesser.html)
 * -Copyright  2001, Thyrell
 * -Author     Andrzej Nowodworski, a.nowodworski@learn.pl
 * -Author     Anderas Aderhold, andi@binarycloud.com
 */
// }}}


//import('phing.system.lang.functions');
//require('functions.php');

/**
 *  @package  phing.system.util
 */

class StringTokenizer {

    var $currentPosition = null;
    var $newPosition     = null;
    var $maxPosition     = null;
    var $str             = null;
    var $delimiters      = null;
    var $retDelims       = null;
    var $delimsChanged   = false;
    var $maxDelimChar    = null;  // ordinal value of char


    function StringTokenizer($str, $delim , $returnDelims = false) {
        $this->currentPosition = (int) 0;
        $this->newPosition     = (int) -1;
        $this->delimsChanged   = false;
        $this->str             = (string) $str;
        $this->maxPosition     = (string) strlen($str);
        $this->delimiters      = (string) $delim;
        $this->retDelims       = (boolean) $returnDelims;
        $this->setMaxDelimChar();
    }

    /** Set maxDelimChar to the highest char in the delimiter set. */
    function setMaxDelimChar() {
        if ($this->delimiters === null) {
            $this->maxDelimChar = 0;
            return;
        }

        $m = 0;

        for ($i = 0; $i < strlen($this->delimiters); ++$i) {
            $c = ord($this->delimiters{$i});  // replace with charAt

            if ($m < $c) {
                $m = $c;
            }
        }
        $this->maxDelimChar = $m;
    }

    /**
     * Skips delimiters starting from the specified position. If retDelims
     * is false, returns the index of the first non-delimiter character at or
     * after startPos. If retDelims is true, startPos is returned.
     */
    function skipDelimiters($startPos) {
        if ($this->delimiters == null) {
            die("NullPointer");
        }

        $position = (int) $startPos;
        while (!$this->retDelims && ($position < $this->maxPosition)) {
            $c = ord($this->str{$position});
            if (($c > $this->maxDelimChar) || strIndexOf(chr($c), $this->delimiters) < 0)
                break;
            $position++;
        }
        return $position;
    }

    /**
     * Skips ahead from startPos and returns the index of the next delimiter
     * character encountered, or maxPosition if no such delimiter is found.
     */
    function scanToken($startPos) {
        $position = (int) $startPos;
        while ($position < $this->maxPosition) {
            $c = ord($this->str{$position});  //char at

            if (($c <= $this->maxDelimChar) && strIndexOf(chr($c), $this->delimiters) >= 0)
                break;
            $position++;
        }
        if ($this->retDelims && ($startPos == $position)) {
            $c = ord($this->str{$position});  //charAt

            if (($c <= $this->maxDelimChar) && strIndexOf(chr($c), $this->delimiters) >= 0)
                $position++;
        }
        return $position;
    }

    /**
     * Tests if there are more tokens available from this tokenizer's string.
     * If this method returns <tt>true</tt>, then a subsequent call to
     * <tt>nextToken</tt> with no argument will successfully return a token.
     *
     * @return  <code>true</code> if and only if there is at least one token
     *          in the string after the current position; <code>false</code>
     *          otherwise.
     */
    function hasMoreTokens() {
        //return ($this->token !== false) ? true :false;


        $this->newPosition = $this->skipDelimiters($this->currentPosition);
        return ($this->newPosition < $this->maxPosition);
    }

    /**
     * Returns the next token from this string tokenizer.
     *
     * @return     the next token from this string tokenizer.
     * @exception  NoSuchElementException  if there are no more tokens in this
     *               tokenizer's string.
     */
    function nextToken($delim = null) {
        if ($delim !== null) {
            $this->delimiters = (string) $delim;
            $this->delimsChanged = true;
            $this->setMaxDelimChar();
        }

        /*
         * If next position already computed in hasMoreElements() and
         * delimiters have changed between the computation and this invocation,
         * then use the computed value.
         */

        $this->currentPosition = (($this->newPosition >= 0) && !$this->delimsChanged) ?
                                 $this->newPosition : $this->skipDelimiters($this->currentPosition);

        $this->delimsChanged = false;
        $this->newPosition = -1;

        if ($this->currentPosition >= $this->maxPosition)
            die("NoSuchElementException");

        // use this explicit casts here to make it more readable

        $start = (int) $this->currentPosition;
        $this->currentPosition = $this->scanToken($this->currentPosition);
        return (string) substr($this->str, $start, $this->currentPosition-$start);
    }

    /**
     * Calculates the number of times that this tokenizer's
     * nextToken method can be called befores it generates an
     * exception. The current position is not advanced.
     *
     * @return  integer the number of tokens remaining in the string using the current
     *          delimiter set.
     * @see     StringTokenizer::nextToken()
     */
    function countTokens() {
        $count  = (int) 0;
        $currpos = (int) $this->currentPosition;
        while ($currpos < $this->maxPosition) {
            $currpos = $this->skipDelimiters($currpos);
            if ($currpos >= $this->maxPosition)
                break;
            $currpos = $this->scanToken($currpos);
            $count++;
        }
        return $count;
    }
}

function is_trie($my_array) {
    foreach($my_array as $key => $value) {
        if(is_array($value)) {
            return 1;
            break;
        }
    }
}

/** tests if a string starts with a given string */
function strStartsWith($_check, $_string) {
    if (empty($_check) || $_check === $_string) {
        return true;
    } else {
        return (strpos((string) $_string, (string) $_check) === 0) ?
true : false;
    }
}

/** tests if a string ends with a given string */
function strEndsWith($_check, $_string) {
    if (empty($_check) || $_check === $_string) {
        return true;
    } else {
        return (strpos(strrev($_string), strrev($_check)) === 0) ?
true : false;
    }
}

function strIndexOf($needle, $hystack, $offset = 0) {
    return ((($res = strpos($hystack, $needle, $offset)) === false) ? -1 :
$res);
}
function strLastIndexOf($needle, $hystack, $offset = 0) {
    // FIXME, use offset
    //$pos = strlen($hystack) - (strpos(strrev($hystack),strrev($needle)) + strlen($needle));
    //return ($pos === false ? -1 : $res);
    return ((($res = strrpos($hystack, $needle)) === false) ? -1 : $res);
}

/** converts a string to an indexed array of chars */
function strToCharArray($_string) {
    $ret = array();
    for ($i=0; $i<strlen($_string); array_push($ret, $_string[$i]), ++$i)
        ;
    return $ret;
}

function isInstanceOf(&$object, $classname) {
    if (is_object($object)
&& (get_class($object) === strtolower($classname))) {
        return true;
    }
    return false;
}

/* a natural way of getting a subtring, php's circular string buffer and
strange
return values suck if you want to program strict as of C or friends */
function substring($string, $startpos, $endpos = -1) {
    $len    = strlen($string);
    $endpos = (int) (($endpos === -1) ? $len-1 : $endpos);
    if ($startpos > $len-1 || $startpos < 0) {
        trigger_error("substring(), Startindex out of bounds must be
0<n<$len", E_USER_ERROR);
    }
    if ($endpos > $len-1 || $endpos < $startpos) {
        trigger_error("substring(), Endindex out of bounds must be
$startpos<n<".($len-1), E_USER_ERROR);
    }
    if ($startpos === $endpos) {
        return (string) $string{$startpos};
    } else {
        $len = $endpos-$startpos;
    }
    return (string) substr($string, $startpos, $len+1);
}

// workaround to compare two references if they are referring the same objcect
function compareReferences(&$a, &$b) {
    $tmp = uniqid("");
    $a->$tmp = (boolean) true;
    $result  = @ ($b->$tmp === true);
    unset($a->$tmp);
    return $result;
}
function getMicrotime() {
    list($usec, $sec) = explode(" ",microtime());
    return ((float)$usec + (float)$sec);
}

/* workaround for php <a 4.2.0 */
if (!function_exists('is_a')) {
    function is_a(&$object, $class_name) {
        if (get_class($object) === strtolower($class_name)) {
            return true;
        } else {
            return is_subclass_of($object, $class_name);
        }
    }
}

/**
 * array better_parse_ini_file (string $filename
[, boolean $process_sections] )
 *
 * Purpose: Load in the ini file specified in filename, and return
 *          the settings in an associative array. By setting the
 *          last $process_sections parameter to true, you get a
 *          multidimensional array, with the section names and
 *          settings included. The default for process_sections is
 *          false.
 *
 * Return: - An associative array containing the data
 *         - false if any error occured
 *
 * Author: Sebastien Cevey <seb@cine7.net>
 *         Original Code base : <info@megaman.nl>
 *         changes for Phing: Manuel Holtgrewe <purestorm at teforge dot
org>
 */
function better_parse_ini_file($filename, $process_sections = false) {
    // build array with environment variables
    $env_vars = array();
    foreach ($_SERVER as $key => $value) {
        if (!is_array($value) and !is_object($value))
            $env_vars["\${env." . $key . "}"] = $value;
    }

    // do INI parsing
    $ini_array = array();
    $sec_name = "";
    $lines = file($filename);
    foreach($lines as $line) {
        $line = trim($line);

        if($line == "")
            continue;

        if($line[0] == "[" && $line[strlen($line) - 1] == "]") {
            $sec_name = substr($line, 1, strlen($line) - 2);
        } else if ($line[0] === "#" or $line[0] === ";") {
            continue;
        } else {
            $pos = strpos($line, "=");
            $property = substr($line, 0, $pos);
            $value = substr($line, $pos + 1);

            if($process_sections) {
                $ini_array[$sec_name][$property] =
                    str_replace(array_keys($env_vars),
array_values($env_vars), $value);
            } else {
                $ini_array[$property] =
                    str_replace(array_keys($env_vars),
array_values($env_vars), $value);
            }
        }
    }
    return $ini_array;
}

// (c) jean-christophe michel 2002
// changed by manuel holtgrewe
// patch with recent php functions

function better_var_export($var, $return = true, $depth = 0) {
    if (is_null($var))
        return "null";

    $result = str_repeat(" ", $depth);
    if (is_array($var)) {
        $result .= "array(\n";
        $depth += 4;
        foreach ($var as $key => $value) {
            $result .= better_var_export($key, true, $depth);
            $result .= " => ";
            $result .= better_var_export($value, true, 0);
            $result .= ",\n";
        }
        $result .= str_repeat(" ", $depth);
        $result .= ")";
        $depth -= 4;
    } elseif (is_string($var)) {
        $result .= "\"" . str_replace("\"", "\\\"", $var) . "\"";
    } elseif (is_bool($var)) {
        $result .= $var ? "true" : "false";
    } else {
        $result .= $var;
    }

    return $result;
}
?>