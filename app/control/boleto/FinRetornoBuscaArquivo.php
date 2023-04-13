<?php
class FinRetornoBuscaArquivo extends TPage
{
    protected $form;
    public function __construct( $param )
    {
        parent::__construct();
        // Executa um script que substitui o Tab pelo Enter.
        parent::include_js('app/lib/include/application.js');

        $this->form = new BootstrapFormBuilder('form_FinRetornoBuscaArquivo');
        $this->form->setFormTitle('Retorno Busca Arquivo');

        // cria os campos do formularios
        $criteria = new TCriteria;
        $criteria->add(new TFilter("id", "=", TSession::getValue('id_condominio'))); 
        $id_condominio = new TDBCombo('id_condominio', 'facilitasmart', 'Condominio', 'id', 'resumo', 'resumo', $criteria);

        $id_banco = new TDBCombo('id_banco', 'facilitasmart', 'Banco', 'id', 'sigla');
        $id_banco->enableSearch();
        
        $criteria_cta_corrente = new TCriteria();        
        $id_conta_corrente = new TDBCombo('id_conta_corrente', 'facilitasmart', 'ContaCorrente', 'id', 'conta','',$criteria_cta_corrente);
        $id_conta_corrente->setChangeAction(new TAction(array($this, 'onBuscaCaminho')));

        $file = new TFile('file');
        $file->setCompleteAction(new TAction(array($this, 'onBuscaFile')));
        
        $caminho      = new TEntry('caminho');
        $caminho->setExitAction(new TAction(array($this, 'onBuscaArquivo')));

        //$arquivo = new TCombo('arquivo');        
        $arquivo = new TEntry('arquivo');
        
        // define os tamanhos
        $this->form->addFields( [new TLabel('Condominio')], [$id_condominio] );
        $this->form->addFields( [new TLabel('Banco')], [$id_banco] );
        $this->form->addFields( [new TLabel('Conta Corrente')], [$id_conta_corrente] );
        $this->form->addFields( [new TLabel('File:')], [$file]);
        $this->form->addFields( [new TLabel('Caminho')], [$caminho] );
        $this->form->addFields( [new TLabel('Arquivo')], [$arquivo] );
        
        // size
        $id_condominio->setSize('100%');
        $id_banco->setSize('100%');
        $id_conta_corrente->setSize('100%');
        $file->setSize('100%');
        $caminho->setSize('100%');
        $arquivo->setSize('100%');

        $panel = new TPanelGroup;
        $panel->getBody()->style = 'overflow-x:auto';

        $btn = $this->form->addAction('Importar',  new TAction([$this, 'onImportar']), 'ico_apply.png');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction('Limpar', new TAction([$this, 'onClear']), 'fa:eraser red');
        //$this->form->addAction(_t('Back'),new TAction(array('FinRemessaList','onReload')),'far:arrow-alt-circle-left red');


        // create the page container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);

        $obj = new StdClass;
        // -- camimho maquina Junior        
        //$obj->caminho = "boletos/ouroverde/sicredi/retorno//";
        // -- caminho maquina Marcelo
        //$obj->caminho = "D:\CLIENTES\CONDOMINIO\OUROVERDE\RETORNO\\";
        
        //$obj->id_banco = 7;
        //$obj->id_conta_corrente = 1;
		$obj->id_condominio = TSession::getValue('id_condominio');
        
        if(isset($param['arquivo']) OR empty($param['arquivo'])){
            TForm::sendData('form_FinRetornoBuscaArquivo', $obj,false,false);
            //print '<br>if<br>';
        }else{
            TForm::sendData('form_FinRetornoBuscaArquivo', $obj);
            //print '<br>else<br>';
        }
        parent::add($container);
    }
    
    public function onClear($param)
    {
        $this->form->clear(TRUE);
    }


    public static function onBuscaCaminho( $param )
    {
        $reg_cta = '';
        TTransaction::open('facilitasmart');
        $reg_cta = new ContaCorrente($param['id_conta_corrente']);
        TTransaction::close();
        $obj = new StdClass; 
        $obj->id_condominio     = $param['id_condominio'];
        $obj->id_banco          = $param['id_banco'];
        $obj->id_conta_corrente = $param['id_conta_corrente'];
        $obj->caminho           = $reg_cta->arq_retorno;
        TForm::sendData('form_FinRetornoBuscaArquivo', $obj,false,false);
    }


    public static function onBuscaFile( $param )
    {
        $obj = new StdClass; 
        $obj->id_condominio     = $param['id_condominio'];
        $obj->id_banco          = $param['id_banco'];
        $obj->id_conta_corrente = $param['id_conta_corrente'];
        $obj->file              = $param['file'];
        $obj->caminho           = $param['caminho'];
        $obj->arquivo           = $param['file'];
        TForm::sendData('form_FinRetornoBuscaArquivo', $obj,false,false);
    }


