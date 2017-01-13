<?php
/**
 * Управление Задачами
 */
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
    
    /**
     * Task constructor.
     */
    public function __construct()
    {
        $this->key_name = 'tasks';
        $this->url_name = $this->key_name;
    }
    
    /**
     * Идентификатор контакта или сделки
     * @param $value
     *
     * @return $this
     */
    public function setElementId($value)
    {
        $this->element_id = $value;

        return $this;
    }
    
    /**
     * Тип привязываемого элемента (1 - контакт, 2- сделка, 3 - компания, константы)
     * @param $value
     *
     * @return $this
     */
    public function setElementType($value)
    {
        $this->element_type = $value;

        return $this;
    }
    
    /**
     * Тип задачи (см константы)
     * @param $value
     *
     * @return $this
     */
    public function setTaskType($value)
    {
        $this->task_type = $value;

        return $this;
    }
    
    /**
     * Ответственный пользователь, ид
     *
     * @param $value
     *
     * @return $this
     */
    public function setResponsibleUserId($value)
    {
        $this->responsible_user_id = $value;

        return $this;
    }
    
    /**
     * Дата до которой необходимо завершить задачу. Если указано время 23:59,
     * то в интерфейсах системы вместо времени будет отображаться "Весь день".
     * Тип - time()
     *
     * @param $value
     *
     * @return $this
     */
    public function setCompleteTill($value)
    {
        $this->complete_till = $value;

        return $this;
    }
    
    /**
     * Текст задачи
     *
     * @param $value
     *
     * @return $this
     */
    public function setText($value)
    {
        $this->text = $value;

        return $this;
    }
}
