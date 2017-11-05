<?php
error_reporting(E_ERROR);
define('api', 'https://api.telegram.org/bot' . token . '/');


// CREDITS FOR ALL FUNCTIONS TO GITHUB.COM/LOR54
// CREDITS FOR THE MULTITHREADING IMPLEMENTATION TO GITHUB.COM/IMNDR

$version = "1.0";
echo "MTgBot Powered By Andrew ( Base by TelebotTeam ) \n";

while(1){
$data = getUpdates();
$updates = json_decode($data, true)['result'];
unset($_TELEGRAM_UPDATES);
$lUpdate = intval(trim(file_get_contents("lastUpdate")));
foreach($updates as $update){
	$thisUpdateID = intval($update["update_id"]);
	if($thisUpdateID > $lUpdate){
	$message = $update["message"];
	$text = $message["text"];
	$msgid = $message["message_id"];
	$reply = $message["reply_to_message"];
	$title = $message["chat"]["title"];
	$chat = $message["chat"];
	$chattype = $message['chat']['type'];
	$from = $message["from"];
	$username = $from["username"];
	$first = $from["first_name"];
	$last = $from["last_name"];
	$uid = $from["id"];
	$cid = $from["id"];
	$cbid = $update["callback_query"]["id"];
	$cbmsg = $update["callback_query"]["message"]["message_id"];
	$cbdata = $update["callback_query"]["data"];
	$cbchat = $update['callback_query']['message']['chat']['id'];
	$cbuid = $update["callback_query"]["from"]["id"];
	$cbfirst = $update["callback_query"]["from"]["first_name"];
	$cblast = $update["callback_query"]["from"]["last_name"];
	$inline = $update["inline_query"]["id"];
	$msgin = $update["inline_query"]["query"];
	$userIDin = $update["inline_query"]["from"]["id"];
	$usernamein = $update["inline_query"]["from"]["username"];
	$namein = $update["inline_query"]["from"]["first_name"];
	$channel = $update["channel_post"];

	unset($_TELEGRAM);
	// collect all values in an array to pass it to the callback function
	foreach(get_defined_vars() as $key=>$value){
		$_TELEGRAM[$key] = $value;
		
	}
	$_TELEGRAM_UPDATES[] = $_TELEGRAM;
	if(intval($update["update_id"]) > $lUpdate){
		$lUpdate = $update["update_id"];
	}
	}
}

$howmany = count($_TELEGRAM_UPDATES);

echo " > Got " .$howmany. " new updates - Last UpdateID: $lUpdate.\n";

file_put_contents("lastUpdate", $lUpdate);

$index = max(array_keys($_TELEGRAM_UPDATES));
	for($t = 0; $t < threads; $t++){
		if($howmany <= 0){
			break;
		}
		$threads[$t][] = $_TELEGRAM_UPDATES[$index];
		$index--;
		$howmany--;
	
	}
for($i = 0; $i < count($threads); $i ++){
	$pid = pcntl_fork();
	if($pid == -1) {
		echo "Something went wrongs.\n";
		exit();
	} elseif($pid) {
		continue;
	} else {
		if(!isset($threads[$i][0])){
			echo " > Thread $i has nothing to do.\n";
			exit;
		}

		foreach($threads[$i] as $update){
			callBackUpdate($update, $i, count($threads[$i]));
		}
		echo " > Threads $i processed " . count($threads[$i]) . " messages.\n";
		exit;
	}
}
/*for($j = 0; $j < $threads; $j++) {
	$pid = pcntl_wait($status);
}*/
unset($threads);
usleep(100000);

echo " > Getting Updates.\n";
}
function getUpdates()
{	
	$lUpdate = intval(trim(file_get_contents("lastUpdate")));
	if($lUpdate <= 0){
		$lUpdate = "";
	} else {
		$lUpdate = "offset=$lUpdate";
	}
	return apiRequest("getUpdates", $lUpdate);
}



// Functions

function loadplugin($name)
{
	return include "plugins/$name.php";

}


function type($cha)
{
	return $cha["type"];
}

if ($message['chat']['type'] == 'private')
{
	$cid = $message['from']['id'];
}
else
if ($message['chat']['type'] == 'group' || $message['chat']['type'] == 'supergroup')
{
	$cid = $message['chat']['id'];
}

function is($word, $con)
{
	return strncmp($word, $con, strlen($con)) === 0;
}

function apiRequest($method, $var)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, api . $method);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $var);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$req = curl_exec($ch);
	curl_close($ch);
	return $req;
}

function deleteMessage($cd, $ms)
{
	return apiRequest("deleteMessage", "chat_id=$cd&message_id=$ms");
}

// SEND MESSAGE (parse modes: markdown, html)

function send($id, $text, $parse, $webp, $reply_id, $hk)
{
	if (strpos($text, "\n"))
	{
		$text = urlencode($text);
	}

	$r = "text=$text&chat_id=$id";
	if (strtolower($parse) == "markdown")
	{
		$r.= "&parse_mode=Markdown";
	}

	if (strtolower($parse) == "html")
	{
		$r.= "&parse_mode=HTML";
	}

	if ($webp == false)
	{
		$r.= "&disable_web_page_preview=true";
	}

	if (isset($reply_id))
	{
		$r.= "&reply_to_message_id=$reply_id";
	}

	if ($hk == true)
	{
		$r.= "&remove_keyboard=true";
	}

	return apiRequest("sendMessage", $r);
}

