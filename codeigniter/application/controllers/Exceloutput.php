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

        $ItemType=array(
            "Unspecified"=>0,
            "HighOctanOil"=>1,
            "NormalOil"=>2,
            "LightOil"=>3,
            "KeroceneOil"=>4,
            "NotOil"=>99,
            "Unspecified_tax"=>100,
            "HighOctanOil_tax"=>101,
            "NormalOil_tax"=>102,
            "LightOil_tax"=>103,
            "KeroceneOil_tax"=>104,
            "NotOil_tax"=>199,
            
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

        //デフォルト罫線
        $sheet->getStyle('A1:Q'.($page_all*52-2))
            ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);


        
        //デフォルト文字サイズの設定
        $spreadsheet->getDefaultStyle()->getFont()->setSize(16);
        

        //デフォルト行幅の設定
        for($default_row=1;$default_row<($page_all*52-2);$default_row++):
            $sheet->getRowDimension($default_row)->setRowHeight(22);
        endfor;


        //1ページ目(請求書)

        //セルのマージ

        
        
        //ヒナ型部分の作成
        $row=1;

        $sheet->getStyle('A'.$row.':P'.($row+7))
                 ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G'.$row)->getFont()->setSize(24);
        $sheet->getStyle('G'.$row)
        ->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->setCellValue('G'.$row, '請求書');
        $sheet->mergecells('G'.$row++.':J'.$row);
        $sheet->mergecells('A'.$row.':B'.$row);
        $sheet->setCellValue('A'.$row, '請求書NO.');
        $sheet->mergecells('C'.$row.':E'.$row);
        $sheet->setCellValue('C'.$row, '');
        $sheet->mergecells('N'.$row.':O'.$row);
        $sheet->setCellValue('N'.$row, 'ページ');
        $sheet->setCellValue('P'.$row, '1/'.$page_all);
        $sheet->setCellValue('G'.$row, '請求日');
        $sheet->mergecells('H'.($row=$row+2).':J'.$row);
        $sheet->setCellValue('H'.$row++,$closed_year.'年'.$closed_month.'日末日');
        $sheet->getStyle('I'.$row)->getFont()->setSize(36);
        $sheet->setCellValue('I'.$row, 'シューワ株式会社');
        $sheet->mergecells('I'.$row.':P'.($row=$row+4));
        $sheet->mergecells('B'.$row.':C'.$row);
        $sheet->setCellValue('B'.$row++, '〒'.$customer['CustomerZipCode']);
        $sheet->mergecells('B'.$row.':G'.($row+1));
        $sheet->getStyle('B'.$row)->getFont()->setSize(24);
        $sheet->setCellValue('B'.$row++, $customer['CustomerAddress1']);
        $sheet->mergecells('B'.($row+1).':G'.($row+2));
        $sheet->getStyle('B'.($row+1))->getFont()->setSize(24);
        $sheet->setCellValue('B'.($row+1), $customer['CustomerAddress2']);
        $sheet->mergecells('I'.$row.':J'.$row);
        $sheet->setCellValue('I'.$row, '〒599-8242');
        $sheet->mergecells('K'.$row.':O'.$row++);
        $sheet->setCellValue('K'.$row, '大阪府堺市中区陶器北244-5');
        $sheet->mergecells('K'.$row.':O'.$row++);
        $sheet->setCellValue('K'.$row++, 'TEL:072-236-8846');
        $sheet->mergecells('B'.$row.':F'.($row+1));
        $sheet->getStyle('B'.$row)->getFont()->setSize(24);
        $sheet->setCellValue('B'.$row, $customer['CustomerName']);
        $sheet->mergecells('K'.$row.':O'.$row);
        $sheet->setCellValue('K'.$row++, 'FAX:072-236-6588');
        $sheet->setCellValue('G'.$row,'様');
        $sheet->mergecells('J'.$row.':P'.$row++);
        $sheet->mergecells('J'.$row.':P'.$row++);
        $sheet->mergecells('J'.$row.':P'.$row++);
        $sheet->mergecells('J'.$row.':P'.$row);
        $sheet->getStyle('J'.($row-3).':P'.$row)
        ->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('J'.($row-3).':P'.$row)
        ->getBorders()->getVertical()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $row++;
        $sheet->mergecells('B'.$row.':G'.$row);
        $sheet->setCellValue('B'.$row++, ' 毎度お引き立て頂き、有難うございます。');
        $sheet->mergecells('B'.$row.':G'.$row);
        $sheet->setCellValue('B'.$row, '下記の通りご請求申し上げます。');
        $sheet->mergecells('H'.$row.':K'.$row);
        $sheet->setCellValue('H'.$row++, ' 本書に関してのお問い合わせは、');
        $sheet->mergecells('H'.$row.':K'.$row);
        $sheet->setCellValue('H'.$row, '上記の担当者までお願い致します。');
        $row=$row+3;
        $sheet->getStyle('A'.$row.':P'.($row+3))
        ->getBorders()->getVertical()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('A'.$row.':C'.($row+1))
        ->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('A'.$row.':P'.($row+1))
        ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->mergecells('A'.$row.':C'.$row);
        $sheet->setCellValue('A'.$row, '前月請求額');
        $sheet->mergecells('D'.$row.':F'.$row);
        $sheet->setCellValue('D'.$row, '前月ご入金額');
        $sheet->mergecells('G'.$row.':I'.$row);
        $sheet->setCellValue('G'.$row, '繰越残高');
        $sheet->mergecells('J'.$row.':M'.$row);
        $sheet->setCellValue('J'.$row, '当月ご請求額');
        $sheet->mergecells('N'.$row.':P'.$row);
        $sheet->setCellValue('N'.$row, '当月ご請求残高');
        $sheet->getStyle('A'.$row.':C'.($row+1))
        ->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('D'.$row.':F'.($row+1))
        ->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('G'.$row.':I'.($row+1))
        ->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('J'.$row.':M'.($row+1))
        ->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('N'.$row.':O'.$row++)
        ->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->mergecells('A'.$row.':C'.$row);
        $sheet->setCellValue('A'.$row, '(A)');
        $sheet->mergecells('D'.$row.':F'.$row);
        $sheet->setCellValue('D25', '(B)');
        $sheet->mergecells('G'.$row.':I'.$row);
        $sheet->setCellValue('G'.$row, '(C=A-B)');
        $sheet->mergecells('J'.$row.':M'.$row);
        $sheet->setCellValue('J'.$row, '(D)');
        $sheet->mergecells('N'.$row.':P'.$row);
        $sheet->setCellValue('N'.$row++, '(E=C+D)');
        $sheet->mergecells('A'.$row.':C'.($row+1));
        $sheet->getStyle('A'.$row.':C'.($row+1))
        ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->mergecells('D'.$row.':F'.($row+1));
        $sheet->getStyle('D'.$row.':F'.($row+1))
        ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->mergecells('G'.$row.':I'.($row+1));
        $sheet->getStyle('G'.$row.':I'.($row+1))
        ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->mergecells('J'.$row.':M'.($row+1));
        $sheet->getStyle('J'.$row.':M'.($row+1))
        ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->mergecells('N'.$row.':P'.($row+1));
        $sheet->getStyle('N'.$row.':P'.($row+1))
        ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $row=$row+3;
        $sheet->mergecells('G'.$row.':I'.$row);
        $sheet->setCellValue('G'.$row, 'お支払予定日');
        $sheet->mergecells('K'.$row.':N'.$row++);
        $row++;
        $sheet->mergecells('G'.$row.':H'.$row);
        $sheet->setCellValue('G'.$row++, '取引銀行');
        $sheet->mergecells('G'.$row.':H'.$row);
        $sheet->mergecells('J'.$row.':L'.$row);
        $sheet->mergecells('N'.$row.':O'.$row++);
        $sheet->mergecells('G'.$row.':H'.$row);
        $sheet->mergecells('J'.$row.':L'.$row);
        $sheet->mergecells('N'.$row.':O'.$row++);
        $row=$row+1;
        $sheet->mergecells('C'.$row.':E'.$row);
        $sheet->setCellValue('C'.$row, '<御案内>');
        $sheet->getStyle('C'.$row++.':N'.($row+1))
        ->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->mergecells('C'.$row.':N'.($row+1));
        $sheet->getStyle('C'.$row.':N'.($row+1))
        ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('C'.$row.':N'.($row++))
        ->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);
        $row=$row+2;
        $sheet->mergecells('B'.$row.':C'.$row);
        $sheet->setCellValue('B'.$row++, '[商品別御請求額]');
        $sheet->getStyle('A'.$row.':P'.($row+1))
        ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->mergecells('A'.$row.':D'.($row+1));
        $sheet->setCellValue('A'.$row, '商品名');
        $sheet->mergecells('E'.$row.':F'.($row+1));
        $sheet->setCellValue('E'.$row, '数量');
        $sheet->mergecells('G'.$row.':H'.($row+1));
        $sheet->setCellValue('G'.$row, '金額');
        $sheet->mergecells('I'.$row.':L'.($row+1));
        $sheet->setCellValue('I'.$row, '商品名');
        $sheet->mergecells('M'.$row.':N'.($row+1));
        $sheet->setCellValue('M'.$row, '数量');
        $sheet->mergecells('O'.$row.':P'.($row+1));
        $sheet->setCellValue('O'.$row++, '金額');

        $row++;

        $row_table_start=$row;

        $row_product_sum=$row+7;
        $row_oiltax_sum=$row_product_sum+1;
        $row_consum_sum=$row_oiltax_sum+1;

        for($cnt=0; $cnt<10; $cnt++):

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
            

        $sheet->setCellValue('I'.($row_product_sum), '**商品計**');
        $sheet->setCellValue('I'.($row_oiltax_sum), '**軽油税計**');
        $sheet->setCellValue('I'.($row_consum_sum), '**消費税計**');
 
        //2ページ目(請求明細書)
        //これ以降はイテレータがベースになるので、rowごとの処理(マージ、フォント、内容、罫線)
        
        
        
        $all_total=0;
        $lightoil_subtotal_all=0;
        $lightoil_amount_all=0;
        $consum_tax_all=0;
        $oiltax_total_all=0;
        $oiltax_amount_all=0;
        

        $row--;

        for($page_number=2;$page_number<=$page_all;$page_number++):

        //セルのマージ
        //ページの最初の行番号

            $sheet->getRowDimension(++$row)->setRowHeight(25);

            $sheet->getStyle('A'.$row.':P'.($row+3))
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->mergecells('G'.$row++.':J'.$row--);
            $sheet->getStyle('G'.$row)->getFont()->setSize(24);
            $sheet->setCellValue('G'.$row++, '請求明細書');
            $sheet->getRowDimension($row++)->setRowHeight(25);
            $sheet->mergecells('N'.$row.':O'.$row);        
            $sheet->setCellValue('N'.$row, 'ページ');
            $sheet->setCellValue('P'.$row, $page_number.'/'.$page_all);
        
            $sheet->mergecells('C'.$row.':D'.$row++);
            $sheet->mergecells('A'.$row.':B'.$row);
            $sheet->setCellValue('A'.$row, '得意先');
            $sheet->mergecells('C'.$row.':I'.$row);
            $sheet->setCellValue('C'.$row, $customer['CustomerName']);
            $sheet->getStyle('A'.$row.':I'.$row)
            ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);


            for($table=1;$table<=3;$table++):

                if($car_num>=$car_sum):
                    break;
                endif;

                $total=0;
                $item_cnt=0;
                $row++;
                $table_row=0;
                $lightoil_amount=0;
                $lightoil_subtotal=0;
                $oiltax_amount=0;
                $oiltax_total=0;
                $consum_tax=0;
                $oiltax_unit=32.1;
                
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
                
               
        
                for ($cnt=0;$cnt<30;$cnt++):

                    if($item_cnt<count($car_table) and $car_table[$item_cnt]['OilTaxFlag']==1):

                        $oiltax_amount+=$car_table[$item_cnt]['Amount'];

                        $oiltax_total+=$car_table[$item_cnt]['Total'];

                        $total+=$car_table[$item_cnt]['Total'];

                        $item_cnt++;

                    else:
                        
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
                            $sheet->setCellValue('O'.$row,  round($car_table[$item_cnt]['SubTotal']));
                            $lightoil_amount+=$car_table[$item_cnt]['Amount'];
                            $lightoil_subtotal+=$car_table[$item_cnt]['SubTotal'];
                            $consum_tax+= $car_table[$item_cnt]['ConsumptionTax'];
                            $total+=$car_table[$item_cnt]['Total'];
                            $table_row++;
                            $item_cnt++;
                            
                        elseif($item_cnt==count($car_table)):
                        
                            $sheet->mergecells('A'.$row.':B'.$row);
                            $sheet->mergecells('C'.$row.':F'.$row);
                            $sheet->mergecells('G'.$row.':J'.$row);
                            $sheet->setCellValue('G'.$row,'<軽油税>');
                            $sheet->mergecells('K'.$row.':L'.$row);
                            $sheet->setCellValue('K'.$row,$oiltax_amount);
                            $oiltax_amount_all+=$oiltax_amount;
                            $sheet->mergecells('M'.$row.':N'.$row);
                            $sheet->setCellValue('M'.$row,$oiltax_unit);
                            $sheet->mergecells('O'.$row.':P'.$row);
                            $sheet->setCellValue('O'.$row,  round($oiltax_total));
                            $oiltax_total_all+=$oiltax_total;
                            $table_row++;
                            $item_cnt++;
                        
                        elseif($item_cnt==count($car_table)+1):
                            
                            $sheet->mergecells('A'.$row.':B'.$row);
                            $sheet->mergecells('C'.$row.':F'.$row);
                            $sheet->mergecells('G'.$row.':J'.$row);
                            $sheet->setCellValue('G'.$row,'<消費税>');
                            $sheet->mergecells('K'.$row.':L'.$row);
                            $sheet->mergecells('M'.$row.':N'.$row);
                            $sheet->mergecells('O'.$row.':P'.$row);
                            $sheet->setCellValue('O'.$row,  round($consum_tax));
                            $consum_tax_all+=$consum_tax;
                            $table_row++;
                            $item_cnt++;

                        elseif($item_cnt>count($car_table)+1):
                            $sheet->mergecells('A'.$row.':B'.$row);
                            $sheet->mergecells('C'.$row.':F'.$row);
                            $sheet->mergecells('G'.$row.':J'.$row);
                            $sheet->mergecells('K'.$row.':L'.$row);
                            $sheet->mergecells('M'.$row.':N'.$row);
                            $sheet->mergecells('O'.$row.':P'.$row);
                            $table_row++;

                        endif;

                        $row++;

                        if ($table_row>8):

                            break;

                        endif;
                        
                    endif;
                    

                endfor;

                

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
                $sheet->mergecells('A'.$row++.':B'.$row--);
                $sheet->setCellValue('A'.$row, '数量');
                $sheet->mergecells('C'.$row++.':D'.$row--);
                $sheet->setCellValue('C'.$row, '');
                $sheet->mergecells('E'.$row++.':F'.$row--); 
                $sheet->setCellValue('E'.$row, '');
                $sheet->mergecells('G'.$row++.':H'.$row--);
                $sheet->setCellValue('G'.$row, $lightoil_amount);
                $lightoil_amount_all+=$lightoil_amount;
                $lightoil_subtotal_all+=$lightoil_subtotal;
                $sheet->mergecells('I'.$row++.':J'.$row--);
                $sheet->setCellValue('I'.$row, '');
                $sheet->mergecells('K'.$row++.':M'.$row--);   
                $sheet->setCellValue('K'.$row, '');
                $sheet->mergecells('N'.$row++.':P'.$row--);
                $sheet->setCellValue('N'.$row++, round($total));
                $all_total+=$total;
                $sheet->getStyle('A'.($row-2).':P'.$row)
                ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $sheet->getStyle('A'.$table_begin.':P'.$row)
                ->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                $sheet->getRowDimension($row++)->setRowHeight(25);
                
               
                $car_num++;
                   
               
            endfor;

           
            
            

        endfor;

        $item_subtotal=0;
        $sheet->getStyle('J26')->getFont()->setSize(20);
        $sheet->setCellValue('J26',round($all_total));
        $sheet->getStyle('N26')->getFont()->setSize(20);
        $sheet->setCellValue('N26',round($all_total));
        $sheet->setCellValue('A'.$row_table_start,'軽油');
        $sheet->setCellValue('E'.$row_table_start,$lightoil_amount_all);
        $sheet->setCellValue('G'.$row_table_start,round($lightoil_subtotal_all));
        $item_subtotal+=$lightoil_subtotal_all;
        $sheet->setCellValue('O'.($row_product_sum), round($item_subtotal));
        $sheet->setCellValue('M'.($row_oiltax_sum), $oiltax_amount_all);
        $sheet->setCellValue('O'.($row_oiltax_sum), round($oiltax_total_all));
        $sheet->setCellValue('O'.($row_consum_sum), round($consum_tax_all));


        
        


        

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