<?php

function random_code($length = 8,$chars = null)
    {
	 	if(empty($chars))
	 	{
	 		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		}
	    $count = strlen($chars) - 1;
	  	$code = '';
	    while( strlen($code) < $length)
    	{
    		$code .= substr($chars,rand(0,$count),1);
	  	}
	  	return $code;
	}