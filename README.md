# amocrm
Простая обертка для работы с API AmoCRM
## Что умеет
- Получать информацию об аккаунте
- Получать список объектов
- Создавать новые объекты
- Работать с кастомными полями

На данный момент для запросов доступны следующие объекты:
- Контакт [https://developers.amocrm.ru/rest_api/#contact](https://developers.amocrm.ru/rest_api/#contact)
- Сделка [https://developers.amocrm.ru/rest_api/#lead](https://developers.amocrm.ru/rest_api/#lead)
- Задача [https://developers.amocrm.ru/rest_api/#tasks](https://developers.amocrm.ru/rest_api/#tasks)
- Событие [https://developers.amocrm.ru/rest_api/#event](https://developers.amocrm.ru/rest_api/#event)

###Требования
- PHP >= 5.4
- libcurl на сервере

## Установка
Добавьте в блок "require" в composer.json вашего проекта
```json
"nabarabane/amocrm": "1.*"
```
## Подготовка к работе и настройка
Создайте папку "config" в корне пакета, куда положите два файла:
- config@{ваш-домен-в-amocrm}.php
- {ваш-домен-в-amocrm}@{email-пользователя-для-запросов}.key

Директория должна быть доступна для записи, туда сохраняются куки, которые необходимы для работы API.

Файл с конфигом используется для хранения номеров кастомных полей из AmoCRM, которые вы будете использовать в своей работе, и должен возвращать ассоциативный массив, например:
```php
<?php

return [
	'ResponsibleUserId' => 330242, //ID ответственного менеджера
	'LeadStatusId' => 8156376, // ID первого статуса сделки
	'ContactFieldPhone' => 1426544, // ID поля номера телефона
	'ContactFieldEmail' => 1426546, // ID поля номера телефона
	'LeadFieldCustom' => 1740351, // ID кастомного поля сделки
	'LeadFieldCustomValue1' => 4055517, // ID первого значения кастомного поля сделки
	'LeadFieldCustomValue2' => 4055519 // ID второго значения кастомного поля сделки
];
```
Номера полей вашего аккаунта можно получить так:
```php
<?php

use AmoCRM\Handler;
use AmoCRM\Request;

require('autoload.php');

$api = new Handler('domain', 'user@example.com');
print_r($api->request(new Request(Request::INFO))->result);
```
На страницу будет выведена вся информация об аккаунте. Выбираете номера нужных полей (номера пользователей, номера кастомных полей сделок и т.д.) и сохраняете в конфиг с понятными вам названиями.

Файл с ключом должен содержать в себе API-ключ выбранного пользователя.
## Использование
```php
<?php

use \AmoCRM\Handler;
use \AmoCRM\Request;
use \AmoCRM\Lead;
use \AmoCRM\Contact;
use \AmoCRM\Note;
use \AmoCRM\Task;

require('autoload.php');

// Создание экземпляра API, где "domain" - имя вашего домена в AmoCRM, а
// "user@example.com" - email пользователя, от чьего имени будут совершаться запросы
$api = new Handler('domain', 'user@example.com');

// Создание экземляра запроса
// Для примера - поиск пользователя по номеру телефона
// 1 - тип запроса (Request::GET - получить, Request::POST - отправить)
// 2 - GET - параметры запроса (свои для каждого метода, сверяйтесь с документацией), SET - объект
// 2 - только для GET - метод запроса ([объект_запроса, метод_запроса]).
$request = new Request(Request::GET, ['query' => '79161111111'], ['contacts', 'list']);

//Выполнение запроса
$result = $api->request($request)->result;
// Вернется объект, какой конкретно - сверяйтесь с документацией для кажого метода.
// Ошибка запроса выбросит исключение
// Вернется false, если ответ пустой (то есть контакты с таким телефоном не найдены)
```

## Создание и отправка нового объекта
Вот пример кода, который покрывает все доступные возможности.
Пользователь ввел некоторые данные в форму на сайте:
```php
<?php

use \AmoCRM\Handler;
use \AmoCRM\Request;
use \AmoCRM\Lead;
use \AmoCRM\Contact;
use \AmoCRM\Note;
use \AmoCRM\Task;

require __DIR__.'/lib/autoload.php';

$name = 'Пользователь';
$phone = '79161111111';
$email = 'user@user.com';
$message = 'Здравствуйте';

try {
	$api = new Handler('sektorpriz', 'crm@sektor-priz.ru');


	// Создаем сделку, $api->config содержит в себе массив конфига, который вы создавали в начале
	$lead = new Lead();
	$lead->setName('Заявка')
		->setResponsibleUserId($api->config['ResponsibleUserId'])
		->setCustomField($api->config['LeadFieldCustom'], $api->config['LeadFieldCustomValue1']) // ID поля, ID значения поля
		->setStatusId($api->config['LeadStatusId']);

	$api->request(new Request( Request::SET, $lead ));
	// Сохраняем ID новой сделки для использования в дальнейшем
	$lead = $api->result->leads->add[0]->id;


	// Создаем контакт
	$contact = new Contact();
	$contact->setName($name)
		->setResponsibleUserId($api->config['ResponsibleUserId'])
		->setLinkedLeadsId($lead) // Привязка созданной сделки к контакту
		->setCustomField($api->config['ContactFieldPhone'], $phone, 'MOB') // MOB - enum для этого поля, список доступных значений смотрите в информации об аккаунте
		->setCustomField($api->config['ContactFieldEmail'], $email, 'WORK'); // WORK - enum для этого поля, список доступных значений смотрите в информации об аккаунте

	// Проверяем по емейлу, есть ли пользователь в нашей базе
	$api->request(new Request(Request::GET, ['query' => $email], ['contacts', 'list']));
	$contact_exists = ($api->result) ? $api->result->contacts[0] : false;
	// Если такой пользователь уже есть - мержим поля
	if ($contact_exists) {
		$contact->setUpdate($contact_exists->id, $contact_exists->last_modified + 1)
			->setResponsibleUserId($contact_exists->responsible_user_id)
			->setLinkedLeadsId($contact_exists->linked_leads_id);
	}


	// Создаем заметку с сообщением из формы
	$note = new Note();
	$note->setElementId($lead) // Привязка к созданной сделке
		->setElementType(Note::TYPE_LEAD)
		->setNoteType(Note::COMMON)
		->setText($message);



	// Создаем задачу дял менеджера обработать заявку
	$task = new Task();
	$task->setElementId($lead) // Привязка к созданной сделке
		->setElementType(Task::TYPE_LEAD)
		->setTaskType(Task::CALL)
		->setResponsibleUserId($api->config['ResponsibleUserId'])
		->setCompleteTill(time() + 60 * 2) // В течение какого времени менеджеу нужно обработать заявку
		->setText('Обработать заявку');


	// Отправляем все в AmoCRM
	$api->request(new Request(Request::SET, $contact));
	$api->request(new Request(Request::SET, $note));
	$api->request(new Request(Request::SET, $task));
} catch (\Exception $e) {
	echo $e->getMessage();
}

echo 'Success';
```
