<?php

namespace AmoCRM;

class Entity
{
	public $id;
	public $last_modified;
	public $type;

	public function setUpdate($id, $last_modified)
	{
		$this->id = $id;
		$this->last_modified = $last_modified;

		return $this;
	}
}
