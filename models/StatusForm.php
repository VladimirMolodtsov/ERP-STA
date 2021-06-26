<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;

/**
 *
 */
class StatusForm extends Model
{
	/*
	  Доступно к работе – общее число клиентов доступных для обзвона.
	*/
    public function prepareStatus()
    {
		$ret = Yii::$app->db->createCommand('SELECT {{%orglist}}.id, {{%orglist}}.title, razdel, {{%orglist}}.have_phone,  
		{{%adreslist}}.area, {{%adreslist}}.city, {{%adreslist}}.x, {{%adreslist}}.y,
		isFirstContact, isReject, isFirstContactFinished, 
		{{%orglist}}.contactFIO, {{%orglist}}.contactPhone, {{%orglist}}.contactEmail, 
		isNeedReject, isPreparedForSchet,
		isSchetFinished, schetINN, schetNumber, schetDate		
		from {{%orglist}} left join {{%adreslist}} on {{%orglist}}.id = {{%adreslist}}.ref_org
		order by {{%orglist}}.id
		')->queryAll();
		
		
/*		   print_r($ret);
		   return;
		   */
		$uploadPath=(realpath(dirname(__FILE__)))."/../uploads/";
		$fname =$uploadPath."report_status".".csv";
		$fp = fopen($fname, 'w');
		
		//fputs($fp,"asep=;\n");
		
		$col_title = array (
		iconv("UTF-8", "Windows-1251", "Номер"),
		iconv("UTF-8", "Windows-1251","Организация"),
		iconv("UTF-8", "Windows-1251","Раздел"),
		iconv("UTF-8", "Windows-1251","Число телефонов"),
		iconv("UTF-8", "Windows-1251","Область"),
		iconv("UTF-8", "Windows-1251","Город"),
		"X", 
		"Y",
		iconv("UTF-8", "Windows-1251","Первый контакт"),
		iconv("UTF-8", "Windows-1251","Отказ"),
		iconv("UTF-8", "Windows-1251","Первый контакт завершен"),
		iconv("UTF-8", "Windows-1251","ФИО Снабженца"),
		iconv("UTF-8", "Windows-1251","Телефон Снабженца"),
		iconv("UTF-8", "Windows-1251","contactEmail"),
		iconv("UTF-8", "Windows-1251","Отказ по потребностям"),
		iconv("UTF-8", "Windows-1251","Готов к счету"),
		iconv("UTF-8", "Windows-1251","Счет получен"),
		iconv("UTF-8", "Windows-1251","ИНН"),
		iconv("UTF-8", "Windows-1251","Номер счета"),
		iconv("UTF-8", "Windows-1251","Дата счета"),
		iconv("UTF-8", "Windows-1251","Даты контактов"),
		iconv("UTF-8", "Windows-1251","ФИО контактов"),
		iconv("UTF-8", "Windows-1251","Комментарии"),
		iconv("UTF-8", "Windows-1251","Телефон контакта"), 
		iconv("UTF-8", "Windows-1251","Известные телефоны"), 
		iconv("UTF-8", "Windows-1251","Известные E-Mail")
		
		);
	    fputcsv($fp, $col_title, ",");  
		for($i=0; $i< count($ret); $i++ )
		{
		
		$cont = Yii::$app->db->createCommand ('SELECT  {{%contact}}.ref_org,  
		{{%contact}}.contactDate, 
		{{%contact}}.contactFIO, 
		{{%contact}}.note, 
		{{%phones}}.phone  
		FROM {{%contact}}
		left join {{%phones}} on {{%contact}}.ref_phone = {{%phones}}.id
		Where {{%contact}}.ref_org =:ref_org', [':ref_org' => $ret[$i]['id']])->queryAll();
		
			$contact_date  = "";
			$contact_fio   = "";
			$contact_note  = "";
			$contact_phone = "";
			
			for($j=0; $j< count($cont); $j++ )
			{
				if (empty($cont[$j]['contactFIO']) ){$contact_fio .= "-";} 
												else{$contact_fio .= $cont[$j]['contactFIO'];} 				   
												$contact_fio .= ";";

				if (empty($cont[$j]['contactDate']) ){$contact_date .= "-";} 
												else{$contact_date .= $cont[$j]['contactDate'];} 				   
												$contact_date .= ";";
				
				if (empty($cont[$j]['note'])  ){$contact_note .= "-";} 
												else{$contact_note .= $cont[$j]['note'];} 				   
												$contact_note .= ";";								
				
				if (empty($cont[$j]['phone']) ){$contact_phone .= "-";} 
												else{$contact_phone .= $cont[$j]['phone'];} 				   
												$contact_phone .= ";";								
			}
			
		$phone_list = Yii::$app->db->createCommand ('SELECT GROUP_CONCAT(phone SEPARATOR ";" )
		FROM {{%phones}}  where ref_org  =:ref_org', [':ref_org' => $ret[$i]['id']])->queryScalar();
				
		$email_list = Yii::$app->db->createCommand ('SELECT GROUP_CONCAT(email SEPARATOR ";" )
		FROM {{%emaillist}}  where ref_org  =:ref_org', [':ref_org' => $ret[$i]['id']])->queryScalar();
			
			
			$list = array 
			(
			iconv("UTF-8", "Windows-1251",$ret[$i]['id']), 
			iconv("UTF-8", "Windows-1251",$ret[$i]['title']), 
			iconv("UTF-8", "Windows-1251",$ret[$i]['razdel']), 
			iconv("UTF-8", "Windows-1251",$ret[$i]['have_phone']),			
		    iconv("UTF-8", "Windows-1251",$ret[$i]['area']), 
			iconv("UTF-8", "Windows-1251",$ret[$i]['city']), 
			iconv("UTF-8", "Windows-1251",$ret[$i]['x']), 
			iconv("UTF-8", "Windows-1251",$ret[$i]['y']),
			iconv("UTF-8", "Windows-1251",$ret[$i]['isFirstContact']), 
			iconv("UTF-8", "Windows-1251",$ret[$i]['isReject']), 
			iconv("UTF-8", "Windows-1251",$ret[$i]['isFirstContactFinished']), 
		    iconv("UTF-8", "Windows-1251",$ret[$i]['contactFIO']), 
			iconv("UTF-8", "Windows-1251",$ret[$i]['contactPhone']), 
			iconv("UTF-8", "Windows-1251",$ret[$i]['contactEmail']), 
		    iconv("UTF-8", "Windows-1251",$ret[$i]['isNeedReject']), 
			iconv("UTF-8", "Windows-1251",$ret[$i]['isPreparedForSchet']),		
			iconv("UTF-8", "Windows-1251",$ret[$i]['isSchetFinished']), 
			iconv("UTF-8", "Windows-1251",$ret[$i]['schetINN']), 
			iconv("UTF-8", "Windows-1251",$ret[$i]['schetNumber']), 
			iconv("UTF-8", "Windows-1251",$ret[$i]['schetDate']),
			$contact_date,
			iconv("UTF-8", "Windows-1251",$contact_fio), 
			iconv("UTF-8", "Windows-1251",$contact_note),
			iconv("UTF-8", "Windows-1251",$contact_phone),
			iconv("UTF-8", "Windows-1251",$phone_list),
			iconv("UTF-8", "Windows-1251",$email_list),						
			);
			
			fputcsv($fp, $list, ",");  
		}
		
		fclose($fp);
		
		return "../uploads/report_status".".csv";
    }

	
	
}
