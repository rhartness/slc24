<?php

function print_vars($variables, $type=0, $copy=array(), $exceptions=array(), $function=false, $seperator="&amp;")
{	
	$string = "";
	if($type == 0)
	{
		if(count($copy) == 0)
		{
			foreach($variables as $name => $value)
			{
				if($function)
					$value = $function($value);
					
				if($value == "0")
					$value = "0";
					
				if(!in_array($name, $exceptions) && ($value || $value == "0"))
				{
					$string .= "<input type=\"hidden\" name=\"$name\" value=\"$value\">\r\n";
				}
			}
		}
		else
		{
			foreach($variables as $name => $value)
			{
				if($function)
					$value = $function($value);
					
				if($value == "0")
					$value = "0";
						
				if(in_array($name, $copy) && ($value || $value == "0"))
				{
                    $string .= "<input type=\"hidden\" name=\"$name\" value=\"$value\">\r\n";
                }
			}
		}
	}
	else
	{
		if(count($copy) == 0)
		{
			$first = true;
			foreach($variables as $name => $value)
			{
				if($value == "0")
					$value = "0";
					
				if(!in_array($name, $exceptions) && ($value || $value == "0"))
				{
					if(!$first)
						$string .= $seperator;
					else
						$first = false;
					if($function)
						$value = $function($value);
					$string .= "$name=".urlencode($value);
				}
			}
		}
		else
		{
			$first = true;
			foreach($variables as $name => $value)
			{
				if($value == "0")
					$value = "0";
					
				if(in_array($name, $copy) && ($value || $value == "0"))
				{
                    if(!$first)
                        $string .= $seperator;
                    else
                        $first = false;
					if($function)
						$value = $function($value);
                    $string .= "$name=".urlencode($value);
                }
			}
		}
	}
	return $string;
}

?>