<?php

namespace AmoCRM;

class Task extends Entity {
	public $element_id;
	public $element_type;
	public $task_type;
	public $responsible_user_id;
	public $complete_till;
	public $text;

	const FOLLOW_UP = 1; // Связаться с клиентом
	const CALL      = 1; // Позвонить клиенту (по сути аналогично предыдущему, но в Amo сделано именно так)
	const MEETING   = 2; // Встретиться с клиентом
	const LETTER    = 3; // Написать email клиенту

	const TYPE_CONTACT = 1; // Првязка к контакту
	const TYPE_LEAD    = 2; // Привязка к сделке
	const TYPE_COMPANY = 3; // Привязка к компании

	/**
	 * Task constructor.
	 */
	public function __construct() {
		$this->key_name = 'tasks';
		$this->url_name = $this->key_name;
	}

	/**
	 * @param int $value
	 *
	 * @return $this
	 */
	public function setElementId( $value ) {
		$this->element_id = $value;

		return $this;
	}

	/**
	 * @param int $value
	 *
	 * @return $this
	 */
	public function setElementType( $value ) {
		$this->element_type = $value;

		return $this;
	}

	/**
	 * @param int $value
	 *
	 * @return $this
	 */
	public function setTaskType( $value ) {
		$this->task_type = $value;

		return $this;
	}

	/**
	 * @param int $value
	 *
	 * @return $this
	 */
	public function setResponsibleUserId( $value ) {
		$this->responsible_user_id = $value;

		return $this;
	}

	/**
	 * @param int $value
	 *
	 * @return $this
	 */
	public function setCompleteTill( $value ) {
		$this->complete_till = $value;

		return $this;
	}

	/**
	 * @param string $value
	 *
	 * @return $this
	 */
	public function setText( $value ) {
		$this->text = $value;

		return $this;
	}
}