    public static function onBuscaArquivo( $param )
    {
        TApplication::postData('form_FinRetornoBuscaArquivo',__CLASS__,'LeituraArquivo');
    }
    
    static function LeituraArquivo( $param )
    {
        try
        {
            $arq_cam   = $param['caminho'];
            $arq_arq   = $param['arquivo'];
            $arq_file  = 'tmp/'.$param['file'];
    
            copy($arq_file, $arq_cam . $arq_arq);
                
            // -- validacao da pasta -- //
            if ( !is_dir( $arq_cam ) ) { throw new Exception('Pasta nao existe !!!'); }
    		
            if (is_dir($arq_cam)) //VERIFICA SE REALMENTE É UM DIRETORIO
            {
                //$diretorio = dir($arq_cam); // LE O QUE TEM DENTRO DA PASTA
                //$item_diretorio = array();
    			//$reg_fin_retorno_arquivo = '';
                //while (($arquivo = $diretorio->read()) !== false) //LE ARQUIVO POR ARQUIVO
                //{
                
                    $arquivo = $arq_arq;
                    
                    TTransaction::open('facilitasmart');
                    $reg_fin_retorno_arquivo = FinRetornoArquivo::where('id_condominio', '=', $param['id_condominio'])
                                                ->where('id_banco', '=', $param['id_banco'])
                                                ->where('id_conta_corrente', '=', $param['id_conta_corrente'])
                                                ->where('arquivo', '=', $arquivo)
                                                ->load();
                    
                    //->where('caminho', '=', $arq_cam)
                    
                    TTransaction::close();
					
					if ($reg_fin_retorno_arquivo) { throw new Exception('Arquivo já Processado !!!'); }
					
                    //if (empty($reg_fin_retorno_arquivo))
                    //{
                    //    $item_diretorio[$arquivo] =  $arquivo; //CRIA A ARRAY COM DADOS QUE IRA SER EXIBIDA NA TSortList
                    //}
                //}
                //$diretorio->close();			
            }
            //$edit_field_arquivo = $this->form->getField('arquivo');
            //$edit_field_arquivo->addItems( $item_diretorio );

            $obj = new StdClass; 
            $obj->id_condominio     = $param['id_condominio'];
            $obj->id_banco          = $param['id_banco'];
            $obj->id_conta_corrente = $param['id_conta_corrente'];
            $obj->file              = $param['file'];
            $obj->caminho           = $param['caminho'];
            $obj->arquivo           = $param['arquivo'];
            TForm::sendData('form_FinRetornoBuscaArquivo', $obj,false,false);
    
        } // fim try
        catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
        
        
    }
    

