<?php
/**
 * Author:
 *    Vayn a.k.a. VT <vt@elnode.com>
 *    http://elnode.com
 *
 *    File:         vws_functions.php
 *    Create Date:    2010年 05月 13日 星期四 18:43:21 CST
 */

//
// 从 dict.cn 获得单词音标、解释、中英文例句
//
function dict_query($value_1) {
    $xml = simplexml_load_file('http://api.dict.cn/ws.php?utf8=true&q=' . $value_1);
    if (!($xml->def == 'Not Found')) {
        $arr['key'] = $xml->key;
        $arr['pron'] = $xml->pron;
        $arr['def'] = $xml->def;
        $arr['sent_o'] = $xml->sent->orig;
        $arr['sent_t'] = $xml->sent->trans;

        return $arr;
    }
    else {
        return FALSE;
    }
}

//
// 从 GDict 获得单词数据
//
function gdic_query($w) {
    $json = file_get_contents("http://www.google.com/dictionary/json?callback=dict_api.callbacks.id100&q={$w}&sl=en&tl=zh&restrict=pr%2Cde&client=te");
    $json = substr($json, strpos($json, "(")+1, -10);
    $json = str_replace("\\", "\\\\", $json);
    $decode = json_decode($json, true);

    $aDecode = $aPhonetic = $aMeaning = array();

    $aDecode = $decode['primaries'][0];

    $aPhonetic['label'] = $aDecode['terms'][1]['labels'][0]['text'];
    $aPhonetic['text'] = $aDecode['terms'][1]['text'];
    $aPhonetic['sound'] = $aDecode['terms'][2]['text'];

    $nPartOfSpeechCount = count($aDecode['entries']);

    for ($i = 0; $i < $nPartOfSpeechCount; $i++) {
        $aMeaning['pos'][$i]['type'] = $aDecode['entries'][$i]['labels'][0]['text'];
        if ($aMeaning['pos'][$i]['type'] == null ||
           $aMeaning['pos'][$i]['type'] == "Derivative:" ||
           $aMeaning['pos'][$i]['type'] == "Idiom:") {
           $aMeaning['pos'][$i]['type'] = $aDecode['entries'][$i]['terms'][1]['labels'][0]['text'];
           if ($aMeaning['pos'][$i]['type'] == 'DJ') {
               $aMeaning['pos'][$i]['type'] = $aDecode['entries'][$i]['terms'][0]['labels'][0]['text'];
           }
        }
        elseif ($aMeaning['pos'][$i]['type'] == 'Variant:') {
            $aMeaning['pos'][$i]['type'] = $aDecode['entries'][1]['labels'][0]['text'];
            if (count($aMeaning['pos'][$i]) == 1) {
                unset($aMeaning['pos'][$i]);
            }
        }
        elseif ($aMeaning['pos'][$i]['type'] == 'See also:') {
            $aMeaning['pos'][$i]['type'] .= ' '
                . $aDecode['entries'][$i]['terms'][0]['text'];
        }

          $nMeaningCount = count($aPMeaning = $aDecode['entries'][$i]['entries']);

          for ($j = 0; $j < $nMeaningCount; $j++) {
            $aMeaning['pos'][$i]['meaning'][$j]['def'][0] = $aPMeaning[$j]['terms'][0]['text'];
            $aMeaning['pos'][$i]['meaning'][$j]['def'][1] = $aPMeaning[$j]['terms'][1]['text'];
            if ($aMeaning['pos'][$i]['meaning'][$j]['def'][1] == null) {
                $aMeaning['pos'][$i]['meaning'][$j]['def'][0] = $aDecode['entries'][$i]['terms'][0]['text'];
                $aMeaning['pos'][$i]['meaning'][$j]['def'][1] = $aDecode['entries'][$i]['terms'][1]['text'];
            }

            $nPMExampleCount = count($aPMExample = $aPMeaning[$j]['entries'][0]['terms']);
            if ($nPMExampleCount > 0) {
                $aMeaning['pos'][$i]['meaning'][$j]['example'][0] = $aPMExample[0]['text'];
                $aMeaning['pos'][$i]['meaning'][$j]['example'][1] = $aPMExample[1]['text'];
            }

            if ($aMeaning['pos'][$i]['meaning'][$j]['def'][1] == '') {
                unset($aMeaning['pos'][$i]['meaning'][$j]);
            }
        }
    }

    $aMeaning = $aPhonetic + $aMeaning;
    return $aMeaning;
}


//
// 从 Google Dictinary 获得单词发音
//
function gsound($soundUrl) {
    $ueUrl = urlencode($soundUrl);

    $playcode =<<<EOF
<object data="http://www.google.com/dictionary/flash/SpeakerApp16.swf" type="application/x-shockwave-flash" id="pronunciation" height="16" width=" 16">
<param name="movie" value="http://www.google.com/dictionary/flash/SpeakerApp16.swf">
<param name="flashvars" value="sound_name={$ueUrl}">
<param name="wmode" value="transparent">
<a href="{$soundUrl}"><img src="http://www.google.com/dictionary/flash/SpeakerOffA16.png" alt="发音" height="16" width="16" border="0"></a>
</object>
EOF;
    return $playcode;
}

