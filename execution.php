<?php

// 定数定義
const J_MAX=9; // 条件MAX
const UMABAN_MAX=25; // 馬番MAX
const WAKUBAN_MAX=15; // 枠番MAX
const SEIREI_MIN=2; // 性齢MIN
const SEIREI_MAX=9; // 性齢MAX
const KINRYO_MIN=52; // 斤量MIN
const KINRYO_MAX=59; // 斤量MAX
const NINKI_MAX=25; // 人気MAX

// DBアクセス準備
require_once( dirname( __FILE__ ) . '/wp-load.php' );
global $wpdb;
$db_user = "root"; //データベース接続ユーザーの取得
$db_passwd = "password"; //データベース接続用パスワードの取得
$db_host = "aws-and-infra-web.cc4tusje8m3w.ap-northeast-1.rds.amazonaws.com"; //データベースホストの取得
$keiba_wpdb = new wpdb($db_user, $db_passwd, 'keiba', $db_host);

// 開始年～終了年を配列に格納
$diff = $_POST['year_e'] - $_POST['year_s'];
for ($i=0;$i<=$diff;$i++){
    $year_array[$i]=$_POST['year_s']+$i;
}

// レース名を英語に変換して格納
$target_race_name=$keiba_wpdb->get_row("SELECT ENG_NAME FROM RACENAME_JP_ENG_TRANS WHERE JP_NAME = \"$_POST[race_name] \"")->ENG_NAME;

// データ格納用配列
$delete_check_btn_array=array(); // 削除ボタンの押下状況
$race_name_array_eng=array(); //レース名
$umaban_array=array(); // 馬番
$wakuban_array=array(); // 枠番
$seirei_h_array=array(); // 性齢 牝馬
$seirei_b_array=array(); // 性齢 牡馬
$kinryo_array=array(); // 斤量
$time_min_array=array(); // タイム最小値
$time_max_array=array(); // タイム最大値
$tuka_1_array=array(); // 通過(第1コーナー)
$tuka_2_array=array(); // 通過(第2コーナー)
$tuka_3_array=array(); // 通過(第3コーナー)
$tuka_4_array=array(); // 通過(第4コーナー)
$agari_min_array=array(); // 上り最小値
$agari_max_array=array(); // 上り最大値
$tansho_min_array=array(); // 単勝最小値
$tansho_max_array=array(); // 単勝最大値
$ninki_array=array(); // 人気

// 条件を確認 j:条件 i:個別番号
for ($j=0;$j<J_MAX;$j++){
    // 削除ボタン
    $delete_check_btn_array[$j]=$_POST["j".($j+1)."_delete_checkbox"];

    // レース名
    $race_name_array_jp[$j]=$_POST["j".($j+1)."_race_name"];
    // 英語表記
    $race_name_array_eng[$j]=$keiba_wpdb->get_row("SELECT ENG_NAME FROM RACENAME_JP_ENG_TRANS WHERE JP_NAME = \"$race_name_array_jp[$j]\"")->ENG_NAME;

    // 馬番格納
    for($i=0;$i<UMABAN_MAX;$i++){
        $umaban_array[$j][$i]=$_POST["j".($j+1)."_umaban_".($i+1)];
    }

    // 枠番格納
    for($i=0;$i<WAKUBAN_MAX;$i++){
        $wakuban_array[$j][$i]=$_POST["j".($j+1)."_wakuban_".($i+1)];
    }

    // 性齢牝馬格納
    for($i=0;$i<(SEIREI_MAX-SEIREI_MIN+1);$i++){
        $seirei_h_array[$j][$i]=$_POST["j".($j+1)."_seirei_h".($i+SEIREI_MIN)];
    }
    // 性齢牡馬格納
    for($i=0;$i<(SEIREI_MAX-SEIREI_MIN+1);$i++){
        $seirei_b_array[$j][$i]=$_POST["j".($j+1)."_seirei_b".($i+SEIREI_MIN)];
    }

    // 斤量格納
    for($i=0,$k=KINRYO_MIN;$k<=KINRYO_MAX;$i++,$k=$k+0.5){
        $kinryo_array[$j][$i]=$_POST["j".($j+1)."_kinryo_".($k*10)]; // 斤量のid,namaは52.5→525と表す
    }

    // タイム最小値格納
    $time_min_array[$j]=$_POST["j".($j+1)."_time_min"];
    // タイム最大値格納
    $time_max_array[$j]=$_POST["j".($j+1)."_time_max"];

    // 通過(第1コーナー)格納
    $tuka_1_array[$j]=$_POST["j".($j+1)."_tuka_1"];
    // 通過(第2コーナー)格納
    $tuka_2_array[$j]=$_POST["j".($j+1)."_tuka_2"];
    // 通過(第3コーナー)格納
    $tuka_3_array[$j]=$_POST["j".($j+1)."_tuka_3"];
    // 通過(第4コーナー)格納
    $tuka_4_array[$j]=$_POST["j".($j+1)."_tuka_4"];

    // 上り最小値格納
    $agari_min_array[$j]=$_POST["j".($j+1)."_agari_min"];
    // 上り最大値格納
    $agari_max_array[$j]=$_POST["j".($j+1)."_agari_max"];

    // 単勝最小値格納
    $tansho_min_array[$j]=$_POST["j".($j+1)."_tansho_min"];
    // 単勝最大値格納
    $tansho_max_array[$j]=$_POST["j".($j+1)."_tansho_max"];

    // 人気格納
    for($i=0;$i<NINKI_MAX;$i++){
        $ninki_array[$j][$i]=$_POST["j".($j+1)."_ninki_".($i+1)];
    }
}