// SEND A PHOTO

function sendPhoto($id, $im, $cap)
{
	return apiRequest("sendPhoto", "photo=$im&chat_id=$id&caption=$cap");
}

// SEND AN AUDIO

function sendAudio($id, $au, $ti)
{
	return apiRequest("sendAudio", "audio=$au&chat_id=$id&title=$ti");
}

// SEND A VOICE

function sendVoice($id, $au, $ti)
{
	return apiRequest("sendVoice", "audio=$au&chat_id=$id&title=$ti");
}

// SEND A DOCUMENT

function sendDocument($id, $dc, $ti)
{
	return apiRequest("sendDocument", "document=$dc&chat_id=$id&caption=$ti");
}

// CREATE A KEYBOARD (Not Inline)

function keyboard($tasti, $text, $cd)
{
	$buttons = $tasti;
	$encoded = json_encode($buttons);
	if (strpos($text, "\n"))
	{
		$text = urlencode($text);
	}

	apiRequest("sendMessage", "text=$text&chat_id=$cd&reply_markup=$encoded");
}

function callback($up)
{
	return $up["callback_query"];
}

function newMember($up)
{
	return $up["new_chat_member"];
}

function leftMember($up)
{
	return $up["left_chat_member"];
}

// BAN AN USER (From a group)

function ban($kid, $cd)
{
	apiRequest("kickChatMember", "chat_id=$kid&user_id=$cd");
}

// UNBAN AN USER (From a group)

function unban($kid, $cd)
{
	apiRequest("unbanChatMember", "chat_id=$kid&user_id=$cd");
}

// ANSWER TO CALLBACK QUERY

function callbackanswer($id, $text, $alert)
{
	apiRequest("answerCallbackQuery", "callback_query_id=$id&show_alert=$alert&text=$text");
}

// EDIT A MESSAGE

function edit($menud, $chat, $inmsg, $parse, $tx)
{
	$menu = $menud;
	if (strpos($tx, "\n"))
	{
		$tx = urlencode($tx);
	}

	$d2 = array(
		"inline_keyboard" => $menu,
	);
	$d2 = json_encode($d2);
	return apiRequest("editMessageText", "chat_id=$chat&message_id=$inmsg&parse_mode=$parse&text=$tx&reply_markup=$d2");
}

// FORWARD A MESSAGE

function forward($id, $frm, $mid)
{
	return apiRequest("forwardMessage", "chat_id=$id&from_chat_id=$frm&message_id=$mid");
}

// GET ALL ADMINS OF THE CHAT IN AN ARRAY

function getAdmins($cha)
{
	$req = apiRequest("getChatAdministrators", "chat_id=$cha");
	$admins = json_decode($req, true);
	$idlist = array();
	foreach($admins['result'] as $adm)
	{
		$dd = $adm["user"];
		array_push($idlist, $dd);
	}

	return $idlist;
}

// GET CREATOR OF THE CHAT

function getCreator($cha)
{
	$req = apiRequest("getChatAdministrators", "chat_id=$cha");
	$j = json_decode($req, true);
	foreach($j as $a)
	{
		$x = array();
		array_push($x, $a);
	}

	$i = 0;
	foreach($x as $d)
	{
		foreach($d as $q)
		{
			if ($q["status"] != "creator")
			{
				$i+= 1;
			}
			else
			{
				return array_diff($q["user"], [$q[$i]]);
			}
		}
	}
}

// CREATE AN INLINE KEYBAORD

function inlineKeyboard($menud, $chat, $parse, $tx)
{
	$menu = $menud;
	if (strpos($tx, "\n"))
	{
		$tx = urlencode($tx);
	}

	$d2 = array(
		"inline_keyboard" => $menu,
	);
	$d2 = json_encode($d2);
	return apiRequest("sendmessage", "chat_id=$chat&parse_mode=$parse&text=$tx&reply_markup=$d2");
}

// REPLY INLINE KEYBOARD

function replyInlineKeyboard($menud, $chat, $parse, $omgreply, $tx)
{
	$menu = $menud;
	if (strpos($tx, "\n"))
	{
		$tx = urlencode($tx);
	}

	$d2 = array(
		"inline_keyboard" => $menu,
	);
	$d2 = json_encode($d2);
	return apiRequest("sendmessage", "chat_id=$chat&reply_to_message_id=$omgreply&parse_mode=$parse&text=$tx&reply_markup=$d2");
}

// ANSWER TO INLINE QUERY

function answerInlineQuery($in, $arr, $ct = 5)
{
	$json = json_encode($array);
	return apiRequest("answerInlineQuery", "inline_query_id=$inline&results=$json&cache_time=$ct");
}

// LEAVE A CHAT

function leaveChat($chatt)
{
	apiRequest("leaveChat", "chat_id=$chatt");
}

// GET TELEGRAM TIME

function getTime($m)
{
	return $m["date"];
}

// GET STATUS OF A USER IN A GROUP

function getChatMember($group, $user_id)
{
	return apiRequest("getChatMember", "chat_id=$gruppoh&user_id=$utenteh");
}



?>
