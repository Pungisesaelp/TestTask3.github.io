<?php
/**
 * Created by PhpStorm.
 * User: zarpom
 * Date: 04.04.18
 * Time: 12:54
 */
$file = 'excel-file_1.xlsx';

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="excel-file_1.xlsx"');
readfile($file);