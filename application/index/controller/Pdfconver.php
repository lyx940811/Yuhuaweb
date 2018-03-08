<?php
namespace app\index\controller;
use think\Controller;

use PDFConverter\PDFConverter;
class Pdfconver extends Controller
{

    public function ppttest()
    {
        $converter = new PDFConverter();
        $source = ROOT_PATH."public\\test/test3.xlsx" ;
        $export = ROOT_PATH."public\\test/test4.pdf";
        $converter->execute($source, $export);
        echo 'Done';
    }





}
