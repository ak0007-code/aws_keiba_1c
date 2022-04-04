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

// DBに格納しているレース名を格納(RACE_NAME_ENG_JPテーブルも含まれることに注意)
// $all_table=$keiba_wpdb->get_results("SHOW TABLES;");
$all_table=$keiba_wpdb->get_results("SELECT * FROM RACE_DATE_SORT");

// レース名ごとに結果を格納
$race_results=array(); // [レース番号][開催年][行]
$race_name=array();
$num=0;
$target_num=0;
for($i=0;$i<count($all_table);$i++){
    $table_name_jp=$all_table[$i]->RACE_NAME;
    $table_name_eng=$keiba_wpdb->get_row("SELECT ENG_NAME FROM RACENAME_JP_ENG_TRANS WHERE JP_NAME = \"$table_name_jp\"")->ENG_NAME;
    $num_tmp=$num;
    $add_num=0;
    for($j=0;$j<count($year_array);$j++){
        $tmp_array=array();
        $tmp_array=$keiba_wpdb->get_results("SELECT * FROM " . $table_name_eng . " WHERE 年度 REGEXP \"$year_array[$j]\" AND 馬名 REGEXP \"$umamei_regexp[$j]\"");
        if($tmp_array){
            $race_results[$num][$add_num]=$keiba_wpdb->get_results("SELECT * FROM " . $table_name_eng . " WHERE 年度 REGEXP \"$year_array[$j]\" AND 馬名 REGEXP \"$umamei_regexp[$j]\"");
            $add_num++;
        }
    }

    if($race_results[$num]){
        $race_name[$num]=$table_name_jp;
        // $race_name[$table_name_jp]=$table_name_jp
        if($race_name[$num]==$target_race_name_jp){
            $target_num=$num;
        }
        $num++;
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
</script>

<!-- 値保持についてJavaScriptでは最初の更新しか適用されないため、下記PHPを実行する -->
<?php echo '<script type="text/javascript">','targetracesave();','</script>'; ?>
<?php echo '<script type="text/javascript">','targetyearsave();','</script>'; ?>
<?php echo '<script type="text/javascript">','targetchakujunsave();','</script>'; ?>

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
            font-size: 12px;
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
    </style>
</head>
<body>
    <p><?php echo "◆".$race_name[$target_num]; ?></p>
    <div class="tatget_scroll">
        <table class="target_table" border="1" style="margin-top: -3%;">
            <tr><th>年度</th><th>馬名</th><th>着順</th><th>人気</th><th>馬番</th><th>枠番</th><th>性齢</th><th>斤量</th><th>騎手</th><th>タイム</th><th>通過</th><th>上り</th><th>単勝</th><th>馬体重</th></tr>
            <?php for($j=0;$j<count($race_results[$target_num]);$j++) : ?>
                <?php for($k=0;$k<count($race_results[$target_num][$j]);$k++) : ?>
                    <tr><td bgcolor="white"><?php echo $race_results[$target_num][$j][$k]->年度 ?></td><td bgcolor="white"><?php echo $race_results[$target_num][$j][$k]->馬名 ?></td><td bgcolor="white"><?php echo $race_results[$target_num][$j][$k]->着順 ?></td><td bgcolor="white"><?php echo $race_results[$target_num][$j][$k]->人気 ?></td><td bgcolor="white"><?php echo $race_results[$target_num][$j][$k]->馬番 ?></td><td bgcolor="white"><?php echo $race_results[$target_num][$j][$k]->枠番 ?></td><td bgcolor="white"><?php echo $race_results[$target_num][$j][$k]->性齢 ?></td><td bgcolor="white"><?php echo $race_results[$target_num][$j][$k]->斤量 ?></td><td bgcolor="white"><?php echo $race_results[$target_num][$j][$k]->騎手 ?></td><td bgcolor="white"><?php echo substr_replace(substr($race_results[$target_num][$j][$k]->タイム, 1, 7),".",4,1) ?></td><td bgcolor="#ffffff"><?php echo $race_results[$target_num][$j][$k]->通過 ?></td><td bgcolor="white"><?php echo $race_results[$target_num][$j][$k]->上り ?></td><td bgcolor="white"><?php echo $race_results[$target_num][$j][$k]->単勝 ?></td><td bgcolor="white"><?php echo $race_results[$target_num][$j][$k]->馬体重 ?></td></tr>
                <?php endfor ?>
            <?php endfor; ?>
        </table>
    </div>

    <p><?php echo "以下、参考レース"; ?></p>

    <?php for($i=0;$i<count($race_name);$i++) : ?>
        <?php if($i==$target_num){ break;} ?>
        <p><?php echo "◆".$race_name[$i]; ?></p>
        <div class="tatget_scroll">
            <table class="target_table" border="1" style="margin-top: -3%;">
                <tr><th>年度</th><th>馬名</th><th>着順</th><th>人気</th><th>馬番</th><th>枠番</th><th>性齢</th><th>斤量</th><th>騎手</th><th>タイム</th><th>通過</th><th>上り</th><th>単勝</th><th>馬体重</th></tr>
                <?php for($j=0;$j<count($race_results[$i]);$j++) : ?>
                    <?php for($k=0;$k<count($race_results[$i][$j]);$k++) : ?>
                        <tr><td bgcolor="white"><?php echo $race_results[$i][$j][$k]->年度 ?></td><td bgcolor="white"><?php echo $race_results[$i][$j][$k]->馬名 ?></td><td bgcolor="white"><?php echo $race_results[$i][$j][$k]->着順 ?></td><td bgcolor="white"><?php echo $race_results[$i][$j][$k]->人気 ?></td><td bgcolor="white"><?php echo $race_results[$i][$j][$k]->馬番 ?></td><td bgcolor="white"><?php echo $race_results[$i][$j][$k]->枠番 ?></td><td bgcolor="white"><?php echo $race_results[$i][$j][$k]->性齢 ?></td><td bgcolor="white"><?php echo $race_results[$i][$j][$k]->斤量 ?></td><td bgcolor="white"><?php echo $race_results[$i][$j][$k]->騎手 ?></td><td bgcolor="white"><?php echo substr_replace(substr($race_results[$i][$j][$k]->タイム, 1, 7),".",4,1) ?></td><td bgcolor="#ffffff"><?php echo $race_results[$i][$j][$k]->通過 ?></td><td bgcolor="white"><?php echo $race_results[$i][$j][$k]->上り ?></td><td bgcolor="white"><?php echo $race_results[$i][$j][$k]->単勝 ?></td><td bgcolor="white"><?php echo $race_results[$i][$j][$k]->馬体重 ?></td></tr>
                    <?php endfor ?>
                <?php endfor; ?>
            </table>
        </div>
    <?php endfor; ?>
</body>
</html>

<?php return 0; ?>