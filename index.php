<?php
require 'vendor/autoload.php';

include_once __DIR__ . '/FileReport.php';
use FileReport;
/**
 * Created by PhpStorm.
 * User: zarpom
 * Date: 02.04.18
 * Time: 17:44
 */
$url = $_POST['url'] . "/robots.txt";


$report = createTextReport($url);
$count =0;
 foreach ($report as $r) {
    if (empty($r)) {
        continue;
    }
    ++$count;
    echo "$count)Название проверки: " . $r["verification_title"] . "<br>";
    echo "Статус: " . $r["status"] . "<br>";
    echo "Состояние: " . $r["state"] . "<br>";
    echo "Рекомендации: " . $r["recommen"] . "<br> <hr><hr>";
}
echo "<a href=\"download.php\">Скачать отчет</a>";
//header("Location: view.php"); /* Перенаправление браузера */

FileReport::createFileReport($report);

/**
 * @param $url
 * @return array
 */
function createTextReport($url)
{
    if (getExist($url)) {
        $exist = array(
            "status" => "ОК",
            "state" => "Файл robots.txt присутствует",
            "recommen" => "Доработки не требуются"
        );
    } else {
        $exist = array(
            "status" => "ОШИБКА",
            "state" => "Файл robots.txt отсутствует",
            "recommen" => "Программист: Создать файл robots.txt и разместить его на сайте."
        );
    }

    $exist["verification_title"] = "Проверка наличия файла robots.txt";
    if (searchDirective("Host", read($url))) {
        $searchHostFile = array(
            "status" => "ОК",
            "state" => "Директива Host указана",
            "recommen" => "Доработки не требуются"
        );

        $countOfHost = array("verification_title" => "Проверка количества директив Host, прописанных в файле");
        if (getCountOfDirective("Host", read($url)) === 1) {
            $countOfHost["status"] = "ОК";
            $countOfHost["state"] = "В файле прописана 1 директива Host";
            $countOfHost["recommen"] = "ОК";
            $countOfHost["status"] = "Доработки не требуются";
        } else {
            $countOfHost = array(
                "status" => "ОШИБКА",
                "state" => "В файле прописано несколько директив Host",
                "recommen" => "Программист: Директива Host должна быть указана в файле толоко 1 раз. 
    Необходимо удалить все дополнительные директивы Host и оставить только 1, 
    корректную и соответствующую основному зеркалу сайта");
        }

    } else {
        $searchHostFile = array(
            "status" => "ОШИБКА",
            "state" => "В файле robots.txt не указана директива Host",
            "recommen" => "Программист: Для того, чтобы поисковые системы знали, 
        какая версия сайта является основных зеркалом, необходимо прописать адрес
         основного зеркала в директиве Host. В данный момент это не прописано. 
         Необходимо добавить в файл robots.txt директиву Host. Директива Host 
         задётся в файле 1 раз, после всех правил."
        );
    }
    $searchHostFile["verification_title"] = "Проверка указания директивы Host";
    $remoteSizeOfFile = getRemoteFileSize($url);
    if ($remoteSizeOfFile <= 32768) {
        $sizeFile = array(
            "status" => "ОК",
            "state" => "Размер файла robots.txt составляет $remoteSizeOfFile байта, что находится в пределах допустимой нормы",
            "recommen" => "Доработки не требуются");
    } else {
        $sizeFile = array(
            "status" => "ОШИБКА",
            "state" => "Размера файла robots.txt составляет $remoteSizeOfFile байт, что превышает допустимую норму",
            "recommen" => "Программист: Максимально допустимый размер файла robots.txt составляем 32 кб. Необходимо 
        отредактировть файл robots.txt таким образом, чтобы его размер не превышал 32 Кб");
    }
    $sizeFile["verification_title"] = "Проверка размера файла robots.txt";

    if (searchDirective("Sitemap", read($url))) {
        $searchSitemap = array(
            "status" => "ОК",
            "state" => "Директива Sitemap указана",
            "recommen" => "Доработки не требуются");
    } else {
        $searchSitemap = array(
            "status" => "ОШИБКА",
            "state" => "В файле robots.txt не указана директива Sitemap",
            "recommen" => "Программист: Добавить в файл robots.txt директиву Sitemap");
    }
     $searchSitemap["verification_title"] = "Проверка указания директивы Sitemap";
    $requestCode = getRequest($url);
    if ($requestCode === 200) {
        $request = array(
            "status" => "ОК",
            "state" => "Файл robots.txt отдаёт код ответа сервера 200",
            "recommen" => "Доработки не требуются");
    } else {
        $searchSitemap = array(
            "status" => "ОШИБКА",
            "state" => "При обращении к файлу robots.txt сервер возвращает код ответа ($requestCode)",
            "recommen" => "Программист: Файл robots.txt должны отдавать код ответа 200, иначе файл не будет обрабатываться. 
        Необходимо настроить сайт таким образом, чтобы при обращении к файлу robots.txt сервер возвращает код ответа 200");
    }
    $request["verification_title"] = "Проверка кода ответа сервера для файла robots.txt";
    $reportText = array(

        "exist_file" => $exist,
        "search_host_directive" => $searchHostFile,
        "count_of_host_directive" => $countOfHost,
        "size_of_file" => $sizeFile,
        "search_sitemap_directive" => $searchSitemap,
        "request" => $request
    );
    return $reportText;
}

function getCountOfDirective($directive, $text)
{
    return mb_substr_count("$text", "$directive");
}


function getRequest($url)
{
    $urlHeaders = @get_headers($url);
    if (strpos($urlHeaders[0], '200')) {
        $request = 200;
    } else {
        $request = $urlHeaders[0];
    }

    return $request;
}

function searchDirective($directive, $text)
{
    $re = "/\n($directive)/";
    $request = preg_match_all($re, $text, $matches, PREG_SET_ORDER, 0);
// Print the entire match result
    return $request;
}

function read($url)
{
    $file = fopen($url, "r");
    if (!$file) {
        echo "<p>Невозможно открыть удаленный файл.\n";
        exit;
    }
    $text = "";
    while (!feof($file)) {
        $text .= fgets($file, 1024) . PHP_EOL;

    }
    return $text;
}


function getExist($url)
{
    $urlHeaders = @get_headers($url);
    $request = false;
    if (strpos($urlHeaders[0], '200')) {
        $request = true;
    } else {
        $request = false;
    }
    return $request;
}

function getRemoteFileSize($url)

{
    $fsize = 0;
    $fh = fopen($url, "r");
    while (($str = fread($fh, 1024)) != null) $fsize += strlen($str);

    return $fsize;
}
  function pushButton()
{
    if (isset($_POST['button'])) {
        file_force_download(excel-file_1.xlsx);
    }
}
function file_force_download($file) {
    if (file_exists($file)) {
        // сбрасываем буфер вывода PHP, чтобы избежать переполнения памяти выделенной под скрипт
        // если этого не сделать файл будет читаться в память полностью!
        if (ob_get_level()) {
            ob_end_clean();
        }
        // заставляем браузер показать окно сохранения файла
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        // читаем файл и отправляем его пользователю
        if ($fd = fopen($file, 'rb')) {
            while (!feof($fd)) {
                print fread($fd, 1024);
            }
            fclose($fd);
        }
        exit;
    }
}