    public function onImportar( $param )
    {
    
        try
        {
            $data = $this->form->getData();
            
            $arq_cam  = $data->caminho;
            $arq_plan = $data->arquivo;

            // -- validacao da pasta -- //
            if ( !is_dir( $arq_cam ) ) { throw new Exception('Pasta nao existe !!!'); }
            
            set_time_limit(0);
    		
            $txt = new TReadTxt($arq_cam . $arq_plan);
            $conteudo = $txt->abre();
    
            TTransaction::open('facilitasmart');
            
            foreach ($conteudo as $dados)
            {
    
                $cod = substr($dados,7,1 );
                
                if ($cod == 0)
                {
                    $status = '';
                    $okstatus = 0;
                    if ($status == '')
                    {
                        $okstatus = 0;
                    }
                    if ($status == 'REMESSA PROCESSADA')
                    {
                        $okstatus = 2;
                    }
                    if ($status == 'REMESSA REJEITADA')
                    {
                        $okstatus = 3;
                    }
                } // fim if ($cod == 0)
                
                if ($cod == 1)
                {
                    $bco = substr ($dados,0,3);
                    $ope = substr ($dados,8,1);
                    $rem = substr ($dados,183,8);
    		        if ($rem == 0)
    		        {
                        /*
                        1100 	C CAM
                        		S X=$ZU(68,40,0)
                        		D 1200
                        		G 9999
                        1200	; -- move arquivo --
                        		I OKARQ=1 S XB=$ZF(-1,"move "_CAM_" "_CAMSEG)
                        		Q
            		    */  
    		        }
    		        
    		        
                    $reg_fin_retorno = FinRetorno::where('id_condominio', '=', $param['id_condominio'])
                                                ->where('id_banco', '=', $param['id_banco'])
                                                ->where('id_conta_corrente', '=', $param['id_conta_corrente'])
                                                ->where('numero_retorno', '=', $rem)
                                                ->load();
                    //var_dump($reg_fin_retorno);
					if ($reg_fin_retorno) { throw new Exception('Arquivo já Processado !!!'); }
    		        
    		        
    		        $age = substr($dados,53,5);
    		        $dvage = substr($dados,58,1);
    		        $cta = substr($dados,59,12);
    		        $dvcta = substr($dados,71,1);
    		        $cc = $age . "-" . $dvage . "-" . $cta . "-" . $dvcta;
    		        $ctaa = $data->id_conta_corrente;
    		        if ($okstatus == 0)
    		        {
    		            // If $Data(^CRRETORNO(EMP,BCO,CTAA,REM)) G 1000  // se ja existir
    		        }
    		        if ($okstatus == 2)
    		        {
    		            //I $D(^CRRETORNOPRO(EMP,BCO,CTAA,REM)) G 1000 // se ja existir
    		        }
    		        if ($okstatus == 3)
    		        {
    		            //I $D(^CRRETORNOREJ(EMP,BCO,CTAA,REM)) G 1000 // se ja existir
    		        }
    		        $dtretn = '';  $dtret = trim(substr($dados,191,8));  if ($dtret != '') { $dtretn = substr($dtret,4,4) . "-" . substr($dtret,2,2) . "-" . substr($dtret,0,2); }
    		        
                    if ($okstatus == 0)
                    {
                        //F RET=1:1 I $D(^CRRETORNO(EMP,BCO,CTAA,REM,RET))=0 Lock +^CRRETORNO(EMP,BCO,CTAA,REM,RET):0 I $T=1 Q
                    }
                    if ($okstatus == 2)
                    {
                        //F RET=1:1 I $D(^CRRETORNOPRO(EMP,BCO,CTAA,REM,RET))=0 L +^CRRETORNOPRO(EMP,BCO,CTAA,REM,RET):0 I $T=1 Q
                    }
                    if ($okstatus == 3)
                    {
                        //F RET=1:1 I $D(^CRRETORNOREJ(EMP,BCO,CTAA,REM,RET))=0 L +^CRRETORNOREJ(EMP,BCO,CTAA,REM,RET):0 I $T=1 Q
                    }
        		    if ($okstatus == 0)
        		    {
        		        //S ^CRRETORNO(EMP,BCO,CTAA,REM,RET)=CRRETORNO
        		    }
        		    if ($okstatus == 2)
        		    {
        		        //S ^CRRETORNOPRO(EMP,BCO,CTAA,REM,RET)=CRRETORNO
        		    }
        		    if ($okstatus == 3)
        		    {
        		        //S ^CRRETORNOREJ(EMP,BCO,CTAA,REM,RET)=CRRETORNO
        		    }
        		    
        		    // gravar fin_retorno
        		    $fin_retorno = new FinRetorno;
                    $fin_retorno->id_condominio     = $data->id_condominio;
                    $fin_retorno->id_banco          = $data->id_banco;
                    $fin_retorno->id_conta_corrente = $data->id_conta_corrente;
                    $fin_retorno->id_layout_cnab    = 1;
                    $fin_retorno->numero_retorno    = $rem;
                    $fin_retorno->operacao          = $ope;
                    $fin_retorno->dt_retorno        = $dtretn;
                    //$fin_retorno->dt_processamento  = date('Y-m-d');
                    $fin_retorno->nr_cedente        = ''; //$cc;
                    $fin_retorno->store();
                    
                    // gravar fin_retorno_arquivo
                    $fin_retorno_arquivo = new FinRetornoArquivo;
                    $fin_retorno_arquivo->id_condominio    = $data->id_condominio;
                    $fin_retorno_arquivo->id_banco          = $data->id_banco;
                    $fin_retorno_arquivo->id_conta_corrente = $data->id_conta_corrente;
                    $fin_retorno_arquivo->caminho           = $data->caminho;
                    $fin_retorno_arquivo->arquivo           = $data->arquivo;
                    $fin_retorno_arquivo->importado         = 0;
                    $fin_retorno_arquivo->store();
                    
                } // fim if ($cod == 1)
                
                if ($cod == 3)
                {
                    $seg = substr($dados,13,1);
                    
                    if ($seg == 'T')
                    {
                        //F RETITE=1:1 I $D(^CRRETORNO(EMP,BCO,CTAA,REM,RET,RETITE))=0 L +^CRRETORNO(EMP,BCO,CTAA,REM,RET,RETITE):0 I $T=1 Q
                    }
        		    if ($seg == 'W')
        		    {
        		        //F RETITE=1:1 I $D(^CRRETORNOREJ(EMP,BCO,CTAA,REM,RET,RETITE))=0 L +^CRRETORNOREJ(EMP,BCO,CTAA,REM,RET,RETITE):0 I $T=1 Q
        		    }
        		    if ($seg == 'T')
        		    {
        		    	$nossonr = trim(substr($dados,37,20));
        		    	$nossonr = substr($nossonr,0,2) . "/" . substr($nossonr,2,6) . "-" . substr($nossonr,8,1);
        		    	$docseq = trim(substr($dados,105,25));
                        $ret_docseq = $this->onBuscaDOCSEQ( $data->id_condominio , $docseq , $nossonr , $data->id_banco , $data->id_conta_corrente );
        		    	$motforma = ''; //substr($dados,213,2);
        		    	$vlrtxa = round ( ( substr($dados,198,15) / 100 ) , 2);
        		    	$vlrtit = round ( ( substr($dados,81,15) / 100 ) , 2);
        		        $dtvctn = '';  $dtvct = trim(substr($dados,73,8));  if ($dtvct != '') { $dtvctn = substr($dtvct,4,4) . "-" . substr($dtvct,2,2) . "-" . substr($dtvct,0,2); }
        		        $bco_cred = substr($dados,96,3);
        		        $age_cred = substr($dados,99,6);
        		        $cli_pfj = substr($dados,132,1);
        		        if ($cli_pfj == 2) { $cli_cnpj_cpf = substr($dados,134,14); }
        		        if ($cli_pfj == 1) { $cli_cnpj_cpf = substr($dados,137,11); }
        		        $cli_nome = substr($dados,148,40);
        		        if ($cli_pfj == 0) { $cli_cnpj_cpf = '00000000000'; $cli_nome = 'NAO DEFINIDO'; $docseq = '00000000/00'; }
        		    	$movret = substr($dados,15,2);  $desc_movret = $idmovret = '';
        		    	$reg_movret = $this->onBuscaMovRet( $data->id_condominio , $data->id_banco , $movret );
        		    	$idmovret = $reg_movret['idmovret']; 
        		    	$desc_movret = $reg_movret['desc_movret'];
        		    	$motoco = substr($dados,213,10);
        		    	$motocores = substr($dados,213,2);  $desc_motocores = $idmotocores = '';
        		    	$reg_movoco = $this->onBuscaMovOco( $data->id_condominio , $idmovret , $motocores );
        		    	$idmotocores = $reg_movoco['idmotocores']; 
        		    	$desc_motocores = $reg_movoco['desc_motocores'];
    
                        /*
        		    	$reg_tipo_motvo_retorno = TipoMovtoRetorno::where('id_banco', '=' , $data->id_banco)->where('codigo', '=' , $movret)->load();
                        if (!empty($reg_tipo_motvo_retorno))
                        {
                            foreach($reg_tipo_motvo_retorno as $value_reg_tipo_motvo_retorno)
                            {
                                $desc_movret = $value_reg_tipo_motvo_retorno->descricao;
                                $idmovret    = $value_reg_tipo_motvo_retorno->id;
                		    	$reg_tipo_motvo_retorno_item = TipoMovtoRetornoItem::where('id_movto_retorno', '=' , $idmovret)->where('codigo', '=' , $motocores)->load();
                                if (!empty($reg_tipo_motvo_retorno_item))
                                {
                                    foreach($reg_tipo_motvo_retorno_item as $value_reg_tipo_motvo_retorno_item)
                                    {
                                        $desc_motocores = $value_reg_tipo_motvo_retorno_item->descricao;
                                        $idmotocores = $value_reg_tipo_motvo_retorno_item->id;
                                    }
                                } // fim if (empty($reg_tipo_motvo_retorno_item))
                            } // fim foreach($reg_tipo_motvo_retorno as $value_reg_tipo_motvo_retorno)
                        } // fim if (empty($reg_tipo_motvo_retorno))
                        */
        		    	$idmotforma = 0;
        		    	if ( ($motforma == '') || ($motforma == 0) ) { $idmotforma = 0; }
        		    	if ($motforma == 1) { $idmotforma = 1; }
        		    	if ($motforma == 2) { $idmotforma = 2; }
        		    	
        		    	
        		    	/*
        		    	print "<br>" . "forma -> " . $motforma;
        		        print "<br>" . " - movret -> " . $idmovret . "-" . $movret . "-" . $desc_movret;
        		    	print "<br>" . " - movoco -> " . $idmotocores . "-" . $motocores . "-" . $desc_motocores;
        		    	print "<br>";
        		    	*/
        		    	
        		    	// gravar fin_retorno_seg_T
                        if ($ret_docseq != 0)
        		    	{
                		    $fin_retorno_segT = new FinRetornoSegT;
                            $fin_retorno_segT->id_condominio         = $data->id_condominio;
                            $fin_retorno_segT->id_fin_retorno        = $fin_retorno->id;
                            $fin_retorno_segT->seguimento            = $seg;
                            $fin_retorno_segT->nosso_numero          = $nossonr;
                            $fin_retorno_segT->id_contas_receber     = $ret_docseq;
                            $fin_retorno_segT->docto                 = $docseq;
                            $fin_retorno_segT->id_movto_retorno      = $idmovret;
                            $fin_retorno_segT->id_movto_retorno_item = $idmotocores;
                            $fin_retorno_segT->vlr_titulo            = $vlrtit;
                            $fin_retorno_segT->vlr_taxa              = $vlrtxa;
                            $fin_retorno_segT->forma                 = $idmotforma;
                            $fin_retorno_segT->sequencia             = 0;
                            $fin_retorno_segT->id_fin_retornosegu    = 0;
                            $fin_retorno_segT->dt_vencto             = $dtvctn;
                            $fin_retorno_segT->bco_cred              = $bco_cred;
                            $fin_retorno_segT->age_cred              = $age_cred;
                            $fin_retorno_segT->cli_pfj               = $cli_pfj;
                            $fin_retorno_segT->cli_cnpj_cpf          = $cli_cnpj_cpf;
                            $fin_retorno_segT->cli_nome              = $cli_nome;
                            $fin_retorno_segT->store();
        		        } // fim if ($ret_docseq != 0)
        		    	// gravar fin_retorno_seg_TX
                        if ( ($ret_docseq == 0) || ($ret_docseq == '') )
        		    	{
                		    $fin_retorno_segTX = new FinRetornoSegTX;
                            $fin_retorno_segTX->id_condominio         = $data->id_condominio;
                            $fin_retorno_segTX->id_fin_retorno        = $fin_retorno->id;
                            $fin_retorno_segTX->seguimento            = $seg;
                            $fin_retorno_segTX->nosso_numero          = $nossonr;
                            $fin_retorno_segTX->id_contas_receber     = $ret_docseq;
                            $fin_retorno_segTX->docto                 = $docseq;
                            $fin_retorno_segTX->id_movto_retorno      = $idmovret;
                            $fin_retorno_segTX->id_movto_retorno_item = $idmotocores;
                            $fin_retorno_segTX->vlr_titulo            = $vlrtit;
                            $fin_retorno_segTX->vlr_taxa              = $vlrtxa;
                            $fin_retorno_segTX->forma                 = $idmotforma;
                            $fin_retorno_segTX->sequencia             = 0;
                            $fin_retorno_segTX->id_fin_retornosegux   = 0;
                            $fin_retorno_segTX->dt_vencto             = $dtvctn;
                            $fin_retorno_segTX->bco_cred              = $bco_cred;
                            $fin_retorno_segTX->age_cred              = $age_cred;
                            $fin_retorno_segTX->cli_pfj               = $cli_pfj;
                            $fin_retorno_segTX->cli_cnpj_cpf          = $cli_cnpj_cpf;
                            $fin_retorno_segTX->cli_nome              = $cli_nome;
                            $fin_retorno_segTX->store();
        		        } // fim if ( ($ret_docseq == 0) || ($ret_docseq == '') )
        		        // --
        		    } // fim if ($seg == 'T')
        		    
        		    if ($seg == 'U')
        		    {
        		        $dtocon = '';  $dtoco = trim(substr($dados,137,8));  if ($dtoco != '') { $dtocon = substr($dtoco,4,4) . "-" . substr($dtoco,2,2) . "-" . substr($dtoco,0,2); }
        			    $dtcren = '';  $dtcre = trim(substr($dados,145,8));  if ($dtcre != '') { $dtcren = substr($dtcre,4,4) . "-" . substr($dtcre,2,2) . "-" . substr($dtcre,0,2); }
        			    $vlrjrs = round ( ( substr($dados,17,15) / 100 ) , 2);
        			    $vlrdes = round ( ( substr($dados,32,15) / 100 ) , 2);
        			    $vlrabt = round ( ( substr($dados,47,15) / 100 ) , 2);
        			    $vlriof = round ( ( substr($dados,62,15) / 100 ) , 2);
        			    $vlrpago = round ( ( substr($dados,77,15) / 100 ) , 2);
        			    $vlrliqu = round ( ( substr($dados,92,15) / 100 ) , 2);
        			    $vlrdesp = round ( ( substr($dados,107,15) / 100 ) , 2);
        			    $vlrcred = round ( ( substr($dados,122,15) / 100 ) , 2);
        			    $vlrocor = round ( ( substr($dados,165,15) / 100 ) , 2);
        				$movret = substr($dados,15,2);
    
        		        // gravar fin_retorno_seg_U
        		        if ($ret_docseq != 0)
        		        {
                		    $fin_retorno_segU = new FinRetornoSegU;
                            $fin_retorno_segU->id_condominio      = $data->id_condominio;
                            $fin_retorno_segU->id_fin_retorno     = $fin_retorno->id;
                            $fin_retorno_segU->seguimento         = $seg;
                            $fin_retorno_segU->id_movto_retorno   = $idmovret;
                            $fin_retorno_segU->dt_baixa           = $dtocon;
                            $fin_retorno_segU->dt_taxa            = $dtocon;
                            $fin_retorno_segU->dt_credito         = $dtcren;
                            $fin_retorno_segU->vlr_juros          = $vlrjrs;
                            $fin_retorno_segU->vlr_descto         = $vlrdes;
                            $fin_retorno_segU->vlr_abatimento     = $vlrabt;
                            $fin_retorno_segU->vlr_pago           = $vlrpago;
                            $fin_retorno_segU->vlr_credito        = $vlrcred;
                            $fin_retorno_segU->vlr_out_desp       = $vlrdesp;
                            $fin_retorno_segU->vlr_out_credito    = $vlrcred;
                            $fin_retorno_segU->sequencia          = 0;
                            $fin_retorno_segU->id_contas_receber  = $ret_docseq;
                            $fin_retorno_segU->docto              = $docseq;
                            $fin_retorno_segU->id_fin_retornosegt = $fin_retorno_segT->id;
                            $fin_retorno_segU->store();
                            //--
                            $xx_fin_retorno_segT = FinRetornoSegT::find($fin_retorno_segT->id , false);
                            $xx_fin_retorno_segT->id_fin_retornosegu    = $fin_retorno_segU->id;
                            $xx_fin_retorno_segT->store();
                        } // fim if ($ret_docseq != 0)
    
        		    	// gravar fin_retorno_seg_UX
                        if ( ($ret_docseq == 0) || ($ret_docseq == '') )
        		    	{
                		    $fin_retorno_segUX = new FinRetornoSegUX;
                            $fin_retorno_segUX->id_condominio       = $data->id_condominio;
                            $fin_retorno_segUX->id_fin_retorno      = $fin_retorno->id;
                            $fin_retorno_segUX->seguimento          = $seg;
                            $fin_retorno_segUX->id_movto_retorno    = $idmovret;
                            $fin_retorno_segUX->dt_baixa            = $dtocon;
                            $fin_retorno_segUX->dt_taxa             = $dtocon;
                            $fin_retorno_segUX->dt_credito          = $dtcren;
                            $fin_retorno_segUX->vlr_juros           = $vlrjrs;
                            $fin_retorno_segUX->vlr_descto          = $vlrdes;
                            $fin_retorno_segUX->vlr_abatimento      = $vlrabt;
                            $fin_retorno_segUX->vlr_pago            = $vlrpago;
                            $fin_retorno_segUX->vlr_credito         = $vlrcred;
                            $fin_retorno_segUX->vlr_out_desp        = $vlrdesp;
                            $fin_retorno_segUX->vlr_out_credito     = $vlrcred;
                            $fin_retorno_segUX->sequencia           = 0;
                            $fin_retorno_segUX->id_contas_receber   = $ret_docseq;
                            $fin_retorno_segUX->docto               = $docseq;
                            $fin_retorno_segUX->id_fin_retornosegtx = $fin_retorno_segTX->id;
                            $fin_retorno_segUX->store();
                            //--
                            $xx_fin_retorno_segTX = FinRetornoSegTX::find($fin_retorno_segTX->id , false);
                            $xx_fin_retorno_segTX->id_fin_retornosegux   = $fin_retorno_segUX->id;
                            $xx_fin_retorno_segTX->store();
        		        } // fim if ( ($ret_docseq == 0) || ($ret_docseq == '') )
        		        // --
        		    } // fim if ($seg == 'U')
    
                    if ($seg == 'W')
                    {
                        $motret = substr($dados,15,2);
                        $qtdpos = substr($dados,17,6);   
    
                        if ($ret_docseq != 0)
                        {
                            /*                                 
            			    S $P(CRRETORNO01,M,1)=SEG
            		    	S $P(CRRETORNO01,M,2)=MOTRET
            		    	S $P(CRRETORNO01,M,3)=$P($G(^RRMOTRET(EMP,BCO,MOTRET)),M,1)
            		    	S $P(CRRETORNO01,M,4)=QTDPOS
            			    I MOTRET=1 S ^CRRETORNOPRO(EMP,BCO,CTAA,REM,RET,RETITE,SEG)=CRRETORNO01
            			    I MOTRET=2 {
            		    		S ^CRRETORNOREJ(EMP,BCO,CTAA,REM,RET,RETITE,SEG)=CRRETORNO01
            		    		K CAMPO,TIPO,SEGUI,TAB,POS1,POS2 
            		    		S POS1=25,POS2=29
            		    		F L0=1:1:19 {
            			    		S CAMPO(L0)=$E(XV,POS1,(POS1+1)),TIPO(L0)=$E(XV,(POS1+2),(POS1+2))
            			    		S SEGUI(L0)=$E(XV,(POS1+3),(POS1+3)),POS1=POS1+4+3
            			    		S TAB(L0)=+$E(XV,POS2,(POS2+2)),POS2=POS2+4+3
            			    		S ^CRRETORNOREJ(EMP,BCO,CTAA,REM,RET,RETITE,SEG,L0)=CAMPO(L0)_M_TIPO(L0)_M_SEGUI(L0)_M_$P($G(^CRLAYOUT(EMP,BCO,1,TIPO(L0),SEGUI(L0),CAMPO(L0))),M,1)_M_TAB(L0)_M_$P($G(^RRMOTRET(EMP,BCO,MOTRET,TAB(L0))),M,1)
            		    		}
            		    	}
            		    	*/
            		    	
            		    	// gravar fin_retorno_seg_W
            		    	
        		    	} // fim if ($ret_docseq != 0)
        		    	
        		    } // fim if ($seg == 'W')
        		    
                    //$ret_controle = $this->geraControleMovTitulo($param);
                
                } // fim if ($cod == 3)
                
                
                
            } // fim foreach ($conteudo as $dados)
            
            new TMessage('info', 'Retorno Importado com Sucesso !!!');
            TTransaction::close();
            
        } // fim try
        catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }

        
    }
    
    
    // 2000
    function geraControleMovContasReceber( $param )
    {
		//I ((+DOC=0)&(+SEQ=0)) G 2009
		$dtbmov = $dtbxac;
		if ($dtbmov == 0)
		{
		    $dtbmov = $dttxac;
		    if ($dtbmov == $dttxac)
		    {
		        $dtbmov = $dtcrec;
		        if ($dtbmov == 0)
		        {
		            $dtbmov = $ndsis;
		        }
		    }
		}
		/*
		F SEX=1:1 Q:$D(^CRDOCMOV(EMP,DOC,SEQ,DTBMOV,SEX))=0
		S $P(CRDOCMOV,M,1)=+MOVRET
		S $P(CRDOCMOV,M,2)=$P($G(^RRMOVRET(1,BCO,+MOVRET)),M,1)
		S $P(CRDOCMOV,M,3)=+MOTOCORES
		S $P(CRDOCMOV,M,4)=$P($G(^RRMOTRET(1,BCO,+MOVRET,+MOTOCORES)),M,1)
		S $P(CRDOCMOV,M,5)=$P($G(^RRMOTRET(1,BCO,+MOVRET,+MOTOCORES)),M,2)
		S $P(CRDOCMOV,M,6)=+MOTFORMA
		S $P(CRDOCMOV,M,7)=""
		*/
		if ($motforma == 1)
		{
		    //S $P(CRDOCMOV,M,7)="dinheiro"
		}
		if ($motforma == 2)
		{
		    //$P(CRDOCMOV,M,7)="cheque"
		}
		/*
		S $P(CRDOCMOV,M,8)=%CODUSU
		S $P(CRDOCMOV,M,9)=%USUARIO
		S $P(CRDOCMOV,M,10)="AUTOMATICO"
		S ^CRDOCMOV(EMP,DOC,SEQ,DTBMOV,SEX)=CRDOCMOV
		*/
    
    }




    function onBuscaDOCSEQ( $idcond , $docseq , $nossonr , $idbco , $idcta )
    {
        //TTransaction::open('facilitasmart');
        $reg_contas_receber = ContasReceber::where('condominio_id' , '=' , $idcond)
                        ->where('id_conta_corrente', '=', $idcta)
                        ->where('nosso_numero', '=', $nossonr)
                        ->load();
        //TTransaction::close();
        $id_contas_receber = 0 ;
        foreach ($reg_contas_receber as $value_reg_contas_receber)
        {
            $id_contas_receber = $value_reg_contas_receber->id;
        }

        //print "<br>Busca -> " . $idcond . " - " . $docseq . " - " . $nossonr . " - " . $idbco . " - " . $idcta . " - " . $id_contas_receber;
        return $id_contas_receber;        
    }



    function onBuscaMovRet( $idcond , $idbco , $movret )
    {
	    $desc_movret = $idmovret = '' ;
    	TTransaction::open('facilitasmart');
    	$reg_tipo_motvo_retorno = TipoMovtoRetorno::where('id_banco', '=' , $idbco)->where('codigo', '=' , $movret)->load();
        if (!empty($reg_tipo_motvo_retorno))
        {
            foreach($reg_tipo_motvo_retorno as $value_reg_tipo_motvo_retorno)
            {
                $desc_movret = $value_reg_tipo_motvo_retorno->descricao;
                $idmovret    = $value_reg_tipo_motvo_retorno->id;
            } // fim foreach($reg_tipo_motvo_retorno as $value_reg_tipo_motvo_retorno)
        } // fim if (empty($reg_tipo_motvo_retorno))
        TTransaction::close();
        
        $ret_movret = array();
        $ret_movret['idmovret'] = $idmovret;
        $ret_movret['desc_movret'] = $desc_movret;
        return $ret_movret;
    }  // fim onBuscaMovRet
    
    

    function onBuscaMovOco( $idcond , $idmovret , $motocores )
    {
	    $desc_motocores = $idmotocores = '';
    	TTransaction::open('facilitasmart');


    	$reg_tipo_motvo_retorno_item = TipoMovtoRetornoItem::where('id_movto_retorno', '=' , $idmovret)->where('codigo', '=' , $motocores)->load();
        if (!empty($reg_tipo_motvo_retorno_item))
        {
            foreach($reg_tipo_motvo_retorno_item as $value_reg_tipo_motvo_retorno_item)
            {
                $desc_motocores = $value_reg_tipo_motvo_retorno_item->descricao;
                $idmotocores = $value_reg_tipo_motvo_retorno_item->id;
            }
        } // fim if (empty($reg_tipo_motvo_retorno_item))
        TTransaction::close();
        
        $ret_movoco = array();
        $ret_movoco['idmotocores'] = $idmotocores;
        $ret_movoco['desc_motocores'] = $desc_motocores;
        return $ret_movoco;
    }  // fim onBuscaMovOco
    


    
        /*
		; -- LAYOUT
		; 
		; CRRETORNO(EMP,BCO,CTA,REM,RET)
		; 01-OPERACAO
		; 02-NUMERO REMESSA
		; 03-DT RETORNO
		; 04-DT RETORNO
		; 05-DT RETORNO INTERNA
		; 06-NUMERO CEDENTE
		; 07-CONTA CORRENTE
		; 
		; CRRETORNO(EMP,BCO,CTA,REM,RET,ITE,SEG)
		; SEG="T"
		; 01-SEG
		; 02-CODIGO RETORNO
		; 03-DESCRICAO RETORNO
		; 04-NOSSO NUMERO
		; 05-DOC/SEQ
		; 06-SRDOC
		; 07-DOC
		; 08-SEQ
		; 09-CODIGO MOT OCO
		; 10-
		; 11-CODIGO MOT OCO RES
		; 12-DESCRICAO
		; 13-DESCRICAO
		; 14-VLR TITULO
		; 15-VLR TAXA
		; 16-MOT FORMA
		; 17-DESC FORMA
		; 
		; CRRETORNO(EMP,BCO,CTA,REM,RET,ITE,SEG)
		; SEG="U"
		; 01-SEG
		; 02-CODIGO RETORNO
		; 03-DESCRICAO RETORNO
		; 04-DATA BAIXA
		; 05-DATA BAIXA INTERNA
		; 06-DATA TAXA
		; 07-DATA TAXA INTERNA
		; 08-DATA CREDITO
		; 09-DATA CREDITO INTERNA
		; 10-VLR JUROS
		; 11-VLR DESCONTO
		; 12-VLR ABATIMENTO
		; 13-
		; 14-VLR PAGO
		; 15-VLR CREDITO
		; 16-VLR OUTRAS DESPESAS
		; 17-VLR OUTROS CREDITOS
		; 
		; I MOTRET=1 CRRETORNOPRO(EMP,BCO,CTA,REM,RET,ITE,SEG)
		; SEG="W"
		; 01-SEG
		; 02-CODIGO RETORNO
		; 03-DESCRICAO RETORNO
		; 04-QTD
		; 
		; I MOTRET=2 CRRETORNOREJ(EMP,BCO,CTA,REM,RET,ITE,SEG)
		; SEG="W"
		; 01-SEG
		; 02-CODIGO RETORNO
		; 03-DESCRICAO RETORNO
		; 04-POSICAO
		; CRRETORNOREJ(EMP,BCO,CTA,REM,RET,ITE,SEG,L0)
		; 01-CAMPO --> 
		; 02-TIPO --> 
		; 03-SEGUI --> 
		; 04-$P($G(^CRLAYOUT(EMP,BCO,1,TIPO,SEGUI,CAMPO)),M,1)  --> DESCRICAO LAYOUT
		; 05-TAB  --> 
		; 06-$P($G(^RRMOTRET(EMP,BCO,MOTRET,TAB)),M,1) --> DESCRICAO MOTIVO RETORNO
		; 
		; 
		; CRDOCMOV(EMP,DOC,SEQ,DTBMOV,SEX)
 		; 01-CODIGO RETORNO
		; 02-DESCRICAO RETORNO
		; 03-CODIGO MOT OCO RES
		; 04-DESCRICAO
		; 05-DESCRICAO
		; 06-MOT FORMA
		; 07-DESC FORMA
		; 08-COD USU
		; 09-NOME USU
		; 10-OBSERVACAO
		; 
        */
        
    

}
?>