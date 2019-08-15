<?php
    set_time_limit(0);

function getHtml($offset, $query) {

    $curl = curl_init();

   //$query = '%28"Internet%20of%20Things"%20OR%20"IoT"%20OR%20"Internet%20of%20Medical%20Things"%20OR%20"iomt"%20OR%20"%2Ahealth%2A"%20OR%20"AAL"%20OR%20"Ambient%20Assisted%20Living"%29%20AND%20%28"%2Aelder%2A"%20OR%20"old%20people"%20OR%20"older%20person"%20OR%20"senior%20citizen"%20OR%20"aged%20people"%29%20AND%20%28"Smart%20Cities"%20OR%20"Smart%20City"%29';
    //$query = rawurlencode('("Internet of Things" OR "IoT" OR "Internet of Medical Things" OR "iomt" OR "*health*" OR "AAL" OR "Ambient Assisted Living") AND ("*elder*" OR "old people" OR "older person" OR "senior citizen" OR "aged people") AND ("Smart Cities" OR "Smart City")');
    $query = rawurlencode($query);
    curl_setopt_array($curl, array(
    CURLOPT_URL => "https://www.sciencedirect.com/search/advanced?tak=$query&show=100&sortBy=relevance&articleTypes=REV%2CFLA%2CABS&offset=$offset",
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
        "Cookie: search_ab=%7B%7D; __cfduid=d738348cc60f8946c043de9c898ef5c4f1565817401; id_ab=B:100:3; EUID=106eaf15-bd6a-41cb-99c7-d48e23b53348; csrf_token=ec36dc85-b4e8-4678-a2ca-e6f50a651dd6; sd_access=eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..Mh1OWsFsOGOL9DVpHzAFYw.36tfhlzyq8q3I8MS5ea5MAZfBhNkg3wSLCcPD2RrU4sIxa_i5Dl0glPokqPK3N8IDqOihdMVOzpC_2644N2S4rx7FbDhmfwvmR-5l_RK3qHtkg7aD0jhHjDlMVLp9el9sn-QAyXQHWiUPALBRD-9Ow.sZ7z4q-aCnhHzzKBNNHIww; sd_session_id=4c3a0dfa36df444c318bfa57c252aea937c4gxrqb; has_multiple_organizations=false; MIAMISESSION=632763ac-a55a-43c2-abfe-b2fae30a7bf6:3743273211",
        "Host: www.sciencedirect.com",
        "Postman-Token: 97ffddf5-5f91-45dd-bb68-3450c55194e0,e8cba0b6-9ca4-4171-87ed-0b277773d4b9",
        "User-Agent: PostmanRuntime/7.15.2",
        "cache-control: no-cache"
    ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
    echo "cURL Error #:" . $err;
    } else {
      return $response;
    }

}


    spl_autoload_register(function ($class_name) {
        include $class_name . '.php';
    });
    $cookie = 'search_ab=%7B%7D; __cfduid=d738348cc60f8946c043de9c898ef5c4f1565817401; id_ab=B:100:3; EUID=106eaf15-bd6a-41cb-99c7-d48e23b53348; csrf_token=ec36dc85-b4e8-4678-a2ca-e6f50a651dd6; sd_access=eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..Mh1OWsFsOGOL9DVpHzAFYw.36tfhlzyq8q3I8MS5ea5MAZfBhNkg3wSLCcPD2RrU4sIxa_i5Dl0glPokqPK3N8IDqOihdMVOzpC_2644N2S4rx7FbDhmfwvmR-5l_RK3qHtkg7aD0jhHjDlMVLp9el9sn-QAyXQHWiUPALBRD-9Ow.sZ7z4q-aCnhHzzKBNNHIww; sd_session_id=4c3a0dfa36df444c318bfa57c252aea937c4gxrqb; has_multiple_organizations=false; MIAMISESSION=632763ac-a55a-43c2-abfe-b2fae30a7bf6:3743273211"';


    $break_line         = "<br>";
    $query_string       = trim(@$_GET['query']);
    $file_name          = trim(@$_GET['query']);
    $results            = (int) @$_GET['results'];
    $page               = (int) @$_GET['page'];
    
    define('BREAK_LINE', $break_line);

    try {
        if (empty($query_string)) {
            throw new Exception("Query String not found");
        }         
        if ( ( isset($_GET['page']) && isset($_GET['results']) ) || !isset($_GET['page']) && !isset($_GET['results']) ) {
            throw new Exception("Only one parameter: Page or Results");
        }

        $total          = (int) ($results / 100) + 1;
        $file           = "bibtex/" . Util::slug(trim($file_name)) . ".bib";
        $user_agent     = (!empty($_SERVER["HTTP_USER_AGENT"])) ? $_SERVER["HTTP_USER_AGENT"] : "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.13; rv:58.0) Gecko/20100101 Firefox/58.0";
        define('USER_AGENT', $user_agent);   
        define('COOKIE', @$cookie);
        define('FILE', $file);
        define('FILE_LOG', Util::slug(trim($file_name)) . "_log.txt");
                   
        for($offset=0; $offset<=$total; $offset++) {
            $html = getHtml($offset*100, $query_string);
            $sleep = rand(3,6);
            Util::showMessage("Page " . ($offset+1) . " / " .  $total);
            preg_match_all('#<script(.*?)</script>#is', $html, $matches);
            $js = "";
            $value = "";
            foreach ($matches[0] as $value) {
                if (strpos($value, "INITIAL_STATE") !== false) {
                    $jsonFile = strip_tags(str_replace("var INITIAL_STATE = ", "", $value));
                    break;
                }
            }

            ElsevierScienceDirect::progress($jsonFile);

            if ($offset != $total) {
                Util::showMessage("Wait for " . $sleep . " seconds before executing next page");
                Util::showMessage("");
                sleep($sleep);
            }
        }

    } catch(Exception $e) {
        Util::showMessage($e->getMessage());
    }
?>
