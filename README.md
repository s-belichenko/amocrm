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

### Установка версии 1.4
Добавьте в блок "require" в composer.json вашего проекта
```json
"nabarabane/amocrm": "~1.1"
```

Или введите в консоли
```sh
composer require nabarabane/amocrm:"~1.1"
```

### Установка версии 2
Добавьте блок:
```json
"repositories": [
    {
      "url": "https://github.com/mavsan/amocrm",
      "type": "git"
    }
  ],
```

Добавьте в блок "require" в composer.json вашего проекта
```json
"nabarabane/amocrm": "~2.0"
```

## Подготовка к работе и настройка
Создайте папку "config" в корне пакета, куда положите два файла:
- config@{ваш-домен-в-amocrm}.php
- {ваш-домен-в-amocrm}@{email-пользователя-для-запросов}.key
Директория должна быть доступна для записи, туда сохраняются куки, которые необходимы для работы API.

Так-же есть возможность указать папку, где хранятся файлы конфигруации и куда будут сохраняться куки curl. Для этого при создании экземпляра объекта `Handler` последним параметром необходимо передать путь к папке конфигурации.

Файл с конфигом используется для хранения номеров кастомных полей из AmoCRM, которые вы будете использовать в своей работе, и должен возвращать ассоциативный массив, например:
```php
return [
	'ResponsibleUserId' => 330242, //ID ответственного менеджера
	'LeadStatusId' => 8156376, // ID первого статуса сделки
	'ContactFieldPhone' => 1426544, // ID поля номера телефона
	'ContactFieldPhoneMOB' => 3972237, // ID поля ENUM - мобильный телефон
	'ContactFieldPhoneWORK' => 3972233, // ID поля ENUM - рабочий телефон
	'ContactFieldEmail' => 1426546, // ID поля емейла
	'LeadFieldCustom' => 1740351, // ID кастомного поля сделки
	'LeadFieldCustomValue1' => 4055517, // ID первого значения кастомного поля сделки
	'LeadFieldCustomValue2' => 4055519 // ID второго значения кастомного поля сделки
];
```

Номера полей вашего аккаунта можно получить так:

```php
use use \AmoCRM\Account;

require('autoload.php');

$api = new Handler('domain', 'user@example.com');

var_dump(Account::getAccountInfo($api));
// или:
print_r(Account::getAccountInfo($api));
```

На страницу будет выведена вся информация об аккаунте.  
Выбираете номера нужных полей (номера пользователей, номера кастомных полей сделок и т.д.) и сохраняете в конфиг с понятными вам названиями.

Файл с ключом должен содержать в себе API-ключ выбранного пользователя.

---

## Использование
### Получение данных

```php
use \AmoCRM\Handler;

require('autoload.php');

/* Создание экземпляра API, где "domain" - имя вашего домена в AmoCRM, а
"user@example.com" - email пользователя, от чьего имени будут совершаться запросы */
$api = new Handler('domain', 'user@example.com', 'путь к файлам конфигурации, если они вынесены отдельно');
```

Далее можно использовать один из двух вариантов:

```php
/* Вариант 1: */
use \AmoCRM\Contact;
use \AmoCRM\ContactsList;

$contactList = new ContactsList();
/* поиск по произвольным данным (телефон, email...) */
$result = $contactList->getByQuery($api, 'homer@simpson.com');
/* или поиск по идентификатору пользователя в AmoCRM */
$result = $contactList->getByID($api, 123456790);

/* В $result будет или массив экземпляров класса Contact или false, если ничего не найдено */
```

```php
/* Вариант 2 (лучше не использовать, это старый путь просто для иллюстрации, как еще можно сделать): */
use \AmoCRM\Request;

/* Создание экземляра запроса */

/* Вторым параметром можно передать дополнительные параметры поиска (смотрите в документации)
В этом примере мы ищем пользователя с номером телефона +7 916 111-11-11
Чтобы получить полный список, укажите пустой массив []
Третьим параметром указывается метод в формате [название объекта, метод] */
$request_get = new Request(Request::GET, ['query' => '79161111111'], ['contacts', 'list']);

/* Выполнение запроса */
$result = $api->request($request_get)->result;

/* Результат запроса сохраняется в свойстве "result" объекта \AmoCRM\Handler()
Содержит в себе объект, полученный от AmoCRM, какой конкретно - сверяйтесь с документацией для каждого метода
Ошибка запроса выбросит исключение */
$api->result == false, если ответ пустой (то есть контакты с таким телефоном не найдены) */
```

