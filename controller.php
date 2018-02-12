<?php

/*
 * "Контроллер" MVC
 */

include 'model.php';
include 'view.php';

// получим многомерный массив данных для выбранного города, 
// запросив у "Модели" MVC
$obj = model($_GET['city']);

// выведем данные на экран с помощью "Представления" MVC
display_view($obj);
?>
