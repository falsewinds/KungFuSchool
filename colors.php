<?php
require_once('plugins/kjAccessor.php');

$dba = new kjPHP\Accessor('localhost','style','phpdude','dude!@#$');
$colors = $dba->_query('SELECT * FROM colors');
?>

<!DOCTYPE html>

<html>
    <head>
        <meta charset="UTF-8" />
        <title>StyleBase : Color</title>
        <link rel="stylesheet" type="text/css" href="styles/common.css" />
        <link rel="stylesheet" type="text/css" href="styles/template.css" />
    </head>

    <body>
<?
while( $color = $colors->fetch(PDO::FETCH_ASSOC) )
{
?>
        <div class="color-ticket">
            <div class="color" style="background-color:<?=$color['id']?>;"></div>
            <table>
                <thead><tr><td colspan="2" style="color:<?=$color['id']?>;"><?=$color['name']?></td></tr></thead>
                <tbody>
                    <tr>
                        <th>RGB (CSS3)</th>
                        <td>(<?=$color['red']?>,<?=$color['green']?>,<?=$color['blue']?>)</td>
                    </tr>
                    <tr>
                        <th>HSV</th>
                        <td>(<?=$color['hue']?>,<?=$color['sv']?>)</td>
                    </tr>
                    <tr>
                        <th>HSL</th>
                        <td>(<?=$color['hue']?>,<?=$color['sl']?>)</td>
                    </tr>
                    <tr>
                        <th>CMYK</th>
                        <td>(<?=intval($color['cyan'])/100?>,<?=intval($color['magenta'])/100?>,<?=intval($color['yellow'])/100?>,<?=intval($color['black'])/100?>)</td>
                    </tr>
                </tbody>
            </table>
        </div>
<?
}
?>

    </body>
</html>