// 結果フラグ 0:結果無し 1:結果有り
$j_result_flg=array();

// 条件の結果をj_resultsに格納
for ($j=0;$j<J_MAX;$j++){

    // レース名が「選択無し」以外かつ削除ボタンが押されていない条件のみ実行
    if($race_name_array_eng[$j] != "" && $delete_check_btn_array[$j] != 1){
        // 馬番
        $umaban_regexp[$j]=".*";
        for($i=0;$i<UMABAN_MAX;$i++){
            if($umaban_array[$j][$i]){
                if($umaban_regexp[$j]==".*"){
                    $umaban_regexp[$j]=(string)$umaban_array[$j][$i];
                }else{
                    $umaban_regexp[$j]="$umaban_regexp[$j]"."|".(string)$umaban_array[$j][$i];
                }
            }
        }

        // 枠番
        $wakuban_regexp[$j]=".*";
        for($i=0;$i<WAKUBAN_MAX;$i++){
            if($wakuban_array[$j][$i]){
                if($wakuban_regexp[$j]==".*"){
                    $wakuban_regexp[$j]=(string)$wakuban_array[$j][$i];
                }else{
                    $wakuban_regexp[$j]="$wakuban_regexp[$j]"."|".(string)$wakuban_array[$j][$i];
                }
            }
        }

        // 性齢
        $seirei_regexp[$j]=".*";
        for($i=0;$i<(SEIREI_MAX-SEIREI_MIN-1);$i++){
            if($seirei_h_array[$j][$i]){
                if($seirei_regexp[$j]==".*"){
                    $seirei_regexp[$j]=(string)$seirei_h_array[$j][$i];
                }else{
                    $seirei_regexp[$j]="$seirei_regexp[$j]"."|".(string)$seirei_h_array[$j][$i];
                }
            }
            if($seirei_b_array[$j][$i]){
                if($seirei_regexp[$j]==".*"){
                    $seirei_regexp[$j]=(string)$seirei_b_array[$j][$i];
                }else{
                    $seirei_regexp[$j]="$seirei_regexp[$j]"."|".(string)$seirei_b_array[$j][$i];
                }
            }
        }

        // 斤量
        $kinryo_regexp[$j]=".*";
        for($i=0,$k=KINRYO_MIN;$k<=KINRYO_MAX;$i++,$k=$k+0.5){
            if($kinryo_array[$j][$i]){
                if($kinryo_regexp[$j]==".*"){
                    $kinryo_regexp[$j]=(string)$kinryo_array[$j][$i];
                }else{
                    $kinryo_regexp[$j]="$kinryo_regexp[$j]"."|".(string)$kinryo_array[$j][$i];
                }
            }
        }

        // タイム最小値
        $time_min_regexp="0:00";
        if($time_min_array[$j]){
            $time_min_regexp=$time_min_array[$j];
        }
        // タイム最大値
        $time_max_regexp="9:59";
        if($time_max_array[$j]){
            $time_max_regexp=$time_max_array[$j];
        }

        // 通過(第１コーナー)
        $tuka_1_regexp="[0-9]{1,2}";
        if($tuka_1_array[$j]){
            $tuka_1_regexp=$tuka_1_array[$j];
        }
        // 通過(第2コーナー)
        $tuka_2_regexp="[0-9]{1,2}";
        if($tuka_2_array[$j]){
            $tuka_2_regexp=$tuka_2_array[$j];
        }
        // 通過(第3コーナー)
        $tuka_3_regexp="[0-9]{1,2}";
        if($tuka_3_array[$j]){
            $tuka_3_regexp=$tuka_3_array[$j];
        }
        // 通過(第4コーナー)
        $tuka_4_regexp="[0-9]{1,2}";
        if($tuka_4_array[$j]){
            $tuka_4_regexp=$tuka_4_array[$j];
        }

        // 通過は第2コーナーまでの表記しかないデータもあるので、OR条件で3表記作っておく
        $tuka_regexp_a=$tuka_1_regexp."-".$tuka_2_regexp;
        $tuka_regexp_b=$tuka_1_regexp."-".$tuka_2_regexp."-".$tuka_3_regexp;
        $tuka_regexp_c=$tuka_1_regexp."-".$tuka_2_regexp."-".$tuka_3_regexp."-".$tuka_4_regexp;
        $tuka_regexp=$tuka_regexp_a."|".$tuka_regexp_b."|".$tuka_regexp_c;

        // 上り最小値
        $agari_min_regexp="0";
        if($agari_min_array[$j]){
            $agari_min_regexp=$agari_min_array[$j];
        }
        // 上り最大値
        $agari_max_regexp="99";
        if($agari_max_array[$j]){
            $agari_max_regexp=$agari_max_array[$j];
        }

        // 単勝最小値
        $tansho_min_regexp="0";
        if($tansho_min_array[$j]){
            $tansho_min_regexp=$tansho_min_array[$j];
        }
        // 単勝最大値
        $tansho_max_regexp="1000";
        if($tansho_max_array[$j]){
            $tansho_max_regexp=$tansho_max_array[$j];
        }

        // 人気
        $ninki_regexp[$j]=".*";
        for($i=0;$i<NINKI_MAX;$i++){
            if($ninki_array[$j][$i]){
                if($ninki_regexp[$j]==".*"){
                    $ninki_regexp[$j]=(string)$ninki_array[$j][$i];
                }else{
                    $ninki_regexp[$j]="$ninki_regexp[$j]"."|".(string)$ninki_array[$j][$i];
                }
            }
        }

        // 年度
        $year_regexp=".*";
        foreach ($year_array as $year){
            if($year_regexp==".*"){
                $year_regexp=(string)$year;
            }else{
                $year_regexp="$year_regexp"."|".(string)$year;
            }
        }
        
        // 文字列整形
        $umaban_regexp[$j]="^(".$umaban_regexp[$j].")$";
        $wakuban_regexp[$j]="^(".$wakuban_regexp[$j].")$";
        $seirei_regexp[$j]="^(".$seirei_regexp[$j].")$";
        $kinryo_regexp[$j]="^(".$kinryo_regexp[$j].")$";
        $ninki_regexp[$j]="^(".$ninki_regexp[$j].")$";
        $tuka_regexp="^(".$tuka_regexp.")$";
        $year_regexp=="^(".$year_regexp.")$";

        // 条件j:SQL実行
        $j_results[$j]=$keiba_wpdb->get_results("SELECT * FROM " . $race_name_array_eng[$j] . " WHERE 馬番 REGEXP \"$umaban_regexp[$j]\" AND 枠番 REGEXP \"$wakuban_regexp[$j]\" AND 年度 REGEXP \"$year_regexp\" AND 性齢 REGEXP \"$seirei_regexp[$j]\" AND 斤量 REGEXP \"$kinryo_regexp[$j]\" AND タイム BETWEEN \"$time_min_regexp\" AND \"$time_max_regexp\" AND 通過 REGEXP \"$tuka_regexp\" AND 上り BETWEEN \"$agari_min_regexp\" AND \"$agari_max_regexp\" AND 単勝 BETWEEN \"$tansho_min_regexp\" AND \"$tansho_max_regexp\" AND 人気 REGEXP \"$ninki_regexp[$j]\"");
        if(count($j_results[$j])!=0){
            $j_result_flg[$j]=1;
        }
    }
}

