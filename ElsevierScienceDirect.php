<?php

class ElsevierScienceDirect {

    private static $URL = 'https://www.sciencedirect.com';

    public static function getURL($offset, $query) 
    {
        // $url = self::$URL . "/search?qs=$query&sortBy=relevance&articleTypes=REV%2CFLA&lastSelectedFacet=articleTypes&show=100&offset=$offset";
        $url = self::$URL . "/search?qs=$query&show=100&sortBy=relevance&articleTypes=REV%2CFLA&offset=$offset";
        Util::showMessage($url);
        return $url;
    }

    public static function start($page, $query_string, $url) {
        Util::showMessage("Page: " . $page);
        $url = self::getUrl($page, $query_string);
        self::progress($url);
    }

    public static function progress($url) {        
        $html = Util::loadURL($url, COOKIE, USER_AGENT);
        
        // Check Google Captcha
        if ( strpos($html, "gs_captcha_cb()") !== false || strpos($html, "sending automated queries") !== false ) {
            Util::showMessage("Captha detected"); exit;
        }

        $classname="ResultItem col-xs-24 push-m";
        $htmlValues = Util::getHTMLFromClass($html, $classname, "li");        
        $bibtex_new = "";
        $could_not_downloaded = 0;

        foreach($htmlValues as $htmlValue) {

            $data     = self::getDataArticle($htmlValue);            
            Util::showMessage($data["title"]);            
            $bibtex      = self::getBibtex($data["doc"]);
            
            if ( strpos($bibtex, "innerHTML") !== false || 
                 strpos($bibtex, "<body>") !== false || 
                 strpos($bibtex, "function(") !== false ||
                 strpos($bibtex, "gs_captcha_cb()") !== false ||
                 strpos($bibtex, "sending automated queries") !== false ||
                 strpos($bibtex, "<html>") !== false) {
                Util::showMessage("Detected HTML or Captha detected"); exit;
            }
            if (empty( $bibtex)) {
                Util::showMessage("Bibtex could not be downloaded"); 
                $could_not_downloaded++;
                if ($could_not_downloaded > 3) exit;
            } else {
                unset($data["title"]);
                unset($data["doc"]);
                $bibtex_new .= Util::add_fields_bibtex($bibtex, $data);
                Util::showMessage("Download bibtex file OK.");
                Util::showMessage("");
            }

            sleep(rand(2,4)); // rand between 5 and 8 seconds            
        }

        if (!empty($bibtex_new)) {
            file_put_contents(FILE, $bibtex_new, FILE_APPEND);
            Util::showMessage("File " . FILE . " saved successfully.");
            Util::showMessage("");
        }
    }

    public static function getDataArticle($html) {
        $retorno        = array("url_article"=>"", "title"=> "", "doc"=>"", "link_pdf"=>"");
        $classname      = "result-list-title-link u-font-serif text-s";
        $titleValues    = Util::getHTMLFromClass($html, $classname, "a");
                
        $classname      = "download-link";
        $pdfLinkValues  = Util::getHTMLFromClass($html, $classname, "a");
            
        $title          = trim(strip_tags(@$titleValues[0]));
        $link_pdf       = Util::getURLFromHTML(@$pdfLinkValues[0]);
        $url_article    = Util::getURLFromHTML(@$titleValues[0]);

        $doc = explode("/", $url_article);
        if (!empty($doc)) {
            $retorno["doc"] = $doc[count($doc)-1];
        }
        $url_article = rtrim($url_article, "/");
        if (strpos($url_article, "http") === false) {
            $url_article = self::$URL . $url_article;
        }
        if (!empty($url_article)) {
            $retorno["url_article"] = $url_article;
        }
        if (!empty($title)) {
            $retorno["title"] = $title;
        }        
        if (!empty($link_pdf)) {
            $link_pdf = rtrim($link_pdf, "/");
            $retorno["link_pdf"] = self::$URL . $link_pdf;
        }

        return $retorno;
    }
        
    public static function getBibtex($doc) {
        $url = self::$URL . "/sdfe/arp/cite?pii=$doc&format=text/x-bibtex&withabstract=true";
        // $url = "https://citation-needed.springer.com/v2/references/$doc?format=bibtex&flavour=citation";
        $bibtex = Util::loadURL($url, COOKIE, USER_AGENT);
        $bibtex = strip_tags($bibtex); // remove html tags 
        return $bibtex;        
    }
    
    public static function getPDF($file) {
        $pdf = Util::loadURL($file, COOKIE, USER_AGENT);
        return $pdf;
    }
}
    
?>
