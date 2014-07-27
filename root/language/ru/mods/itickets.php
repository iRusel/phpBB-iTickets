<?php
/**
*
* @package iTickets
* @author iRusel www.irusel.com
* @version 0.0.2
* @copyright (c) 2014 iRusel www.irusel.com
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}


$lang = array_merge($lang, array(		
		'TICKETS_TICKETS'			=>	'Тикеты',
		'TICKETS_NO_TICKETS'		=>	'Тикеты ещё не созданы.',
		'TICKETS_TITLE'				=>	'Заголовок',
		'TICKETS_AUTHOR'			=>	'Автор',
		'TICKETS_TIME'				=>	'Создано',
		'TICKETS_SMALLT'			=>	'Краткое описание:',
		'TICKETS_ANSWERS'			=>	'Ответов',
		'TICKETS_STATUS'			=>	'Статус',
		'TICKETS_S1'				=>	'Новый',
		'TICKETS_S2'				=>	'Ожидает ответа',
		'TICKETS_S3'				=>	'Закрыто (П)',
		'TICKETS_S4'				=>	'Закрыто (А)',
		'TICKETS_S_CLOSE'			=>	'Закрыто',
		'TICKETS_LIST_ARTICLE'		=>	'1 запись',
		'TICKETS_LIST_ARTICLES'		=>	'%s записей',	
		'TICKETS_TICKETS_ID'		=>	'Тикет #',
		'TICKETS_NO_SELECTED'		=>	'Вы не выделили ни одной записи',
		'TICKETS_NO_ACCESS'			=>	'У Вас нет доступа к этой части форума',
		'TICKETS_INFO'				=>	'Информация о тикете',
		'TICKETS_NUMBER'			=>	'Тикет номер',
		'TICKETS_SML'				=>	'Краткое содержание',
		'TICKETS_FULL'				=>	'Полное содержимое',
		'TICKETS_COMMENTS'			=>	'Ответы',
		'TICKETS_COMMENTS_NO'		=>	'Ответов нет',
		'TICKETS_ANSW'				=>	'Ответ от:',
		'TICKETS_SPS'				=>	'С уважением, поддержка Resamp.com',
		'TICKETS_LIST_COMMENT'		=>	'1 комментарий',
		'TICKETS_LIST_COMMENTS'		=>	'%s ответов',
		'TICKETS_CREATE'			=> 	'Новый тикет',
		'TICKETS_CREATE_1'			=>	'Создать тикет',
		'TICKETS_MISSING_ERROR'    	=> 	'Вы не заполнили всю форму',
		'TICKETS_CREATE_NAME'		=>	'заголовок тикета',
		'TICKETS_CREATE_SM'			=>	'кратко опишите суть',
		'TICKETS_CREATE_TEXT'		=>	'Текст',
		'TICKETS_CREATE_SUCCESS'    =>  'Тикет успешно отправлен.<br/>Ожидайте ответа от Администрации.',
		'TICKETS_VIEW' 				=>  '%sПерейти к записи%s',
		'TICKETS_CLOSE'				=>	'Закрыть',
		'TICKETS_CLOSED'			=>	'Тикет успешно закрыт.',
		'TICKETS_RETURN'			=>	'Вернуться к списку тикетов',
		'TICKETS_AANSW'				=>	'Оставить ответ',
		'TICKETS_OTV'				=>	'Ответить',
		'TICKETS_SENDERS'			=>	'Ответ отправлен',
		'TICKETS_LASTT'				=>	'Последний ответ',
		'TICKETS_AUTH'				=>	'Авторизация',
		)
	);
?>