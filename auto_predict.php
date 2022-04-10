<?php

// DBアクセス準備
require_once( dirname( __FILE__ ) . '/wp-load.php' );
global $wpdb;
$db_user = "root"; //データベース接続ユーザーの取得
$db_passwd = "password"; //データベース接続用パスワードの取得
$db_host = "aws-and-infra-web.cc4tusje8m3w.ap-northeast-1.rds.amazonaws.com"; //データベースホストの取得
$keiba_wpdb = new wpdb($db_user, $db_passwd, 'keiba', $db_host);

// レース名
$target_race_name_jp=$_POST['race_name'];
$target_race_name_eng=$keiba_wpdb->get_row("SELECT ENG_NAME FROM RACENAME_JP_ENG_TRANS WHERE JP_NAME = \"$_POST[race_name] \"")->ENG_NAME;

// 前年検索
$pre_year=$_POST['pre_year_select'];

// 開始年～終了年を配列に格納(老番から格納する)
$year_array=array();
$diff = $_POST['year_e'] - $_POST['year_s'];
for ($i=0;$i<=$diff;$i++){
    $year_array[$i]=$_POST['year_e']-$i;
}

// 着順を配列に格納
$chakujun_array=array();
$diff = $_POST['chakujun_max'] - $_POST['chakujun_min'];
for ($i=0;$i<=$diff;$i++){
    $chakujun_array[$i]=$_POST['chakujun_min']+$i;
}

// 着順の正規表現を作成
$chakujun_regexp=".*";
for($i=0;$i<count($chakujun_array);$i++){
    if($chakujun_regexp==".*"){
        $chakujun_regexp=(string)$chakujun_array[$i];
    }else{
        $chakujun_regexp="$chakujun_regexp"."|".(string)$chakujun_array[$i];
    }
}
$chakujun_regexp="^(".$chakujun_regexp.")$";

// レース結果の馬名正規表現を格納
for($i=0;$i<count($year_array);$i++){
    $tmp_array[$i]=$keiba_wpdb->get_results("SELECT * FROM " . $target_race_name_eng . " WHERE 年度 REGEXP \"$year_array[$i]\" AND 着順 REGEXP \"$chakujun_regexp\"");
    // 正規表現を作成
    $umamei_regexp[$i]=".*";
    foreach($tmp_array[$i] as $tmp){
        if($umamei_regexp[$i]==".*"){
            $umamei_regexp[$i]=(string)$tmp->馬名;
        }else{
            $umamei_regexp[$i]="$umamei_regexp[$i]"."|".(string)$tmp->馬名;
        }
    }
    $umamei_regexp[$i]="^(".$umamei_regexp[$i].")$";
}

// print_r($umamei_regexp);

// DBに格納しているレース名を格納(RACE_NAME_ENG_JPテーブルも含まれることに注意)
// $all_table=$keiba_wpdb->get_results("SHOW TABLES;");
$all_table=$keiba_wpdb->get_results("SELECT * FROM RACE_DATE_SORT");

// レース名ごとに結果を格納
$race_results=array(); // [レース番号][開催年][行]
$race_name=array();
$num=0;
$target_num=0;

// array_push($year_array,($year_array[count($year_array)-1]-1));
// print_r($year_array);
// return 0;

