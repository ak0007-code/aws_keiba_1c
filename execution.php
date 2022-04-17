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
const AGARIJUN_MAX=25; // 上り順位MAX
const CHAKUJUN_MAX=25; // 着順MAX
const TUKA_MAX=25; //通過MAX
const TUKA_MIN=1; // 通過MIN

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
$target_race_name_jp=$_POST['race_name'];
$target_race_name_eng=$keiba_wpdb->get_row("SELECT ENG_NAME FROM RACENAME_JP_ENG_TRANS WHERE JP_NAME = \"$_POST[race_name] \"")->ENG_NAME;

// データ格納用配列
$delete_check_btn_array=array(); // 削除ボタンの押下状況
$and_or_select_array=array(); // ANN・ORのセレクト状況
$pre_year_select_array=array(); // 前年検索のセレクト状況
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
$tuka_1_symbol_array=array(); // 通過記号(第1コーナー)
$tuka_2_symbol_array=array(); // 通過記号(第2コーナー)
$tuka_3_symbol_array=array(); // 通過記号(第3コーナー)
$tuka_4_symbol_array=array(); // 通過記号(第4コーナー)
$agari_min_array=array(); // 上り最小値
$agari_max_array=array(); // 上り最大値
$tansho_min_array=array(); // 単勝最小値
$tansho_max_array=array(); // 単勝最大値
$ninki_array=array(); // 人気
$agarijun_array=array(); // 上り順位
$chakujun_array=array(); // 着順

// DBに格納しているレース名を日付順に格納
$all_race=$keiba_wpdb->get_results("SELECT * FROM RACE_DATE_SORT");

// 条件を確認 j:条件 i:個別番号
for ($j=0;$j<J_MAX;$j++){
    // ANN・ORのセレクト状況
    $and_or_select_array[$j]=$_POST["j".($j+1)."_and_or_select"];

    // 削除ボタン
    $delete_check_btn_array[$j]=$_POST["j".($j+1)."_delete_checkbox"];

    // レース名
    $race_name_array_jp[$j]=$_POST["j".($j+1)."_race_name"];
    // 英語表記
    $race_name_array_eng[$j]=$keiba_wpdb->get_row("SELECT ENG_NAME FROM RACENAME_JP_ENG_TRANS WHERE JP_NAME = \"$race_name_array_jp[$j]\"")->ENG_NAME;

    // 前年検索のセレクト状況(レースの日付を見てPRE=去年かCURRENT=本年を調整する)
    $jouken_num=array_search($race_name_array_jp[$j],array_column($all_race,'RACE_NAME'));
    $target_num=array_search($target_race_name_jp,array_column($all_race,'RACE_NAME'));
    if($jouken_num > $target_num){
        $pre_year_select_array[$j]="PRE";
    }else{
        $pre_year_select_array[$j]="CURRENT";
    }

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

    // 通過(第1コーナー)格納
    $tuka_1_symbol_array[$j]=$_POST["j".($j+1)."_tuka_1_symbol"];
    // 通過(第2コーナー)格納
    $tuka_2_symbol_array[$j]=$_POST["j".($j+1)."_tuka_2_symbol"];
    // 通過(第3コーナー)格納
    $tuka_3_symbol_array[$j]=$_POST["j".($j+1)."_tuka_3_symbol"];
    // 通過(第4コーナー)格納
    $tuka_4_symbol_array[$j]=$_POST["j".($j+1)."_tuka_4_symbol"];

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

    // 上り順位格納
    for($i=0;$i<AGARIJUN_MAX;$i++){
        $agarijun_array[$j][$i]=$_POST["j".($j+1)."_agarijun_".($i+1)];
    }

    // 着順格納
    for($i=0;$i<CHAKUJUN_MAX;$i++){
        $chakujun_array[$j][$i]=$_POST["j".($j+1)."_chakujun_".($i+1)];
    }
}

// 結果フラグ 0:結果無し 1:結果有り
$j_result_flg=array();

