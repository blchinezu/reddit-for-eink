<?php

class reddit4eink {

    private $_sCacheDir = './cache/';

    private $_sUrl;
    private $_sPageTitle = 'Untitled';
    private $_sPageMenu = '[ No page menu ]';
    private $_sPageContent = '[ No page content ]';
    private $_sPageLocation = 'unknown';

    public function __construct($sUrl = '') {

        $this->_sUrl = $sUrl;

        $this->_grabPage();
        $this->_removeFoo();
        $this->_reconstructUrlsIn($this->_sPageMenu);
        $this->_reconstructUrlsIn($this->_sPageContent);
        $this->_showPage();
    }

    private function _grabPage() {

        $tmp = file_get_contents($this->_sUrl);

        preg_match_all('/\<title\>([^\<]+)/i', $tmp, $aMatches);
        if( isset($aMatches[1][0]) && !empty($aMatches[1][0]) )
            $this->_sPageTitle = $aMatches[1][0];

        $tmp = explode('<div id="siteTable" class="sitetable linklisting">', $tmp,2);
        if( !isset($tmp[1]) )
            $this->_die('Website structure has changed!');

        $tmp[1] = explode('</div><div class="footer-parent">', $tmp[1],2);
        if( !isset($tmp[1][1]) )
            $this->_die('Website structure has changed!');

        $this->_sPageContent = $tmp[1][0];

        $tmp[0] = explode('<ul class="tabmenu " >', $tmp[0],2);
        if( !isset($tmp[0][1]) )
            $this->_die('Website structure has changed!');

        $tmp[0] = explode('</ul>', $tmp[0][1],2);
        if( !isset($tmp[0][1]) )
            $this->_die('Website structure has changed!');

        $this->_sPageMenu = '<ul class="tabmenu">'.$tmp[0][0].'</ul>';

        $tmp = explode("<li class='selected'><a href", $this->_sPageMenu, 2);
        $tmp = explode('>', $tmp[1], 2);
        $tmp = explode('<', $tmp[1], 2);
        $this->_sPageLocation = $tmp[0];
    }

    private function _removeFoo() {
        if( empty($this->_sPageContent) )
            return;

        $aFoo = array(
            '<div class="child" ></div>',
            'click_thing(this)',
            'onclick=""',
            );

        foreach( $aFoo AS $sFoo )
            $this->_sPageContent = str_replace($sFoo, '', $this->_sPageContent);
    }

    private function _reconstructUrlsIn(&$sHtml) {

        $sHtml = preg_replace_callback(
            '/href="([^"]+)"/i',
            function ($matches) {
                if( strpos($matches[1], 'http://old.reddit.com') === 0 )
                    return 'href="http://brucelee.duckdns.org/rwp/?url='.urlencode($matches[1]).'"';
                elseif( strpos($matches[1], 'https://old.reddit.com') === 0 )
                    return 'href="http://brucelee.duckdns.org/rwp/?url='.urlencode($matches[1]).'"';
                elseif( strpos($matches[1], '/') === 0 )
                    return 'href="http://brucelee.duckdns.org/rwp/?url='.urlencode('http://old.reddit.com'.$matches[1]).'"';
                else
                    return 'href="http://brucelee.duckdns.org/rwp/?url='.urlencode('http://old.reddit.com/'.$matches[1]).'"';
            },
            $sHtml
            );
    }

    private function _showPage() {
        $sPage = file_get_contents('mask.html');
        $sPage = str_replace('[[PAGE_URL]]',      $this->_sUrl,          $sPage);
        $sPage = str_replace('[[PAGE_TITLE]]',    $this->_sPageTitle,    $sPage);
        $sPage = str_replace('[[PAGE_MENU]]',     $this->_sPageMenu,     $sPage);
        $sPage = str_replace('[[PAGE_CONTENT]]',  $this->_sPageContent,  $sPage);
        $sPage = str_replace('[[PAGE_LOCATION]]', $this->_sPageLocation, $sPage);
        die($sPage);
    }

    private function _die($sMessage) {
        die($sMessage);
    }
}

?>