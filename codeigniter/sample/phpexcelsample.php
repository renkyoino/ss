<?php
require realpath('../vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class OutputExcelDailyReportController extends Orangeliner_Controller_Action_List {

    public $acl;
    public $role;
    public $date;

    public function init() {
        $this->acl = $this->getHelper('acl')->getAcl();
        $this->role = $this->getHelper('actor')->getActor();

        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {

        } else {
            $this->_redirect("/auth/");
            return;
        }

        ini_set('memory_limit', '512M');

        $db = Zend_Registry::get('db');
        $table = new Application_Model_DbTable_Sitedata($db);
        $select = $table->select();
        $sitedata = $db->fetchRow($select);
        $this->initView()->sitedata = $sitedata;
    }

    public function isAllowed() {
        $acl = $this->getHelper('acl')->getAcl();
        $role = $this->getHelper('actor')->getActor();
        if ($role->getRoleId() == 'guest') {
            return false;
        }
        return true;
    }

    public function deny() {
        $this->_redirect('/auth');
    }


    public function indexAction() {
        $user_section = $this->role->getUserSection();
        if(!($user_section == Application_Model_DbTable_Userdata::USER_SECTION_PAYROLL)){
            $this->deny();
        }

        $Date = new Application_Model_Date();
        $request = $this->getRequest();

        $query_ab_id = isset($request->ab_id) ? $request->ab_id : "78";
        $query_target_date = isset($request->date) ? $request->date : "2018-07-01";

        $db = Zend_Registry::get('db');

        $business_data = $this->getBusinessData($query_ab_id);
        $agency_data = $this->getAgencyData($query_ab_id);

        $where_agency = $agency_data->id;
        $where_business = $business_data->id;

        $query = 'SELECT '
                .' aa.applicant_code AS code,'
                .' a.name AS a_name,'
                .' s.id AS s_id,'
                .' s.name AS s_name,'
                .' wrr.base_salary,'
                .' wrr.non_scheduled_allowance,'
                .' wrr.non_statutory_allowance,'
                .' wrr.early_morning_allowance,'
                .' wrr.late_night_allowance,'
                .' wrr.weekly_overtime_allowance,'
                .' wrr.monthly_extra_overtime_allowance,'
                .' wrr.holiday_work_allowance,'
                .' wrr.total_minutes,'
                .' wrr.early_morning_minutes,'
                .' wrr.late_night_minutes,'
                .' bc.basic_comission_rate,'
                .' bc.job_posting_comission'
                .' FROM'
                .' work_records_results AS wrr'
                .' LEFT JOIN work_records AS wr ON wrr.id_work_records = wr.id'
                .' LEFT JOIN entries_offers AS eo ON wr.id_entries_offers = eo.id'
                .' LEFT JOIN business_offers AS bo ON eo.id_business_offers = bo.id'
                .' LEFT JOIN agencies_businesses AS ab ON bo.id_agencies_businesses = ab.id'
                .' LEFT JOIN small_sections AS ss ON bo.id_small_sections = ss.id'
                .' LEFT JOIN sections AS s ON ss.id_sections = s.id'
                .' LEFT JOIN applicant_entries AS ae ON eo.id_applicant_entries = ae.id'
                .' LEFT JOIN agencies_applicants AS aa ON ae.id_agencies_applicants = aa.id'
                .' LEFT JOIN applicants AS a ON aa.id_applicants = a.id'
                .' LEFT JOIN business_configurations AS bc ON ab.id_business_configurations = bc.id'
                .' WHERE'
                .' ab.id_agencies = '.$where_agency
                .' AND'
                .' s.id_businesses = '.$where_business
                .' AND'
                .' wr.target_date = \'' . $query_target_date . '\''
                .' AND'
                .' wr.validity = 1'
                .' ORDER BY s.id';
        $stmt = $db->query($query);
        $data_list = $stmt->fetchAll();

        $query_section = 'SELECT'
                        .' DISTINCT s.id,'
                        .' s.name'
                        .' FROM'
                        .' work_records_results AS wrr'
                        .' LEFT JOIN work_records AS wr ON wrr.id_work_records = wr.id'
                        .' LEFT JOIN entries_offers AS eo ON wr.id_entries_offers = eo.id'
                        .' LEFT JOIN business_offers AS bo ON eo.id_business_offers = bo.id'
                        .' LEFT JOIN agencies_businesses AS ab ON bo.id_agencies_businesses = ab.id'
                        .' LEFT JOIN small_sections AS ss ON bo.id_small_sections = ss.id'
                        .' LEFT JOIN sections AS s ON ss.id_sections = s.id'
                        .' WHERE'
                        .' ab.id_agencies = '.$where_agency
                        .' AND'
                        .' s.id_businesses = '.$where_business
                        .' AND'
                        .' wr.target_date = \'' . $query_target_date . '\''
                        .' AND'
                        .' wr.validity = 1'
                        .' ORDER BY s.id';
        $stmt_section = $db->query($query_section);
        $data_list_section = $stmt_section->fetchAll();


        //ここからExcell処理
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        //文字サイズの設定
        $sheet->getStyle('A')->getFont()->setSize(9);
        $sheet->getStyle('B')->getFont()->setSize(9);
        $sheet->getStyle('C')->getFont()->setSize(9);
        $sheet->getStyle('D')->getFont()->setSize(9);
        $sheet->getStyle('E')->getFont()->setSize(9);
        $sheet->getStyle('F')->getFont()->setSize(9);
        $sheet->getStyle('G')->getFont()->setSize(9);


        //デフォルトのフォントと文字サイズの設定
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(8);

        //セルのマージ
        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('A3:B3');
        $sheet->mergeCells('A4:C4');
        $sheet->mergeCells('E2:H2');
        $sheet->mergeCells('E3:G3');
        //$sheet->mergeCells('G3:H3');
        $sheet->mergeCells('E4:G4');
        $sheet->mergeCells('A9:A10');
        $sheet->mergeCells('B9:B10');
        $sheet->mergeCells('C9:C10');
        $sheet->mergeCells('D9:D10');
        $sheet->mergeCells('E9:E10');
        $sheet->mergeCells('F9:F10');
        $sheet->mergeCells('G9:G10');




        //セル内の文字を中央、右ぞろえ
         //$sheet->getStyle('')
         //        ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('A1')
                 ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A6:D7')
                 ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A9:G9')
                 ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D51:F51')
                 ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('A52:F52')
                 ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A11:A51')
                 ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('B11:B50')
                 ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('B11:B50')->getFont()->setSize(12);
        $sheet->getStyle('C11:C50')
                 ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('D11:D50')
                 ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('E11:E50')
                 ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('F11:F50')
                 ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('G11:G50')
                 ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('A53:A54')
                 ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('C53:C54')
                 ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('D53:D54')
                 ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('E53:E54')
                 ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('F53:F54')
                 ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('G53:G54')
                 ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);



        //列幅の設定
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(8);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(10);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(25);

        //行幅の設定
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(20);
        $sheet->getRowDimension(3)->setRowHeight(30);



        $styleArray = [
            'borders' => [
                'bottom' => [
                    // 線のスタイル
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    // 線の色
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];


        $styleArraySide = [
            'borders' => [
                'right' => [
                    // 線のスタイル
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    // 線の色
                    'color' => ['argb' => 'FF000000'],
                ],

                'left' => [
                    // 線のスタイル
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    // 線の色
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];

        //項目部分への枠線の追加

        //$sheet->getStyle('A7:D8')
        //->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        $sheet->getStyle('A6:D7')
        ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('A9:G10')
        ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);


        $sheet->getStyle('A1')->getFont()->setSize(18);

        $sheet->getCell('A1')->setValue('勤務日報');

        $sheet->getCell('A3')->setValue('(求人者名)');

        $sheet->getCell('A4')->setValue($business_data->name);
        $sheet->getCell('D4')->setValue('様');
        $sheet->getCell('A6')->setValue('求職日');
        $sheet->getCell('B6')->setValue(date('m月d日', strtotime($query_target_date . ' -1 day' )));
        $sheet->getCell('C6')->setValue('紹介日');
        $sheet->getCell('D6')->setValue(date('m月d日', strtotime($query_target_date)));
        $sheet->getCell('A7')->setValue('就労期間');
        $sheet->getCell('B7')->setValue(date('m月d日', strtotime($query_target_date)));
        $sheet->getCell('C7')->setValue('〜');
        $sheet->getCell('D7')->setValue(date('m月d日', strtotime($query_target_date)));

        $sheet->getCell('E2')->setValue($agency_data->name);
        $sheet->getCell('E3')->setValue('〒'.$agency_data->zip_code.$agency_data->address1."\n                   ".$agency_data->address2);
        //$sheet->getCell('G3')->setValue($agency_data->address1."\n".$agency_data->address2);
        $sheet->getCell('E4')->setValue('TEL '.$agency_data->tel_number);

        $sheet->getCell('A9')->setValue('コード');
        $sheet->getCell('B9')->setValue('氏名');
        $sheet->getCell('C9')->setValue('時給');
        $sheet->getCell('D9')->setValue('場所');
        $sheet->getCell('E9')->setValue('賃金総額');
        $sheet->getCell('F9')->setValue('雇用期間'."\n".'（内数深夜早朝）');
        $sheet->getCell('G9')->setValue('備考');

        $offset = 10;
        $cnt = 1;
        $sid = 'default';

        $sum_people = 0;
        $sum_salary = 0;
        $sum_minutes = 0;
        $sum_minutes_sub = 0;
        $all_sum_people = 0;
        $all_sum_salary = 0;
        $all_sum_minutes = 0;
        $all_sum_minutes_sub = 0;

        foreach($data_list_section as $index => $data_section) {

            $sum_people_sec = 0;
            $sum_salary_sec = 0;

            foreach ($data_list as $data) {
                if ($data_section->id == $data->s_id) {
                    $total_salary = $data->base_salary
                        + $data->non_scheduled_allowance
                        + $data->non_statutory_allowance
                        + $data->early_morning_allowance
                        + $data->late_night_allowance
                        + $data->weekly_overtime_allowance
                        + $data->monthly_extra_overtime_allowance
                        + $data->holiday_work_allowance;


                    $sheet->getCell('A'.($offset+$cnt))->setValue($data->code);
                    $sheet->getCell('B'.($offset+$cnt))->setValue($data->a_name);
                    $sheet->getCell('D'.($offset+$cnt))->setValue($data->s_name);
                    $sheet->getCell('E'.($offset+$cnt))->setValue(number_format($total_salary));
                    $sheet->getCell('F'.($offset+$cnt))->setValue(($data->total_minutes/60).'('.(($data->early_morning_minutes + $data->late_night_minutes)/60).')');

                    $sid = $data->s_id;
                    $cnt++;

                    $sum_people_sec++;
                    $sum_salary_sec += $total_salary;
                    $sum_minutes += $data->total_minutes;
                    $sum_minutes_sub += $data->early_morning_minutes + $data->late_night_minutes;
                    $all_sum_people++;
                    $all_sum_salary += $total_salary;
                    $all_sum_minutes += $data->total_minutes;
                    $all_sum_minutes_sub += $data->early_morning_minutes + $data->late_night_minutes;
                    $all_sum_job_posting_comission += $data->job_posting_comission;
                    $all_sum_basic_comission_rate += $data->basic_comission_rate * $data->base_salary;
                }
            }
            $cnt++;
            $sheet->getCell('A'.($offset+$cnt))->setValue('計'.$sum_people_sec.'名');
            $sheet->getCell('E'.($offset+$cnt))->setValue(number_format($sum_salary_sec));
            $sheet->getCell('F'.($offset+$cnt))->setValue(($sum_minutes / 60).'('.($sum_minutes_sub / 60).')');

            $sum_people+=$sum_people_sec;
            $sum_salary+=$sum_salary_sec;
            $cnt += 2;
        }



        $cnt += 4;

        $sheet->getCell('A51')->setValue('合計');
        $sheet->getCell('D51')->setValue($sum_people.'名');
        $sheet->getCell('E51')->setValue(number_format($sum_salary).'円');
        $sheet->getCell('F51')->setValue(($all_sum_minutes / 60).'('.($all_sum_minutes_sub / 60).')'.'時間');
        // $sheet->getStyle('A11:A' . ($offset+$cnt-1))->applyFromArray($styleArraySide);
        // $sheet->getStyle('B11:B' . ($offset+$cnt-1))->applyFromArray($styleArraySide);
        // $sheet->getStyle('C11:C' . ($offset+$cnt-1))->applyFromArray($styleArraySide);
        // $sheet->getStyle('D11:D' . ($offset+$cnt-1))->applyFromArray($styleArraySide);
        // $sheet->getStyle('E11:E' . ($offset+$cnt-1))->applyFromArray($styleArraySide);
        // $sheet->getStyle('F11:F' . ($offset+$cnt-1))->applyFromArray($styleArraySide);
        // $sheet->getStyle('G11:G' . ($offset+$cnt-1))->applyFromArray($styleArraySide);
        $sheet->getStyle('A11:A50' )->applyFromArray($styleArraySide);
        $sheet->getStyle('B11:B50' )->applyFromArray($styleArraySide);
        $sheet->getStyle('C11:C50' )->applyFromArray($styleArraySide);
        $sheet->getStyle('D11:D50' )->applyFromArray($styleArraySide);
        $sheet->getStyle('E11:E50' )->applyFromArray($styleArraySide);
        $sheet->getStyle('F11:F50' )->applyFromArray($styleArraySide);
        $sheet->getStyle('G11:G50' )->applyFromArray($styleArraySide);
        $cnt++;
        // $sheet->mergeCells('A'.($offset+$cnt).':C'.($offset+$cnt));
        // $sheet->mergeCells('D'.($offset+$cnt).':E'.($offset+$cnt));
        // $sheet->mergeCells('F'.($offset+$cnt).':G'.($offset+$cnt));
        // $sheet->getCell('A'.($offset+$cnt))->setValue('求人受付事務費');
        // $sheet->getCell('D'.($offset+$cnt))->setValue('紹介手数料');
        // $sheet->getCell('F'.($offset+$cnt))->setValue('手数料総額');
        $cnt++;
        $sheet->mergeCells('A52:C52');
        $sheet->mergeCells('D52:E52');
        $sheet->mergeCells('F52:G52');
        $sheet->getCell('A52')->setValue('求人受付事務費');
        $sheet->getCell('D52')->setValue('紹介手数料');
        $sheet->getCell('F52')->setValue('手数料総額');
        $cnt++;
        // $sheet->getCell('A'.($offset+$cnt))->setValue("(イ)　".$all_sum_job_posting_comission.'円');
        // $sheet->getCell('D'.($offset+$cnt))->setValue("(ロ)　".$all_sum_basic_comission_rate.'円');
        // $sheet->getCell('F'.($offset+$cnt))->setValue("(イ)+(ロ)　".($all_sum_job_posting_comission + $all_sum_basic_comission_rate).'円');
        //$sheet->mergeCells('A53:C53');
        //$sheet->mergeCells('D53:E53');
        //$sheet->mergeCells('F53:G53');
        $sheet->getCell('A53')->setValue("(イ)　");
        $sheet->getCell('C53')->setValue(number_format($all_sum_job_posting_comission).'円');
        $sheet->getCell('D53')->setValue("(ロ)　");
        $sheet->getCell('E53')->setValue(number_format($all_sum_basic_comission_rate).'円');
        $sheet->getCell('F53')->setValue("(イ)+(ロ)　");
        $sheet->getCell('G53')->setValue(number_format($all_sum_job_posting_comission + $all_sum_basic_comission_rate).'円');
        $cnt++;
        //$sheet->mergeCells('A54:C54');
        //$sheet->mergeCells('D54:E54');
        //$sheet->mergeCells('F54:G54');
        $sheet->getCell('A54')->setValue("上記消費税等");
        $sheet->getCell('C54')->setValue(floor(0.08 * $all_sum_job_posting_comission).'円');
        $sheet->getCell('D54')->setValue("上記消費税等");
        $sheet->getCell('E54')->setValue(floor(0.08 * $all_sum_basic_comission_rate).'円');
        $sheet->getCell('F54')->setValue("消費税等計");
        $sheet->getCell('G54')->setValue(floor(0.08 * ($all_sum_job_posting_comission + $all_sum_basic_comission_rate)).'円');
        // $sheet->getStyle('A'.($offset+$cnt-3).':G'.($offset+$cnt))
        //     ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('A51:G52')
            ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle( 'A53:C53' )
            ->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle( 'D53:E53' )
            ->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle( 'F53:G53' )
            ->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle( 'A54:C54' )
            ->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle( 'D54:E54' )
            ->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle( 'F54:G54' )
            ->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);



        // Print Options
        $sheet->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

        $sheet->getPageSetup()->setHorizontalCentered(true);
        $sheet->getPageSetup()->setVerticalCentered(false);

        $sheet->getPageSetup()->setPrintArea('A1:G55'); // 印刷領域（固定）
        $sheet->getPageSetup()->setFitToWidth(TRUE);  // 印刷領域をWidthに合わせる
        $sheet->getPageSetup()->setFitToHeight(FALSE);

        $sheet->getPageMargins()->setTop(0.25);
        $sheet->getPageMargins()->setBottom(0.25);


        // ダウンロード用
        // MIMEタイプ：https://technet.microsoft.com/ja-jp/ee309278.aspx
        header("Content-Description: File Transfer");
        header('Content-Disposition: attachment; filename="勤務日報.xlsx"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        ob_end_clean(); //バッファ消去

        //Excellファイルへの書き込み
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }


    // 事業所データを取得
    public function getBusinessData($query_ab_id) {
        $db = Zend_Registry::get('db');
        $query = 'SELECT '
                . ' b.*'
                . ' FROM'
                . ' agencies_businesses AS ab'
                . ' LEFT JOIN businesses AS b ON ab.id_businesses = b.id'
                . ' WHERE'
                . ' ab.id = ' . $query_ab_id
                . ' AND'
                . ' b.validity = 1'
                . ' AND'
                . ' ab.validity = 1';
        $stmt = $db->query($query);
        return $stmt->fetch();
    }

        // 事業所データを取得
    public function getAgencyData($query_ab_id) {
        $db = Zend_Registry::get('db');
        $query = 'SELECT '
                . ' a.id,'
                . ' a.name,'
                . ' bi.zip_code,'
                . ' bi.address1,'
                . ' bi.address2,'
                . ' bi.tel_number,'
                . ' bi.fax_number'
                . ' FROM'
                . ' agencies_businesses AS ab'
                . ' LEFT JOIN agencies AS a ON ab.id_agencies = a.id'
                . ' LEFT JOIN basic_information AS bi ON a.id_basic_information'
                . ' WHERE'
                . ' ab.id = ' . $query_ab_id
                . ' AND'
                . ' a.validity = 1'
                . ' AND'
                . ' ab.validity = 1'
                . ' AND'
                . ' bi.validity = 1'
                ;
        $stmt = $db->query($query);
        return $stmt->fetch();
    }

}
