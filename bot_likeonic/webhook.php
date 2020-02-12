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
// $bot->command('start', function ($message) use ($bot) {
// 	Start($message, $bot);
// });

$bot->on(function ($Update) use ($bot) {

	$message = $Update->getMessage();

	$mtext = $message->getText();
	$chat_id = $message->getChat()->getId();
	$user_id = $message->getFrom()->getId();
	// SaveMessage($mtext, $chat_id, $user_id);
	if (mb_stripos($mtext, "/start") !== false) {
		$nick = $message->getFrom()->getUsername();
		$name = $message->getFrom()->getFirstName();
		SaveUser($message->getFrom()->getId(), $nick, $name);
		SaveChat($message->getChat()->getId(), $message->getFrom()->getId());
	}
	if (GetUserLang($user_id) == "") {
		$tmp = LangQuestion();
		$keyboard = $tmp[1];
		$answer .= $tmp[0];
		// $keyboard = AskCurrentAnswers($user_id);
		// $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardHide();
		// $bot->sendMessage($message->getChat()->getId(), $answer);
		$bot->sendMessage($message->getChat()->getId(), $answer, false, null, null, $keyboard);
		return;
	}
	if (is_numeric($mtext)) {
		SetCurrentQuestion($user_id, $mtext);
	}
	SaveMessage($mtext, $chat_id, $user_id);
	$answer = AskCurrentQuestion($user_id);
	$tmp = AskNextQuestion($user_id);
	$keyboard = $tmp[1];
	$answer .= $tmp[0];
	// $keyboard = AskCurrentAnswers($user_id);
	// $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardHide();
	// $bot->sendMessage($message->getChat()->getId(), $answer);
	$bot->sendMessage($message->getChat()->getId(), $answer, false, null, null, $keyboard);
}, function ($message) use ($name) {
	return true; // когда тут true - команда проходит
});

$bot->run();
pg_close($dbconn);

function Start($message, $bot) {
	$nick = $message->getFrom()->getUsername();
	$name = $message->getFrom()->getFirstName();
	SaveUser($message->getFrom()->getId(), $nick, $name);
	SaveChat($message->getChat()->getId(), $message->getFrom()->getId());
	SaveMessage($message->getText(), $message->getChat()->getId(), $message->getFrom()->getId());
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
	$answer = 'Добро пожаловать ' . $name . '!';
	// $answer = SaveUser($message->getFrom()->getId(), $nick, $name);
	$bot->sendMessage($message->getChat()->getId(), $answer);
}
function SaveUser($id, $nick, $name) {
	$id = intval($id);
	$nick = pg_escape_literal($nick);
	$name = pg_escape_literal($name);
	$query = "INSERT INTO users (id, username,name) values ($id,$nick,$name);";
	pg_query($query);
	// return $query;
	// pg_execute($query);
}

function SaveChat($id, $user) {
	$id = intval($id);
	$user = intval($user);
	$query = "INSERT INTO chats (chat_id, user_id,chat_state,current_question) values ($id,$user,0,1);";
	pg_query($query);
	// return $query;
	// pg_execute($query);
}

function SaveMessage($text, $chat, $user) {
	$text = pg_escape_literal($text);
	$chat = intval($chat);
	$user = intval($user);
	$query = "INSERT INTO messages_history (timemark,message,chat_id,user_id) values (CURRENT_TIMESTAMP,$text,$chat,$user);";
	pg_query($query);
	// return $query;
	// pg_execute($query);
}

function AskCurrentQuestion($user) {
	$user = intval($user);
	$query = "Select question from questions where id=(select current_question from chats where user_id=$user);";
	$result = pg_query($query);
	while ($data = pg_fetch_object($result)) {
		$question = $data->question;
	}
	return $question;
	// return $query;
};
function AskCurrentAnswers($user) {
	$user = intval($user);
	$query = "Select answer from answers where question=(select current_question from chats where user_id=$user);";
	$result = pg_query($query);
	$answers = array();
	while ($data = pg_fetch_object($result)) {
		array_push($answers, $data->answer);
	}
	$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup([[]], true, true);
	// return var_export($result, true);
	return $query;
}

function AskNextQuestion($user) {
	$user = intval($user);
	$query = "Select question from questions where parent=(select current_question from chats where user_id=$user);";
	$result = pg_query($query);
	$answers = array();
	$i = 1;
	$question = "\n";
	while ($data = pg_fetch_object($result)) {
		array_push($answers, ["text" => $i]);
		$question .= $i . ") " . $data->question . "\n";
		$i++;
	}
	array_push($answers, ["text" => $i]);
	// TODO Это не мультиязычно!
	$question .= $i . ") " . "Назад" . "\n";
	$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup([$answers], true, true);
	// $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardHide();
	// return var_export($keyboard, true);
	$res = array();
	$res[0] = $question;
	// $res[0] = $query;
	$res[1] = $keyboard;
	return $res;
}

function GetCurrentState($user) {
	$user = intval($user);
	$query = "Select chat_state from chats where user_id=$user;";
	$result = pg_query($query);
	while ($data = pg_fetch_object($result)) {

		$state = $data->chat_state;
	}
	return $state;
}

function GetUserLang($user) {
	$user = intval($user);
	$query = "Select lang from users where id=$user;";
	$result = pg_query($query);
	while ($data = pg_fetch_object($result)) {

		$lang = $data->lang;
	}
	return $lang;
}

function LangQuestion() {
	$query = "Select id,lang from langs;";
	$result = pg_query($query);
	$answers = array();
	$answer = "Please select your language\n";
	while ($data = pg_fetch_object($result)) {
		array_push($answers, ["text" => $data->id]);
		$answer .= $data->id . ") " . $data->lang . "\n";
	}
	// array_push($answers, ["text" => $i]);
	// TODO Это не мультиязычно!
	// $question .= $i . ") " . "Назад" . "\n";
	$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup([$answers], true, true);
	// $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardHide();
	// return var_export($keyboard, true);
	$res = array();
	$res[0] = $answer;
	// $res[0] = $query;
	$res[1] = $keyboard;
	return $res;
}

function SetUserLang($user, $lang) {
	$user = intval($user);
	$lang = intval($lang);
	$query = "Select lang from langs where id=$lang;";
	$result = pg_query($query);
	$lang_t = "";
	while ($data = pg_fetch_object($result)) {

		$lang_t = $data->lang;
	}
	if ($lang_t != "") {
		$query = "Update users set lang=$lang where id=$lang;";
		$result = pg_query($query);
	}
	return $lang;
}

function SetCurrentQuestion($user, $offset) {
	$user = intval($user);
	$offset = intval($offset);
	$query = "Update chats set current_question=(Select id from questions where parent=(select current_question from chats where user_id=$user) UNION ALL (Select parent as id from questions where id=(select current_question from chats where user_id=$user)) offset $offset-1 limit 1) where user_id=$user;";
	$result = pg_query($query);
}
?>