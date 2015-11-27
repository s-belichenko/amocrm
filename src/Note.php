<?php

namespace AmoCRM;

class Note extends Entity
{
	public $element_id;
	public $element_type;
	public $note_type;
	public $text;

	const DEAL_CREATED = 1;            // Сделка создана
	const CONTACT_CREATED = 2;         // Контакт создан
	const DEAL_STATUS_CHANGED = 3;     // Статус сделки изменен
	const COMMON = 4;                  // Обычное примечание
	const ATTACHEMENT = 5;             // Файл
	const CALL = 6;                    // Звонок приходящий от айфон приложений
	const MAIL_MESSAGE = 7;            // Письмо
	const MAIL_MESSAGE_ATTACHMENT = 8; // Письмо с файлом
	const CALL_IN = 10;                // Входящий звонок
	const CALL_OUT = 11;               // Исходящий звонок
	const COMPANY_CREATED = 12;        // Компания создана
	const TASK_RESULT = 13;            // Результат по задаче
	const SMS_IN = 102;                // Входящее смс
	const SMS_OUT = 103;               // Исходящее смс

	const TYPE_CONTACT = 1;            // Привязка к контакту
	const TYPE_LEAD = 2;               // Привязка к сделке

	public function __construct()
	{
		$this->type = 'notes';
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

	public function setNoteType($value)
	{
		$this->note_type = $value;

		return $this;
	}

	public function setText($value)
	{
		$this->text = $value;

		return $this;
	}
}
