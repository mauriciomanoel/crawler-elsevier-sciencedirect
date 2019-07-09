<?php

class ElsevierScienceDirect {

    private static $URL = 'https://www.sciencedirect.com';

    public static function getURL($offset, $query) 
    {
        $url = self::$URL . "/search/api?qs=$query&show=100&sortBy=relevance&articleTypes=REV%2CFLA%2CABS&offset=$offset";
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

        $jsonValue = json_decode($html, true);


        $bibtex_new = "";
        //var_dump($jsonValue); 
        $articles = $jsonValue["searchResults"];
        //echo "<pre>"; var_dump($articles); exit;
        foreach($articles as $article) {
            $data     = self::getDataArticle($article);
            Util::showMessage($article["title"]);
            $bibtex      = self::getBibtex($article["pii"]);
            $bibtex_new .= Util::add_fields_bibtex($bibtex, $data);
            sleep(rand(2,4)); // rand between 5 and 8 seconds
        }
        Util::showMessage("Download bibtex file OK.");
        Util::showMessage("");

        if (!empty($bibtex_new)) {
            file_put_contents(FILE, $bibtex_new, FILE_APPEND);
            Util::showMessage("File " . FILE . " saved successfully.");
            Util::showMessage("");
        }
    }

    public static function getDataArticle($value) {
        $retorno        = array("url_article"=>"", "title"=> "", "doc"=>"", "link_pdf"=>"");
                  
        $title          = $value["sourceTitle"];
        $link_pdf       = self::$URL . $value["pdf"]["downloadLink"];
        $url_article    = self::$URL . $value["link"];

        $retorno["doc"] = $value["pii"];
        $retorno["title"] = $title;
        $retorno["url_article"] = $url_article;
        $retorno["link_pdf"] = $link_pdf;
        
        return $retorno;
    }
        
    public static function getBibtex($doc) {
        $url = self::$URL . "/sdfe/arp/cite?pii=$doc&format=text%2Fx-bibtex&withabstract=true";
        
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
