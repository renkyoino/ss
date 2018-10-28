<?php

//require realpath('vendor/autoload.php');



use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;



class Exceloutput extends CI_Controller 
{
    function index() {

        $CustomerName=array(
            20=>"アートバンライン 舞洲",
            2=>"シューワ（株）",
            21=>"東和運輸株式会社 本社",
        );

       
        $ClosedYear=array(
            0=>2018,
            1=>2019,
            2=>2020,
            3=>2021,
            4=>2022,

        );

        $ClosedMonth=array(
            1=>1,
            2=>2,
            3=>3,
            4=>4,
            5=>5,
            6=>6,
            7=>7,
            8=>8,
            9=>9,
            10=>10,
            11=>11,
            12=>12,
        );

        $invoice_data = $this->get_csv('inputfile/Invoice.csv');
        $invoice_detail_data = $this->get_csv('inputfile/InvoiceDetail.csv');
        $customer_data= $this->get_csv('inputfile/Customer.csv');

        $request=$_POST;

        $customer_id=$request['CustomerId'];
        $closed_year=$ClosedYear[$request['ClosedYear']];
        $closed_month=$ClosedMonth[$request['ClosedMonth']];
        $customer_name=$CustomerName[$customer_id];

        $customer=array();

        for($i=0;$i<count($customer_data);$i++):

            if($customer_data[$i]['CustomerId']==$customer_id):

                $customer=$customer_data[$i];

                break;

            endif;

        endfor;

        $invoice=array();

        for($i=0;$i<count($invoice_data);$i++):

            $year=substr($invoice_data[$i]['Date'],0,4);

            $month=substr($invoice_data[$i]['Date'],5,2);

            if($invoice_data[$i]['CustomerId']==$customer_id AND $year==$closed_year AND $month==$closed_month):

                $invoice=$invoice_data[$i];

                break;

            endif;
            
        endfor;

        $invoice_detail=array();

        for($i=0;$i<count($invoice_detail_data);$i++):

            if($invoice_detail_data[$i]['InvoiceId']==$invoice['Id']):

                $invoice_detail[]=$invoice_detail_data[$i];

            endif;
            
        endfor;

        foreach ((array) $invoice_detail as $key => $value) {
            $sort[$key] = $value['HouseCardNumber'];
        }
        
        $invoice_num=array_multisort($sort, SORT_ASC, $invoice_detail);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        //デフォルト罫線
        $sheet->getStyle('A1:Q500')
            ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);


        
        //デフォルト文字サイズの設定
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        

        //デフォルト行幅の設定
        for($default_row=1;$default_row<500;$default_row++):
            $sheet->getRowDimension($default_row)->setRowHeight(15);
        endfor;


        //1ページ目(請求書)

