<?php
/**
 * Author:
 *    Vayn a.k.a. VT <vt@elnode.com>
 *    http://elnode.com
 *
 *    File:             vws_check.php
 *    Create Date:      2010年 05月 06日 星期四 09:04:55 CST
 */
  require('config.php');
  require('vws_functions.php');

  if ($_GET['pass'] == $vw_password) {
    session_start();

    $_SESSION['SESS_QUERY'] = 'Project_VaynWords';
    require('TwitterSearch.php');

    $search = new TwitterSearch();
    $search->user_agent = $vw_useragent;
      
    $search->from($vw_username)->with($vw_hashtag);
    $results = $search->rpp(1)->results();

    if (count($results) == 0) {
      session_destroy();
      require('header.php');
  ?>
      <div id="header">
        <h1>No new tweets.</h1>
      </div>
      <div id="main">
      <div id="whale_error"><img src="img/whale_error.gif" alt="No new tweets." /></div>
  <?php
      require('footer.php');
    }
    else {
      $update = substr($results[0]->text, 0, strrpos($results[0]->text, '#'));
      $update = strtolower(str_replace(' ', '', $update));

      if (!file_exists('vws_data.xml')) {
        header('Location: VaynWords.php');
        exit;
      }

      $defExist = dict_query($update);

      if ($defExist == FALSE) {
        exit;
      }

      $xml = simplexml_load_file('vws_data.xml');

      $flag = strtolower($xml->word->key);

      if ($flag == $update) {
        exit; 
      }
      else {
        header('Location: VaynWords.php');
        exit;
      }
    }
  }
  else {
    header('Location: ./');
    exit;
  }

?>