for($i=0;$i<count($all_table);$i++){
    $table_name_jp=$all_table[$i]->RACE_NAME;
    $table_name_eng=$keiba_wpdb->get_row("SELECT ENG_NAME FROM RACENAME_JP_ENG_TRANS WHERE JP_NAME = \"$table_name_jp\"")->ENG_NAME;
    $num_tmp=$num;
    $add_num=0;
    for($j=0;$j<count($year_array);$j++){
        $tmp_array=array();
        if($pre_year=="PRE" && $table_name_eng!=$target_race_name_jp){
            $tg_year=$year_array[$j]."|".($year_array[$j]-1);
        }else{
            $tg_year=$year_array[$j];
        }
        $tmp_array=$keiba_wpdb->get_results("SELECT * FROM " . $table_name_eng . " WHERE 年度 REGEXP \"$tg_year\" AND 馬名 REGEXP \"$umamei_regexp[$j]\"");
        // if($table_name_eng=="Hanshin_Juvenile_Fillies"){
        //     print_r($tmp_array);
        //     // return 0;
        // }
        //$tmp_array=$keiba_wpdb->get_results("SELECT * FROM " . $table_name_eng . " WHERE 年度 REGEXP \"$year_array[$j]\" AND 馬名 REGEXP \"$umamei_regexp[$j]\"");
        if($tmp_array){
            $race_results[$num][$add_num]=$keiba_wpdb->get_results("SELECT * FROM " . $table_name_eng . " WHERE 年度 REGEXP \"$tg_year\" AND 馬名 REGEXP \"$umamei_regexp[$j]\"");
            $add_num++;
        }
    }
    // for($j=0;$j<count($year_array);$j++){
    //     $tmp_array=array();
    //     $tmp_array=$keiba_wpdb->get_results("SELECT * FROM " . $table_name_eng . " WHERE 年度 REGEXP \"$year_array[$j]\" AND 馬名 REGEXP \"$umamei_regexp[$j]\"");
    //     if($tmp_array){
    //         $race_results[$num][$add_num]=$keiba_wpdb->get_results("SELECT * FROM " . $table_name_eng . " WHERE 年度 REGEXP \"$year_array[$j]\" AND 馬名 REGEXP \"$umamei_regexp[$j]\"");
    //         $add_num++;
    //     }
    // }

    if($race_results[$num]){
        $race_name[$num]=$table_name_jp;
        // $race_name[$table_name_jp]=$table_name_jp
        if($race_name[$num]==$target_race_name_jp){
            $target_num=$num;
        }
        $num++;
    }
}

// $unique = array_unique($race_results[$target_num]);
// print_r($unique);

// $copy=$race_results[$target_num][0][0];
// print_r($race_results[$target_num]);
// print_r($copy);

$race_results_as_umamei=array();
// 予想レースは1頭ごとに結果表を作る
for($i=0;$i<count($race_results[$target_num]);$i++){
    for($j=0;$j<count($race_results[$target_num][$i]);$j++){
        $uma_name=$race_results[$target_num][$i][$j]->馬名;

        // 予想レースの馬名ごとに結果表を作成
        for($x=0;$x<count($race_name);$x++){
            $tmp_num=0;
            for($y=0;$y<count($race_results[$x]);$y++){
                for($z=0;$z<count($race_results[$x][$y]);$z++){
                    if($race_results[$x][$y][$z]->馬名==$uma_name){
                        $race_results_as_umamei[$uma_name][$x][$tmp_num]=$race_results[$x][$y][$z];
                        $tmp_num++;
                    }
                }
            }
        }
    }
}

// print($target_num);
// print_r($race_name);
// print_r($race_results[$target_num]);
// return 0;

// print_r($race_results[0]);
// print_r($race_name[0]);
// return 0;
?>

<!-- 入力情報を保存する -->
<script type="text/javascript">
    // ターゲットレース
    function targetracesave(){
        document.getElementById("race_name").value="<?php echo $_POST["race_name"]; ?>";
    }
    // ターゲット期間
    function targetyearsave(){
        document.getElementById("year_s").value=<?php echo $_POST['year_s']; ?>;
        document.getElementById("year_e").value=<?php echo $_POST['year_e']; ?>;
    }
    // ターゲット着順
    function targetchakujunsave(){
        document.getElementById("chakujun_min").value=<?php echo $_POST['chakujun_min']; ?>;
        document.getElementById("chakujun_max").value=<?php echo $_POST['chakujun_max']; ?>;
    }
    // 前年検索着順
    function preyearselectsave(){
        document.getElementById("pre_year_select").value="<?php echo $_POST["pre_year_select"]; ?>";
    }
</script>

