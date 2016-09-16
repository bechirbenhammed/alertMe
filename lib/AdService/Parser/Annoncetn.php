<?php

namespace AdService\Parser;

use AdService\Filter;
use AdService\Ad;

class Annoncetn extends AbstractParser
{
    protected static $months = array(
        "jan" => 1, "fév" => 2, "mars" => 3, "avr" => 4,
        "mai" => 5, "juin" => 6, "juillet" => 7, "août" => 8,
        "sept" => 9, "oct" => 10, "nov" => 11,
        "déc" => 12
    );

    protected $scheme;

    public function process($content, Filter $filter = null, $scheme = "http") {
        if (!$content) {
            return;
        }
        $this->scheme = $scheme;
        $this->loadHTML($content);
        $timeToday = strtotime(date("Y-m-d")." 23:59:59");
        $dateYesterday = $timeToday - 24*3600;
        $ads = array();
        $adNodes = $this->getElementsByTagName("tr");
        
        foreach ($adNodes AS $result) {
            $class = ($result->getAttribute("class")); 
            if (false !== strpos($class, "Tableau1")) {
                
                    $ad = new Ad();
                    $i=0;
                    foreach ($result->getElementsByTagName("td") AS $tdResult) {
                        if($tdResult->getAttribute("bgcolor")){
                            continue;
                        } else {
                           $i++;
                           if($i == 1){                        
                              $city = $tdResult->getElementsByTagName("a");
                              foreach ($city as $ct){
                                  $ad->setCity($ct->nodeValue);
                                  $arrayCountry =(string) $ct->getAttribute("onmouseover");
                                  $arrayGouvernorat = explode('Gouvernorat',$arrayCountry);
                                  $arrayDelegation = explode('Délégation',$arrayGouvernorat[1]);
                                  $country = $arrayDelegation[0]; 
                                  $cityArray = explode('Localité',$arrayDelegation[1]);
                                  $ad->setCountry(str_replace('<br/>','',(str_replace(':','',$cityArray[0]).$country)));
                               }
                               
                           } else if($i == 2) {
                              $ad->setCategory(utf8_decode($tdResult->nodeValue)); 
                               
                           } else if($i == 4) {
                              $resulTag = $tdResult->getElementsByTagName("a");
                              foreach ($resulTag as $ct){          
                                  $arraydesc =(string) $ct->getAttribute("onmouseover");
                                  $arrayfinalDescription = explode("return escape('",$arraydesc);
                                  $ad->setDescription(str_replace('<br/>',' ',(str_replace("');",'',  utf8_decode($arrayfinalDescription[1])))));
                                  
                                  $ad->setLink($this->formatLink($ct->getAttribute("href")))
                                    ->setTitle(utf8_decode($ct->nodeValue))
                                    ->setLinkMobile(str_replace(
                                        array("http://www.", "https://www."),
                                        array("http://mobile.", "https://mobile."),
                                        $ad->getLink()
                                  ));
                                  $arrayId = explode('=',$ct->getAttribute("href")); 
                                  $ad->setId($arrayId[1]);
                               }
                           } else if($i == 5) {
                              $ad->setPrice((int)utf8_decode($tdResult->nodeValue)); 
                           } else if($i == 6) {
                                  $ad->setDate(utf8_decode($tdResult->nodeValue));
                           }
                        }
                    }
                  $ads[$ad->getId()] = $ad;
            }    
        }
        return $ads;
    }

    protected function formatLink($link)
    {
        $link = "http://www.tunisie-annonce.com/".$link;
        return $link;
    }
}
