<?php
/**
 * Генерация письма на почту посредством PHPMailer
 * @api Evolution CMS 1.4.*
 * @param &sendmail, &subjects, &tpl, &values, &from, &validate
 * @return string 
 * @version 0.3
 * @todo Разобраться как парсить чанк с отработанными снипетами [!if!]. Сделать универсальную версию без MODX API.
 * @author bezalkogoln1y-coder
 */

if(!defined('MODX_BASE_PATH')) {
	die('HACK???');
}

/**
 * Функция обработки полей для вывода ошибки
 * @param $data
 * @return string
 */

function lang($data) {
   switch ($data) {
      case 'name':
         return 'Имя';
      break;
      case 'phone':
         return 'Телефон';
      break;
      case 'email':
         return 'E-mail';
      break;
      case 'surname':
         return 'Фамилия';
      break;
      case 'price':
         return 'Цена';
      break;
   }
}

require MODX_BASE_PATH.'/assets/snippets/send/PHPMailer/PHPMailerAutoload.php';

if(!isset($from)) $from = $modx->getConfig('emailsender');
isset($_POST['formname']) ? $nameForm = $_POST['formname']:$nameForm = 'Форма обратной связи';
$names = $_POST;
$names['title'] = $nameForm;
$nameForm = mb_strtolower(str_replace(' ', '', $nameForm));
$vArr = explode(',',$sendmail);
$subjects = explode(',',$subjects);
$mail = new PHPMailer;                          
$mail->CharSet = "utf-8";
$mail->setFrom($from, $nameFrom);
foreach ($vArr as $element) {
	$mail->addAddress($element);	
}
$mail->isHTML(true);
foreach ($subjects as $subject) {
   $mail->Subject = $subject;
   /* Эта проверка нужна если несколько форм у вас (и они отличаются по структуре)
   $flag = mb_strtolower(str_replace(' ', '', $subject));
	if ($nameForm === $flag) {
		$mail->Subject = $subject;
	} */
}


//Validate form
$validate = explode('||',$validate);
foreach ($validate as $element) {
	$element = explode('::',$element);
	if (empty($_POST[$element[0]])) {
      $field = lang($element[0]);
		echo 'Поле '.$field.' обязательно для заполнения';
		return false;
	} 
	$pElement = $_POST[$element[0]];
   $proverka = explode(',',$element[1]);
   foreach ($proverka as $iProverka) {
      if (!empty($iProverka)) {
         if (substr_count($_POST[$element[0]], $iProverka) !== 1) {
            $field = lang($element[0]);
            echo 'Неверный формат '.$field;
            return false;
         }
      }
   }
}

$chunk = $tplPrefix.'__sent';
$tpl = $modx->parseChunk($chunk, $names,'[+', '+]');
$mail->Body = $tpl;

if(!$mail->send()) {
	echo 'no \n';  // если письмо не отправлено
    echo $nameForm.' \n';
	echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
	echo 'ok'; // если письмо отправлено
}