<!-- 値保持についてJavaScriptでは最初の更新しか適用されないため、下記PHPを実行する -->
<?php echo '<script type="text/javascript">','targetracesave();','</script>'; ?>
<?php echo '<script type="text/javascript">','targetyearsave();','</script>'; ?>
<?php echo '<script type="text/javascript">','targetchakujunsave();','</script>'; ?>
<?php echo '<script type="text/javascript">','preyearselectsave();','</script>'; ?>

<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Hello!</title>
    <link rel="stylesheet" href="/execution.css">
    <style>
        .target_table {
            border-collapse: collapse;
        }
        .target_table th {
            border: 1px solid gray;
            text-align: center;
            width: max-content;
            color: gray;
            /* background: gainsboro; */
            background: deepskyblue;
            color: white;
            font-size: 14px;
        }
        .target_table td {
            border: 1px solid gray;
            text-align:left;
        }

        .jouken_table {
            border-collapse: collapse;
        }
        .jouken_table th {
            border: 1px solid gray;
            text-align: center;
            width: max-content;
            color: gray;
            background: gainsboro;
            /* background: deepskyblue; */
            color: gray;
            font-size: 13px;
        }
        .jouken_table td {
            border: 1px solid gray;
            text-align:left;
            font-size: 13px;
            color:black;
        }

        .tatget_scroll{
            overflow: visible; /*tableをスクロールさせる*/
            white-space: nowrap; /*tableのセル内にある文字の折り返しを禁止*/
        }
        .tatget_scroll::-webkit-scrollbar{ /*tableにスクロールバーを追加*/
            height: 5px;
        }
        .tatget_scroll::-webkit-scrollbar-track{/*tableにスクロールバーを追加*/
            background: #F1F1F1;
        }
        .tatget_scroll::-webkit-scrollbar-thumb { /*tableにスクロールバーを追加*/
            background: #BCBCBC;
        }

        /* .target_table_wakuban {
            background-color: red;;
        } */

        /* ポップアップ表示 */
        #popup{
            width:auto;
            height:auto;
            background:ghostwhite;
            padding:0 4%;
            box-sizing:border-box;
            display:none;
            position:fixed;
            top:50%;
            left:50%;
            -webkit-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
        }
        input[type="checkbox"]{
            display:none;
        }
        input[type="checkbox"]:checked + #popup{
            display:block;
            transition:.2s;
        }        
    </style>
