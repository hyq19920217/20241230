<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="vocabulary_template.xlsx"');
header('Cache-Control: max-age=0');

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// 设置表头
$headers = ['单词*', '词性*', '中文含义*', '英语例句*', '例句翻译*', '备注'];
foreach (range('A', 'F') as $i => $col) {
    $sheet->setCellValue($col.'1', $headers[$i]);
    $sheet->getStyle($col.'1')->getFont()->setBold(true);
}

// 添加示例数据
$example = [
    ['agile', 'adj.', '敏捷的，灵活的', 'We follow agile development methodology.', '我们遵循敏捷开发方法论。', '必填字段已标*号'],
];
$sheet->fromArray($example, null, 'A2');

// 设置列宽
foreach (range('A', 'F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$writer = new Xlsx($spreadsheet);
$writer->save('php://output'); 