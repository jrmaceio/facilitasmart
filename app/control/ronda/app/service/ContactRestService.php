<?php

//class
class ContactRestService extends AdiantiRecordService
    {
        const DATABASE      = 'ronda';
        const ACTIVE_RECORD = 'Ronda';
        
        //method
        public static function teste( $request )
        {
            TTransaction::open('ronda');
            $response = array();
            
            // carrega os contatos
            $all = Ronda::where('id', '>=', $request['from'])
                          ->where('id', '<=', $request['to'])
                          ->load();
            foreach ($all as $ronda)
            {
                $response[] = $ronda->toArray();
            }
            TTransaction::close();
            return $response;
        }
    }
    ?>



