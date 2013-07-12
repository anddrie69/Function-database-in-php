<?php
function db_connect(){
  global $_DB_CONFIG, $_DB_CON;
	$_DB_CON = mysql_connect( $_DB_CONFIG['host'], $_DB_CONFIG['user'], $_DB_CONFIG['pass'] ) or die(mysql_error());
	mysql_select_db($_DB_CONFIG['name'], $_DB_CON);
}

function db_close(){
	global $_DB_CON;
	mysql_close($_DB_CON);
}

function db_query($query = ''){
	db_connect();
	$query = mysql_query($query)or die(mysql_error());
	db_close();
	return $query;
}

function db_num_rows($query = ''){
	return mysql_num_rows($query);
}

function db_fetch_rows($query = ''){
	return mysql_fetch_row($query);
}

function db_fetch_array($query = ''){
	return mysql_fetch_array($query);
}

function db_fetch_assoc($query = ''){
	return mysql_fetch_assoc($query);
}

function db_real_escape($str, $is_like = FALSE){
	global $_DB_CON;
	
	if(is_array($str)){
		foreach($str as $key => $val)
			$str[$key] = db_real_escape($val, $like);
			
			return $str;
	}
	
	db_connect();
	
	if(function_exists('mysql_real_escape_string') AND is_resource($_DB_CON))
		$str = mysql_real_escape_string($str, $_DB_CON);
	elseif (function_exists('mysql_escape_string'))
		$str = mysql_escape_string(str);
	else
		$str = addslashes($str);
		
	db_close();
	
	if( $is_like )
		$str = str_replace(array('%', '_'), array('\\%', '\\_'), $str );
		
	return $str;
}

function db_escape($str){
	if (is_string($str))
		$str = "'". db_real_escape($str)."'";
	elseif (is_bool($str))
		$str = ($str === FALSE) ? 0 : 1;
	elseif (is_null($str))
		$str = 'NULL';
	
	return	$str;
}

function db_match_operator($str){
	$str = trim($str);
	if ( ! preg_match("/(\s|<|>|!|=|is null|is not null)/i", $str))
		return FALSE;
	
	return TRUE;
}

function db_get($table = '', $where = array(), $orderby = array()){
	
	$wherestr	= array();
	foreach ($where as $key => $val)
	{
		if (!db_match_operator($key))
			$key	.= " = ";
		
		$wherestr[]	= $key . "'" . $val ."'";
	}
	
	$orderstr	= array();
	foreach ($orderby as $key => $val)
		$orderstr[]	= $key." ".$val;
	
	
	$query	= "SELECT * FROM ".$table;
	$query	.= ($where != '' AND count($where) >=1) ? " WHERE ".implode(" AND ", $wherestr) : '';
	$query	.= ($orderby != '' AND count($orderby) != 0) ? " ORDER BY ".implode(" ", $orderstr) : '';
	
	return	db_query($query);
}

function db_insert($table = '', $fields = array()){
	$keys	= array();
	$values	= array();
	foreach ($fields as $key => $val)
	{
		$keys[]		= $key;
		$values[]	= $val;
	}
	
	db_query("INSERT INTO ".$table." (".implode(', ', $keys).") VALUES ('".implode("', '", $values)."')");
}

function db_update($table = '', $values = array(), $where = array()){
	$valstr	= array();
	foreach ($values as $key => $val)
		$valstr[] = $key . " = '" . $val ."'";
	
	$wherestr	= array();
	foreach ($where as $key => $val)
	{
		if (!db_match_operator($key))
			$key	.= " = ";
		
		$wherestr[]	= $key . "'" . $val ."'";
	}
	
	$query	= "UPDATE ".$table." SET ".implode(', ', $valstr);
	$query	.= ($where != '' AND count($where) >=1) ? " WHERE ".implode(" AND ", $wherestr) : '';
	
	db_query($query);
}

function db_delete($table = '', $where = array()){
	$wherestr	= array();
	foreach ($where as $key => $val)
	{
		if (!db_match_operator($key))
			$key	.= " = ";
		
		$wherestr[]	= $key . "'" . $val ."'";
	}
	
	$query	= "DELETE FROM ".$table;
	$query	.= ($where != '' AND count($where) >=1) ? " WHERE ".implode(" AND ", $wherestr) : '';
	
	db_query($query);
}
