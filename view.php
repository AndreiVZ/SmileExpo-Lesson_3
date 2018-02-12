<?php

/*
 * "Представление" MVC
 * Функция принимает многомерный массив данных 
 * и выводит данные на экран
 */
function display_view($obj) {
?>

<!-- код JS для работы с таб-панелью -->
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
   <title>Weather forecast</title>
   <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
   <form action="controller.php" method="get">
      <fieldset>
         <legend>For weather forecast, please select a city</legend>
         <select name="city" id="city">
         <optgroup label="Russia">
             <option value="Yaroslavl">Yaroslavl</option> 
             <option value="Moscow">Moscow</option> 
             <option value="Sankt-Peterburg">Sankt-Peterburg</option>
             <option value="Ivanovo">Ivanovo</option>
             <option value="Vladimir">Vladimir</option>
             <option value="Ryazan">Ryazan</option>
         </optgroup>
         <optgroup label="Ukraine">
             <option value="Kiev">Kiev</option>
             <option value="Kharkiv">Kharkiv</option>
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
   $arr_dt = $list->dt_txt;

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
   $arr_dt = $list->dt_txt;
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
   <div><p class="day-temp"><?php echo $list->main->temp;?>
   <img src='images/<?php echo $list->weather[0]->icon;?>.png' width="48" height="48" /></p></div>
   <p class="day-descr"><strong><?php echo $list->weather[0]->description;?></strong></p>
   <div class="day-param">
   <p><?php echo 'wind: '.$list->wind->deg.'   '.$list->wind->speed.' mps';?></p>
   <p><?php echo 'humidity: '.$list->main->humidity.'%';?></p>
   <p><?php echo 'pressure: '.$list->main->pressure.' mmHg';?></p><br></div></div>
<?php }}?>
</div>

<?php }?>