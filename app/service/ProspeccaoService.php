<?php
/**
 * Prospeccao REST service
 */
class ProspeccaoService extends AdiantiRecordService
{
    const DATABASE      = 'facilitasmart';
    const ACTIVE_RECORD = 'Prospeccao';
    
    /*
 
    http://www.facilitahomeservice.com.br/facilitasmart/rest.php?class=ProspeccaoService&method=getAgenda&condominio_id=5
    */
    public static function getAgenda( $param )
    {
        TTransaction::open('facilitasmart');
        $response = array();
        
        // define o critério
        $criteria = new TCriteria;
        $criteria->add(new TFilter('condominio_id', '=', $param['condominio_id']));
        //$criteria->add(new TFilter('unidade_id', '=', '')); // não mostra agenda de uma unidade, somente a publica 
        $limite = 50;
        $param1['order'] = 'horario_inicial'; 
        $param1['direction'] = 'desc';
        $criteria->setProperties($param1);
        $criteria->setProperty('limit', $limite);   
 
        // carrega
        $all = Prospeccao::getObjects( $criteria );
        foreach ($all as $agenda)
        {
            $response[] = $agenda->toArray();
        }
        
        TTransaction::close();
        return $response;
    }
}
