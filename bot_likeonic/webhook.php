<?php
header('Content-Type: text/html; charset=utf-8');
// подрубаем API
require_once "vendor/autoload.php";

// Подтягивание конфиг файла, для подключения БД
$file = file_get_contents(realpath(dirname(__FILE__)) . "/../.config.json");
$config = json_decode($file, true);

$query = "host={$config['host']} dbname={$config['dbname']} user={$config['user']} password={$config['password']}";
// $dbconn = pg_pconnect($query) or die('Не удалось соединиться: ' . pg_last_error());
$dbconn = pg_connect($query) or die('Не удалось соединиться: ' . pg_last_error());
// Токен бота
$token = $config['token'];
// Инициализация бота
$bot = new \TelegramBot\Api\Client($token);

// команда для start
$bot->command('start', function ($message) use ($bot) {
	Start($message, $bot);
});

$bot->run();
pg_close($dbconn);

function Start($message, $bot) {
	$nick = $message->getFrom()->getUsername();
	$name = $message->getFrom()->getFirstName();
	// SaveUser($message->getFrom()->getId(), $nick, $name);
	// $query = "INSERT INTO users" . $db_name . " (id, username,name,chat_id) values ({$message->getFrom()->getId()},'$nick','$name',{$message->getChat()->getId()});\n";
	// $result = pg_query($query) or $answer = 'Не удалось соединиться: ' . pg_last_error();
	// if (mb_stripos($answer, "Не удалось соединиться:") !== false) {
	// 	$query = "UPDATE users" . $db_name . " set username='$nick' and name='$name' where id={$message->getFrom()->getId()} and chat_id={$message->getChat()->getId()};\n";
	// 	$result = pg_query($query) or $answer = 'Не удалось соединиться: ' . pg_last_error();
	// }
	// SetState($message, $bot, "start");
	// if ($message->getChat()->getType() != "private") {
	// 	$answer = 'Простите, кажется это групповой чат. На данный момент я не могу гарантировать коректную работу в групповых чатах. Простите :(';
	// 	$bot->sendMessage($message->getChat()->getId(), $answer);
	// }
	// $answer = 'Добро пожаловать ' . $name . '!';
	$answer = SaveUser($message->getFrom()->getId(), $nick, $name);
	$bot->sendMessage($message->getChat()->getId(), $answer);
}
function SaveUser($id, $nick, $name) {
	$id = intval($id);
	$nick = pg_escape_literal($nick);
	$name = pg_escape_literal($name);
	$query = "INSERT INTO users (id, username,name) values ($id,$nick,$name);";
	pg_query($query);
	return $query;
	// pg_execute($query);
}
?>