// 条件の結果をj_resultsに格納
for ($j=0;$j<J_MAX;$j++){

    // レース名が「選択無し」以外かつ削除ボタンが押されていない条件のみ実行
    if($race_name_array_eng[$j] != "" && $delete_check_btn_array[$j] != 1){
        // いずれかの選択がされているか確認(0=全て選択無し、1=選択有り)
        $all_result_check_flg=0;

        // AND・ORのセレクト状況
        if($and_or_select_array[$j]=='AND'){
            $umaban_regexp[$j]=".*";
            $wakuban_regexp[$j]=".*";
            $seirei_regexp[$j]=".*";
            $kinryo_regexp[$j]=".*";
            $time_min_regexp="0:00";
            $time_max_regexp="9:59";
            $tuka_1_regexp="[0-9]{1,2}";
            $tuka_2_regexp="[0-9]{1,2}";
            $tuka_3_regexp="[0-9]{1,2}";
            $tuka_4_regexp="[0-9]{1,2}";
            $agari_min_regexp="0";
            $agari_max_regexp="99";
            $tansho_min_regexp="0";
            $tansho_max_regexp="10000";
            $ninki_regexp[$j]=".*";
            $agarijun_regexp[$j]=".*";
            $chakujun_regexp[$j]=".*";
        }else{
            $umaban_regexp[$j]="99";
            $wakuban_regexp[$j]="99";
            $seirei_regexp[$j]="99";
            $kinryo_regexp[$j]="99";
            $time_min_regexp="0:00";
            $time_max_regexp="9:99";
            $tuka_1_regexp="[0-9]{1,2}";
            $tuka_2_regexp="[0-9]{1,2}";
            $tuka_3_regexp="[0-9]{1,2}";
            $tuka_4_regexp="[0-9]{1,2}";
            $agari_min_regexp="0";
            $agari_max_regexp="99";
            $tansho_min_regexp="0";
            $tansho_max_regexp="10000";
            $ninki_regexp[$j]="99";
            $agarijun_regexp[$j]="99";
            $chakujun_regexp[$j]="99";
        }

        // 馬番
        for($i=0;$i<UMABAN_MAX;$i++){
            if($umaban_array[$j][$i]){
                if($umaban_regexp[$j]=="99" || $umaban_regexp[$j]==".*"){
                    $umaban_regexp[$j]=(string)$umaban_array[$j][$i];
                }else{
                    $umaban_regexp[$j]="$umaban_regexp[$j]"."|".(string)$umaban_array[$j][$i];
                }
                $all_result_check_flg=1;
            }
        }

        // 枠番
        for($i=0;$i<WAKUBAN_MAX;$i++){
            if($wakuban_array[$j][$i]){
                if($wakuban_regexp[$j]=="99" || $wakuban_regexp[$j]==".*"){
                    $wakuban_regexp[$j]=(string)$wakuban_array[$j][$i];
                }else{
                    $wakuban_regexp[$j]="$wakuban_regexp[$j]"."|".(string)$wakuban_array[$j][$i];
                }
                $all_result_check_flg=1;
            }
        }

        // 性齢
        for($i=0;$i<(SEIREI_MAX-SEIREI_MIN-1);$i++){
            if($seirei_h_array[$j][$i]){
                if($seirei_regexp[$j]=="99" || $seirei_regexp[$j]==".*"){
                    $seirei_regexp[$j]=(string)$seirei_h_array[$j][$i];
                }else{
                    $seirei_regexp[$j]="$seirei_regexp[$j]"."|".(string)$seirei_h_array[$j][$i];
                }
                $all_result_check_flg=1;
            }
            if($seirei_b_array[$j][$i]){
                if($seirei_regexp[$j]=="99" || $seirei_regexp[$j]==".*"){
                    $seirei_regexp[$j]=(string)$seirei_b_array[$j][$i];
                }else{
                    $seirei_regexp[$j]="$seirei_regexp[$j]"."|".(string)$seirei_b_array[$j][$i];
                }
                $all_result_check_flg=1;
            }
        }

        // 斤量
        for($i=0,$k=KINRYO_MIN;$k<=KINRYO_MAX;$i++,$k=$k+0.5){
            if($kinryo_array[$j][$i]){
                if($kinryo_regexp[$j]=="99" || $kinryo_regexp[$j]==".*"){
                    $kinryo_regexp[$j]=(string)$kinryo_array[$j][$i];
                }else{
                    $kinryo_regexp[$j]="$kinryo_regexp[$j]"."|".(string)$kinryo_array[$j][$i];
                }
                $all_result_check_flg=1;
            }
        }

        // タイムフラグ:最小値か最大値に選択があれば1を入れる
        $time_check_flg=0;
        // タイム最小値
        if($time_min_array[$j]){
            $time_min_regexp=$time_min_array[$j];
            $all_result_check_flg=1;
            $time_check_flg=1;
        }
        // タイム最大値
        if($time_max_array[$j]){
            $time_max_regexp=$time_max_array[$j];
            $all_result_check_flg=1;
            $time_check_flg=1;
        }
        if($time_check_flg == 0){
            if($and_or_select_array[$j]=='OR'){
                $time_min_regexp="9:99";
                $time_max_regexp="9:99";
            }
        }

        // 通過フラグ：1~4コーナーまでひとつでも選択があれば1を入れる
        $tuka_check_flg=0;
        // 通過(第１コーナー)
        if($tuka_1_array[$j]){
            if($tuka_1_symbol_array[$j]=="eq"){
                $tuka_1_regexp=$tuka_1_array[$j];
            }else if($tuka_1_symbol_array[$j]=="ge"){
                for($i=$tuka_1_array[$j];$i<=TUKA_MAX;$i++){
                    if($i==$tuka_1_array[$j]){
                        $tuka_1_regexp=$tuka_1_array[$j];
                    }else{
                        $tuka_1_regexp="$tuka_1_regexp"."|"."$i";
                    }
                }
            }else if($tuka_1_symbol_array[$j]=="le"){
                for($i=$tuka_1_array[$j];$i>=TUKA_MIN;$i--){
                    if($i==$tuka_1_array[$j]){
                        $tuka_1_regexp=$tuka_1_array[$j];
                    }else{
                        $tuka_1_regexp="$tuka_1_regexp"."|"."$i";
                    }
                }
            }
            $tuka_1_regexp="(".$tuka_1_regexp.")";
            $all_result_check_flg=1;
            $tuka_check_flg=1;
        }
        // 通過(第2コーナー)
        if($tuka_2_array[$j]){
            if($tuka_2_symbol_array[$j]=="eq"){
                $tuka_2_regexp=$tuka_2_array[$j];
            }else if($tuka_2_symbol_array[$j]=="ge"){
                for($i=$tuka_2_array[$j];$i<=TUKA_MAX;$i++){
                    if($i==$tuka_2_array[$j]){
                        $tuka_2_regexp=$tuka_2_array[$j];
                    }else{
                        $tuka_2_regexp="$tuka_2_regexp"."|"."$i";
                    }
                }
            }else if($tuka_2_symbol_array[$j]=="le"){
                for($i=$tuka_2_array[$j];$i>=TUKA_MIN;$i--){
                    if($i==$tuka_2_array[$j]){
                        $tuka_2_regexp=$tuka_2_array[$j];
                    }else{
                        $tuka_2_regexp="$tuka_2_regexp"."|"."$i";
                    }
                }
            }
            $tuka_2_regexp="(".$tuka_2_regexp.")";
            $all_result_check_flg=1;
            $tuka_check_flg=1;
        }
        // 通過(第3コーナー)
        if($tuka_3_array[$j]){
            if($tuka_3_symbol_array[$j]=="eq"){
                $tuka_3_regexp=$tuka_3_array[$j];
            }else if($tuka_3_symbol_array[$j]=="ge"){
                for($i=$tuka_3_array[$j];$i<=TUKA_MAX;$i++){
                    if($i==$tuka_3_array[$j]){
                        $tuka_3_regexp=$tuka_3_array[$j];
                    }else{
                        $tuka_3_regexp="$tuka_3_regexp"."|"."$i";
                    }
                }
            }else if($tuka_3_symbol_array[$j]=="le"){
                for($i=$tuka_3_array[$j];$i>=TUKA_MIN;$i--){
                    if($i==$tuka_3_array[$j]){
                        $tuka_3_regexp=$tuka_3_array[$j];
                    }else{
                        $tuka_3_regexp="$tuka_3_regexp"."|"."$i";
                    }
                }
            }
            $tuka_3_regexp="(".$tuka_3_regexp.")";
            $all_result_check_flg=1;
            $tuka_check_flg=1;
        }
        // 通過(第4コーナー)
        if($tuka_4_array[$j]){
            if($tuka_4_symbol_array[$j]=="eq"){
                $tuka_4_regexp=$tuka_4_array[$j];
            }else if($tuka_4_symbol_array[$j]=="ge"){
                for($i=$tuka_4_array[$j];$i<=TUKA_MAX;$i++){
                    if($i==$tuka_4_array[$j]){
                        $tuka_4_regexp=$tuka_4_array[$j];
                    }else{
                        $tuka_4_regexp="$tuka_4_regexp"."|"."$i";
                    }
                }
            }else if($tuka_4_symbol_array[$j]=="le"){
                for($i=$tuka_4_array[$j];$i>=TUKA_MIN;$i--){
                    if($i==$tuka_4_array[$j]){
                        $tuka_4_regexp=$tuka_4_array[$j];
                    }else{
                        $tuka_4_regexp="$tuka_4_regexp"."|"."$i";
                    }
                }
            }
            $tuka_4_regexp="(".$tuka_4_regexp.")";
            $all_result_check_flg=1;
            $tuka_check_flg=1;
        }
        if($tuka_check_flg == 1){
            // 通過は第2コーナーまでの表記しかないデータもあるので、OR条件で3表記作っておく
            $tuka_regexp_a=$tuka_1_regexp."-".$tuka_2_regexp;
            $tuka_regexp_b=$tuka_1_regexp."-".$tuka_2_regexp."-".$tuka_3_regexp;
            $tuka_regexp_c=$tuka_1_regexp."-".$tuka_2_regexp."-".$tuka_3_regexp."-".$tuka_4_regexp;
            $tuka_regexp=$tuka_regexp_a."|".$tuka_regexp_b."|".$tuka_regexp_c;
        }else{
            if($and_or_select_array[$j]=='OR'){
                $tuka_regexp="99";
            }else{
                $tuka_regexp=".*";
            }
        }

        // 上りフラグ:最小値か最大値に選択があれば1を入れる
        $agari_check_flg=0;
        // 上り最小値
        if($agari_min_array[$j]){
            $agari_min_regexp=$agari_min_array[$j];
            $all_result_check_flg=1;
            $agari_check_flg=1;
        }
        // 上り最大値
        if($agari_max_array[$j]){
            $agari_max_regexp=$agari_max_array[$j];
            $all_result_check_flg=1;
            $agari_check_flg=1;
        }
        if($agari_check_flg == 0){
            if($and_or_select_array[$j]=='OR'){
                $agari_min_regexp="99";
                $agari_max_regexp="99";
            }
        }

        // 単勝フラグ:最小値か最大値に選択があれば1を入れる
        $tansho_check_flg=0;
        // 単勝最小値
        if($tansho_min_array[$j]){
            $tansho_min_regexp=$tansho_min_array[$j];
            $all_result_check_flg=1;
            $tansho_check_flg=1;
        }
        // 単勝最大値
        if($tansho_max_array[$j]){
            $tansho_max_regexp=$tansho_max_array[$j];
            $all_result_check_flg=1;
            $tansho_check_flg=1;
        }
        if($tansho_check_flg == 0){
            if($and_or_select_array[$j]=='OR'){
                $tansho_min_regexp="99999";
                $tansho_max_regexp="99999";
            }
        }

        // 人気
        for($i=0;$i<NINKI_MAX;$i++){
            if($ninki_array[$j][$i]){
                if($ninki_regexp[$j]=="99" || $ninki_regexp[$j]==".*"){
                    $ninki_regexp[$j]=(string)$ninki_array[$j][$i];
                }else{
                    $ninki_regexp[$j]="$ninki_regexp[$j]"."|".(string)$ninki_array[$j][$i];
                }
                $all_result_check_flg=1;
            }
        }

        // 上り順位
        for($i=0;$i<AGARIJUN_MAX;$i++){
            if($agarijun_array[$j][$i]){
                if($agarijun_regexp[$j]=="99" || $agarijun_regexp[$j]==".*"){
                    $agarijun_regexp[$j]=(string)$agarijun_array[$j][$i];
                }else{
                    $agarijun_regexp[$j]="$agarijun_regexp[$j]"."|".(string)$agarijun_array[$j][$i];
                }
                $all_result_check_flg=1;
            }
        }

        // 着順
        for($i=0;$i<CHAKUJUN_MAX;$i++){
            if($chakujun_array[$j][$i]){
                if($chakujun_regexp[$j]=="99" || $chakujun_regexp[$j]==".*"){
                    $chakujun_regexp[$j]=(string)$chakujun_array[$j][$i];
                }else{
                    $chakujun_regexp[$j]="$chakujun_regexp[$j]"."|".(string)$chakujun_array[$j][$i];
                }
                $all_result_check_flg=1;
            }
        }

        // 年度
        $year_regexp=".*";
        foreach ($year_array as $year){
            if($pre_year_select_array[$j]=="PRE"){
                $year=$year-1;
            }
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
        $agarijun_regexp[$j]="^(".$agarijun_regexp[$j].")$";
        $chakujun_regexp[$j]="^(".$chakujun_regexp[$j].")$";
        $tuka_regexp="^(".$tuka_regexp.")$";
        $year_regexp="^(".$year_regexp.")$";

        // 条件j:SQL実行        
        // いずれかの選択がされている場合は条件通りに検索、されていない場合は全検索する
        if($all_result_check_flg == 1){
            if($and_or_select_array[$j]=='OR'){
                $j_results[$j]=$keiba_wpdb->get_results("SELECT * FROM " . $race_name_array_eng[$j] . " WHERE 年度 REGEXP \"$year_regexp\" AND (馬番 REGEXP \"$umaban_regexp[$j]\" OR 枠番 REGEXP \"$wakuban_regexp[$j]\" OR 性齢 REGEXP \"$seirei_regexp[$j]\" OR 斤量 REGEXP \"$kinryo_regexp[$j]\" OR (タイム BETWEEN \"$time_min_regexp\" AND \"$time_max_regexp\") OR 通過 REGEXP \"$tuka_regexp\" OR (上り BETWEEN \"$agari_min_regexp\" AND \"$agari_max_regexp\") OR (単勝 BETWEEN \"$tansho_min_regexp\" AND \"$tansho_max_regexp\") OR 人気 REGEXP \"$ninki_regexp[$j]\" OR 上り順位 REGEXP \"$agarijun_regexp[$j]\" OR 着順 REGEXP \"$chakujun_regexp[$j]\")");
            }else{
                $j_results[$j]=$keiba_wpdb->get_results("SELECT * FROM " . $race_name_array_eng[$j] . " WHERE 年度 REGEXP \"$year_regexp\" AND 馬番 REGEXP \"$umaban_regexp[$j]\" AND 枠番 REGEXP \"$wakuban_regexp[$j]\" AND 性齢 REGEXP \"$seirei_regexp[$j]\" AND 斤量 REGEXP \"$kinryo_regexp[$j]\" AND タイム BETWEEN \"$time_min_regexp\" AND \"$time_max_regexp\" AND 通過 REGEXP \"$tuka_regexp\" AND 上り BETWEEN \"$agari_min_regexp\" AND \"$agari_max_regexp\" AND 単勝 BETWEEN \"$tansho_min_regexp\" AND \"$tansho_max_regexp\" AND 人気 REGEXP \"$ninki_regexp[$j]\" AND 上り順位 REGEXP \"$agarijun_regexp[$j]\" AND 着順 REGEXP \"$chakujun_regexp[$j]\"");
            }
        }else{
            $j_results[$j]=$keiba_wpdb->get_results("SELECT * FROM " . $race_name_array_eng[$j] . " WHERE 年度 REGEXP \"$year_regexp\"");
        }
        if(count($j_results[$j])!=0){
            $j_result_flg[$j]=1;
        }
    }
}

// 結果が0件(j_result_flgに1が無い)の場合は警告を出して結果格納処理はスキップする
if(!(in_array(1,$j_result_flg))){
    echo "条件が選択されていないか合致する検索結果がありませんでした。";
}else{
    // 年度ごとの結果をyear_resultsに格納
    $tmp_results=array(); // 多次元配列→[year][0]=年度 [year][1]=馬名1 [year][2]=馬名2
    $tmp_array=array(); // 一時格納用
    $win_rates=array(); // 年度ごとの勝率→[year][0]=勝率 [year][1]=連体率 [year][2]=複勝率
    $year_results=array(); // 開催年ごとの結果
    $year_results_race_info=array(); // 開催年ごとのレース情報
    $year_result_flg=array(); // 開催年に値があったかどうか
    $year_results_others=array(); // 検索条件で選ばれた別レースの結果
    $year_results_others_jp=array(); // 別レースの一覧
    $year_results_others_race_info=array(); // 開催年ごとの別レース情報
    $j_num=count($j_results); // 条件数
    $i=0;

    // 開催年ごとに処理を実行していく
    foreach ($year_array as $year){

        $tmp_results[$i][0]=$year;
        $tmp_array[$i][0]=null; // 0番目にnullを入れないとarray_pushが使えないため

        // 全条件に対して開催年=$yearに合致する要素を取り出す
        for ($k=0;$k<array_key_last($j_results)+1;$k++){
            if(count($j_results[$k])==0){
                continue;
            }
            if($pre_year_select_array[$k]=="PRE"){
                $tg_year=$year-1;
            }else{
                $tg_year=$year;
            }
            foreach ($j_results[$k] as $row){
                if($row->年度 == $tg_year){
                    array_push($tmp_array[$i],$row->馬名);
                }
            }
        }

        // 全ての条件(出現回数が$j_num回)で出てきた馬名のみtmp_resultsに格納
        foreach (array_count_values($tmp_array[$i]) as $key => $value){
            if($value==$j_num && $key!=null){
                array_push($tmp_results[$i],$key);
            }
        }

        // 結果が0件(tmp_resultsに年度情報しか入っていない)の場合、処理をスキップする
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
        $year_results[$i]=$keiba_wpdb->get_results("SELECT * FROM $target_race_name_eng WHERE 年度 = ". $year . " AND 馬名 REGEXP \"$uma_name_regexp\"");
        if(count($year_results[$i])!=0){
            $year_result_flg[$i]=1;
        }

        // レース情報取得
        $year_results_race_info[$i]=$keiba_wpdb->get_results("SELECT * FROM RACE_ID WHERE RACE_NAME REGEXP \"$target_race_name_jp\" AND RACE_DATE REGEXP \"^$year\";");

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

        // 検索条件で指定されたレースの結果も格納
        $j=0;
        $k=0;
        foreach($race_name_array_eng as $race_name_eng){
            if($race_name_eng != $target_race_name_eng && $delete_check_btn_array[$j] != 1){
                if(!in_array($race_name_eng,$tmp_array[$i])){
                    array_push($tmp_array[$i],$race_name_eng);            
                    // レース名を日本語に変換して格納
                    $year_results_others_jp[$k]=$keiba_wpdb->get_row("SELECT JP_NAME FROM RACENAME_JP_ENG_TRANS WHERE ENG_NAME = \"$race_name_eng\"")->JP_NAME;

                    if($pre_year_select_array[$j]=="PRE"){
                        $tg_year=$year-1;
                    }else{
                        $tg_year=$year;
                    }
                    $year_results_others[$i][$k]=$keiba_wpdb->get_results("SELECT * FROM $race_name_eng WHERE 年度 = ". $tg_year . " AND 馬名 REGEXP \"$uma_name_regexp\"");
                    $year_results_others_race_info[$i][$k]=$keiba_wpdb->get_results("SELECT * FROM RACE_ID WHERE RACE_NAME REGEXP \"$year_results_others_jp[$k]\" AND RACE_DATE REGEXP \"^$tg_year\";");
                    $k++;
                }

            }
            $j++;
        }

        $i++;
    }

    // 全体勝率計算
    $all_num=0;
    $first_num=0;
    $second_num=0;
    $third_num=0;
    foreach ($year_results as $year_result){
        foreach($year_result as $row){
            if($row->着順==3 || $row->着順==2 || $row->着順==1){
                $third_num++;
            }
            if($row->着順==2 || $row->着順==1){
                $second_num++;
            }
            if($row->着順==1){
                $first_num++;
            }
            $all_num++;
        }
    }
    $win_rates_sum=array(); // 勝率合計
    $win_rates_sum[0]=($first_num/$all_num)*100;
    $win_rates_sum[1]=($second_num/$all_num)*100;
    $win_rates_sum[2]=($third_num/$all_num)*100;

    // 結果が0件(year_result_flgに1が無い)の場合は警告を出して結果格納処理はスキップする
    if(!(in_array(1,$year_result_flg))){
        echo "条件に合致する検索結果がありませんでした。";
    }
}

// 予想レースの結果に出力された馬の正規表現を作成する
$umamei_regexp=".*";
for($i=0;$i<count($year_results);$i++){
    for($j=0;$j<count($year_results[$i]);$j++){
        if($umamei_regexp==".*"){
            $umamei_regexp=$year_results[$i][$j]->馬名;
        }else{
            $umamei_regexp="$umamei_regexp"."|".$year_results[$i][$j]->馬名;
        }
    }
}
$umamei_regexp="^(".$umamei_regexp.")$";

// DBに格納しているレース名一覧を取り出す
$all_table=$keiba_wpdb->get_results("SELECT * FROM RACE_DATE_SORT");
// テーブルごとに馬を検索
$num=0;
$table_results_as_umamei=array();
$race_name=array();
for($i=0;$i<count($all_table);$i++){
    $table_name_jp=$all_table[$i]->RACE_NAME;
    $table_name_eng=$keiba_wpdb->get_row("SELECT ENG_NAME FROM RACENAME_JP_ENG_TRANS WHERE JP_NAME = \"$table_name_jp\"")->ENG_NAME;
    $tmp=$keiba_wpdb->get_results("SELECT * FROM $table_name_eng WHERE 馬名 REGEXP \"$umamei_regexp\"");
    if($tmp){
        $table_results_as_umamei[$num]=$tmp;
        $race_name[$num]=$table_name_jp;
        $num++;
    }
}

// 馬名ごとに他のレース結果を出せるようにする
$year_results_as_umamei=array();
for($i=0;$i<count($year_results);$i++){
    for($j=0;$j<count($year_results[$i]);$j++){
        $umamei=$year_results[$i][$j]->馬名;
        for($x=0;$x<count($table_results_as_umamei);$x++){
            for($y=0;$y<count($table_results_as_umamei[$x]);$y++){
                if($table_results_as_umamei[$x][$y]->馬名==$umamei){
                    $year_results_as_umamei[$umamei][$x]=$table_results_as_umamei[$x][$y];
                }
            }
        }
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
    // AND・ORセレクト
    function andorselectsave(){
        <?php $json_array = json_encode($and_or_select_array); ?>
        let js_array = <?php echo $json_array; ?>;
        for(let j=1;j<=<?php echo J_MAX; ?>;j++){
            if(js_array[j-1]){
                document.getElementById("j"+j+"_and_or_select").value=js_array[j-1];
            }
        }
    }
    // // PRE YEARセレクト
    // function preyearselectsave(){
    //     <?php //$json_array = json_encode($pre_year_select_array); ?>
    //     let js_array = <?php //echo $json_array; ?>;
    //     for(let j=1;j<=<?php //echo J_MAX; ?>;j++){
    //         if(js_array[j-1]){
    //             document.getElementById("j"+j+"_pre_year_select").value=js_array[j-1];
    //         }
    //     }
    // }
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
    // 通過記号
    function tukasymbolsave(){
        <?php $json_array_a = json_encode($tuka_1_symbol_array); ?>
        <?php $json_array_b = json_encode($tuka_2_symbol_array); ?>
        <?php $json_array_c = json_encode($tuka_3_symbol_array); ?>
        <?php $json_array_d = json_encode($tuka_4_symbol_array); ?>
        let js_array_a = <?php echo $json_array_a; ?>;
        let js_array_b = <?php echo $json_array_b; ?>;
        let js_array_c = <?php echo $json_array_c; ?>;
        let js_array_d = <?php echo $json_array_d; ?>;
        for(let j=1;j<=<?php echo J_MAX; ?>;j++){
            if(js_array_a[j-1]){
                document.getElementById("j"+j+"_tuka_1_symbol").value=js_array_a[j-1];
            }
            if(js_array_b[j-1]){
                document.getElementById("j"+j+"_tuka_2_symbol").value=js_array_b[j-1];
            }
            if(js_array_c[j-1]){
                document.getElementById("j"+j+"_tuka_3_symbol").value=js_array_c[j-1];
            }
            if(js_array_d[j-1]){
                document.getElementById("j"+j+"_tuka_4_symbol").value=js_array_d[j-1];
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
    // 上り順位
    function agarijunsave(){
        <?php $json_array = json_encode($agarijun_array); ?>
        let js_array = <?php echo $json_array; ?>;
        for(let j=1;j<=<?php echo J_MAX; ?>;j++){
            for(let i=1;i<=<?php echo AGARIJUN_MAX; ?>;i++){
                if(js_array[j-1][i-1]){
                    document.getElementById("j"+j+"_agarijun_"+i).checked="true";
                }
            }
        }
    }
    // 着順
    function chakujunsave(){
        <?php $json_array = json_encode($chakujun_array); ?>
        let js_array = <?php echo $json_array; ?>;
        for(let j=1;j<=<?php echo J_MAX; ?>;j++){
            for(let i=1;i<=<?php echo CHAKUJUN_MAX; ?>;i++){
                if(js_array[j-1][i-1]){
                    document.getElementById("j"+j+"_chakujun_"+i).checked="true";
                }
            }
        }
    }
</script>

<!-- 値保持についてJavaScriptでは最初の更新しか適用されないため、下記PHPを実行する -->
<?php echo '<script type="text/javascript">','targetracesave();','</script>'; ?>
<?php echo '<script type="text/javascript">','targetyearsave();','</script>'; ?>
<?php echo '<script type="text/javascript">','deletebtnsave();','</script>'; ?>
<?php echo '<script type="text/javascript">','andorselectsave();','</script>'; ?>
<?php echo '<script type="text/javascript">','racesave();','</script>'; ?>
<?php echo '<script type="text/javascript">','umabansave();','</script>'; ?>
<?php echo '<script type="text/javascript">','wakubansave();','</script>'; ?>
<?php echo '<script type="text/javascript">','seireihsave();','</script>'; ?>
<?php echo '<script type="text/javascript">','seireibsave();','</script>'; ?>
<?php echo '<script type="text/javascript">','kinryosave();','</script>'; ?>
<?php echo '<script type="text/javascript">','timesave();','</script>'; ?>
<?php echo '<script type="text/javascript">','tukasave();','</script>'; ?>
<?php echo '<script type="text/javascript">','tukasymbolsave();','</script>'; ?>
<?php echo '<script type="text/javascript">','agarisave();','</script>'; ?>
<?php echo '<script type="text/javascript">','tanshosave();','</script>'; ?>
<?php echo '<script type="text/javascript">','agarijunsave();','</script>'; ?>
<?php echo '<script type="text/javascript">','ninkisave();','</script>'; ?>
<?php echo '<script type="text/javascript">','chakujunsave();','</script>'; ?>

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

        .jouken_result_table {
            border-collapse: collapse;
        }
        .jouken_result_table th {
            border: 1px solid gray;
            text-align: center;
            width: max-content;
            color: gray;
            background: gainsboro;
            /* background: deepskyblue; */
            color: gray;
            font-size: 13px;
        }
        .jouken_result_table td {
            border: 1px solid gray;
            text-align:left;
            font-size: 12px;
            color: black;
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

        /* ポップアップ表示 */
        #popup{
            width:70%;
            height:auto;
            background:ghostwhite;
            padding:0 4%;
            box-sizing:border-box;
            display:none;
            position:fixed;
            top:50%;
            left:35%;
            -webkit-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
        }
        input[type="checkbox"]:checked + #popup{
            display:block;
            transition:.2s;
        }
    </style>
</head>
<body>
    <hr>
    <br>
    <b>全レース：<?php echo $first_num."-".($second_num-$first_num)."-".($third_num-$second_num)."-".($all_num-$third_num); ?></b>
    <p style="margin-top: 0%;"><?php echo "単勝率:".round($win_rates_sum[0],1)."%"."(".$first_num."/".$all_num.")"."</br>"." 連体率:".round($win_rates_sum[1],1)."%"."(".$second_num."/".$all_num.")"."</br>"." 複勝率:".round($win_rates_sum[2],1)."%"."(".$third_num."/".$all_num.")"; ?></p>
    <?php for($i=0,$year_tmp=$_POST['year_e'] ;$i<=$diff;$i++,$year_tmp--) : ?>
        <?php if(!$year_results[$diff-$i]){ continue; } ?>
        <b><?php echo $year_tmp." - ".$_POST['race_name']."(".$year_results_race_info[$diff-$i][0]->PLACE."/".$year_results_race_info[$diff-$i][0]->HOLD_NUM."/".$year_results_race_info[$diff-$i][0]->DISTANCE."/".$year_results_race_info[$diff-$i][0]->WEATHER."/".$year_results_race_info[$diff-$i][0]->STATE.")"; ?></b>
        <p style="margin-top: -0%;"><?php echo "単勝率:".round($win_rates[$diff-$i][0],1)."%"." 連体率:".round($win_rates[$diff-$i][1],1)."%"." 複勝率:".round($win_rates[$diff-$i][2],1)."%"; ?></p>
        <div class="tatget_scroll">
            <table class="target_table" border="1" style="margin-top: -3%;">
                <tr><th>年度</th><th>馬名</th><th>着順</th><th>人気</th><th>馬番</th><th>枠番</th><th>性齢</th><th>斤量</th><th>騎手</th><th>タイム</th><th>通過</th><th>上り</th><th>上り順位</th><th>単勝</th><th>馬体重</th></tr>
                    <?php foreach ($year_results[$diff-$i] as $row) : ?>
                        <tr><td bgcolor="white"><?php echo $row->年度 ?></td>
                        <td bgcolor="white">
                            <label>
                                <span><u style="color: blue;"><?php echo $row->馬名 ?><u></span>
                                <input type="checkbox" name="checkbox" style="display: none;">
                                <div id="popup">
                                    <?php for($x=0;$x<count($race_name);$x++) : ?>
                                        <?php if(!$year_results_as_umamei[$row->馬名][$x]){ continue; } ?>
                                        <?php if($year_results_as_umamei[$row->馬名][$x]->年度 != $row->年度 && $year_results_as_umamei[$row->馬名][$x]->年度 != ($row->年度-1) ){ continue; } ?>
                                        <div class="tatget_scroll">
                                            <table class="jouken_result_table">
                                                <p style="margin-bottom: 0%;margin-top: -2%;color: black;font-size: 15px"><?php echo $race_name[$x]; ?></p>
                                                <tr><th>年度</th><th>馬名</th><th>着順</th><th>人気</th><th>馬番</th><th>枠番</th><th>性齢</th><th>斤量</th><th>騎手</th><th>タイム</th><th>通過</th><th>上り</th><th>上り順位</th><th>単勝</th><th>馬体重</th></tr>
                                                <tr><td bgcolor="white"><?php echo $year_results_as_umamei[$row->馬名][$x]->年度 ?></td><td bgcolor="white"><?php echo $year_results_as_umamei[$row->馬名][$x]->馬名 ?></td><td bgcolor="white"><?php echo $year_results_as_umamei[$row->馬名][$x]->着順 ?></td><td bgcolor="white"><?php echo $year_results_as_umamei[$row->馬名][$x]->人気 ?></td><td bgcolor="white"><?php echo $year_results_as_umamei[$row->馬名][$x]->馬番 ?></td><td bgcolor="white"><?php echo $year_results_as_umamei[$row->馬名][$x]->枠番 ?></td><td bgcolor="white"><?php echo $year_results_as_umamei[$row->馬名][$x]->性齢 ?></td><td bgcolor="white"><?php echo $year_results_as_umamei[$row->馬名][$x]->斤量 ?></td><td bgcolor="white"><?php echo $year_results_as_umamei[$row->馬名][$x]->騎手 ?></td><td bgcolor="white"><?php echo substr_replace(substr($year_results_as_umamei[$row->馬名][$x]->タイム, 1, 7),".",4,1) ?></td><td bgcolor="#ffffff"><?php echo $year_results_as_umamei[$row->馬名][$x]->通過 ?></td><td bgcolor="white"><?php echo $year_results_as_umamei[$row->馬名][$x]->上り ?></td><td bgcolor="white"><?php echo $year_results_as_umamei[$row->馬名][$x]->上り順位 ?></td><td bgcolor="white"><?php echo $year_results_as_umamei[$row->馬名][$x]->単勝 ?></td><td bgcolor="white"><?php echo $year_results_as_umamei[$row->馬名][$x]->馬体重 ?></td></tr>    
                                            </table>
                                        </div>
                                    <?php endfor ?>
                                </div>
                            </label>
                        </td>
                        <td bgcolor="white"><?php echo $row->着順 ?></td><td bgcolor="white"><?php echo $row->人気 ?></td><td bgcolor="white"><?php echo $row->馬番 ?></td><td bgcolor="white"><?php echo $row->枠番 ?></td><td bgcolor="white"><?php echo $row->性齢 ?></td><td bgcolor="white"><?php echo $row->斤量 ?></td><td bgcolor="white"><?php echo $row->騎手 ?></td><td bgcolor="white"><?php echo substr_replace(substr($row->タイム, 1, 7),".",4,1) ?></td><td bgcolor="#ffffff"><?php echo $row->通過 ?></td><td bgcolor="white"><?php echo $row->上り ?></td><td bgcolor="white"><?php echo $row->上り順位 ?></td><td bgcolor="white"><?php echo $row->単勝 ?></td><td bgcolor="white"><?php echo $row->馬体重 ?></td></tr>
                    <?php endforeach; ?>
            </table>
            <details style="margin-bottom: 4%; margin-top: -2%; margin-left:1%; font-size: 15px;">
                <summary style="color: black;color:darkslategrey">条件レース</summary>
                <?php for($k=0;$k<count($year_results_others_jp);$k++) : ?>
                    <p style="font-size: 14px; margin-left: 0%;"><?php echo $year_results_others_jp[$k]."(".$year_results_others_race_info[$diff-$i][$k][0]->PLACE."/".$year_results_others_race_info[$diff-$i][$k][0]->HOLD_NUM."/".$year_results_others_race_info[$diff-$i][$k][0]->DISTANCE."/".$year_results_others_race_info[$diff-$i][$k][0]->WEATHER."/".$year_results_others_race_info[$diff-$i][$k][0]->STATE.")"; ?></p>
                    <table class="jouken_result_table" border="1" style="margin-top: -2%;">
                        <tr><th>年度</th><th>馬名</th><th>着順</th><th>人気</th><th>馬番</th><th>枠番</th><th>性齢</th><th>斤量</th><th>騎手</th><th>タイム</th><th>通過</th><th>上り</th><th>上り順位</th><th>単勝</th><th>馬体重</th></tr>
                            <?php foreach ($year_results_others[$diff-$i][$k] as $row) : ?>
                                <tr><td bgcolor="white"><?php echo $row->年度 ?></td><td bgcolor="white"><?php echo $row->馬名 ?></td><td bgcolor="white"><?php echo $row->着順 ?></td><td bgcolor="white"><?php echo $row->人気 ?></td><td bgcolor="white"><?php echo $row->馬番 ?></td><td bgcolor="white"><?php echo $row->枠番 ?></td><td bgcolor="white"><?php echo $row->性齢 ?></td><td bgcolor="white"><?php echo $row->斤量 ?></td><td bgcolor="white"><?php echo $row->騎手 ?></td><td bgcolor="white"><?php echo substr_replace(substr($row->タイム, 1, 7),".",4,1) ?></td><td bgcolor="#ffffff"><?php echo $row->通過 ?></td><td bgcolor="white"><?php echo $row->上り ?></td><td bgcolor="white"><?php echo $row->上り順位 ?></td><td bgcolor="white"><?php echo $row->単勝 ?></td><td bgcolor="white"><?php echo $row->馬体重 ?></td></tr>
                            <?php endforeach; ?>
                    </table>
                <?php endfor; ?>
            </details>
        </div>
    <?php endfor; ?>
</body>
</html>