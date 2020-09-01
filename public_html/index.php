<html>
<head>
    <title>PSO2 events tracker</title>
    <style>
    /* roboto-regular - latin */
        @font-face {
        font-family: 'Roboto';
        font-style: normal;
        font-weight: 400;
        src: url('fonts/roboto-v20-latin-regular.eot'); /* IE9 Compat Modes */
        src: local('Roboto'), local('Roboto-Regular'),
           url('fonts/roboto-v20-latin-regular.eot?#iefix') format('embedded-opentype'), /* IE6-IE8 */
           url('fonts/roboto-v20-latin-regular.woff2') format('woff2'), /* Super Modern Browsers */
           url('fonts/roboto-v20-latin-regular.woff') format('woff'), /* Modern Browsers */
           url('fonts/roboto-v20-latin-regular.ttf') format('truetype'), /* Safari, Android, iOS */
           url('fonts/roboto-v20-latin-regular.svg#Roboto') format('svg'); /* Legacy iOS */
        }
        body
        {
            background-image: url("images/sitebg.png");
            background-repeat: repeat-y;
            font-family: Roboto,sans-serif;
            font-weight: 400
        }
        maingrid
        {
            display: grid;
            grid-template-columns: 288px 288px 288px;
            grid-template-rows: repeat(162px);
            grid-column-gap: 15px;
            grid-row-gap: 25px;
        }
        eventitem
        {
            display: grid;
            grid-template-columns: 288px;
            grid-template-rows: 162px 75px;
        }
        eventtopbox
        {
            background-image: url('images/background.png');
        }
        eventpromoimageoverlay
        {
            display: block;
            width: 288px;
            height: 162px;
            background-image: url('images/overlay.png');
        }
        eventpromoimage
        {
            display: block;
            width: 288px;
            height: 162px;
        }
        eventbottombox
        {
            display: block;
            background: rgb(2,0,36);
            background: linear-gradient(0deg, rgb(0,0,0) 0%, rgb(4,18,111) 55%, rgb(12,52,190) 100%);
            border-radius: 0px 0px 12px 12px;
            -moz-border-radius: 0px 0px 12px 12px;
            -webkit-border-radius: 0px 0px 12px 12px;
            width: 288px;
        }
        eventbottombottext
        {
            display: block;
            width: 90%;
            color: #ffffff;
            margin: auto;
            padding-top: 15px;
        }
        eventbottombottomcountdown
        {
            display: block;
            width: 85%;
            color: #ffffff;
            margin: auto;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <maingrid>
<?php
function normalizename(string $input="")
{
    return preg_replace("/[^A-Za-z0-9 ]/", "", $input);
}
/*
sources:
	https://pso2.com/news/urgent-quests/
*/
$source_file = "https://docs.google.com/spreadsheets/d/e/2PACX-1vTsyc5dokzG7uKsksWs2jU-muUUqiG2D_o8V3f8j-VegplVP1U7rb6Blso9aYKa51LyvSSMWYLGh8wd/pub?gid=0&single=true&output=csv";
// https://docs.google.com/spreadsheets/d/1HpjCJ0zHHQ2mAjAxPBQ7HLsrt7RXvHck2Z3J0ZUHoi8
$unixtime = time();
$loop = 0;
$local_file = false;


$startdate = time();
$days = 0;
$localdate = date('Ymd',time());
$filename = "localcsv".$localdate.".csv";
while(($days<3) && ($local_file == false))
{
    $localdate = date('Ymd',time()+(((60*60)*24)*$days));
    $filename = "localcsv".$localdate.".csv";
    if(file_exists($filename) == true)
    {
        $local_file = true;
    }
    $days++;
}
if($local_file == true)
{
    $source_file = $filename;
}
else
{
    file_put_contents($filename,fopen($source_file, "r"));
}
$lines = array();
$row = 1;
$data_names = array("date","time","name");
if (($handle = fopen($source_file, "r")) !== FALSE)
{
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $num = count($data);
        $entry = array();
        for ($c=0; $c < $num; $c++) {
            if($c < count($data_names))
            {
                $entry[$data_names[$c]] = $data[$c];
            }
        }
        $lines[] = $entry;
    }
    fclose($handle);
}
$start_index = 1;

$unixtime = 0;

$loop = 6;
$max = count($lines);
//$max = 21;
date_default_timezone_set('Etc/GMT-7');
$date_in_pm = array();

$seen_events = array();
while($loop < $max)
{
    $normaized_name = normalizename($lines[$loop]["name"]);
    if(in_array($normaized_name,$seen_events) == false)
    {
        $datedump = explode("/",$lines[$loop]["date"]);
        $timebits = explode(" ",$lines[$loop]["time"]);
        $offset_hours = 0;
        if($timebits[1] == "PM")
        {
            $offset_hours = 12;
        }
        $timedump = explode(":",$timebits[0]);
        $hours = 0;
        if($timedump[0] != 12)
        {
            $hours = $timedump[0]+$offset_hours;
        }
        // only show next 48 hours
        $mins = $timedump[1];
        $unixtime = gmmktime($hours,$mins,0,$datedump[1],$datedump[0],$datedump[2])+((60*60)*7);
        //echo "Item:  <br/>";
        //echo "cfg: ".$lines[$loop]["date"]." - ".$timebits[0]." ".$timebits[1]."<br/>";
        //echo "h: ".$hours." m: ".$mins."  mn: ".$datedump[0]." dy: ".$datedump[1]." yr: ".$datedump[2]."<br/>";
        //echo "Unixtime: ".$unixtime." (".time().") [".($unixtime-time())."]<br/>";
        $status = "ended";
        if(time() > $unixtime)
        {
            $dif = time() - $unixtime;
            if($dif >= (60*34))
            {
                //echo "Status: Ended";
            }
            else if($dif >= (60*10))
            {
                $status = "Status: Underway / ending";
            }
            else
            {
                $status = "Status: Starting";
            }
        }
        else
        {
            $dif = $unixtime - time();
            $cdmins = $dif/60;
            $cdhours = floor($cdmins/60);
            $cdmins -= $cdhours * 60;
            $cdmins = floor($cdmins);
            if($cdhours > 65)
            {
                $status = "hidden";
            }
            else
            {
                $status = "Status: Countdown: ".$cdhours."h , ".$cdmins."m ";
            }
        }
        if(($status != "ended") && ($status != "hidden"))
        {
            //$seen_events[] = $normaized_name;
            $pathtoimage = "events/".$normaized_name.".png";
            echo "<eventitem style='order:".$unixtime."'>";
                echo "<eventtopbox>";
                if(file_exists($pathtoimage) == true)
                {
                    echo "<eventpromoimage style='background-image: url(\"".$pathtoimage."\");'><eventpromoimageoverlay></eventpromoimageoverlay></eventpromoimage>";
                }
                else
                {
                    echo "<eventpromoimage><eventpromoimageoverlay></eventpromoimageoverlay></eventpromoimage>";
                }
                echo "</eventtopbox>";
                echo "<eventbottombox><eventbottombottext>";
                    echo "<center>".$normaized_name."</center><eventbottombottomcountdown>".$status."</eventbottombottomcountdown>";
                echo "</eventbottombottext></eventbottombox>";
            echo "</eventitem>";
        }
    }
    $loop++;

}
//
?>
</maingrid>
</body>
</html>
