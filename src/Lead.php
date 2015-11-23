<?php

namespace AmoCRM;

class Lead
{
	public $name;
	public $responsible_user_id;
	public $tags;
	public $status_id;
	public $custom_fields = [];
	public $id;
	public $last_modified;
	public $_name = 'leads';

	private $tags_array = [];

	public function setName($value)
	{
		$this->name = $value;

		return $this;
	}

	public function setResponsibleUserId($value)
	{
		$this->responsible_user_id = $value;

		return $this;
	}

	public function setStatusId($value)
	{
		$this->status_id = $value;

		return $this;
	}

	public function setTags($value)
	{
		if (!is_array($value)) {
			$value = [$value];
		}

		$this->tags_array = array_merge($this->tags_array, $value);
		$this->tags = implode(',', $this->tags_array);

		return $this;
	}

	public function setCustomField($name, $value, $enum = false)
	{
		$field = [
			'id' => $name,
			'values' => []
		];

		$field_value = [];
		$field_value['value'] = $value;

		if ($enum) {
			$field_value['enum'] = $enum;
		}

		$field['values'][] = $field_value;

		$this->custom_fields[] = $field;

		return $this;
	}

	public function setUpdate($id, $last_modified)
	{
		$this->id = $id;
		$this->last_modified = $last_modified;

		return $this;
	}
}
