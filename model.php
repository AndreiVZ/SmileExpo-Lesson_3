<?php

/*
 * "Модель" MVC
 * Функция принимает название города, запрошенного пользователем.
 * Функция возвращает многомерный массив данных, декодированных из JSON
 */
function model($cityName) {

   // id города из файла city.list.json.gz
   $city_id = getCityID($cityName); 
   // API key for api.openweathermap.org
   $api_key = 'ba53279f5f36a4546d18433a0c4d5c86'; 
   // адрес ресурса прогноза на 5 дней
   $GLOBALS['url'] = sprintf('api.openweathermap.org/data/2.5/forecast?id=%s&APPID=%s', $city_id , $api_key);
   //время кэша файла в секундах, 3600=1 час (Нет смысла обновлять файл чаще, чем 1 раз в час)
   $GLOBALS['cache_lifetime'] = 3600; 

   // декодируем строку JSON
   $obj = json_decode(loadOpenWeatherMap($city_id));

   // в цикле заменим значения параметров на адаптированные под нашу местность, 
   // для доступа к ним "Представления" MVC из "Контроллера" MVC
   foreach($obj->list as $list) {
      $list->dt_txt = getDayDate($list->dt_txt);
      $list->main->temp = getTempConvert($list->main->temp);
      $list->wind->deg = getWindDirection($list->wind->deg);
      $list->main->pressure = getPressure_mmHg($list->main->pressure);
   }

   return $obj;
}

/*
 * Функция принимает название города и возвращает его id из файла city.list.json.gz 
 */
function getCityID($city)
{
   $array_id = [
      // Russia
      "Yaroslavl" => 468902,
      "Moscow" => 524901,
      "Sankt-Peterburg" => 536203,
      "Ivanovo" => 6608392,
      "Vladimir" => 2013364,
      "Ryazan" => 500095,
      // Ukraine
      "Kiev" => 703448,
      "Kharkiv" => 706483
   ];

   return $array_id[$city];
}

/*
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

   // если файл существует, но устарел, то сначала удалим существующий:
   // если этого не сделать, то функция filesize() будет возвращать размер старого файла,
   // тем самым обрезая новый до своего размера
   unlink($json_file);

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

   // тестовая функция вывода уведомления при загрузе файла
   // function alert($msg) {
   //    echo "<script type='text/javascript'>alert('$msg');</script>";
   // }
   // alert('Загрузился файл '.$json_file);
   }

   // на данном этапе есть файл $json_file формата JSON, прочитаем его в строку $json_string
   $myfile = fopen($json_file, "r") or die("Unable to open file!");
   $json_string = fread($myfile, filesize($json_file));
   fclose($myfile);

   return $json_string;
}

/*
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

/*
 * Функция принимает температуру по Кельвину и преобразует ее в Цильсий с добавлением знака. 
 * -273.15 абсолютный 0 по Кельвину.
 */
function getTempConvert($temp)
{
   $temp = round(-273.15 + $temp);
   return $temp > 0 ? '+'.$temp.' °C' : $temp.' °C';
}

/*
 * Функция принимает атм. давление в гектопаскалях и преобразует в миллиметр ртутного столба
 * по формуле: 1 hPa = 0.75006375541921 mmHg
 */
function getPressure_mmHg($pressure)
{
   $pressure *= 0.75006375541921;
   return round($pressure);
}

/*
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
?>