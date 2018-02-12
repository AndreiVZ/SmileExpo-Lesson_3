<?php

/**
 * Smile Expo PHP Learning Lesson 3
 */

require_once "model.php";
// id города из файла city.list.json.gz
$city_id = getCityID($_GET['city']); 
// API key for api.openweathermap.org
$api_key = 'ba53279f5f36a4546d18433a0c4d5c86'; 
// адрес ресурса прогноза на 5 дней
$url = sprintf('api.openweathermap.org/data/2.5/forecast?id=%s&APPID=%s', $city_id , $api_key);
//время кэша файла в секундах, 3600=1 час (Нет смысла обновлять файл чаще, чем 1 раз в час)
$cache_lifetime = 3600; 
// декодируем строку JSON
$obj = json_decode(loadOpenWeatherMap($city_id));
?>

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

<!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8">
   <title>Select menus</title>
   <style>
      body {
         font: 100% arial, helvetica, sans-serif;
      }

      fieldset {
         padding: 0 1em;
      }

      legend {
         padding: 1em;
      }

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

   p.day-part {
      font-family: "Georgia";
      font-size:18px; 
      text-align: center;
      font-weight:700;
   }
   p.day-descr {
      font-size:15px;
      text-transform:uppercase;
   }
   p.day-temp {font-size:30px;}
   div.day-param {font-size:15px;}
   </style>
</head>
<body>
   <form action="controller.php" method="get">
      <fieldset>
         <legend>For weather forecast, please select a city</legend>
         <select name="city" id="city">
         <optgroup label="Russia">
             <option value="Yaroslavl">Yaroslavl</option> <!-- 468902 -->
             <option value="Moscow">Moscow</option> <!-- 524901 -->
             <option value="Sankt-Peterburg">Sankt-Peterburg</option> <!-- 536203 -->
             <option value="Ivanovo">Ivanovo</option> <!-- 6608392 -->
             <option value="Vladimir">Vladimir</option> <!-- 2013364 -->
             <option value="Ryazan">Ryazan</option> <!-- 500095 -->
         </optgroup>
         <optgroup label="Ukraine">
             <option value="Kiev">Kiev</option> <!-- 703448 -->
             <option value="Kharkiv">Kharkiv</option> <!-- 706483 -->
         </optgroup>
         </select>
         <p><input type="submit" value="Select"/></p>
      </fieldset>
   </form>

   <p class="day-part">Weather in <?php echo $_GET['city']; ?>.</p>

</body>
</html>

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
   // $date_old понадобится, чтобы к каждой дате заголовка привязывались только его прогнозы
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
   <img src='icons_weather/<?php echo $list->weather[0]->icon;?>.png' width="48" height="48" /></p></div>
   <p class="day-descr"><strong><?php echo $list->weather[0]->description;?></strong></p>
   <div class="day-param">
   <p><?php echo 'wind: '.getWindDirection($list->wind->deg).'   '.$list->wind->speed.' mps';?></p>
   <p><?php echo 'humidity: '.$list->main->humidity.'%';?></p>
   <p><?php echo 'pressure: '.getPressure_mmHg($list->main->pressure).' mmHg';?></p><br></div></div>
<?php 
   }   
}?>
</div>