        //セルのマージ
        $sheet->mergecells('A2:B2');
        $sheet->mergecells('C2:E2');
        $sheet->mergecells('G1:J2');
        $sheet->mergecells('H4:J4');
        $sheet->mergecells('I5:P9');
        $sheet->mergecells('B9:C9');
        $sheet->mergecells('B10:E10');
        $sheet->mergecells('B11:E11');
        $sheet->mergecells('I11:J11');
        $sheet->mergecells('K11:O11');
        $sheet->mergecells('K12:O12');
        $sheet->mergecells('C13:F13');
        $sheet->mergecells('K13:O13');
        $sheet->mergecells('J15:P15');
        $sheet->mergecells('J16:P16');
        $sheet->mergecells('J17:P17');
        $sheet->mergecells('J18:P18');
        $sheet->mergecells('B19:F19');
        $sheet->mergecells('B20:F20');
        $sheet->mergecells('H20:K20');
        $sheet->mergecells('H21:K21');
        $sheet->mergecells('A24:C24');
        $sheet->mergecells('A25:C25');
        $sheet->mergecells('D24:F24');
        $sheet->mergecells('D25:F25');
        $sheet->mergecells('G24:I24');
        $sheet->mergecells('G25:I25');
        $sheet->mergecells('J24:M24');
        $sheet->mergecells('J25:M25');
        $sheet->mergecells('N24:P24');
        $sheet->mergecells('N25:P25');
        $sheet->mergecells('A26:C27');
        $sheet->mergecells('D26:F27');
        $sheet->mergecells('G26:I27');
        $sheet->mergecells('J26:M27');
        $sheet->mergecells('N26:P27');
        $sheet->mergecells('H29:I29');
        $sheet->mergecells('K29:N29');
        $sheet->mergecells('G31:H31');
        $sheet->mergecells('G32:H32');
        $sheet->mergecells('G33:H33');
        $sheet->mergecells('I32:K32');
        $sheet->mergecells('I33:K33');
        $sheet->mergecells('N32:O32');
        $sheet->mergecells('N33:O33');
        $sheet->mergecells('C36:N37');
        $sheet->mergecells('B39:C39');
        $sheet->mergecells('A40:D41');
        $sheet->mergecells('E40:F41');
        $sheet->mergecells('G40:H41');
        $sheet->mergecells('I40:L41');
        $sheet->mergecells('M40:N41');
        $sheet->mergecells('O40:P41');
        $sheet->mergecells('G40:H41');
        $sheet->mergecells('G40:H41');

        $row=42;

        $row_table_start=$row;

        $row_product_sum=$row+15;
        $row_oiltax_sum=$row_product_sum+1;
        $row_consum_sum=$row_oiltax_sum+1;

        for($cnt=0; $cnt<18; $cnt++):

            $sheet->mergeCells('A'.($row).':D'.($row));
            $sheet->mergeCells('E'.($row).':F'.($row));
            $sheet->mergeCells('G'.($row).':H'.($row));
            $sheet->mergeCells('I'.($row).':L'.($row));
            $sheet->mergeCells('M'.($row).':N'.($row));
            $sheet->mergeCells('O'.($row).':P'.($row));
        
            $row++;

        endfor;

