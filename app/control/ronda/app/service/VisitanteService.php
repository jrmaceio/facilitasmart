<?php
/**
 * Visitante REST service
 */
class VisitanteService extends AdiantiRecordService
{
    const DATABASE      = 'ronda';
    const ACTIVE_RECORD = 'Visitante';
    
    //http://www.facilitahomeservice.com.br/ronda/rest.php?class=VisitanteService&method=getVisitante&visitante_id=3 // antigo uolhost
    public static function getVisitante( $param )
    {
        try
        {
            TTransaction::open('ronda');
            $response = array();
     
            // define o critério
            $criteria = new TCriteria;
            $criteria->add(new TFilter('id', '=', $param['visitante_id']));
            
            // carrega
            $all = Visitante::getObjects( $criteria );
            foreach ($all as $visitante)
            {
                $response[] = $visitante->toArray();
            }
            TTransaction::close();
            return $response;

        }
        catch (Exception $e)
        {
            echo 'Error: ' . $e->getMessage();
        }

    }

}
