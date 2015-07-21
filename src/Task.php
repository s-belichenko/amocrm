<?php

namespace AmoCRM;

class Task
{
	public $element_id;
	public $element_type;
	public $task_type;
	public $responsible_user_id;
	public $complete_till;
	public $text;
	public $id;
	public $last_modified;
	public $_name = 'tasks';

	const CALL = 1;
	const MEETING = 2;
	const LETTER = 3;

	const TYPE_CONTACT = 1;
	const TYPE_LEAD = 2;

	public function setElementId($value)
	{
		$this->element_id = $value;

		return $this;
	}

	public function setElementType($value)
	{
		$this->element_type = $value;

		return $this;
	}

	public function setTaskType($value)
	{
		$this->task_type = $value;

		return $this;
	}

	public function setResponsibleUserId($value)
	{
		$this->responsible_user_id = $value;

		return $this;
	}

	public function setCompleteTill($value)
	{
		$this->complete_till = $value;

		return $this;
	}

	public function setText($value)
	{
		$this->text = $value;

		return $this;
	}

	public function setUpdate($id, $lat_modified)
	{
		$this->id = $id;
		$this->last_modified = $last_modified;

		return $this;
	}
}
