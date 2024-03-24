<?php 
function format_date($date_time, $format = 'd/m/Y')
{
	return date($format, strtotime($date_time));
}