<?php
/**
 * Created by PhpStorm.
 * User: zarpom
 * Date: 03.04.18
 * Time: 2:03
 *///include the file that loads the PhpSpreadsheet classes
require 'vendor/autoload.php';

//include the classes needed to create and write .xlsx file
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class FileReport
{

    public static function createTextForFile()
    {


    }

    public static function createFileReport($report)
    {
//object of the Spreadsheet class to create the excel data
        $spreadsheet = new Spreadsheet();

        //var_dump($report);
//add some data in excel cells
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', '№')
            ->setCellValue('B1', 'Название проверки')
            ->setCellValue('C1', 'Статус')
            ->setCellValue('E1', 'Текущее состояние');
        $count = 0;
        $i=0;
foreach ($report as $r) {
    if (empty($r)){
        continue;
    }
    $i+=2;
    $spreadsheet->setActiveSheetIndex(0)   ->
    setCellValue('A' . ($i + 1), ++$count)
        ->setCellValue('B' . ($i + 1), $r["verification_title"])
        ->setCellValue('C' . ($i + 1), $r["status"])
        ->setCellValue('D' . ($i + 2), "состояние")
        ->setCellValue('D' . ($i + 1), 'рекомендации')
        ->setCellValue('E' . ($i + 2), $r["state"])
        ->setCellValue('E' . ($i + 1), $r["recommen"]);


    $spreadsheet->getActiveSheet()->mergeCells('A' . ($i + 1) . ':A' . ($i + 2));
    $spreadsheet->getActiveSheet()->mergeCells('B' . ($i + 1) . ':B' . ($i + 2));
    $spreadsheet->getActiveSheet()->mergeCells('C' . ($i + 1) . ':C' . ($i + 2));


        }//set style for A1,B1,C1 cells
        $cell_st = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => ['bottom' => ['style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM]]
        ];



//set columns width
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(40);

        $spreadsheet->getActiveSheet()->setTitle('Simple'); //set a title for Worksheet

//make object of the Xlsx class to save the excel file
        $writer = new Xlsx($spreadsheet);
        $fxls = 'excel-file_1.xlsx';
        $writer->save($fxls);
    }
}