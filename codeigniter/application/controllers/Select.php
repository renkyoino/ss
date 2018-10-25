<?php
class Select extends CI_Controller
{
    function index() {

        $filepath='inputfile/Invoice.csv';
        $file = new SplFileObject($filepath); 
        $file->setFlags(SplFileObject::READ_CSV); 
        foreach($file as $line):
            //終端の空行を除く処理　空行の場合に取れる値は後述

                $records[] = $line;
            
        endforeach;

        $this->load->view('select');
        
    }
}
?>
