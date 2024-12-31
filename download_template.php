<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="vocabulary_template.xlsx"');
header('Cache-Control: max-age=0');

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// 设置标题行
$sheet->setCellValue('A1', '单词');
$sheet->setCellValue('B1', '词性');
$sheet->setCellValue('C1', '中文含义');
$sheet->setCellValue('D1', '英语例句');
$sheet->setCellValue('E1', '例句翻译');

// 设置列宽
$sheet->getColumnDimension('A')->setWidth(20);
$sheet->getColumnDimension('B')->setWidth(10);
$sheet->getColumnDimension('C')->setWidth(30);
$sheet->getColumnDimension('D')->setWidth(40);
$sheet->getColumnDimension('E')->setWidth(40);

// 添加示例数据
$sheet->setCellValue('A2', 'iteration');
$sheet->setCellValue('B2', 'n.');
$sheet->setCellValue('C2', '迭代');
$sheet->setCellValue('D2', 'We need to complete three iterations before the final release.');
$sheet->setCellValue('E2', '在最终发布前我们需要完成三次迭代。');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output'); 