<?php
/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
    $lang = array();
}
// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(

	'ACP_ITICKETS'									=> 'Тикеты',
    'ACP_ITICKETS_INDEX_TITLE'                      => 'Общие настройки',
    'ACP_ITICKETS_INDEX_TITLE_EXPLAIN'              => 'Здесь вы можете настроить основные параметры для Тикетов',
    'ACP_ITICKETS_ENABLE'							=> 'Включить тикеты',
    'ACP_ITICKETS_ENABLE_EXPLAIN'					=> 'Вы можете включить или выключить Тикеты',
    'ACP_ITICKETS_SETTINGS_UPDATE'					=> 'Настройки обновлены',
    'ACP_ITICKETS_NUM_TP'							=> 'Количество тикетов на страницу',
    'ACP_ITICKETS_NUM_TP_EXPLAIN'					=> 'Введите число тикетов которое хотите показывать на страницу.<br/>По умолчанию показывается 15 тикетов<br/>0 - будет загружать все',
    'ACP_ITICKETS_NUM_CP'							=> 'Количество ответов на страницу',
    'ACP_ITICKETS_NUM_CP_EXPLAIN'					=> 'Введите число ответов которое хотите показывать на страницу.<br/>По умолчанию показывается 5 ответов<br/>0 - покажет все',

));
?>