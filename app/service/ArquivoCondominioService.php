<?php
/**
 * ArquivoCondominio REST service
 */
class ArquivoCondominioService extends AdiantiRecordService
{
    const DATABASE      = 'facilitasmart';
    const ACTIVE_RECORD = 'ArquivoCondominio';
    
    /*
    http://www.facilitahomeservice.com.br/facilitasmart/rest.php?class=ArquivoCondominioService&method=getArquivos&condominio_id=6
    */
    public static function getArquivos( $param )
    {
        TTransaction::open('facilitasmart');
        $response = array();
        
        // define o critério
        $criteria = new TCriteria;
        $criteria->add(new TFilter('condominio_id', '=', $param['condominio_id']));     
        
        //$limite = 6;
        $param1['order'] = 'id'; 
        $param1['direction'] = 'desc';
        $criteria->setProperties($param1);
        //$criteria->setProperty('limit', $limite);   
            
        // carrega 
        $all = ArquivoCondominio::getObjects( $criteria );
        foreach ($all as $arquivo)
        {
            $response[] = $arquivo->toArray();
        }
        TTransaction::close();
        return $response;
    }
}
