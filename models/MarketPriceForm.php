<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\data\SqlDataProvider;


/**
 * MarketViewForm  - модель 
 */


class MarketPriceForm extends Model
{

    //public $items= array();'items'
	public $count = 0;
	public $orgId = 0;
	public $id = 0;
	public $zakazId = 0;
	
	
	public function rules()
    {
        return [
			[['count', 'id', 'orgId','zakazId' ], 'safe'],
			['zakazId', 'integer'],			
			['orgId', 'integer'],

        ];
		
    }

	public function get_web_page( $url )
	{
    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_USERAGENT      => "spider", // who am i
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT        => 120,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
        CURLOPT_SSL_VERIFYPEER => false     // Disabled SSL Cert checks
    );

    $ch      = curl_init( $url );
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );

    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
    return $header;
	}
	
	public function getPrice()
	{
	
		
		$list = Yii::$app->db->createCommand(
            'SELECT {{%warehouse}}.id,  {{%warehouse}}.title, articul, amount, price, grpGood,ed, edPrice, isOtves, reserved, isValid 
			from {{%warehouse}} where isValid=:isValid ', 
            [
			':isValid'   => 1,
			])->queryAll();
		
		
		for ($i=0;$i<count($list); $i++ )
		{
			$add['articule']=$list[$i]['articul'];
			$add['grpGood'] =$list[$i]['grpGood'];			
			$add['GoodTitle']=$list[$i]['title'];
			$add['RemainCount']=$list[$i]['amount'];
			$add['ed']=$list[$i]['ed'];
			$add['Val']=$list[$i]['price'];
			$add['edVal']=$list[$i]['ed'];
	
			$res[]=$add;	
		}	

	return $res;		
	}
	
	
	public function getPrice1C()
	{
		mb_internal_encoding("UTF-8");
		
		$url = Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 10')->queryScalar();
		$url.="1";

		
		/*список организаций через ','*/
		$val= Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 11')->queryScalar();
		
		$orgList = str_getcsv($val,",");
		
		/*список складов через ','*/
		$val= Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 12')->queryScalar();
		$scladList = str_getcsv($val,",");
				
        $page = $this->get_web_page( $url);			
		$content = mb_split('\r\n', $page['content'] );		
		
		$parse = str_getcsv($content[0],",");	
		$rowNum=$parse[0];
		
		$n=count($content);
		$res=array();
		for ($i=1; $i<$n;$i++ )
		{
			$parse = str_getcsv($content[$i],",");	
			if (! in_array($parse[1],$orgList) ) {continue;}
			if (! in_array($parse[2],$scladList) ) {continue;}
			
			$add['articule']=$parse[3];
			$add['grpGood'] =$parse[4];			
			$add['GoodTitle']=$parse[0];
			
			/*$parse[1] = str_replace(',', '.',$parse[1]); 			
			$parse[1] =(float)str_replace(' ', '',$parse[1]); */
			$add['RemainCount']=$parse[1];
			$add['ed']=$parse[2];			
			$add['Val']=(float)str_replace(',', '.',$parse[3]); 
			$add['edVal']=$parse[4];
			$res[]=$add;			
		}
		

		
		return $res;

	}
	
	
	public function getGooglePrice()
	{
		mb_internal_encoding("UTF-8");
		$url = Yii::$app->db->createCommand(
            'SELECT keyValue FROM {{%config}} WHERE id = 1')->queryScalar();
		$url.="&single=true&output=csv";

        $page = $this->get_web_page( $url);			
		$content = mb_split('\r\n', $page['content'] );		
		
		$n=count($content);
		$res=array();

		for ($i=1; $i<$n;$i++ )
		{
			$parse = str_getcsv($content[$i],",");	
			$add['GoodTitle']=$parse[0];
			
			/*$parse[1] = str_replace(',', '.',$parse[1]); 			
			$parse[1] =(float)str_replace(' ', '',$parse[1]); */
			$add['RemainCount']=$parse[1];
			$add['ed']=$parse[2];			
			$add['Val']=(float)str_replace(',', '.',$parse[3]); 
			$add['edVal']=$parse[4];
			$res[]=$add;			
		}
		

		
		return $res;

	}
	
	/****/
 }
