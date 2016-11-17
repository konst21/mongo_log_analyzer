<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="ru">
<head>
    <title></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

    <link rel="icon" href="{$smarty.const.PROJECT_URL}/views/img/favicon.ico" type="image/x-icon" />

    <link type="text/css" rel="stylesheet" href="{$smarty.const.FULL_URL_TO_FW}/common_views/css/bootstrap.css"/>

    <link type="text/css" rel="stylesheet" href="{$smarty.const.PROJECT_URL}/views/css/style.css"/>

    <script type="text/javascript" src="{$smarty.const.FULL_URL_TO_FW}/common_views/js/jquery.js"></script>
    <script type="text/javascript" src="{$smarty.const.FULL_URL_TO_FW}/common_views/js/jquery.cookie.js"></script>
    <script type="text/javascript" src="{$smarty.const.FULL_URL_TO_FW}/common_views/js/bootstrap.js"></script>

    <script type="text/javascript" src="{$smarty.const.PROJECT_URL}/views/js/common/bootstrap-datetimepicker.min.js"></script>
    <script type="text/javascript" src="{$smarty.const.PROJECT_URL}/views/js/common/bootstrap-datetimepicker.ru.js"></script>
    <script type="text/javascript" src="{$smarty.const.PROJECT_URL}/views/js/common/jquery.flot.js"></script>
    <script type="text/javascript" src="{$smarty.const.PROJECT_URL}/views/js/common/jquery.flot.crosshair.js"></script>
    <script type="text/javascript" src="{$smarty.const.PROJECT_URL}/views/js/common/jquery.flot.selection.js"></script>
    <script type="text/javascript" src="{$smarty.const.PROJECT_URL}/views/js/common/jquery.flot.time.js"></script>

    {*Клиентские яваскрипты - массивом*}
    {if isset($js)}
        {foreach $js as $client_js}
        <script type="text/javascript" src="{$smarty.const.PROJECT_URL}/views/js/{$client_js}"></script>
        {foreachelse}
        {/foreach}
    {/if}

    {*Клиентские стили - кроме style.css, если есть конечно*}
    {if isset($css)}
        {foreach $css as $client_css}
            <link rel="stylesheet" type="text/css" href="{$smarty.const.PROJECT_URL}/views/css/{$client_css}"/>
        {foreachelse}
        {/foreach}
    {/if}


</head>
<body>
<div class="container">