<?php
class GoogleMaps2 extends TPage
{
    private $form;
    
    function __construct()
        {
            parent::__construct();
            
            TPage::include_js('https://maps.googleapis.com/maps/api/js?sensor=false');
            TPage::include_js('http://www.ciapomares.com.br/Adm5269/geo/pesquisa_coordenadas/js/map.js');
            TPage::include_js('https://maps.googleapis.com/maps-api-v3/api/js/31/1/intl/pt_br/common.js');
            TPage::include_js('https://maps.googleapis.com/maps-api-v3/api/js/31/1/intl/pt_br/map.js');
            TPage::include_js('https://maps.googleapis.com/maps-api-v3/api/js/31/1/intl/pt_br/util.js');
            TPage::include_js('https://maps.googleapis.com/maps-api-v3/api/js/31/1/intl/pt_br/onion.js');
            TPage::include_js('https://maps.googleapis.com/maps-api-v3/api/js/31/1/intl/pt_br/controls.js');
            TPage::include_js('https://maps.googleapis.com/maps-api-v3/api/js/31/1/intl/pt_br/marker.js');
            TPage::include_js('https://maps.googleapis.com/maps-api-v3/api/js/31/1/intl/pt_br/stats.js');
            parent::include_css('app/control/patrulha/geo/style.css');         
            parent::__construct();
            
            // create the form using TQuickForm class
            $this->form = new TQuickForm('ObterLatitudeLongitude');
            $this->form->class = 'tform';
            $this->form->setFormTitle('Obter Latitude Longitude');        
                   
            $this->form->style = 'width:100%;height:450px';
                           
            //campos
            $lat = new TEntry('lat');
            $lat->id = 'lat';
            $lat->name = 'lat';     
            $lat->value = '0';
            
            $lng = new TEntry('Lng');
            $lng->id = 'lng';
            $lng->name = 'lng';
            $lng->value = '0';                             
            
            $div1 = new TElement('div');       
            $div1->{'id'} = 'map-canvas';  
            $div1->id = 'map-canvas';  
            $div1->style = 'width:100%;height:300px;border: 2px solid red;padding:2px';  
            $div1->{'class'} = 'map-canvas';   
            
            $this->form->addQuickField('Latitude', $lat,  '40%' );
            $this->form->addQuickField('Longitude', $lng,  '40%' );      
            $this->form->add($div1,  '100%' ); 
          
            $container = new TVBox;
            $container->style = 'width: 100%';
            $container->add(TPanelGroup::pack('titulo', $this->form));        
            parent::add($container);   
        }
    }
    ?> 