//
// Get word data from database
//
function pullword() {
    global $vw_perpage, $page, $dbhost, $dbuser, $dbpassword, $dbdatabase;

    $db = mysql_connect($dbhost, $dbuser, $dbpassword);
    mysql_select_db($dbdatabase, $db);
    mysql_query("set names 'utf8';");

    $start = ($page==1) ? $start = 0 : $start = (($page - 1) * $vw_perpage) + 1;

    $words = array();
    $wsql = "SELECT * FROM vws_wordlist ORDER BY wl_date DESC LIMIT {$start}, {$vw_perpage};";
    $wres = mysql_query($wsql);
    $wnum = mysql_num_rows($wres);
    $i = 0;

    if ($wnum) {
        while ($wrow = mysql_fetch_assoc($wres)) {
            $words[$i] = array('id'=>$wrow['id'], 'key'=>$wrow['wl_key'], 'label'=>$wrow['label'], 'text'=>$wrow['text'], 'sound'=>$wrow['sound'],);
            $psql = "SELECT id, type FROM vws_pos WHERE wid=" . $wrow['id'] . ";";
            $pres = mysql_query($psql);
            $j = 0;
            while ($prow = mysql_fetch_assoc($pres)) {
                $words[$i]['type'][$j]['pos'] = $prow['type'];
                $dsql = "SELECT m_en, m_zh, eg_en, eg_zh FROM vws_def WHERE pid=" . $prow['id'] . ";";
                $dres = mysql_query($dsql);
                $k = 0;
                while ($drow = mysql_fetch_assoc($dres)) {
                    $words[$i]['type'][$j]['def'][$k] = array('m_en'=>$drow['m_en'], 'm_zh'=>$drow['m_zh'], 'eg_en'=>$drow['eg_en'], 'eg_zh'=>$drow['eg_zh']);
                    $k++;
                }
                $j++;
            }
            $i++;
        }
    }

    return $words;
}

//
// Generate words table
//
function generate_content() {
    $words = pullword();
    $tablecount = 0;

    foreach ($words as $word) {
        $id = $word['id'];
        $key = $word['key'];
        $pron = $word['text'];
        $mp3 = $word['sound'];
        $def = $word['type'][0]['def'][0]['m_zh'];
        $sent_o = $word['type'][0]['def'][0]['eg_en'];
        $sent_t = $word['type'][0]['def'][0]['eg_zh'];

        $arr[$tablecount] = '<table class="word_fleet" cellspacing="2">';
        $arr[$tablecount] .= '<tr>';
        $arr[$tablecount] .= '<td class="word_box_s">';
        $arr[$tablecount] .= $key . '<span id="' . $id . '"></span>';
        $arr[$tablecount] .= '</td>';
        if ($pron == '') {
            $arr[$tablecount] .='<td class="word_box_s">' . gsound($mp3) . '</td>';
        }
        else {
            $arr[$tablecount] .= '<td class="word_box_s">/' . $pron . '/ ' . gsound($mp3) . '</td>';
        }
        $arr[$tablecount] .= '<td class="word_box_s">' . $def . '</td>';
        $arr[$tablecount] .= '</tr>';

        if ($sent_o != '' || $sent_t != '') {
            $arr[$tablecount] .= '<tr><td class="word_box_l" colspan=3>' . $sent_o . '</td></tr>';
            $arr[$tablecount] .= '<tr><td class="word_box_l" colspan=3>' . $sent_t;
            if (($i%5 == 0) && ($i != 0)) {
                $arr[$tablecount] .= '<a href="#top" title="Back to top"><div class="back">&uarr;<div></a>';
            }
            $arr[$tablecount] .= '</td></tr>';
        }
        $arr[$tablecount] .= '</table>';
        $tablecount++;
    }

    $show = pagination($arr);

    foreach ($show as $key) {
        echo $key;
    }

}

//
// Pagination
//
function pagination($aContent) {
    global $page, $pages, $vw_perpage;

    if ($pages > 1 && $page > 1) {
        // Assign the previous page
        $plink = '<a href="?page=' . ($page - 1) . '">&laquo; Prev</a>';
        if ($page < $pages) {
             $nlink = '<a href="?page=' . ($page + 1) . '">Next &raquo;</a>';
        }
    }
    else {
        $nlink = '<a href="?page=' . ($page + 1) . '">Next &raquo;</a>';
    }

        // Assign all the page numbers and links to the string
        for ($l = 1; $l < $pages+1; $l++) {
            if ($page == $l) {
                $link .= ' <span class="current">' . $l . '</span> '; // If we are on the current page
            }
            else {
                $link .= ' <a href="?page=' . $l . '" class="page">' . $l . '</a> ';
            }
        }

        $aContent[] = '<div id="pagination">' . $plink . $link . $nlink . '</div>';
        return $aContent;
}

?>

