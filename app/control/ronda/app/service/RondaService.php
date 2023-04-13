<?php
/**
 * Ronda REST service
 */
class RondaService extends AdiantiRecordService
{
    const DATABASE      = 'ronda';
    const ACTIVE_RECORD = 'Ronda';
    
    // pega a ultima ronda feita em um ponto
    //http://www.facilitahomeservice.com.br/ronda/rest.php?class=RondaService&method=getRondaReg&unidade_id=6&ponto_ronda_id=29 // antigo uolhost
    public static function getRondaReg( $param )
    {
        try
        {
   
            TTransaction::open('ronda');
            $response = array();
        
            // define o critério
            $criteria = new TCriteria;
            $criteria->add(new TFilter('unidade_id', '=', $param['unidade_id']));
            $criteria->add(new TFilter('ponto_ronda_id', '=', $param['ponto_ronda_id']));
            $limite = 10;
            $param1['order'] = 'id'; 
            $param1['direction'] = 'asc';
            $criteria->setProperties($param1);
            $criteria->setProperty('limit', $limite);   
            
            // carrega 
            $all = Ronda::getObjects( $criteria );
            foreach ($all as $ronda)
            {                               
                $response[] = $ronda->toArray();
            }
            
            TTransaction::close();
            return $response;

        }
        catch (Exception $e)
        {
            echo 'Error: ' . $e->getMessage();
        }

    }
    
    //http://www.facilitahomeservice.com.br/ronda/rest.php?class=RondaService&method=getPontosRonda&unidade_id=6 // antigo uolhost
    public static function getPontosRonda( $param )
    {
        try
        {
   
            TTransaction::open('ronda');
            $response = array();
        
            // define o critério
            $criteria = new TCriteria;
            $criteria->add(new TFilter('unidade_id', '=', $param['unidade_id']));
            $limite = 1000;
            $param1['order'] = 'id'; 
            $param1['direction'] = 'desc';
            $criteria->setProperties($param1);
            $criteria->setProperty('limit', $limite);   
            
            // carrega 
            $all = PontoRonda::getObjects( $criteria );
            foreach ($all as $pontoronda)
            {
            
                // pega a ultima ronda feita neste ponto
                // define o critério
                $criteria1 = new TCriteria;
                $criteria1->add(new TFilter('unidade_id', '=', $param['unidade_id']));
                $criteria1->add(new TFilter('ponto_ronda_id', '=', $pontoronda->id));
                $limite1 = 1;
                $param2['order'] = 'id'; 
                $param2['direction'] = 'desc';
                $criteria1->setProperties($param2);
                $criteria1->setProperty('limit', $limite1);   
            
                // carrega 
                $all1 = Ronda::getObjects( $criteria1 );
                
                foreach ($all1 as $ronda)
                {
                    $dataronda = $ronda->data_ronda;
                    $horaronda = $ronda->hora_ronda;
                }
                
                $x = new StdClass;
                $x->data_ronda = $dataronda;
                $x->hora_ronda = $horaronda;
                $x->id = 'xxxxxxxxxxxx';
                
                $response[] = $x->toArray();
            }
            TTransaction::close();
            return $response;

        }
        catch (Exception $e)
        {
            echo 'Error: ' . $e->getMessage();
        }

    }
    
    // antigo uolhost
    //http://www.facilitahomeservice.com.br/ronda/rest.php?class=RondaService&method=getRonda&unidade_id=3&descricao="TESTE"&patrulheiro_id=4&ponto_ronda_id=9&posto_id=2&longitude="-36.6460585"&latitude="-9.7688983"&data_ronda=2021-01-12&hora_ronda=21:38:45
    public static function getRonda( $param )
    {
        try
        {
   
            TTransaction::open('ronda');
            $response = array();
        
            // define o critério
            $criteria = new TCriteria;
            $criteria->add(new TFilter('unidade_id', '=', $param['unidade_id']));
            $criteria->add(new TFilter('ponto_ronda_id', '=', $param['ponto_ronda_id']));
            $criteria->add(new TFilter('data_ronda', '=', $param['data_ronda']));
            $criteria->add(new TFilter('hora_ronda', '=', $param['hora_ronda']));
            $limite = 1;
            $param1['order'] = 'id'; 
            $param1['direction'] = 'desc';
            $criteria->setProperties($param1);
            $criteria->setProperty('limit', $limite);   
            
            // carrega 
            $all = Ronda::getObjects( $criteria );
            foreach ($all as $ronda)
            {
                $response[] = $ronda->toArray();
            }
            TTransaction::close();
            return $response;

        }
        catch (Exception $e)
        {
            echo 'Error: ' . $e->getMessage();
        }

    }
    
    // http://www.facilitahomeservice.com.br/v2/rest.php?class=OcorrenciaUnidadeService&method=newRonda&condominio_id=99&descricao='teste 2'
    public static function newRonda( $param )
    {
      try
      {
        $location =  'https://ronda.facilitahomeservice.com.br/rest.php';
        $parameters = array();
        $parameters['class'] = 'RondaService';
        $parameters['method'] = 'store';
        $parameters['data'] = [
                                'unidade_id' => $param['unidade_id'],
                                'patrulheiro_id' => $param['patrulheiro_id'],
                                'ponto_ronda_id' => $param['ponto_ronda_id'],
                                'posto_id' => $param['posto_id'],
                                'data_ronda' => date("Y-m-d"),
                                'hora_ronda' => date('H:i:s'),
                                'status_tratamento' => 0, 
                                'descricao' => $param['descricao'],
                                'tipo_id' => 8, // 8 - Ronda
                                'latitude' => $param['latitude'],
                                'longitude' => $param['longitude'],
                              ];
        $url = $location . '?' . http_build_query($parameters);
        //var_dump( json_decode( file_get_contents($url) ) );        
        // pegar o objeto que vem do serviço
        $obj = json_decode( file_get_contents($url) ); 
        //$obj['id');
        return $obj;
        
      }
      catch (Exception $e)
      {
        echo 'Error: '. $e->getMessage();
      }    
    }
    
    //http://www.facilitahomeservice.com.br/ronda/rest.php?class=RondaService&method=SincRonda&unidade_id=3&descricao="TESTE"&patrulheiro_id=4&ponto_ronda_id=9&posto_id=2&longitude="-36.6460585"&latitude="-9.7688983"&data_ronda=2021-01-12&hora_ronda=16:38:45
    public static function SincRonda( $param )
    {
        try
        {
            $location =  'https://ronda.facilitahomeservice.com.br/rest.php';
            $parameters = array();
            $parameters['class'] = 'RondaService';
            $parameters['method'] = 'store';
            $parameters['data'] = [
                                'unidade_id' => $param['unidade_id'],
                                'patrulheiro_id' => $param['patrulheiro_id'],
                                'ponto_ronda_id' => $param['ponto_ronda_id'],
                                'posto_id' => $param['posto_id'],
                                'data_ronda' => $param['data_ronda'],
                                'hora_ronda' => $param['hora_ronda'],
                                'status_tratamento' => 0, 
                                'descricao' => 'ronda sincronizada (feita offline)',
                                'tipo_id' => 8, // 8 - Ronda
                                'latitude' => $param['latitude'],
                                'longitude' => $param['longitude'],
                              ];
            $url = $location . '?' . http_build_query($parameters);
            //var_dump( json_decode( file_get_contents($url) ) );
            // pegar o objeto que vem do serviço
            $obj = json_decode( file_get_contents($url) ); 
            //$obj['id');
            return $obj;
                    
        }
        catch (Exception $e)
        {
            echo 'Error: ' . $e->getMessage();
        }

    }
    
    
    
}
