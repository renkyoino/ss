<style>

</style>

<html lang="en">
    <head>
	<title >請求書</title>
    </head>
    <body>
    <center>
    <table>
    <tr>
    <th>請求書No.</th>
    <th><u>xxx-xxxx</u></th>
    <th style="font-size : 40px;"><u>請求書</u></th>
    <th></th>
    <th>ページxx/xx</th>
    </tr>
    </table>
    </center>
    <center>請求日：平成xx年xx月xx日</center>
    <p style='text-align: right'>シューワ株式会社</p>
    <p style='text-align: left'>〒</p>
    <p style='text-align: left'>住所</p>
    <p style='text-align: right'>〒</p>
    <p style='text-align: left'>会社名</p>
    <p style='text-align: right'>住所</p>
    <p style='text-align: right'>会社名</p>
    
    <p style='text-align: left'>毎度お引き立て頂き、有り難うございます。<br>下記の通りご請求申し上げます。</p>
    <p style='text-align: center'>本書に関してのお問い合わせは、<br>上記の担当者までお願い致します。</p>    

        <table>
        <tr>
        <th>前月請求額<br>(A)</th>
        <th>当月御入金額<br>(B)</th>
        <th>繰越残高<br>(C=A-B-G+F)</th>
        <th>当月ご請求額<br>(D)</th>
        <th>当月ご請求残高<br>(E=C+D)</th>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        </table>
        
        <table>
        <tr>
        <th>商品名</th>
        <th>数量</th>
        <th>金額</th>
        <th>商品名</th>
        <th>数量</th>
        <th>金額</th>
        </tr>
        <?php for ($i=1;$i<=15;$i++):?> 
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <?php endfor;?>
        </table>
        <table>
        <tr>
        <th>月日</th>
        <th>給油SS</th>
        <th>商品名</th>
        <th>数量</th>
        <th>単価</th>
        <th>金額</th>
        </tr>
        <?php for ($i=1;$i<=15;$i++):?>
        <tr> 
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <?php endfor;?>
        </tr>
        </table>
        
    </body>
</html>