</head>
<body>
    <b><?php echo $race_name[$target_num]; ?></b>
    <div class="tatget_scroll">
        <table class="target_table" border="1" style="margin-top: 0%;">
            <tr><th>年度</th><th>馬名</th><th>着順</th><th>人気</th><th>馬番</th><th>枠番</th><th>性齢</th><th>斤量</th><th>騎手</th><th>タイム</th><th>通過</th><th>上り</th><th>上り順位</th><th>単勝</th><th>馬体重</th></tr>
            <?php for($j=0;$j<count($race_results[$target_num]);$j++) : ?>
                <?php for($k=0;$k<count($race_results[$target_num][$j]);$k++) : ?>
                    <tr><td bgcolor="white"><?php echo $race_results[$target_num][$j][$k]->年度 ?></td>
                    <td bgcolor="white">
                    <label>
                        <span><u style="color: blue;"><?php echo $race_results[$target_num][$j][$k]->馬名 ?><u></span>
                        <input type="checkbox" name="checkbox">
                        <div id="popup">
                            <div class="tatget_scroll">
                                <table class="jouken_table">
                                <p style="margin-bottom: 0%;color: black;font-size: 15px">関連レース</p>
                                <tr><th>レース名</th><th>年度</th><th>馬名</th><th>着順</th><th>人気</th><th>馬番</th><th>枠番</th><th>性齢</th><th>斤量</th><th>騎手</th><th>タイム</th><th>通過</th><th>上り</th><th>上り順位</th><th>単勝</th><th>馬体重</th></tr>
                                    <?php $umamei=$race_results[$target_num][$j][$k]->馬名 ?>
                                    <?php for($x=0;$x<count($race_name);$x++) : ?>
                                        <?php if(!$race_results_as_umamei["$umamei"][$x]){ continue; } ?>
                                        <?php for($y=0;$y<count($race_results_as_umamei["$umamei"][$x]);$y++) : ?>
                                            <tr><td bgcolor="white"><?php echo $race_name[$x]; ?></td><td bgcolor="white"><?php echo $race_results_as_umamei["$umamei"][$x][$y]->年度 ?></td><td bgcolor="white"><?php echo $race_results_as_umamei["$umamei"][$x][$y]->馬名 ?></td><td bgcolor="white"><?php echo $race_results_as_umamei["$umamei"][$x][$y]->着順 ?></td><td bgcolor="white"><?php echo $race_results_as_umamei["$umamei"][$x][$y]->人気 ?></td><td bgcolor="white"><?php echo $race_results_as_umamei["$umamei"][$x][$y]->馬番 ?></td><td bgcolor="white"><?php echo $race_results_as_umamei["$umamei"][$x][$y]->枠番 ?></td><td bgcolor="white"><?php echo $race_results_as_umamei["$umamei"][$x][$y]->性齢 ?></td><td bgcolor="white"><?php echo $race_results_as_umamei["$umamei"][$x][$y]->斤量 ?></td><td bgcolor="white"><?php echo $race_results_as_umamei["$umamei"][$x][$y]->騎手 ?></td><td bgcolor="white"><?php echo substr_replace(substr($race_results_as_umamei["$umamei"][$x][$y]->タイム, 1, 7),".",4,1) ?></td><td bgcolor="#ffffff"><?php echo $race_results_as_umamei["$umamei"][$x][$y]->通過 ?></td><td bgcolor="white"><?php echo $race_results_as_umamei["$umamei"][$x][$y]->上り ?></td><td bgcolor="white"><?php echo $race_results_as_umamei["$umamei"][$x][$y]->上り順位 ?></td><td bgcolor="white"><?php echo $race_results_as_umamei["$umamei"][$x][$y]->単勝 ?></td><td bgcolor="white"><?php echo $race_results_as_umamei["$umamei"][$x][$y]->馬体重 ?></td></tr>
                                        <?php endfor ?>
                                    <?php endfor ?>
                                </table>
                            </div>
                        </div>
                    </label>
                    </td>
                    <td bgcolor="white"><?php echo $race_results[$target_num][$j][$k]->着順 ?></td><td bgcolor="white"><?php echo $race_results[$target_num][$j][$k]->人気 ?></td><td bgcolor="white"><?php echo $race_results[$target_num][$j][$k]->馬番 ?></td>
                    <td bgcolor="white" class="target_table_wakuban_<?php echo $j."_".$k ?>" id="target_table_wakuban_<?php echo $j."_".$k ?>"><?php echo $race_results[$target_num][$j][$k]->枠番 ?></td>
                    <td bgcolor="white"><?php echo $race_results[$target_num][$j][$k]->性齢 ?></td><td bgcolor="white"><?php echo $race_results[$target_num][$j][$k]->斤量 ?></td><td bgcolor="white"><?php echo $race_results[$target_num][$j][$k]->騎手 ?></td><td bgcolor="white"><?php echo substr_replace(substr($race_results[$target_num][$j][$k]->タイム, 1, 7),".",4,1) ?></td><td bgcolor="#ffffff"><?php echo $race_results[$target_num][$j][$k]->通過 ?></td><td bgcolor="white"><?php echo $race_results[$target_num][$j][$k]->上り ?></td><td bgcolor="white"><?php echo $race_results[$target_num][$j][$k]->上り順位 ?></td><td bgcolor="white"><?php echo $race_results[$target_num][$j][$k]->単勝 ?></td><td bgcolor="white"><?php echo $race_results[$target_num][$j][$k]->馬体重 ?></td></tr>
                <?php endfor ?>
            <?php endfor; ?>
        </table>
    </div>

    <p><?php echo "<以下関連レース>"; ?></p>  

    <?php for($i=0;$i<count($race_name);$i++) : ?>
        <?php if($i==$target_num){ continue;} ?>
        <b><?php echo $race_name[$i]; ?></b>
        <div class="tatget_scroll">
            <table class="target_table" border="1" style="margin-top: 0%;">
                <tr><th>年度</th><th>馬名</th><th>着順</th><th>人気</th><th>馬番</th><th>枠番</th><th>性齢</th><th>斤量</th><th>騎手</th><th>タイム</th><th>通過</th><th>上り</th><th>上り順位</th><th>単勝</th><th>馬体重</th></tr>
                <?php for($j=0;$j<count($race_results[$i]);$j++) : ?>
                    <?php for($k=0;$k<count($race_results[$i][$j]);$k++) : ?>
                        <tr><td bgcolor="white"><?php echo $race_results[$i][$j][$k]->年度 ?></td><td bgcolor="white"><?php echo $race_results[$i][$j][$k]->馬名 ?></td><td bgcolor="white"><?php echo $race_results[$i][$j][$k]->着順 ?></td><td bgcolor="white"><?php echo $race_results[$i][$j][$k]->人気 ?></td><td bgcolor="white"><?php echo $race_results[$i][$j][$k]->馬番 ?></td>
                        <td bgcolor="white" class="jouken_table_wakuban_<?php echo $i."_".$j."_".$k ?>" id="jouken_table_wakuban_<?php echo $i."_".$j."_".$k ?>"><?php echo $race_results[$i][$j][$k]->枠番 ?></td>
                        <td bgcolor="white"><?php echo $race_results[$i][$j][$k]->性齢 ?></td><td bgcolor="white"><?php echo $race_results[$i][$j][$k]->斤量 ?></td><td bgcolor="white"><?php echo $race_results[$i][$j][$k]->騎手 ?></td><td bgcolor="white"><?php echo substr_replace(substr($race_results[$i][$j][$k]->タイム, 1, 7),".",4,1) ?></td><td bgcolor="#ffffff"><?php echo $race_results[$i][$j][$k]->通過 ?></td><td bgcolor="white"><?php echo $race_results[$i][$j][$k]->上り ?></td><td bgcolor="white"><?php echo $race_results[$i][$j][$k]->上り順位 ?></td><td bgcolor="white"><?php echo $race_results[$i][$j][$k]->単勝 ?></td><td bgcolor="white"><?php echo $race_results[$i][$j][$k]->馬体重 ?></td></tr>
                    <?php endfor ?>
                <?php endfor; ?>
            </table>
        </div>
    <?php endfor; ?>
