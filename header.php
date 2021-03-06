<?php
/**
 * Author:
 *    Vayn a.k.a. VT <vt@elnode.com>
 *    http://elnode.com
 *
 *    File:             header.php
 *    Create Date:      2010年04月30日 星期五 05时58分42秒
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
        <title><?php echo $vw_sitename ?> - Project VaynWords</title>

        <meta charset="UTF-8" />
        <meta name="description" content="Project Vaynwords. Help you remember words." />
        <meta name="generator" content="http://lab.jixia.org" />
        <meta name="revisit-after" content="2 days" />
        <meta http-equiv="content-script-type" content="text/javascript" />

        <link href='http://fonts.googleapis.com/css?family=IM+Fell+English+SC' rel='stylesheet' type='text/css'>
        <link rel="shortcut icon" href="favicon.ico" />
        <link rel="stylesheet" href="css/style.css" type="text/css" />
        <link rel="alternate" type="application/rss+xml" title="Project VaynWords RSS 2.0" href="./feed" />
</head>
<body onDblClick="s=setInterval('scrollBy(0,2)',50)" onMousedown="clearInterval(s)" onload="s=0;">
    <div id="wrapper">
        <div id="shell">
            <div id="header">
                <div id="nav">
                    <h1><?php echo $vw_sitename ?></h1>
                    <ul id="navigation">
                        <li><span><a href="http://lab.jixia.org">JiXia Lab</a></span></li>
                        <li><span><a href="http://vk.elnode.com">Weblog</a></span></li>
                        <li><span><a href="http://twiter.com/vayn">Twitter</a></span></li>
                    </ul>
                </div>
            </div>
            <hr />
            <div id="content">
                <div class="container">
                    <div id="wordlist">
