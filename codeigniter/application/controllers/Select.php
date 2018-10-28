<?php
class Select extends CI_Controller
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

        $data['CustomerName']=$CustomerName;
        $data['ClosedYear']=$ClosedYear;
        $data['ClosedMonth']=$ClosedMonth;
        

        
        $this->load->view('select',$data);
        
    }

}
?>
