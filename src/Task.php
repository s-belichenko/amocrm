<?php

namespace AmoCRM;

class Task extends Entity
{
	public $element_id;
	public $element_type;
	public $task_type;
	public $responsible_user_id;
	public $complete_till;
	public $text;

	const CALL = 1;
	const MEETING = 2;
	const LETTER = 3;

	const TYPE_CONTACT = 1; // Првязка к контакту
	const TYPE_LEAD = 2; // Привязка к сделке

	public function __construct()
	{
		$this->type = 'tasks';
	}

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
}