        $sheet->getStyle('A'.$row_table_start.':P'.($row-1))
        ->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $sheet->getStyle('A'.$row_table_start.':P'.($row-1))
        ->getBorders()->getVertical()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        
        $sheet->mergeCells('M'.($row).':N'.($row));
            
        
        //ヒナ型部分の作成
        $sheet->getStyle('A1:P9')
                 ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('A2', '請求書NO.');
        $sheet->getStyle('G1')->getFont()->setSize(24);
        $sheet->getStyle('G1')
        ->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->setCellValue('G1', '請求書');
        $sheet->setCellValue('H4',$closed_year.'年'.$closed_month.'日末日');
        $sheet->setCellValue('N2', 'ページ');
        $sheet->setCellValue('G4', '請求日');
        $sheet->getStyle('I5')->getFont()->setSize(36);
        $sheet->setCellValue('I5', 'シューワ株式会社');
        $sheet->setCellValue('B9', $customer['CustomerZipCode']);
        $sheet->setCellValue('B10', $customer['CustomerAddress1']);
        $sheet->setCellValue('B11', $customer['CustomerAddress2']);
        $sheet->setCellValue('I11', '〒599-8242');
        $sheet->setCellValue('K11', '大阪府堺市中区陶器北244-5');
        $sheet->setCellValue('K12', 'TEL:072-236-8846');
        $sheet->setCellValue('C13', $customer['CustomerName']);
        $sheet->setCellValue('K13', 'FAX:072-236-6588');
        $sheet->setCellValue('F14','様');
        $sheet->getStyle('J15:P18')
        ->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('J15:P18')
        ->getBorders()->getVertical()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->setCellValue('B19', ' 毎度お引き立て頂き、有難うございます。');
        $sheet->setCellValue('B20', '下記の通りご請求申し上げます。');
        $sheet->setCellValue('H20', ' 本書に関してのお問い合わせは、');
        $sheet->setCellValue('H21', '上記の担当者までお願い致します。');
        $sheet->getStyle('A24:P27')
        ->getBorders()->getVertical()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('A24:C25')
        ->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('A24:P25')
        ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('A24', '前月請求額');
        $sheet->setCellValue('A25', '(A)');
        $sheet->getStyle('A26:C27')
        ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('D24:F25')
        ->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->setCellValue('D24', '前月ご入金額');
        $sheet->setCellValue('D25', '(B)');
        $sheet->getStyle('D26:F27')
        ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('G24:I25')
        ->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->setCellValue('G24', '繰越残高');
        $sheet->setCellValue('G25', '(C=A-B)');
        $sheet->getStyle('G26:I27')
        ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('J24:M25')
        ->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->setCellValue('J24', '当月ご請求額');
        $sheet->setCellValue('J25', '(D)');
        $sheet->getStyle('J26:M27')
        ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('N24:P25')
        ->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->setCellValue('N24', '当月ご請求残高');
        $sheet->setCellValue('N25', '(E=C+D)');
        $sheet->getStyle('N26:P27')
        ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        $sheet->getStyle('A28:P41')
        ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('H29', 'お支払予定日');
        $sheet->setCellValue('G31', '取引銀行');
        $sheet->getStyle('C35:N37')
        ->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('C36:N37')
        ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('C36:N37')
        ->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);
        $sheet->setCellValue('C35', '<御案内>');
        $sheet->setCellValue('B39', '[商品別御請求額]');
        $sheet->getStyle('A40:P41')
        ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->setCellValue('A40', '商品名');
        $sheet->setCellValue('E40', '数量');
        $sheet->setCellValue('G40', '金額');
        $sheet->setCellValue('I40', '商品名');
        $sheet->setCellValue('M40', '数量');
        $sheet->setCellValue('O40', '金額');

        $sheet->setCellValue('I'.($row_product_sum), '**商品計**');
        $sheet->setCellValue('I'.($row_oiltax_sum), '**軽油税計**');
        $sheet->setCellValue('I'.($row_consum_sum), '**消費税計**');
 
        $sheet->getRowDimension($row++)->setRowHeight(50);
        $sheet->getRowDimension($row++)->setRowHeight(25);        
        //2ページ目(請求明細書)
        //これ以降はイテレータがベースになるので、rowごとの処理(マージ、フォント、内容、罫線)
        
        $car=array();

        $car_num=-1;

        for($i=0;$i<count($invoice_detail);$i++):

            if($car_num!=$invoice_detail[$i]['HouseCardNumber']):

                $car[]=$invoice_detail[$i]['HouseCardNumber'];
                $car_num=$invoice_detail[$i]['HouseCardNumber'];

            endif;
            
        endfor;
        
        $car_sum=count($car);
        
        $car_num=0;

        $page_all=ceil($car_sum/3)+1;

        $item_cnt=0;

        $all_total=0;

        for($page_number=2;$page_number<=$page_all;$page_number++):


        //セルのマージ
        //ページの最初の行番号

            $sheet->getStyle('A'.$row.':P'.($row+3))
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->mergecells('G'.$row++.':J'.$row--);
            $sheet->getStyle('G'.$row)->getFont()->setSize(24);
            $sheet->setCellValue('G'.$row++, '請求明細書');
            $sheet->getRowDimension($row++)->setRowHeight(25);        
            $sheet->setCellValue('N'.$row, 'ページ');
            $sheet->setCellValue('O'.$row, $page_number.'/'.$page_all);
        
            $sheet->mergecells('C'.$row.':D'.$row++);
            $sheet->setCellValue('A'.$row, '得意先');
            $sheet->mergecells('B'.$row.':H'.$row);
            $sheet->getStyle('A'.$row.':H'.$row)
            ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);


            for($table=1;$table<=3;$table++):

                $total=0;


                $row++;
                
                $sheet->setCellValue('A'.++$row, '車番');
                $sheet->setCellValue('B'.$row++,$car[$car_num]);

                $car_table=array();
                for($i=0;$i<count($invoice_detail);$i++):
                    if($invoice_detail[$i]['HouseCardNumber']==$car[$car_num]):
                        $car_table[]=$invoice_detail[$i];
                    endif;
                endfor;

                $table_begin=$row;
        
                $sheet->getStyle('A'.$table_begin.':P'.$table_begin)
                ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                
                $sheet->mergecells('A'.$row.':B'.$row);
                $sheet->setCellValue('A'.$row, '月日');
                $sheet->mergecells('C'.$row.':F'.$row);
                $sheet->setCellValue('C'.$row, '給油　SS');
                $sheet->mergecells('G'.$row.':J'.$row);
                $sheet->setCellValue('G'.$row, '商品名');
                $sheet->mergecells('K'.$row.':L'.$row);
                $sheet->setCellValue('K'.$row, '数量');
                $sheet->mergecells('M'.$row.':N'.$row);
                $sheet->setCellValue('M'.$row, '単価');
                $sheet->mergecells('O'.$row.':P'.$row);
                $sheet->setCellValue('O'.$row, '金額');
                $sheet->getStyle('A'.$row.':P'.$row++)
                ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        
                for ($cnt=0;$cnt<12;$cnt++):

                    if($item_cnt<count($car_table)):

                        

                        $sheet->mergecells('A'.$row.':B'.$row);
                        $sheet->setCellValue('A'.$row, substr($car_table[$item_cnt]['Date'],5,5));
                        $sheet->mergecells('C'.$row.':F'.$row);
                        $sheet->setCellValue('C'.$row, $car_table[$item_cnt]['ServiceStationName']);
                        $sheet->mergecells('G'.$row.':J'.$row);
                        $sheet->setCellValue('G'.$row, $car_table[$item_cnt]['ItemName']);
                        $sheet->mergecells('K'.$row.':L'.$row);
                        $sheet->setCellValue('K'.$row, $car_table[$item_cnt]['Amount']);
                        $sheet->mergecells('M'.$row.':N'.$row);
                        $sheet->setCellValue('M'.$row,  $car_table[$item_cnt]['Price']);
                        $sheet->mergecells('O'.$row.':P'.$row);
                        $sheet->setCellValue('O'.$row,  $car_table[$item_cnt]['Total']);
                        $total+=$car_table[$item_cnt]['Total'];
                        $item_cnt++;
                    else:
                        $sheet->mergecells('A'.$row.':B'.$row);
                        $sheet->mergecells('C'.$row.':F'.$row);
                        $sheet->mergecells('G'.$row.':J'.$row);
                        $sheet->mergecells('K'.$row.':L'.$row);
                        $sheet->mergecells('M'.$row.':N'.$row);
                        $sheet->mergecells('O'.$row.':P'.$row);
                    endif;
                    $row++;

                endfor;

                if($item_cnt>=count($car_table)):
                    $car_num++;
                    $item_cnt=0;
                endif;

                $sheet->getStyle('A'.$table_begin.':P'.$row)
                ->getBorders()->getVertical()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                $sheet->getStyle('A'.$row.':P'.$row)
                ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->mergecells('A'.$row.':B'.$row);
                $sheet->setCellValue('A'.$row, '主燃料');
                $sheet->mergecells('C'.$row.':D'.$row);
                $sheet->setCellValue('C'.$row, 'ハイオク');
                $sheet->mergecells('E'.$row.':F'.$row); 
                $sheet->setCellValue('E'.$row, 'レギュラー');
                $sheet->mergecells('G'.$row.':H'.$row);
                $sheet->setCellValue('G'.$row, '軽油');
                $sheet->mergecells('I'.$row.':J'.$row);
                $sheet->setCellValue('I'.$row, '灯油');
                $sheet->mergecells('K'.$row.':M'.$row);   
                $sheet->setCellValue('K'.$row, '合計');
                $sheet->mergecells('N'.$row.':P'.$row);
                $sheet->setCellValue('N'.$row++, '御請求額');

                $sheet->getStyle('A'.$row)
                ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->mergecells('A'.$row.':B'.$row);
                $sheet->setCellValue('A'.$row, '数量');
                $sheet->mergecells('C'.$row.':D'.$row);
                $sheet->setCellValue('C'.$row, '');
                $sheet->mergecells('E'.$row.':F'.$row); 
                $sheet->setCellValue('E'.$row, '');
                $sheet->mergecells('G'.$row.':H'.$row);
                $sheet->setCellValue('G'.$row, '');
                $sheet->mergecells('I'.$row.':J'.$row);
                $sheet->setCellValue('I'.$row, '');
                $sheet->mergecells('K'.$row.':M'.$row);   
                $sheet->setCellValue('K'.$row, '');
                $sheet->mergecells('N'.$row++.':P'.$row--);
                $sheet->setCellValue('N'.$row++, $total);
                $all_total+=$total;

                $sheet->getStyle('A'.$row)
                ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->mergecells('A'.$row.':B'.$row);
                $sheet->setCellValue('A'.$row, '金額');
                $sheet->mergecells('C'.$row.':D'.$row);
                $sheet->setCellValue('C'.$row, '');
                $sheet->mergecells('E'.$row.':F'.$row); 
                $sheet->setCellValue('E'.$row, '');
                $sheet->mergecells('G'.$row.':H'.$row);
                $sheet->setCellValue('G'.$row, '');
                $sheet->mergecells('I'.$row.':J'.$row);
                $sheet->setCellValue('I'.$row, '');
                $sheet->mergecells('K'.$row.':M'.$row);   
                $sheet->setCellValue('K'.$row, '');
                $sheet->getStyle('A'.($row-2).':P'.$row)
                ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $sheet->getStyle('A'.$table_begin.':P'.$row)
                ->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                $sheet->getRowDimension($row++)->setRowHeight(25);        
            endfor;

            $sheet->getRowDimension(++$row)->setRowHeight(25);
            
            $row++;

        endfor;

        $sheet->setCellValue('J26',$all_total);
        

        //


        //$class = new Mpdf();
        //\PhpOffice\PhpSpreadsheet\IOFactory::registerWriter('Pdf', $class);
        //$writer =  \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Pdf');
        $writer1 = new Xlsx($spreadsheet); 
        $writer = new Mpdf($spreadsheet,'ja+aCJK','msgothic');
        $writer->setFont('msgothic');
            
        $writer->save('outputfile/sample.pdf','ja+aCJK','msgothic');
        $writer1->save('outputfile/sample.xlsx');
        
        //echo ($writer->getFont());

        $this->load->view('exceloutput');
        
    }

             /**
     * CSVローダー
     *
     * @param string $csvfile CSVファイルパス
     * @param string $mode `sjis` ならShift-JISでカンマ区切り、 `utf16` ならUTF-16LEでタブ区切りのCSVを読む。'utf8'なら文字コード変換しないでカンマ区切り。
     * @return array ヘッダ列をキーとした配列を返す
     */
    function get_csv($csvfile, $mode='sjis')
    {
        // ファイル存在確認
        if(!file_exists($csvfile)) return false;
    
        $filter = $csvfile;
    
        // SplFileObject()を使用してCSVロード
        $file = new SplFileObject($filter);
        if($mode === 'utf16') $file->setCsvControl("\t");
        $file->setFlags(
            SplFileObject::READ_CSV |
            SplFileObject::SKIP_EMPTY |
            SplFileObject::READ_AHEAD
        );
    
        // 各行を処理
        $records = array();
        foreach ($file as $i => $row)
        {
            // 1行目はキーヘッダ行として取り込み
            if($i===0) {
                foreach($row as $j => $col) $colbook[$j] = $col;
                continue;
            }
    
            // 2行目以降はデータ行として取り込み
            $line = array();
            foreach($colbook as $j=>$col) $line[$colbook[$j]] = @$row[$j];
            $records[] = $line;
        }
        return $records;
        //return $colbook;
    }
}

?>