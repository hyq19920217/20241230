<?php
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// 设置响应头
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="文章导入模板.xlsx"');
header('Cache-Control: max-age=0');

// 创建一个新的 Excel 文档
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// 设置表头
$sheet->setCellValue('A1', '文章标题');
$sheet->setCellValue('B1', '文章内容');
$sheet->setCellValue('C1', '图片（可选，直接在单元格中插入图片）');

// 设置示例数据
$sheet->setCellValue('A2', '示例标题');
$sheet->setCellValue('B2', '这是一篇示例文章的内容...');

// 调整列宽
$sheet->getColumnDimension('A')->setWidth(30);
$sheet->getColumnDimension('B')->setWidth(50);
$sheet->getColumnDimension('C')->setWidth(40);

// 创建 Excel 写入器并输出文件
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit; 