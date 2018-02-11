<?php

/**
 * Smile Expo PHP Learning Lesson 3
 */

// id города Yaroslavl из файла city.list.json.gz
$city_id = '468902'; 
// id города Rybinsk из файла city.list.json.gz
// $city_id = '500004'; 
// API key for api.openweathermap.org
$api_key = 'ba53279f5f36a4546d18433a0c4d5c86'; 
//время кэша файла в секундах, 3600=1 час (Нет смысла обновлять файл чаще, чем 1 раз в час)
$cache_lifetime = 3600; 
// $url = sprintf('api.openweathermap.org/data/2.5/weather?id=%s&APPID=%s', $city_id , $api_key); // адрес ресурса погоды на текущий момент
$url = sprintf('api.openweathermap.org/data/2.5/forecast?id=%s&APPID=%s', $city_id , $api_key); // адрес ресурса прогноза на 5 дней

/**
 * Функция принимает переметр $city_id - id выбранного города из файла city.list.json.gz,
 * по нему загружаются данные из API (api.openweathermap.org).
 * Т.к. кол-во запросов в ед.времени ограничено сервером, а информация о погоде меняется не слишком часто,
 * принято решение скаченные файлы помещать в папку 'cache' и кешировать на период $cache_lifetime.
 * Функция возвращает стоку данных о погоде выбранного города в формате JSON.
 */
function loadOpenWeatherMap($city_id) {
   global $url;
   global $cache_lifetime;

   $json_file = sprintf('cache/weather_%s.json', $city_id); 

   // Файл нужно создать (файла еще нет), либо файл нужно обновить (время файла кэша устарело)
   if ( !file_exists($json_file) || time() - filemtime($json_file) > $cache_lifetime ) {

   // инициализация нового сеанса cURL
   $ch = curl_init();

   // установка URL и других необходимых параметров
   curl_setopt($ch, CURLOPT_URL, $url);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

   // выполнение запроса cURL
   $output = curl_exec($ch);

   // завершение сеанса cURL и освобождение ресурсов
   curl_close($ch);

   // открывам/создаем для записи файл JSON для выбранного города
   // и записываем в него данные запроса cURL
   $fh = fopen($json_file, 'w');
   fwrite($fh, $output);
   fclose($fh);

   // тестовая информация
   echo 'Подгрузили файл '.$json_file.'<br><br>';
   }

   // на данном этапе есть файл $json_file формата JSON, прочитаем его в строку $json_string
   $myfile = fopen($json_file, "r") or die("Unable to open file!");
   $json_string = fread($myfile, filesize($json_file));
   fclose($myfile);

   return $json_string;
}

// декодируем строку JSON
$obj = json_decode(loadOpenWeatherMap($city_id));


echo $obj->list[0]->dt_txt;
echo '<br>';

$arr_dt = getDayDate($obj->list[0]->dt_txt);
echo $arr_dt['time_text'];
echo '<br>';
echo $arr_dt['date_format'];
echo '<br>';

/**
 * Функция принимает строку с датой и временем, 
 * определяет время дня в текстовом виде и преобразует дату в нужный формат.
 * Функция возвращает массив с этими значениями.
 */
function getDayDate($date) {

   // создадим DateTime объект
   $date = date_create($date);

   // выясним время дня в текстовом виде
   $time = date_format($date,"H");
   switch ($time) {
      case "03":
         $time_text = "Night";
         break;
      case "09":
         $time_text = "Morning";
         break;
      case "15":
         $time_text = "Afternoon";
         break;
      case "21":
         $time_text = "Evening";
         break;
      default:
         $time_text = "Undefined";
   }

   // Создаем массив для вывода
   $array = [
      // время дня в текстовом виде
      "time_text" => $time_text,
      // нужный формат даты
      "date_format" => date_format($date,"D, d/m")
   ];

   return $array;
}

// {
// $date = strtotime($date);
// $months = array('','/01','/02','/03','/04','/05','/06','/07','/08','/09','/10','/11','/12');
// $days = array('ВС','ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ');
// return $days[date('w', $date)].', '.(int)date('d',$date).' '.$months[date('n', $date)];
// }

// echo $obj->city->name;
// echo '<br>';
// echo $obj->list[0]->dt_txt;
// echo '<br>';
// echo $obj->list[0]->main->temp.'   '.$obj->list[0]->weather[0]->icon;
// echo '<br>';
// echo $obj->list[0]->weather[0]->description;
// echo '<br>';
// echo $obj->list[0]->wind->deg.'   '.$obj->list[0]->wind->speed;
// echo '<br>';
// echo $obj->list[0]->main->pressure;
// echo '<br>';
// echo $obj->list[0]->main->humidity;
// echo '<br>';
// echo '<br>';


// DofW, day / month
// dt_txt [Morning Afternoon Evening Night]
// temp °C 'icon'
// description
// wind: list.wind.deg [nw] list.wind.speed mps
// pressure: list.main.pressure hPa mmHg
// humidity: list.main.humidity %

// // получаем знак температуры
// function getTempSign($temp)
// {
// $temp = (int)$temp;
// return $temp > 0 ? '+'.$temp : $temp;
// }
// // получаем направления ветра
// function getWindDirection($wind)
// {
// $wind = (string)$wind;
// $wind_direction = array('s'=>'&#8593; ю','n'=>'&#8595; с','w'=>'&#8594; з','e'=>'&#8592; в','sw'=>'&#8599; юз','se'=>'&#8598; юв','nw'=>'&#8600; сз','ne'=>'&#8601; св');
// return $wind_direction[$wind];
// }

?>
