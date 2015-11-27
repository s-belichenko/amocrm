# amoCRM API
Простая обертка для работы с API AmoCRM.

## Что умеет
- Получать информацию об аккаунте
- Получать список объектов
- Создавать новые объекты
- Работать с кастомными полями
- Работать с вебхуками

На данный момент для запросов доступны следующие объекты:
- Контакт [https://developers.amocrm.ru/rest_api/#contact](https://developers.amocrm.ru/rest_api/#contact)
- Компания [https://developers.amocrm.ru/rest_api/#company](https://developers.amocrm.ru/rest_api/#company)
- Сделка [https://developers.amocrm.ru/rest_api/#lead](https://developers.amocrm.ru/rest_api/#lead)
- Задача [https://developers.amocrm.ru/rest_api/#tasks](https://developers.amocrm.ru/rest_api/#tasks)
- Событие [https://developers.amocrm.ru/rest_api/#event](https://developers.amocrm.ru/rest_api/#event)

### Требования
- PHP >= 5.4
- libcurl на сервере

---

## Установка
Добавьте в блок "require" в composer.json вашего проекта
```json
"nabarabane/amocrm": "~1.1"
```

Или введите в консоли
```sh
composer require nabarabane/amocrm:"~1.1"
```

## Подготовка к работе и настройка
Создайте папку "config" в корне пакета, куда положите два файла:
- config@{ваш-домен-в-amocrm}.php
- {ваш-домен-в-amocrm}@{email-пользователя-для-запросов}.key
Директория должна быть доступна для записи, туда сохраняются куки, которые необходимы для работы API.

Файл с конфигом используется для хранения номеров кастомных полей из AmoCRM, которые вы будете использовать в своей работе, и должен возвращать ассоциативный массив, например:
```php
return [
	'ResponsibleUserId' => 330242, //ID ответственного менеджера
	'LeadStatusId' => 8156376, // ID первого статуса сделки
	'ContactFieldPhone' => 1426544, // ID поля номера телефона
	'ContactFieldEmail' => 1426546, // ID поля емейла
	'LeadFieldCustom' => 1740351, // ID кастомного поля сделки
	'LeadFieldCustomValue1' => 4055517, // ID первого значения кастомного поля сделки
	'LeadFieldCustomValue2' => 4055519 // ID второго значения кастомного поля сделки
];
```

Номера полей вашего аккаунта можно получить так:
```php
use AmoCRM\Handler;
use AmoCRM\Request;

require('autoload.php');

$api = new Handler('domain', 'user@example.com');
print_r($api->request(new Request(Request::INFO))->result);
```

На страницу будет выведена вся информация об аккаунте.  
Выбираете номера нужных полей (номера пользователей, номера кастомных полей сделок и т.д.) и сохраняете в конфиг с понятными вам названиями.

Файл с ключом должен содержать в себе API-ключ выбранного пользователя.

---

## Использование
### Получение данных

```php
use \AmoCRM\Handler;
use \AmoCRM\Request;

require('autoload.php');

/* Создание экземпляра API, где "domain" - имя вашего домена в AmoCRM, а
"user@example.com" - email пользователя, от чьего имени будут совершаться запросы */
$api = new Handler('domain', 'user@example.com');

/* Создание экземляра запроса */

/* Вторым параметром можно передать дополнительные параметры поиска (смотрите в документации)
В этом примере мы ищем пользователя с номером телефона +7 916 111-11-11
Чтобы получить полный список, укажите пустой массив []
Третьим параметром указывается метод в формате [название объекта, метод] */
$request_get = new Request(Request::GET, ['query' => '79161111111'], ['contacts', 'list']);

/* Выполнение запроса */
$result = $api->request($request)->result;

/* Результат запроса сохраняется в свойстве "result" объекта \AmoCRM\Handler()
Содержит в себе объект, полученный от AmoCRM, какой конкретно - сверяйтесь с документацией для каждого метода
Ошибка запроса выбросит исключение */
$api->result == false, если ответ пустой (то есть контакты с таким телефоном не найдены) */
```

### Создание новых объектов
Пример рабочего кода, который покрывает все доступные возможности библиотеки

```php
use \AmoCRM\Handler;
use \AmoCRM\Request;
use \AmoCRM\Lead;
use \AmoCRM\Contact;
use \AmoCRM\Note;
use \AmoCRM\Task;

require('autoload.php');

/* Предположим, пользователь ввел какие-то данные в форму на сайте */
$name = 'Пользователь';
$phone = '79161111111';
$email = 'user@user.com';
$message = 'Здравствуйте';

/* Оборачиваем в try{} catch(){}, чтобы отлавливать исключения */
try {
	$api = new Handler('domain', 'user@example.com');


	/* Создаем сделку,
	$api->config содержит в себе массив конфига,
	который вы создавали в начале */
	$lead = new Lead();
	$lead
		/* Название сделки */
		->setName('Заявка') 
		/* Назначаем ответственного менеджера */
		->setResponsibleUserId($api->config['ResponsibleUserId'])
		/* Кастомное поле */
		->setCustomField(
			$api->config['LeadFieldCustom'], // ID поля
			$api->config['LeadFieldCustomValue1'] // ID значения поля
		)
		/* Теги. Строка - если один тег, массив - если несколько */
		->setTags(['тег 1', 'тег 2'])
		/* Статус сделки */
		->setStatusId($api->config['LeadStatusId']);

	/* Отправляем данные в AmoCRM
	В случае успешного добавления в результате
	будет объект новой сделки */
	$api->request(new Request(Request::SET, $lead));

	/* Сохраняем ID новой сделки для использования в дальнейшем */
	$lead = $api->last_insert_id;


	/* Создаем контакт */
	$contact = new Contact();
	$contact
		/* Имя */
		->setName($name)
		/* Назначаем ответственного менеджера */
		->setResponsibleUserId($api->config['ResponsibleUserId'])
		/* Привязка созданной сделки к контакту */
		->setLinkedLeadsId($lead)
		/* Кастомные поля */
		->setCustomField(
			$api->config['ContactFieldPhone'],
			$phone, // Номер телефона
			'MOB' // MOB - это ENUM для этого поля, список доступных значений смотрите в информации об аккаунте
		) 
		->setCustomField(
			$api->config['ContactFieldEmail'],
			$email, // Email
			'WORK' // WORK - это ENUM для этого поля, список доступных значений смотрите в информации об аккаунте
		) 
		/* Теги. Строка - если один тег, массив - если несколько */
		->setTags(['тег контакта 1', 'тег контакта 2']);

	/* Проверяем по емейлу, есть ли пользователь в нашей базе */
	$api->request(new Request(Request::GET, ['query' => $email], ['contacts', 'list']));

	/* Если пользователя нет, вернется false, если есть - объект пользователя */
	$contact_exists = ($api->result) ? $api->result->contacts[0] : false;

	/* Если такой пользователь уже есть - мержим поля */
	if ($contact_exists) {
		$contact
			/* Указываем, что пользователь будет обновлен */
			->setUpdate($contact_exists->id, $contact_exists->last_modified + 1)
			/* Ответственного менеджера оставляем кто был */
			->setResponsibleUserId($contact_exists->responsible_user_id)
			/* Старые привязанные сделки тоже сохраняем */
			->setLinkedLeadsId($contact_exists->linked_leads_id);
	}


	/* Создаем заметку с сообщением из формы */
	$note = new Note();
	$note
		/* Привязка к созданной сделке*/
		->setElementId($lead)
		/* Тип привязки (к сделке или к контакту). Смотрите комментарии в Note.php */
		->setElementType(Note::TYPE_LEAD)
		/* Тип заметки (здесь - обычная текстовая). Смотрите комментарии в Note.php */
		->setNoteType(Note::COMMON)
		/* Текст заметки*/
		->setText($message);



	/* Создаем задачу для менеджера обработать заявку */
	$task = new Task();
	$task
		/* Привязка к созданной сделке */
		->setElementId($lead)
		/* Тип привязки (к сделке или к контакту) Смотрите комментарии в Task.php */
		->setElementType(Task::TYPE_LEAD)
		/* Тип задачи. Смотрите комментарии в Task.php */
		->setTaskType(Task::CALL)
		/* ID ответственного за задачу менеджера */
		->setResponsibleUserId($api->config['ResponsibleUserId'])
		/* Дедлайн задачи */
		->setCompleteTill(time() + 60 * 2)
		/* Текст задачи */
		->setText('Обработать заявку');


	/* Отправляем все в AmoCRM */
	$api->request(new Request(Request::SET, $contact));
	$api->request(new Request(Request::SET, $note));
	$api->request(new Request(Request::SET, $task));
} catch (\Exception $e) {
	echo $e->getMessage();
}
```

### Мультизагрузка объектов
Есть возможность создавать одновременно несколько объектов одного типа и отправлять их в amoCRM одним запросом

```php
use \AmoCRM\Handler;
use \AmoCRM\Request;
use \AmoCRM\Lead;

require('autoload.php');

try {
	$api = new Handler('domain', 'user@example.com');

	/* Первая сделка */
	$lead1 = new Lead();
	$lead1
	    ->setName('Заявка 1') 
		->setResponsibleUserId($api->config['ResponsibleUserId'])
		->setStatusId($api->config['LeadStatusId']);
	
	/* Вторая сделка */
	$lead2 = new Lead();
	$lead2
	    ->setName('Заявка 2') 
		->setResponsibleUserId($api->config['ResponsibleUserId'])
		->setStatusId($api->config['LeadStatusId']);

	/* Отправляем данные в AmoCRM */
	$api->request(new Request(Request::SET, [$lead1, $lead2]));
```

## Дебаггинг
В случае ошибки запроса к API, AmoCRM возвращает только номер ошибки, без текстовых пояснений.  
Чтобы включить текстовые пояснения для ошибок, передайте в конструктор хендлера "true" третьим параметром:
```php
$api = new Handler('domain', 'user@example.com', true);
```
Теперь вместе с номером ошибки вы будете видеть и что же этот номер означает, и упростите дебаггинг.  
Включение этого режима создает дополнительный запрос к диску на чтение файла, где сохранены описания ошибок, поэтому не забудьте отключить дебаггинг в продакшене.

---

## Webhooks
Как настроить аккаунт на работу с вебхуками смотрите [здесь](https://developers.amocrm.ru/rest_api/webhooks.php).  
Чтобы успешно обрабатывать запрос от AmoCRM на ваш сайт, вам нужно создать слушателя событий в файле, на который AmoCRM шлет свои запросы, и определить функции, которые будут вызываться при определенном событии.

### Список доступных событий

#### Сделки
- **leads-add** Создание сделки
- **leads-update** Изменение сделки
- **leads-delete** Удаление сделки
- **leads-status** Смена статуса сделки
- **leads-responsible** Смена ответственного сделки

#### Контакты
- **contacts-add** Создание контакта
- **contacts-update** Изменение контакта
- **contacts-delete** Удаление контакта

#### Компании
- **companies-add** Создание компании
- **companies-update** Изменение компании
- **companies-delete** Удаление компании

Обратите внимание, что при смене статуса сделки или при смене ответственного сделки, AmoCRM одновременно посылает информацию и об общем изменении сделки, то есть код для **leads-status** и **leads-responsible** всегда будет выполняться вместе с **leads-update.**
```php
use \AmoCRM\Webhook;

require('autoload.php');

$listener = new Webhook();

/* Указываете обработчики событий
Callback-функция, передаваемая вторым параметром,
будет вызвана при наступлении соответстующего события */
$listener->on('leads-add', function($domain, $id, $data, $config) {
	/* Тут делаете, что нужно при этом событии
	
	Сюда передаются следующие параметры:
		$domain - название домена в AmoCRM, с которого пришло событие
		$id - ID сущности
		$data - массив полей сущности
		$config - конфиг этого домена (если вы создавали соответствующий файл, иначе - пустой массив) */
});

/* Если вы хотите назначить одинаковый обработчик нескольким событиям, можно сделать так */
$listener->on(['contacts-add', 'contacts-update'], function($domain, $id, $data, $config) {/* ... */});

/* Запуск слушателя */
$listener->listen();
```