// 結果が0件(j_result_flgに1が無い)の場合は警告を出して結果格納処理はスキップする
if(!(in_array(1,$j_result_flg))){
    echo "条件が選択されていないかヒットする検索結果がありませんでした。";
}else{
    // 年度ごとの結果をyear_resultsに格納
    $tmp_results=array(); // 多次元配列→[year][0]=年度 [year][1]=馬名1 [year][2]=馬名2
    $win_rates=array(); // 年度ごとの勝率→[year][0]=勝率 [year][1]=連体率 [year][2]=複勝率
    $year_results=array();
    $year_result_flg=array();
    $i=0;
    foreach ($year_array as $year){
        $tmp_results[$i][0]=$year;
        foreach ($j_results as $result){
            if(count($result)==0){
                continue;
            }
            foreach ($result as $row){
                if($row->年度 == $year){
                    if(!(in_array($row->馬名,$tmp_results[$i]))){
                        array_push($tmp_results[$i],$row->馬名);
                    }
                }
            }
        }

        // 結果が0件(tmp_resultに年度情報しか入っていない)の場合、処理をスキップする
        if(count($tmp_results[$i])==1){
            $i++;
            continue;
        }
        
        // SQL実行用の馬名を正規表現で格納
        $uma_name_regexp=".*";
        for ($k=1;$k<count($tmp_results[$i]);$k++){
            if($uma_name_regexp==".*"){
                $uma_name_regexp=(string)$tmp_results[$i][$k];
            }else{
                $uma_name_regexp="$uma_name_regexp"."|".(string)$tmp_results[$i][$k];
            }
        }

        $uma_name_regexp="^(".$uma_name_regexp.")$";

        // SQL実行
        $year_results[$i]=$keiba_wpdb->get_results("SELECT * FROM $target_race_name WHERE 年度 = ". $year . " AND 馬名 REGEXP \"$uma_name_regexp\"");
        if(count($year_results[$i])!=0){
            $year_result_flg[$i]=1;
        }

        // 年度ごとの勝率計算
        $first=0;
        $second=0;
        $third=0;
        $all=count($year_results[$i]);
        foreach ($year_results[$i] as $row){
            if($row->着順==1){
                $first=1;
            }else if($row->着順==2){
                $second=1;
            }else if($row->着順==3){
                $third=1;
            }
        }
        $win_rates[$i][0]=($first/$all*100); // 勝率
        $win_rates[$i][1]=(($first+$second)/$all*100); // 連体率
        $win_rates[$i][2]=(($first+$second+$third)/$all*100); // 複勝率

        $i++;
    }

    // print_r($win_rates);
    // echo "<br/>";

    $win_rates_sum=array(); // 勝率合計
    $i=0;
    foreach ($win_rates as $win_rate){
        // 配列の値がNANの場合は処理をスキップする
        if(is_nan($win_rate[0]) || is_nan($win_rate[1]) || is_nan($win_rate[2])){
            continue;
        }

        $win_rates_sum[0]=$win_rates_sum[0]+$win_rate[0];
        $win_rates_sum[1]=$win_rates_sum[1]+$win_rate[1];
        $win_rates_sum[2]=$win_rates_sum[2]+$win_rate[2];
        $i++;
        // print_r($win_rates_sum);
        // echo "<br/>";
    }
    $win_rates_sum[0]=($win_rates_sum[0]/$i);
    $win_rates_sum[1]=($win_rates_sum[1]/$i);
    $win_rates_sum[2]=($win_rates_sum[2]/$i);

    //print_r($win_rates[1][0]);
    // echo "$diff";
    // echo "<br/>";
    // $tmp=0;
    // $tmp=$tmp+$win_rates[1][1];
    // echo $tmp;
    // print_r($win_rates);

    // 結果が0件(year_result_flgに1が無い)の場合は警告を出して結果格納処理はスキップする
    if(!(in_array(1,$year_result_flg))){
        echo "条件に合致する検索結果がありませんでした。";
    }
}
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
    // 削除チェックボタン
    function deletebtnsave(){
        <?php $json_array = json_encode($delete_check_btn_array); ?>
        let js_array = <?php echo $json_array; ?>;
        for(let j=1;j<=<?php echo J_MAX; ?>;j++){
            if(js_array[j-1]){
                document.getElementById("j"+j+"_delete_checkbox").checked="true";
            }
        }
    }
    // レース名
    function racesave(){
        <?php $json_array = json_encode($race_name_array_jp); ?>
        let js_array = <?php echo $json_array; ?>;
        for(let j=1;j<=<?php echo J_MAX; ?>;j++){
            if(js_array[j-1]){
                document.getElementById("j"+j+"_race_name").value=js_array[j-1];
            }
        }
    }
    // 馬番
    function umabansave(){
        <?php $json_array = json_encode($umaban_array); ?>
        let js_array = <?php echo $json_array; ?>;
        for(let j=1;j<=<?php echo J_MAX; ?>;j++){
            for(let i=1;i<=<?php echo UMABAN_MAX; ?>;i++){
                if(js_array[j-1][i-1]){
                    document.getElementById("j"+j+"_umaban_"+i).checked="true";
                }
            }
        }
    }
    // 枠番
    function wakubansave(){
        <?php $json_array = json_encode($wakuban_array); ?>
        let js_array = <?php echo $json_array; ?>;
        for(let j=1;j<=<?php echo J_MAX; ?>;j++){
            for(let i=1;i<=<?php echo WAKUBAN_MAX; ?>;i++){
                if(js_array[j-1][i-1]){
                    document.getElementById("j"+j+"_wakuban_"+i).checked="true";
                }
            }
        }
    }
    // 性齢(牝馬)
    function seireihsave(){
        <?php $json_array = json_encode($seirei_h_array); ?>
        let js_array = <?php echo $json_array; ?>;
        for(let j=1;j<=<?php echo J_MAX; ?>;j++){
            for(let i=0;i< <?php echo (SEIREI_MAX-SEIREI_MIN+1); ?>;i++){
                if(js_array[j-1][i]){
                    document.getElementById("j"+j+"_seirei_h"+(i+<?php echo SEIREI_MIN; ?>)).checked="true";
                }
            }
        }
    }
    // 性齢(牡馬)
    function seireibsave(){
        <?php $json_array = json_encode($seirei_b_array); ?>
        let js_array = <?php echo $json_array; ?>;
        for(let j=1;j<=<?php echo J_MAX; ?>;j++){
            for(let i=0;i< <?php echo (SEIREI_MAX-SEIREI_MIN+1); ?>;i++){
                if(js_array[j-1][i]){
                    document.getElementById("j"+j+"_seirei_b"+(i+<?php echo SEIREI_MIN; ?>)).checked="true";
                }
            }
        }
    }
    // 斤量
    function kinryosave(){
        <?php $json_array = json_encode($kinryo_array); ?>
        let js_array = <?php echo $json_array; ?>;
        for(let j=1;j<=<?php echo J_MAX; ?>;j++){
            for(let i=0,k=<?php echo KINRYO_MIN; ?>;k<=<?php echo KINRYO_MAX; ?>;i++,k=(k+0.5)){
                if(js_array[j-1][i]){
                    document.getElementById("j"+j+"_kinryo_"+(k*10)).checked="true";
                }
            }
        }
    }
    // タイム
    function timesave(){
        <?php $json_array_a = json_encode($time_min_array); ?>
        <?php $json_array_b = json_encode($time_max_array); ?>
        let js_array_a = <?php echo $json_array_a; ?>;
        let js_array_b = <?php echo $json_array_b; ?>;
        for(let j=1;j<=<?php echo J_MAX; ?>;j++){
            if(js_array_a[j-1]){
                document.getElementById("j"+j+"_time_min").value=js_array_a[j-1];
            }
            if(js_array_b[j-1]){
                document.getElementById("j"+j+"_time_max").value=js_array_b[j-1];
            }
        }
    }
    // 通過
    function tukasave(){
        <?php $json_array_a = json_encode($tuka_1_array); ?>
        <?php $json_array_b = json_encode($tuka_2_array); ?>
        <?php $json_array_c = json_encode($tuka_3_array); ?>
        <?php $json_array_d = json_encode($tuka_4_array); ?>
        let js_array_a = <?php echo $json_array_a; ?>;
        let js_array_b = <?php echo $json_array_b; ?>;
        let js_array_c = <?php echo $json_array_c; ?>;
        let js_array_d = <?php echo $json_array_d; ?>;
        for(let j=1;j<=<?php echo J_MAX; ?>;j++){
            if(js_array_a[j-1]){
                document.getElementById("j"+j+"_tuka_1").value=js_array_a[j-1];
            }
            if(js_array_b[j-1]){
                document.getElementById("j"+j+"_tuka_2").value=js_array_b[j-1];
            }
            if(js_array_c[j-1]){
                document.getElementById("j"+j+"_tuka_3").value=js_array_c[j-1];
            }
            if(js_array_d[j-1]){
                document.getElementById("j"+j+"_tuka_4").value=js_array_d[j-1];
            }
        }
    }
    // 上り
    function agarisave(){
        <?php $json_array_a = json_encode($agari_min_array); ?>
        <?php $json_array_b = json_encode($agari_max_array); ?>
        let js_array_a = <?php echo $json_array_a; ?>;
        let js_array_b = <?php echo $json_array_b; ?>;
        for(let j=1;j<=<?php echo J_MAX; ?>;j++){
            if(js_array_a[j-1]){
                document.getElementById("j"+j+"_agari_min").value=js_array_a[j-1];
            }
            if(js_array_b[j-1]){
                document.getElementById("j"+j+"_agari_max").value=js_array_b[j-1];
            }
        }
    }
    // 単勝
    function tanshosave(){
        <?php $json_array_a = json_encode($tansho_min_array); ?>
        <?php $json_array_b = json_encode($tansho_max_array); ?>
        let js_array_a = <?php echo $json_array_a; ?>;
        let js_array_b = <?php echo $json_array_b; ?>;
        for(let j=1;j<=<?php echo J_MAX; ?>;j++){
            if(js_array_a[j-1]){
                document.getElementById("j"+j+"_tansho_min").value=js_array_a[j-1];
            }
            if(js_array_b[j-1]){
                document.getElementById("j"+j+"_tansho_max").value=js_array_b[j-1];
            }
        }
    }
    // 人気
    function ninkisave(){
        <?php $json_array = json_encode($ninki_array); ?>
        let js_array = <?php echo $json_array; ?>;
        for(let j=1;j<=<?php echo J_MAX; ?>;j++){
            for(let i=1;i<=<?php echo NINKI_MAX; ?>;i++){
                if(js_array[j-1][i-1]){
                    document.getElementById("j"+j+"_ninki_"+i).checked="true";
                }
            }
        }
    }
