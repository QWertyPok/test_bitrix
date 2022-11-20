<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php"); 

$lines = fopen($_SERVER['DOCUMENT_ROOT']."/upload/test.csv", "r");    //путь к файлу
$IBLOCK_ID = 6;           											                      // ID инфоблока 

if ($lines) {
    $countr = 0;
    $keys = array();
    $data = array();
    while (($buffer = fgets($lines)) !== false) {
        $countr++;  
        $buffer = str_replace(array("\r\n", "\r", "\n"), '', $buffer); 
        $str =explode(";", $buffer);
        if ($countr ==1){                          // перый строка файла берем как ключ к значению элемента
            $keys = $str;
        }
        else{                                     // остальные строки передаем как значение
            $el = array();
            foreach ($str as $key=>$item){
                $el[$keys[$key]] = $item;
            }
            $data[] = $el;
        }
    }  fclose($lines); 

    CModule::IncludeModule("iblock");                       
	$elementIterator = \Bitrix\Iblock\ElementTable::getList(['select' => ['*',], 'filter' => ['=IBLOCK_ID' => $IBLOCK_ID,]]);
	$PRODUCT_ID = array();
	 foreach ($elementIterator->fetchAll() as $element) {        // берем все ID элементов инфоблокоа 
      array_push($PRODUCT_ID, $element["ID"] ); 
	}
	$f = 0; 
    foreach ($data as  $el){ 
        $bs = new CIBlockElement;  
        $PROP = array();  
        $PROP['PROP1'] = $el['prop1'];
        $PROP['PROP2'] = $el['prop2']; 
 
		if($PRODUCT_ID[$f]){                            // если элемент есть то обнавляем
			$arFields = Array(
				"MODIFIED_BY"    => $USER->GetID(), 
				"IBLOCK_SECTION" => false,          
				"ACTIVE"         => "Y",       
				"NAME" 			 => $el['name'], 
				"PREVIEW_TEXT"   => $el['preview_text'],
				"DETAIL_TEXT"    => $el['detail_text'],
				"PROPERTY_VALUES"=> $PROP, 
				); 

			 $bs->Update($PRODUCT_ID[$f], $arFields);
			 $f++;  
		}else{                                             // если элемент нет то создаём
			$arFields = Array(
			" MODIFIED_BY"    => $USER->GetID(),  
			"IBLOCK_SECTION_ID" => false,           
			"IBLOCK_ID"      => $IBLOCK_ID, 
			"ACTIVE"         => "Y",     
			"NAME"			 => $el['name'],
			"PREVIEW_TEXT"   => $el['preview_text'],
			"DETAIL_TEXT"    => $el['detail_text'],
			"PROPERTY_VALUES"=> $PROP,
			);
	  
			$bs->Add($arFields);  
			$f++; 
		 } 
	}while($PRODUCT_ID[$f]){                         // удаляем лишние элементы  
 \CIBlockElement::Delete($PRODUCT_ID[$f]); 
		$f++; 
} 
}
?>
