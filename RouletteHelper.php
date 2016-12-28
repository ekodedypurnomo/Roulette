<?php

class RouletteHelper
{

	static function getModel($model)
	{
		$CI = get_instance();
		try {
			return $CI->{strtolower($model)};
		} catch (Exception $e) {
			return null;
		}
	}

	static function insert($conn, $table = null, $data = null)
	{
		return $conn->insert($table, $data);
	}

	static function update($conn, $table = null, $data = null, $condition = null)
	{
		return $conn->update($table, $data, $condition);
	}

	static function delete($conn, $table = null, $condition = null)
	{
		return $conn->delete($table, $condition);
	}

	static function select($conn, $table = null, $fields = null,  $condition = null, $order = null, $limit = 0, $start = 0)
	{
		return $result = array();
	}

	static function select_one($conn, $table = null, $fields = null,  $condition = null, $order = null)
	{
		return $result = array();
	}

	static function select_count($conn, $table = null, $fields = null,  $condition = null, $order = null, $limit = 0, $start = 0)
	{
		return count($this->select($conn, $table, $fields,  $condition, $order, $limit, $start));
	}

	static function lastQuery($conn)
	{
		return $conn->last_query();
	}

	static function lastInsertId($conn)
	{
		return $conn->last_query();
	}

}