### Создание новых объектов
Пример рабочего кода, который покрывает все доступные возможности библиотеки

```php
use AmoCRM\AmoCRMException;
use \AmoCRM\Handler;
use \AmoCRM\Request;
use \AmoCRM\Lead;
use \AmoCRM\Contact;
use \AmoCRM\Note;
use \AmoCRM\Task;
use \AmoCRM\ContactsList;

require('vendor/autoload.php');

try
{
	/* Данные, полученные из формы */
	$name = 'Пользователь';
	$phone = '79161111111';
	$email = 'user@user.com';
	$message = 'Здравствуйте';
	
	/* Подключение к API AmoCRM */
	$api = new Handler('domain', 'homer@simpson.com', false, __DIR__ . '/config');
	
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
	
	/* Поиск контакта */
        $contactList = new ContactsList();
        $result = $contactList->getByQuery($api, $email);
        
        if ($result === false) {
            // контакт не найден, надо создать
            $contact = new Contact();
            $contact
                /* Имя */
                ->setName($name)
                /* Назначаем ответственного менеджера */
                ->setResponsibleUserId($api->config['ResponsibleUserId'])
                /* Привязка сделки */
                ->setLinkedLeadsId($lead)
                /* Установка тегов */
                ->setTags(['Тег 1', 'Тег 2'])
                /* Установка тегов, так тоже можно */
                ->setTags('Тег 3')
                /* Установка дополнительных полей, 1й параметр - это код поля в
                справочнике, 2й параметр - значение, 3й параметр - тип ENUM значения
                (см. информацию о аккаунте) */
                /* Email */
                ->setCustomField($api->config['ContactFieldEmail'], $email, 'WORK')
                /* Телефон */
                ->setCustomField($api->config['ContactFieldPhone'], $phone, 'MOB');
        } else {
            /** @var Contact $contact */
            $contact = $result[0];
            $contact
                ->setUpdateIncrementLastModified()
                /* Привязка сделки */
                ->setLinkedLeadsId($lead)
                /* При необходимости можно указать дополнительные поля, по-аналогии
                с созданием контакта. При этом имейте ввиду, что если у контакта
                уже был ранее указан телефон/email/что_то_другое - новый параметр
                будет добавлен к этому спику, а не перезапишет все предыдущие значения,
                при этом если данные дублируются - добавление дубликата не произойдет.
                Т.е. раньше уже был телефон: 123456789, из формы прислали еще один:
                987654321, то после выполнения: */
                ->setCustomField($api->config['ContactFieldPhone'], $phone, $api->config['ContactFieldPhoneMOB']);
                /* в карточке контакта будет 2 телефона: 123456789 и 987654321, если
                же прислали телефон 123456789, то в карточке по-прежнему будет один
                телефон, без дублирования, почему здесь передано $api->config['ContactFieldPhoneMOB'], 
                а не 'MOB' см. ниже, в разделе "Обновление дополнительных полей контакта" */
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
        
} catch (AmoCRMException $e) {
	echo $e->getMessage();
} catch (\Exception $e) {
	echo $e->getMessage();
}

```

### Обновление дополнительных полей контакта
Обратите внимание на тот факт, что в информации о контакте в дополнительных полях вместо `ENUM` возвращается код этого 
`ENUM`, например у меня для телефонов:
 - 3972233 для _WORK_;
 - 3972237 для _MOB_.
 
Оказалось, что если обновлять данные - необходимо для новых данных и старых данных передавать значения полей `ENUM` в одинаковом виде: 
или коды или значения нужных `ENUM`. Если для одних данных передать код, а для других `ENUM` - в лучшем случае у контакта 
останутся только старые данные, в худшем пропадут и они. Поэтому, чтобы избежать подобного при обновлении данных для 
новых значений надо передавать именно код `ENUM`, а не значение.

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
