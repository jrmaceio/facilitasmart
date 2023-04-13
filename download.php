<?php
require_once 'init.php';
new TSession;

if (isset($_GET['file']) AND TSession::getValue('logged') )
{
    $file      = $_GET['file'];
    $info      = pathinfo($file);
    $extension = $info['extension'];
    
    $content_type_list = array();
    $content_type_list['txt']  = 'text/plain';
    $content_type_list['html'] = 'text/html';
    $content_type_list['csv']  = 'text/csv';
    $content_type_list['pdf']  = 'application/pdf';
    $content_type_list['rtf']  = 'application/rtf';
    $content_type_list['doc']  = 'application/msword';
    $content_type_list['docx'] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    $content_type_list['xls']  = 'application/vnd.ms-excel';
    $content_type_list['xlsx'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    $content_type_list['ppt']  = 'application/vnd.ms-powerpoint';
    $content_type_list['pptx'] = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
    $content_type_list['odt']  = 'application/vnd.oasis.opendocument.text';
    $content_type_list['ods']  = 'application/vnd.oasis.opendocument.spreadsheet';
    $content_type_list['jpeg'] = 'image/jpeg';
    $content_type_list['jpg']  = 'image/jpeg';
    $content_type_list['png']  = 'image/png';
    $content_type_list['gif']  = 'image/gif';
    $content_type_list['svg']  = 'image/svg+xml';
    $content_type_list['xml']  = 'application/xml';
    $content_type_list['zip']  = 'application/zip';
    $content_type_list['rar']  = 'application/x-rar-compressed';
    $content_type_list['bz']   = 'application/x-bzip';
    $content_type_list['bz2']  = 'application/x-bzip2';
    $content_type_list['tar']  = 'application/x-tar';

    $content_type_list['001']  = 'application/001';
    $content_type_list['002']  = 'application/002';
    $content_type_list['003']  = 'application/003';
    $content_type_list['004']  = 'application/004';
    $content_type_list['005']  = 'application/005';
    $content_type_list['006']  = 'application/006';
    $content_type_list['007']  = 'application/007';
    $content_type_list['008']  = 'application/008';
    $content_type_list['009']  = 'application/009';
    $content_type_list['010']  = 'application/010';

    $content_type_list['crm']  = 'application/crm';
    $content_type_list['cr1']  = 'application/cr1';
    $content_type_list['cr2']  = 'application/cr2';
    $content_type_list['cr3']  = 'application/cr3';
    $content_type_list['cr4']  = 'application/cr4';
    $content_type_list['cr5']  = 'application/cr5';
    $content_type_list['cr6']  = 'application/cr6';
    $content_type_list['cr7']  = 'application/cr7';
    $content_type_list['cr8']  = 'application/cr8';
    $content_type_list['cr9']  = 'application/cr9';
    $content_type_list['cr10']  = 'application/cr10';
    $content_type_list['cr11']  = 'application/cr11';
    $content_type_list['cr12']  = 'application/cr12';
    $content_type_list['cr13']  = 'application/cr13';
    $content_type_list['cr14']  = 'application/cr14';

    if (file_exists($file) AND in_array(strtolower($extension), array_keys($content_type_list)))
    {
        $basename = basename($file);
        $filesize = filesize($file); // get the filesize
        
        header("Pragma: public");
        header("Expires: 0"); // set expiration time
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-type: " . $content_type_list[strtolower($extension)] );
        header("Content-Length: {$filesize}");
        header("Content-disposition: inline; filename=\"{$basename}\"");
        header("Content-Transfer-Encoding: binary");
        
        // a readfile da problemas no internet explorer
        // melhor jogar direto o conteudo do arquivo na tela
        echo file_get_contents($file);
    }
}
