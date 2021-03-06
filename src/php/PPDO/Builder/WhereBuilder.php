<?php
/**
 * Created by PhpStorm.
 * User: krajewski
 * Date: 15.10.14
 * Time: 17:04
 */

namespace PPDO\Builder;


class WhereBuilder
{
	private $conditions = array();
	private $parameters = array();

	public function addCondition($condition, $param = null)
	{
		if (is_array($condition) && is_null($param)) {
			foreach ($condition as $key => $val) {
				$this->addCondition($key, $val);
			}

		} else if (!is_array($condition)) {
			// ->where('a', ...
			if (preg_match('/^[a-zA-Z0-9\.\(\)]*$/', $condition)) {
				if (!is_array($param)) {
					if ($param === true) {
						// ->where('a', true);
						$param = 1;
					} else if ($param === false) {
						// ->where('a', false);
						$param = 0;
					}
					// ->where('a', 1);
					// ->where('a', 'x');
					$this->conditions[] = $condition . ' = ?';
					$this->parameters[] = $param;

				} else {
					// ->where('a', array(1, 2));
					$this->conditions[] = $condition . ' IN (' . trim(str_repeat('?,', count($param)), ',') . ')';
					$this->parameters = array_merge($this->parameters, $param);
				}

			} else if (preg_match_all('/\?+/', $condition, $matches) && count($matches[0]) > 1 && !is_array($param)) {
				// ->where('a = ? OR b = ?', 1);
				$this->conditions[] = $condition;
				$param = array_fill(0, count($matches[0]), $param);
				$this->parameters = array_merge($this->parameters, $param);
				echo 1;

			} else {
				// by PDO
				$this->conditions[] = $condition;
				if (is_array($param)) {
					$this->parameters = array_merge($this->parameters, $param);
				} else {
					$this->parameters[] = $param;
				}
			}

		} else {
			throw new \ErrorException('NOT_ALLOWED');
		}
	}

	public function hasConditions()
	{
		return !empty($this->conditions);
	}

	public function getConditions()
	{
		return '(' . implode(') AND (', $this->conditions) . ')';
	}

	public function getParameters()
	{
		return $this->parameters;
	}
} 