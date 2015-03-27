<?php
require_once('plugins/kjAccessor.php');

$dba = new kjPHP\Accessor('localhost','KungFuSchool','phpdude','dude!@#$');
$limit = isset($_GET['items']) ? $_GET['limit'] : 50;
$offset = ( isset($_GET['page']) ? $_GET['page'] : 0 ) * $limit;
$list = $dba->_query('SELECT handle,name,weapon,yinyang*wuxing AS attr FROM movementlist LIMIT '.$limit.' OFFSET '.$offset);
$movement = array(
    'name' => '',
    'belong' => '',
    'ordinal' => 0,
);
if( isset($_GET['q']) )
{
    $query = $dba->_query('SELECT * FROM movementlist WHERE handle=:handle LIMIT 1',array(':handle'=>$_GET['q']));
    if( $m = $query->fetch(PDO::FETCH_ASSOC) )
    {
        $movement = $m;
    }
}

$weapon_name = array(
    'unarmed' => '空手',
    'sword' => '劍',
    'blade' => '刀',
    'rod' => '棍',
    'stick' => '棒',
    'staff' => '杖',
    'hammer' => '鎚',
    'glove' => '手套',
    'lance' => '槍',
    'whip' => '鞭',
    'dagger' => '匕首',
    'claw' => '爪',
    'throw' => '暗器',
    'other' => '奇門'
);

$attribute = array(
    1 => '甲木', -1 => '乙木',
    2 => '丙火', -2 => '丁火',
    3 => '戊土', -3 => '己土',
    4 => '庚金', -4 => '辛金',
    5 => '壬水', -5 => '癸水'
);

?>
<!DOCTYPE html>

<html>

<head>
    <meta charset="utf-8" />
    <title>開山祖師 - 招式</title>
    <link rel="stylesheet" type="text/css" href="styles/common.css" />
    <link rel="stylesheet" type="text/css" href="styles/builder.css" />
    <style>
label { display: block; margin: 2px; }
    </style>
</head>

<body>

<div id="builder">
    <header>
        <h1>招式(Movement)產生器</h1>
    </header>
    <form class="-border">
        <label>
            <input type="text" name="title" value="<?=$movement['name']?>" />
        </label>
        <label>
            <input type="text" name="belong" value="<?=$movement['belong']?>" />
            <input type="number" name="ordinal" min="0" value="<?=$movement['ordinal']?>" />
        </label>
        <label>
            <select name="weapon">
                <?
                foreach( $weapon_name as $k => $v )
                {
                    $selected = ( $movement['weapon'] == $k ) ? ' selected' : '';
                    echo '<option value="' . $k . '"' . $selected . '>' . $v . '</option>';
                }
                ?>
                <option value="_new">(新增)</option>
            </select>
            <input type="text" name="new_weapon" />
        </label>
        <label>
            <select name="yinyang">
                <option value="1"<?=(($movement['yinyang']==1)?' selected':'')?>>陽</option>
                <option value="-1"<?=(($movement['yinyang']==-1)?' selected':'')?>>陰</option>
            </select>
            <select name="wuxing">
                <option value="1"<?=(($movement['wuxing']==1)?' selected':'')?>>木</option>
                <option value="2"<?=(($movement['wuxing']==2)?' selected':'')?>>火</option>
                <option value="3"<?=(($movement['wuxing']==3)?' selected':'')?>>土</option>
                <option value="4"<?=(($movement['wuxing']==4)?' selected':'')?>>金</option>
                <option value="5"<?=(($movement['wuxing']==5)?' selected':'')?>>水</option>
            </select>
        </label>
        <label>
            <select name="hitdice">
                <option value="4">四面骰</option>
                <option value="6">六面骰</option>
                <option value="8">八面骰</option>
                <option value="12">十二面骰</option>
                <option value="20">二十面骰</option>
            </select>
        </label>
        <label>
            <select name="timing">
                <option value="0">施展招式</option>
                <option value="1">招式成功</option>
                <option value="2">招式失敗</option>
            </select>
            後
            <input type="number" name="duration" min="0" max="5" />
            招內
            <select name="target">
                <option value="0">所有</option>
                <option value="1">同我</option>
                <option value="2">生我</option>
                <option value="3">我生</option>
                <option value="4">相生</option>
                <option value="5">剋我</option>
                <option value="6">我剋</option>
                <option value="7">相剋</option>
            </select>
            招式
            <select name="effect">
                <option value="0">無效果</option>
                <option value="1">增加命中率</option>
                <option value="2">制敵加成</option>
                <option value="3">加速制敵提昇</option>
                <option value="4">延長持續時間</option>
                <option value="-1">降低命中率</option>
                <option value="-2">制敵減半</option>
                <option value="-3">抑制制敵提昇</option>
                <option value="-4">減少持續時間</option>
            </select>
        </label>
        <button type="button" onclick="KungFu.submit(this.form);">完成</button>
        <button type="reset">重設</button>
    </form>
</div>

<div id="list">
<table>
    <thead>
        <tr>
            <th>招式名稱</th>
            <th style="width:100px;">使用兵器</th>
            <th style="width:80px;">陰陽五行</th>
        </tr>
    </thead>
    <tbody>
<?
while( $move = $list->fetch(PDO::FETCH_ASSOC) )
{
    echo '<tr>';
    echo '<td><a href="movement.php?q=' . $move['handle'] . '">' . $move['name'] . '</a></td>';
    echo '<td>' . $weapon_name[$move['weapon']] . '</td>';
    echo '<td>' . $attribute[$move['attr']] . '</td>';
}
?>
    </tbody>
</table>
</div>

</body>

</html>