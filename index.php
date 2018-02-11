<style>
body {font-family: Arial;}

/* Style the tab */
.tab {
    overflow: hidden;
    border: 1px solid #ccc;
    background-color: #f1f1f1;
}

/* Style the buttons inside the tab */
.tab button {
    background-color: inherit;
    float: left;
    border: none;
    outline: none;
    cursor: pointer;
    padding: 14px 16px;
    transition: 0.3s;
    font-size: 17px;
}

/* Change background color of buttons on hover */
.tab button:hover {
    background-color: #ddd;
}

/* Create an active/current tablink class */
.tab button.active {
    background-color: #ccc;
}

/* Style the tab content */
.tabcontent {
    font-family: "Georgia";
    display: none;
    padding: 6px 12px;
    border: 1px solid #ccc;
    border-top: none;
}

.floating-box {
    display: inline-block;
    width: auto;
    height: auto;
    margin: 10px;
    padding: 10px;
    border: 2px solid #73AD21;  
}

p.day-part {font-size:18px; text-align: center;font-weight:700;}
p.day-temp {font-size:30px;}
p.day-descr {font-size:15px;text-transform:uppercase;}
div.day-param {font-size:15px;}
</style>

<script>
function openData(evt, date) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(date).style.display = "block";
    evt.currentTarget.className += " active";
}
</script>

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

/**
 * Функция принимает температуру по Кельвину и преобразует ее в Цильсий с добавлением знака. 
 * -273.15 абсолютный 0 по Кельвину.
 */
function getTempConvert($temp)
{
   $temp = round(-273.15 + $temp);
   return $temp > 0 ? '+'.$temp.' °C' : $temp.' °C';
}

/**
 * Функция принимает атм. давление в гектопаскалях и преобразует в миллиметр ртутного столба
 * по формуле: 1 hPa = 0.75006375541921 mmHg
 */
function getPressure_mmHg($pressure)
{
   $pressure *= 0.75006375541921;
   return round($pressure);
}

/**
 * Функция принимает направление ветра в градусах, 
 * преобразует в одно из 8 направлений (N, NE, E, SE, S, SW, W, NW).
 * Функция возвращает строку с указателем направления и его буквенным обозначением.
 */
function getWindDirection($wind)
{
   // проверяем какому из 8 направлений сответствует значение в градусах
   $wind = round($wind / 45);
   switch ($wind) {
      case 0:
         $wind_direction = '&#8593; S';
         break;
      case 8:
         $wind_direction = '&#8593; S';
         break;
      case 1:
         $wind_direction = '&#8599; SW';
         break;
      case 2:
         $wind_direction = '&#8594; W';
         break;
      case 3:
         $wind_direction = '&#8600; NW';
         break;
      case 4:
         $wind_direction = '&#8595; N';
         break;
      case 5:
         $wind_direction = '&#8601; NE';
         break;
      case 6:
         $wind_direction = '&#8592; E';
         break;
      case 7:
         $wind_direction = '&#8598; SE';
         break;
      default:
         $wind_direction = "Undefined";
   }

   return $wind_direction;
}

// декодируем строку JSON
$obj = json_decode(loadOpenWeatherMap($city_id));
?>



<div class="tab">
<?php
   // $date_old понадобится, чтобы дата в заголовке не повторялась
   $date_old = array();
   // создадим Tab links, в качестве заголовка дата
   foreach($obj->list as $list) {
   $arr_dt = getDayDate($list->dt_txt);

   // если в масиив $date_old дата еще не записана
   if (!array_key_exists($arr_dt['date_format'],$date_old)) {?><button class="tablinks" onclick="openData(event, <?php echo '\''.$arr_dt['date_format'].'\'';?>)"><?php echo $arr_dt['date_format'];?></button>
   <?php
   $date_old[$arr_dt['date_format']] = 1;
   }
}?>
</div>
<div>

<?php
   // $date_old понадобится, чтобы к  каждой дате заголовка привязывались только его прогнозы
   $date_old = array();
   // выведем в цикле прогноз погоды на 5 дней
   foreach($obj->list as $list) {
   $arr_dt = getDayDate($list->dt_txt);
   // если в масиив $date_old дата еще не записана
   if (!array_key_exists($arr_dt['date_format'],$date_old)) {?></div><div id="<?php echo $arr_dt['date_format'];?>" class="tabcontent">
   <?php
   $date_old[$arr_dt['date_format']] = 1;
   }

   // выводим только промежутки "Night", "Morning", "Afternoon", "Evening"
   if ($arr_dt['time_text'] != 'Undefined') {
   ?>
   <div class="floating-box">
   <p class="day-part"><?php echo $arr_dt['time_text'];?></p>
   <div><p class="day-temp"><?php echo getTempConvert($list->main->temp);?>
   <img src='icons2/<?php echo $list->weather[0]->icon;?>.png' width="48" height="48" /></p></div>
   <p class="day-descr"><strong><?php echo $list->weather[0]->description;?></strong></p>
   <div class="day-param">
   <p><?php echo 'wind: '.getWindDirection($list->wind->deg).'   '.$list->wind->speed.' mps';?></p>
   <p><?php echo 'humidity: '.$list->main->humidity.'%';?></p>
   <p><?php echo 'pressure: '.getPressure_mmHg($list->main->pressure).' mmHg';?></p><br></div></div>
<?php 
   }   
}?>
</div>