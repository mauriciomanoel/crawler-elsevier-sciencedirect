<?php

class ElsevierScienceDirect {

    private static $URL = 'https://www.sciencedirect.com';

    public static function getURL($offset, $query) 
    {
        $url = self::$URL . "/search/advanced?tak=$query&show=100&sortBy=relevance&articleTypes=REV%2CFLA%2CABS&offset=$offset";
        Util::showMessage($url);
        return $url;

    }

    public static function start($page, $query_string, $url) {
        Util::showMessage("Page: " . $page);
        $url = self::getUrl($page, $query_string);
        self::progress($url);
    }

    public static function progress($value) {  
        
        
        //$html = Util::loadURL($url, COOKIE, USER_AGENT);
        //var_dump($url, $html); exit;
        $bibtex_new = "";
        $jsonValue = json_decode($value, true);
        $articles = $jsonValue["search"]["searchResults"];
        //echo "<pre>"; var_dump($articles); exit;
        foreach($articles as $key => $article) {
            $data     = self::getDataArticle($article);
            Util::showMessage(($key + 1) . "/" . count($articles) . " - " .  $article["title"]);
            $bibtex      = self::getBibtex($article["pii"]);

            //var_dump($bibtex); exit;
            $bibtexTemp = Util::add_fields_bibtex($bibtex, $data);
            $bibtex_new .= $bibtexTemp;

            if (!empty($bibtexTemp)) {
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
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://www.sciencedirect.com/sdfe/arp/cite?pii=$doc&format=text%2Fx-bibtex&withabstract=true",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "Accept: */*",
            "Accept-Encoding: gzip, deflate",
            "Cache-Control: no-cache",
            "Connection: keep-alive",
            "Cookie: __cfduid=d738348cc60f8946c043de9c898ef5c4f1565817401; id_ab=B:100:3; EUID=106eaf15-bd6a-41cb-99c7-d48e23b53348; csrf_token=ec36dc85-b4e8-4678-a2ca-e6f50a651dd6; sd_access=eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..Mh1OWsFsOGOL9DVpHzAFYw.36tfhlzyq8q3I8MS5ea5MAZfBhNkg3wSLCcPD2RrU4sIxa_i5Dl0glPokqPK3N8IDqOihdMVOzpC_2644N2S4rx7FbDhmfwvmR-5l_RK3qHtkg7aD0jhHjDlMVLp9el9sn-QAyXQHWiUPALBRD-9Ow.sZ7z4q-aCnhHzzKBNNHIww; sd_session_id=4c3a0dfa36df444c318bfa57c252aea937c4gxrqb; has_multiple_organizations=false; MIAMISESSION=632763ac-a55a-43c2-abfe-b2fae30a7bf6:3743273328",
            "Host: www.sciencedirect.com",
            "Postman-Token: 70d37852-13a3-4d86-864c-5b89242abdab,9be3eaee-00dd-422b-8e90-7fb72d6cb85f",
            "User-Agent: PostmanRuntime/7.15.2",
            "cache-control: no-cache"
          ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        return $response;

    }
    
    public static function getPDF($file) {
        $pdf = Util::loadURL($file, COOKIE, USER_AGENT);
        return $pdf;
    }
}
    
?>
