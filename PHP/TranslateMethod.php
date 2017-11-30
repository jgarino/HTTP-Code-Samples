<?php
/*
 * Create and execute the HTTP CURL request.
 *
 * @param string $url        HTTP Url.
 * @param string $authHeader Authorization Header string.
 * @param string $postData   Data to post.
 *
 * @return string.
 *
 */
function curlRequest($url, $authHeader, $postData=''){
    //Initialize the Curl Session.
    $ch = curl_init();
    //Set the Curl url.
    curl_setopt ($ch, CURLOPT_URL, $url);
    //Set the HTTP HEADER Fields.
    curl_setopt ($ch, CURLOPT_HTTPHEADER, array($authHeader,"Content-Type: text/xml"));
    //CURLOPT_RETURNTRANSFER- TRUE to return the transfer as a string of the return value of curl_exec().
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
    //CURLOPT_SSL_VERIFYPEER- Set FALSE to stop cURL from verifying the peer's certificate.
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    if($postData) {
        //Set HTTP POST Request.
        curl_setopt($ch, CURLOPT_POST, TRUE);
        //Set data to POST in HTTP "POST" Operation.
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    }
    //Execute the  cURL session.
    $curlResponse = curl_exec($ch);
    //Get the Error Code returned by Curl.
    $curlErrno = curl_errno($ch);
    if ($curlErrno) {
        $curlError = curl_error($ch);
        throw new Exception($curlError);
    }
    //Close a cURL session.
    curl_close($ch);
    return $curlResponse;
}

/*
 * Class:HTTPTranslator
 *
 * Processing the translator request.
 */
class HTTPTranslator {
    /*
     * Create and execute the HTTP CURL request.
     *
     * @param string $url        HTTP Url.
     * @param string $authHeader Authorization Header string.
     * @param string $postData   Data to post.
     *
     * @return string.
     *
     */
    function curlRequest($url, $authHeader) {
        //Initialize the Curl Session.
        $ch = curl_init();
        //Set the Curl url.
        curl_setopt ($ch, CURLOPT_URL, $url);
        //Set the HTTP HEADER Fields.
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array($authHeader,"Content-Type: text/xml"));
        //CURLOPT_RETURNTRANSFER- TRUE to return the transfer as a string of the return value of curl_exec().
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //CURLOPT_SSL_VERIFYPEER- Set FALSE to stop cURL from verifying the peer's certificate.
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        //Execute the  cURL session.
        $curlResponse = curl_exec($ch);
        //Get the Error Code returned by Curl.
        $curlErrno = curl_errno($ch);
        if ($curlErrno) {
            $curlError = curl_error($ch);
            echo "CURL Error # ".$curlErrno." => ".$curlError."<br />";
            throw new Exception($curlError);
        }//if
        //Close a cURL session.
        curl_close($ch);
        return $curlResponse;
    }
}

function AzureTranslate($key1, $lang_source='en', $lang_target='fr', $text_to_translate_in_lang_source){
    $translatedStr = NULL;
    try {
        //Pass the KEY1 by header without Access Token
        $authHeader = "Ocp-Apim-Subscription-Key: ". $key1;//Grap your key from Azure Portal => [KEY1]
        //Set the params.//
        //List of LANG CODES => https://msdn.microsoft.com/en-us/library/hh456380.aspx
        $fromLanguage = $lang_source;
        $toLanguage   = $lang_target;
        $inputStr     = $text_to_translate_in_lang_source;
        $contentType  = 'text/plain';
        $category     = 'general';
        
        $params = "text=".urlencode($inputStr)."&to=".$toLanguage."&from=".$fromLanguage."&contentType=".$contentType."&category=".$category;
        $translateUrl = "https://api.microsofttranslator.com/v2/Http.svc/Translate?$params";
        
        //Create the Translator Object.
        $translatorObj = new HTTPTranslator();
        
        //Get the curlResponse.
        $curlResponse = $translatorObj->curlRequest($translateUrl, $authHeader);

        //Interprets a string of XML into an object.
        $xmlObj = simplexml_load_string($curlResponse);
        foreach((array)$xmlObj[0] as $val){
            $translatedStr = $val;
        }//foreach
        echo "<table border=2px>";
        echo "<tr>";
        echo "<td><b>From $fromLanguage</b></td><td><b>To $toLanguage</b></td>";
        echo "</tr>";
        echo "<tr><td>".$inputStr."</td><td>".$translatedStr."</td></tr>";
        echo "</table>";
    }//try
    catch (Exception $e) {
        echo "Exception: " . $e->getMessage() . PHP_EOL;
    }//catch
    return $translatedStr;
}
