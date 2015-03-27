<?php
require_once('../plugins/kjAccessor.php');

function fromHEX($hex,$size = null)
{
    $hexpair = array(
        '0' =>  0, '1' =>  1, '2' =>  2, '3' =>  3,
        '4' =>  4, '5' =>  5, '6' =>  6, '7' =>  7,
        '8' =>  8, '9' =>  9, 'A' => 10, 'B' => 11,
        'C' => 12, 'D' => 13, 'E' => 14, 'F' => 15
    );
    $result = 0;
    if( $size == null ) { $size = strlen($hex); }
    for($i=0;$i<$size;$i++)
    {
        $base = 16 * ($size-$i-1);
        if( $base <= 0 ) { $base = 1; }
        $chr = substr($hex,$i,1);
        $result += $hexpair[$chr] * $base;
    }
    return $result;
}

$dba = new kjPHP\Accessor('localhost','style','phpdude','dude!@#$');

$file = 'ch_color.csv';
$fp = fopen($file,'r');
$header = fgetcsv($fp);
while( $line = fgetcsv($fp) )
{
    $colordata = array();
    $color = array_combine($header,$line);
    $rgb16 = strtoupper($color['rgb']);
    if( empty($rgb16) ) { continue; }

    $c_row = $dba->_query('SELECT name,more,alias FROM colors WHERE id=:cid',array(':cid'=>$rgb16));
    if( $row = $c_row->fetch(PDO::FETCH_ASSOC) )
    {
        $alias = json_decode($row['alias'],true);
        if( isset($color['name']) )
        {
            $name = rtrim($color['name'],'色');
            if( $name != $row['name'] ) { $alias[] = $name; }
        }
        if( isset($color['eng_name']) ) { $alias[] = trim($color['eng_name']); }
        $description = $row['more'];
        if( isset($color['description']) ) { $description .= trim($color['description']); }
        $dba->_execute('UPDATE colors SET more=:more,alias=:alias WHERE id=:cid LIMIT 1',array(
            ':cid' => $rgb16,
            ':more' => $description,
            ':alias' => json_encode($alias)
        ));
        echo $rgb16 . ' has been recorded, update alias & information.' . PHP_EOL;
        continue;
    }

    $red = isset($color['red']) ? $color['red'] : fromHEX(substr($rgb16,1,2));
    $colordata[':red'] = $red;
    $green = isset($color['green']) ? $color['green'] : fromHEX(substr($rgb16,3,2));
    $colordata[':green'] = $green;
    $blue = isset($color['blue']) ? $color['blue'] : fromHEX(substr($rgb16,5,2));
    $colordata[':blue'] = $blue;

    $r_rate = $red / 255;
    $g_rate = $green / 255;
    $b_rate = $blue / 255;
    $rgb_max = max($r_rate,$g_rate,$b_rate);
    $rgb_min = min($r_rate,$g_rate,$b_rate);
    $diff = $rgb_max - $rgb_min;

    // HSL,HSV

    $hue = 0;
    if( $rgb_max == $rgb_min )
    {
        $hue = 0;
    }
    else if ( $rgb_max == $r_rate )
    {
        $hue = 60 * ($g_rate-$b_rate) / $diff;
        if( $g_rate < $b_rate ) { $hue += 360; }
    }
    else if ( $rgb_max == $g_rate )
    {
        $hue = 120 + 60 * ($b_rate-$r_rate) / $diff;
    }
    else
    {
        $hue = 240 + 60 * ($r_rate-$g_rate) / $diff;
    }
    $colordata[':hue'] = round($hue);

    $hsl_l = ( $rgb_max + $rgb_min ) / 2;
    $hsl_s = 0;
    if( $rgb_max == $rgb_min OR $hsl_l == 0 )
    {
        $hsl_s = 0;
    }
    else if ( $hsl_l > 0 AND $hsl_l <= 0.5 )
    {
        $hsl_s = $diff / ($hsl_l*2);
    }
    else
    {
        $hsl_s = $diff / (2-($hsl_l*2));
    }

    $hsv_v = $rgb_max;
    $hsv_s = ( ($rgb_max) == 0 ) ? 0 : 1-$rgb_min/$rgb_max;

    $colordata[':sl'] = round($hsl_s*100) . ',' . round($hsl_l*100);
    $colordata[':sv'] = round($hsv_s*100) . ',' . round($hsv_v*100);

    // CMYK
    $cyan = 0;
    $magenta = 0;
    $yellow = 0;
    $black = 0;
    $c = 1- $r_rate;
    $m = 1- $g_rate;
    $y = 1- $b_rate;
    $k = min($c,$m,$y);
    if( $k == 1 )
    {
        $black = 1;
    }
    else
    {
        $black = $k;
        $key = 1 - $k;
        $cyan = ($c-$k) / $key;
        $magenta = ($m-$k) / $key;
        $yellow = ($y-$k) / $key;
    }
    $colordata[':cyan'] = round($cyan*100);
    $colordata[':magenta'] = round($magenta*100);
    $colordata[':yellow'] = round($yellow*100);
    $colordata[':black'] = round($black*100);

    if( isset($color['name']) ) { $colordata[':name'] = rtrim($color['name'],'色'); }
    if( isset($color['description']) ) { $colordata[':more'] = trim($color['description']); }

    $alias = array();
    if( isset($color['eng_name']) ) { $alias[] = trim($color['eng_name']); }
    $colordata[':alias'] = json_encode($alias,JSON_UNESCAPED_UNICODE);

    $dba->_execute(
        'INSERT INTO colors(id,red,green,blue,hue,sv,sl,cyan,magenta,yellow,black,name,more,alias,tags) '
            . "VALUE ('{$rgb16}',:red,:green,:blue,:hue,:sv,:sl,:cyan,:magenta,:yellow,:black,:name,:more,:alias,'[]')",
        $colordata
    );
    echo ( isset($colordata[':name']) ? $colordata[':name'] : $rgb16 ) . ' is recorded.' . PHP_EOL;
}