<?php

require('class.reddit4eink.php');

if( !isset($_GET['url']) )
    $_GET['url'] = 'http://old.reddit.com/r/WritingPrompts/';

new reddit4eink($_GET['url']);

?>