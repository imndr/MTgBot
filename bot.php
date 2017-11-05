<?php
// YOUR BOT TOKEN, REQUEST ONE OF IT ON @BotFather
define('token',   '463453780:AAFYXeUWYnV53bI6zxfLV4OUi5R1KSt25wY');
define('threads', '100');
include 'base.php';
/*
	
	CREDITS FOR THE MULTITHREADING IMPLEMENTATION TO GITHUB.COM/IMNDR

*/


function callBackUpdate($_TELEGRAM, $th, $tc){
        // YOUR BOT CODE GOES HERE
        if ($_TELEGRAM['text'] == "/start")
        {
                send($_TELEGRAM['cid'], "Hi! Welcome on MTgBot Example!\nThis message was sent by Thread #$th.\nThe Thread #$th sent $tc messages.");
        }

}