</script>

<!-- 値保持についてJavaScriptでは最初の更新しか適用されないため、下記PHPを実行する -->
<?php echo '<script type="text/javascript">','targetracesave();','</script>'; ?>
<?php echo '<script type="text/javascript">','targetyearsave();','</script>'; ?>
<?php echo '<script type="text/javascript">','deletebtnsave();','</script>'; ?>
<?php echo '<script type="text/javascript">','racesave();','</script>'; ?>
<?php echo '<script type="text/javascript">','umabansave();','</script>'; ?>
<?php echo '<script type="text/javascript">','wakubansave();','</script>'; ?>
<?php echo '<script type="text/javascript">','seireihsave();','</script>'; ?>
<?php echo '<script type="text/javascript">','seireibsave();','</script>'; ?>
<?php echo '<script type="text/javascript">','kinryosave();','</script>'; ?>
<?php echo '<script type="text/javascript">','timesave();','</script>'; ?>
<?php echo '<script type="text/javascript">','tukasave();','</script>'; ?>
<?php echo '<script type="text/javascript">','agarisave();','</script>'; ?>
<?php echo '<script type="text/javascript">','tanshosave();','</script>'; ?>
<?php echo '<script type="text/javascript">','ninkisave();','</script>'; ?>

<?php
// 検索結果が0件(j_result_flgに1が無い)の場合、処理を終了する
if(!(in_array(1,$j_result_flg))){
    return 1;
}
// 合致結果が0件(year_result_flgに1が無い)の場合、処理を終了する
if(!(in_array(1,$year_result_flg))){
    return 1;
}
?>