</body>
</html>

<!-- 結果表の色を変える -->
<!-- 枠番 -->
<script type="text/javascript">
    // ターゲットレース
    function wakuban_color_edit_target(i,k){
        var num=document.getElementById('target_table_wakuban_'+i+'_'+k).innerHTML;
        
        if(num == 1){
            document.querySelector('.target_table_wakuban_'+i+'_'+k).style.backgroundColor = '#FEFEFE';
            document.querySelector('.target_table_wakuban_'+i+'_'+k).style.color = "black";
        }else if(num == 2){
            document.querySelector('.target_table_wakuban_'+i+'_'+k).style.backgroundColor = '#444444';
            document.querySelector('.target_table_wakuban_'+i+'_'+k).style.color = "white";
        }else if(num == 3){
            document.querySelector('.target_table_wakuban_'+i+'_'+k).style.backgroundColor = '#E95556';
            document.querySelector('.target_table_wakuban_'+i+'_'+k).style.color = "white";
        }else if(num == 4){
            document.querySelector('.target_table_wakuban_'+i+'_'+k).style.backgroundColor = '#416CBA';
            document.querySelector('.target_table_wakuban_'+i+'_'+k).style.color = "white";
        }else if(num == 5){
            document.querySelector('.target_table_wakuban_'+i+'_'+k).style.backgroundColor = '#E7C52C';
            document.querySelector('.target_table_wakuban_'+i+'_'+k).style.color = "white";
        }else if(num == 6){
            document.querySelector('.target_table_wakuban_'+i+'_'+k).style.backgroundColor = '#45AF4C';
            document.querySelector('.target_table_wakuban_'+i+'_'+k).style.color = "white";
        }else if(num == 7){
            document.querySelector('.target_table_wakuban_'+i+'_'+k).style.backgroundColor = '#EE9738';
            document.querySelector('.target_table_wakuban_'+i+'_'+k).style.color = "white";
        }else if(num == 8){
            document.querySelector('.target_table_wakuban_'+i+'_'+k).style.backgroundColor = '#EF8FA0';
            document.querySelector('.target_table_wakuban_'+i+'_'+k).style.color = "white";
        }
    }
    // 関連レース
    function wakuban_color_edit_jouken(i,k,j){
        var num=document.getElementById('jouken_table_wakuban_'+i+'_'+k+'_'+j).innerHTML;
        
        if(num == 1){
            document.querySelector('.jouken_table_wakuban_'+i+'_'+k+'_'+j).style.backgroundColor = '#FEFEFE';
            document.querySelector('.jouken_table_wakuban_'+i+'_'+k+'_'+j).style.color = "black";
        }else if(num == 2){
            document.querySelector('.jouken_table_wakuban_'+i+'_'+k+'_'+j).style.backgroundColor = '#444444';
            document.querySelector('.jouken_table_wakuban_'+i+'_'+k+'_'+j).style.color = "white";
        }else if(num == 3){
            document.querySelector('.jouken_table_wakuban_'+i+'_'+k+'_'+j).style.backgroundColor = '#E95556';
            document.querySelector('.jouken_table_wakuban_'+i+'_'+k+'_'+j).style.color = "white";
        }else if(num == 4){
            document.querySelector('.jouken_table_wakuban_'+i+'_'+k+'_'+j).style.backgroundColor = '#416CBA';
            document.querySelector('.jouken_table_wakuban_'+i+'_'+k+'_'+j).style.color = "white";
        }else if(num == 5){
            document.querySelector('.jouken_table_wakuban_'+i+'_'+k+'_'+j).style.backgroundColor = '#E7C52C';
            document.querySelector('.jouken_table_wakuban_'+i+'_'+k+'_'+j).style.color = "white";
        }else if(num == 6){
            document.querySelector('.jouken_table_wakuban_'+i+'_'+k+'_'+j).style.backgroundColor = '#45AF4C';
            document.querySelector('.jouken_table_wakuban_'+i+'_'+k+'_'+j).style.color = "white";
        }else if(num == 7){
            document.querySelector('.jouken_table_wakuban_'+i+'_'+k+'_'+j).style.backgroundColor = '#EE9738';
            document.querySelector('.jouken_table_wakuban_'+i+'_'+k+'_'+j).style.color = "white";
        }else if(num == 8){
            document.querySelector('.jouken_table_wakuban_'+i+'_'+k+'_'+j).style.backgroundColor = '#EF8FA0';
            document.querySelector('.jouken_table_wakuban_'+i+'_'+k+'_'+j).style.color = "white";
        }
    }
</script>
<?php
for($i=0;$i<count($race_results[$target_num]);$i++){
    for($k=0;$k<count($race_results[$target_num][$i]);$k++){
        echo '<script type="text/javascript">',"wakuban_color_edit_target($i,$k);",'</script>';
    }
}
for($i=0;$i<count($race_results);$i++){
    if($i==$target_num){ continue; }
    for($k=0;$k<count($race_results[$i]);$k++){
        for($j=0;$j<count($race_results[$i][$k]);$j++){
            echo '<script type="text/javascript">',"wakuban_color_edit_jouken($i,$k,$j);",'</script>';
        }
    }
}
?>

<?php return 0; ?>