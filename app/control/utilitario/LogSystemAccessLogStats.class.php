<?php
/**
     * Sistema > MK_SGC - Sistema de Gestão Condominial
     * Modulo  > Grafico: Status de Acessos
     * Form    > Log_SystemAccessLogStats
     * Versao  > 2.0.Atualizacao
     * @author > 122016 by MarcoARCampos
     */
class LogSystemAccessLogStats extends TPage
    {
        /**
         * Class constructor
         * Creates the page
         */
        function __construct()
        {
            parent::__construct();
            
            $img = '<i class="fa fa-info fa-lg"></i>&nbsp;&nbsp;&nbsp;';
            $this->html = new THtmlRenderer('app/resources/google_bar_chart.html');
            // creates the form
            $this->form = new BootstrapFormBuilder('form_Cad_TblChaveList');
            $this->form->setFormTitle( $img.'STATUS - ACESSOS DO USUARIO');    // define the form title
            
            // Criando os Campos das Datas
            $usulogin = new TDBCombo('usulogin', 'permission', 'SystemUser', 'login', '{login} - {name}', 'login');
            
            $dataini  = new TDate('dataini');
            $datafim  = new TDate('datafim');
            
            // Labels
            $lbl_usu = new TLabel('Usuário:',      brown ); 
            $lbl_dti = new TLabel('Data Inicial:', brown ); 
            $lbl_dtf = new TLabel('Data Final:',   brown ); 
            
            //$lbl_usu->setSize('calc(100% - 200px)');
            //$usulogin->setSize(300);
            
            // add the fields
            //$this->form->addFields([$lbl_usu], [$usulogin], [$lbl_dti], [$dataini], [$lbl_dtf], [$datafim] );
            $this->form->addFields( [new TLabel('Usuário:')], [$usulogin] );
            $this->form->addFields( [new TLabel('Data Inicial:')], [$dataini] );
            $this->form->addFields( [new TLabel('Data Final:')], [$datafim] );
            
            // Configuração e Estilos dos Campos
            $usulogin->setSize('100%'); 
            $usulogin->enableSearch();
            $dataini->setMask('dd/mm/yyyy');
            $datafim->setMask('dd/mm/yyyy');
          
            // add the search form actions
            $this->form->addAction(_t('Find'), new TAction( array( $this, 'onProcLog')), 'fa:search fa-lg purple');
            
            // Cria e Add no Painel
            $panel = new TPanelGroup();
            $panel->add( $this->html );
                                               
            // add the template to the page
            $container = new TVBox;
            $container->style = 'width: 100%';
            $container->add( new TXMLBreadCrumb('menu.xml', __CLASS__));
            $container->add( $this->form );       
            $container->add( $panel );
            parent::add($container);
        }
        
        
    //------------------------------------------------------------------------------
        // Processa e Carrega os Dados no Grafico
            
        public function onProcLog( $param = null )
        {
            TTransaction::open( 'log' );
            $user  = (( isset( $param['usulogin'] ) AND ( $param['usulogin'] )) ? $param['usulogin'] : TSession::getValue('login'));
            $dtini = (( isset( $param['dataini'] ) AND ( $param['dataini'] )) ? $param['dataini'] : date('01/m/Y', strtotime( "-5 month" )));
            $dtfim = (( isset( $param['datafim'] ) AND ( $param['datafim'] )) ? $param['datafim'] : date('t/m/Y'));
            
            // get logs by session id
            $logs = SystemAccessLog::where('login', '=', $user )
                                   ->where('login_time', '>=', TDate::date2us( $dtini ))
                                   ->where('login_time', '<=', TDate::date2us( $dtfim ))
                                   ->orderBy('login_time')
                                   ->load();
                        
            if ( count( $logs ) > 0 )
            {
                foreach ($logs as $log)
                {
                    $d = date('d', strtotime( $log->login_time ));
                    $m = date('m', strtotime( $log->login_time ));
                    $a = date('Y', strtotime( $log->login_time ));
                    
                    // Add mes ao ano 
                    $acessos[0][$a][$m] += 1;
                    
                    // Add qtde ao Dia/Ano/Mes
                    $acessos[$d][$a][$m] += 1;
                }
            }
            if ( count( $acessos ) > 0 )
            {                                       
                ksort( $acessos );
            
                TTransaction::close();
                // Meses 
                $meses = array('JAN', 'FEV', 'MAR', 'ABR', 'MAI', 'JUN', 
                               'JUL', 'AGO', 'SET', 'OUT', 'NOV', 'DEZ');  
            
                // Criando o Grafico
                // Titulo                            
        
                $ttmes  = array();    
                $data   = array();
                $data[] = ['Usuário: '.$user.' -  Acessos/Dia - Período: '.$dtini.' até '.$dtfim];
                foreach ( $acessos[0] as $ano => $acess )
                {
                   foreach ( $acess as $mes => $tot )
                   {          
                       $data[0][] = $meses[( $mes - 1 )].'-'.$ano.'('.$tot.')';
                   
                       $ttmes[]  = $mes.$ano;
                   }    
                }
                // Add os Dias/Mes/Ano -> Qtdes
            
                $seq = 1;
            
                foreach ( $acessos as $dia => $day )
                {
                   if ( $dia > 0 )
                   {
                       foreach ( $day as $ano => $year )
                       {          
                           foreach ( $year as $mes => $tday )
                           {                                                                                                          
                               foreach ( $ttmes as $id => $tmes )
                               {                                                                                                          
                                   $data[ $seq ][0] = 'Dia' . ' ' . $dia;
                               
                                   if (!( $data[ $seq ][ $id + 1 ] > 0 )){ 
                                       $data[ $seq ][ $id + 1 ] = (( $tmes == ( $mes.$ano )) ? $tday : 0 );
                                   }    
                               }     
                           }
                       }    
        
                      $seq++;  
              
                   }   
                } 
                // Carregando os Dados no Grafico
                $this->html->enableSection('main', array('data'   => json_encode($data),
                                                         'width'  => '100%',
                                                         'height' => '300px',
                                                         'title'  => 'Acessos por Dia',
                                                         'ytitle' => 'Acessos', 
                                                         'xtitle' => 'Dia'));
            }
            else      
            {
                new TMessage('info', 'Sem Registros para o Login: '.$user );
                $this->onProcLog();
            }        
        }        
    }
    ?> 