<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Hello!</title>
    <style>
        .table4 {
            border-collapse: collapse;
        }
        .table4 th {
            border: 1px solid gray;
            text-align: center;
            width: max-content;
            color: gray;
            background: gainsboro;
        }
        .table4 td {
            border: 1px solid gray;
            text-align:left;
        }
        .scroll{
            overflow: visible; /*tableをスクロールさせる*/
            white-space: nowrap; /*tableのセル内にある文字の折り返しを禁止*/
        }
        .scroll::-webkit-scrollbar{ /*tableにスクロールバーを追加*/
            height: 5px;
        }
        .scroll::-webkit-scrollbar-track{/*tableにスクロールバーを追加*/
            background: #F1F1F1;
        }
        .scroll::-webkit-scrollbar-thumb { /*tableにスクロールバーを追加*/
            background: #BCBCBC;
        }
    </style>
</head>
<body>
    <p>◆全体</p>
    <p><?php echo "単勝率:".round($win_rates_sum[0],1)."%"." 連体率:".round($win_rates_sum[1],1)."%"." 複勝率:".round($win_rates_sum[2],1)."%"; ?></p>
    <?php for($i=0,$year_tmp=$_POST['year_e'] ;$i<=$diff;$i++,$year_tmp--) : ?>
        <p><?php echo "◆".$year_tmp; ?></p>
        <p><?php echo "単勝率:".round($win_rates[$diff-$i][0],1)."%"." 連体率:".round($win_rates[$diff-$i][1],1)."%"." 複勝率:".round($win_rates[$diff-$i][2],1)."%"; ?></p>
        <div class="scroll">
            <table class="table4" border="1">
                <tr><th>年度</th><th>馬名</th><th>着順</th><th>人気</th><th>馬番</th><th>枠番</th><th>性齢</th><th>斤量</th><th>タイム</th><th>通過</th><th>上り</th><th>単勝</th><th>馬体重</th></tr>
                    <?php foreach ($year_results[$diff-$i] as $row) : ?>
                        <tr><td bgcolor="white"><?php echo $row->年度 ?></td><td bgcolor="white"><?php echo $row->馬名 ?></td><td bgcolor="white"><?php echo $row->着順 ?></td><td bgcolor="white"><?php echo $row->人気 ?></td><td bgcolor="white"><?php echo $row->馬番 ?></td><td bgcolor="white"><?php echo $row->枠番 ?></td><td><?php echo $row->性齢 ?></td><td bgcolor="white"><?php echo $row->斤量 ?></td><td bgcolor="white"><?php echo substr_replace(substr($row->タイム, 1, 7),".",4,1) ?></td><td bgcolor="#ffffff"><?php echo $row->通過 ?></td><td bgcolor="white"><?php echo $row->上り ?></td><td bgcolor="white"><?php echo $row->単勝 ?></td><td bgcolor="white"><?php echo $row->馬体重 ?></td></tr>
                    <?php endforeach; ?>
            </table>
        </div>
    <?php endfor; ?>
</body>
</html>