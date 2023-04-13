<?php
/**
 * PessoaForm Form
 * @author  <your name here>
 */
class PessoaFormMonteSinai extends TPage
{
    protected $form; // form
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
         $this->form = new BootstrapFormBuilder('form_Pessoa');
        // define the form title
        
        $this->form->setFormTitle('Recadastramento Residencial Monte Sinai - Pessoas');
        
        // create the form fields
        $id = new TEntry('id');
        $nome = new TEntry('nome');
        $data_nascimento = new TDate('data_nascimento');
        
        $tipo_pessoa = new TRadioGroup('tipo_pessoa');
        $tipo_pessoa->addItems(array('F'=>'Física', 'J'=>'Jurídica'));
        $tipo_pessoa->setLayout('horizontal');
        $tipo_pessoa->setValue(1);
        
        $rg = new TEntry('rg');
        $cpf = new TEntry('cpf');
        $cnpj = new TEntry('cnpj');
        $telefone1 = new TEntry('telefone1');
        $telefone2 = new TEntry('telefone2');
        $telefone3 = new TEntry('telefone3');
        
        $email = new TEntry('email');
        
        // temporario $observacao = new TText('observacao');
        $observacao = new TEntry('observacao');
        
        $cep = new TEntry('cep');
        $endereco = new TEntry('endereco');
        $bairro = new TEntry('bairro');
        $cidade = new TEntry('cidade');
        $estado = new TEntry('estado');
        $condominio_id = new THidden('condominio_id');

        $nome->addValidation('Nome', new TRequiredValidator()); 
        $telefone1->addValidation('Telefone 1', new TRequiredValidator());
        $email->addValidation('E-mail', new TRequiredValidator());

        $email->addValidation('Email', new TEmailValidator);

        $estado->addValidation('Estado', new TMinLengthValidator, array(2));
        $estado->addValidation('Estado', new TMaxLengthValidator, array(2));

        $telefone1->addValidation('Telefone 1', new TMaxLengthValidator, array(18));
        $telefone2->addValidation('Telefone 2', new TMaxLengthValidator, array(18));
        $telefone3->addValidation('Telefone 3', new TMaxLengthValidator, array(18));

        $id->setSize(100);
        $endereco->setSize('72%');
        $cep->setSize('72%');
        $nome->setSize('70%');
        $telefone1->setSize('72%');
        $telefone2->setSize('72%');
        $telefone3->setSize('72%');
        $rg->setSize('72%');
        $cpf->setSize('100%');
        $cnpj->setSize('100%');
        $email->setSize('72%');
        $bairro->setSize('72%');
        $estado->setSize('72%');
        $cidade->setSize('72%');
        $data_nascimento->setSize('72%');
        // temporario $observacao->setSize('89%', 68);
        $observacao->setSize('50%');
        
        $id->setEditable(FALSE);

      
        $this->form->addFields([new TLabel('Id:')],[$id]);
        
        // temporario
        $this->form->addFields([new TLabel('Lote / Quadra')],[$observacao]);
        
        $this->form->addFields([new TLabel('Nome:', '#ff0000')],[$nome]);
        $this->form->addFields([new TLabel('Telefones:')],[$telefone1],[$telefone2],[$telefone3]);
        $this->form->addFields([new TLabel('Tipo Pessoa:')],[$tipo_pessoa],['CPF'],[$cpf], ['CNPJ'],[$cnpj]);
        $this->form->addFields([new TLabel('RG :')], [$rg], [new TLabel('Email:')],[$email]);
        
        // temporário $this->form->addFields([new TLabel('Obs:')],[$observacao]);

        $this->form->addFields([new TLabel('CEP:')],[$cep], [new TLabel('Endereço:')],[$endereco]);
        //$this->form->addFields([new TLabel('Endereço:')],[$endereco]);
        //$this->form->addFields([new TLabel('Complemento:')],[$complemento],[new TLabel('Bairro:')],[$bairro]);
        $this->form->addFields([new TLabel('Bairro:')],[$bairro],[new TLabel('Cidade:', '#ff0000')],[$cidade]);
        $this->form->addFields([new TLabel('Estado:')],[$estado], [new TLabel('Data Nascimento:')],[$data_nascimento]);
        
        $this->form->addFields([new TLabel('')],[$condominio_id]);



                // mascaras
        $cpf->setMask('000.000.000-00');
        $cnpj->setMask('00.000.000/0000-00');
        $cep->setMask('99.999-999'); 
        $data_nascimento->setMask('dd/mm/yyyy');
        $data_nascimento->setDatabaseMask('yyyy-mm-dd');

        //$telefone1->setMask('(99)99999-9999'); 
        //$telefone2->setMask('(99)99999-9999');
        //$telefone3->setMask('(99)99999-9999');
        
        $tipo_pessoa->setChangeAction( new TAction( array($this, 'onChangeTipoPessoa')) );
        ///self::onChangeTipoPessoa( array('tipo_pessoa'=>1) );
        
        
        // buscar sep
        //$buscaCep = new TAction(array($this, 'onCep'));
        //$cep->setExitAction($buscaCep); 
        
         
        // create the form actions
        $this->form->addAction('Salvar', new TAction([$this, 'onSave']), 'fa:floppy-o')->addStyleClass('btn-primary');
        $this->form->addAction('Novo', new TAction([$this, 'onClear']), 'bs:plus-sign green')->addStyleClass('btn-primary');
        ///////////////$this->form->addAction('Listagem', new TAction(array('PessoaList','onReload')), 'fa:table blue')->addStyleClass('btn-primary');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->class = 'form-container';
        $container->add(new TXMLBreadCrumb('menu.xml', 'PessoaList'));
        $container->add($this->form);

        parent::add($container);
    }
    
    public static function onChangeTipoPessoa($param)
    {
        if ($param['tipo_pessoa'] == 'F')
        {
            TEntry::enableField('form_Pessoa', 'cpf');

            TEntry::disableField('form_Pessoa', 'cnpj');
            
            TEntry::clearField('form_Pessoa', 'cnpj');

        }
        else
        {
            TEntry::disableField('form_Pessoa', 'cpf');
            
            TEntry::enableField('form_Pessoa', 'cnpj');
            
            TEntry::clearField('form_Pessoa', 'cpf');
        }
    }
    
    /* 
    *  Função de busca de Endereço pelo CEP 
    *  -   Desenvolvido Felipe Olivaes para ajaxbox.com.br 
    *  -   Utilizando WebService de CEP da republicavirtual.com.br 
    */
    public static function onCep($param)
        {
            
            if ($param['endereco'] != '') {
              new TMessage('info', 'Se desejar pesquisar o CEP, apague o campo Endereço!');                
              return;
            }
            
            $resultado = @file_get_contents('http://republicavirtual.com.br/web_cep.php?cep='.urlencode($param['cep']).'&formato=query_string');  
            if(!$resultado){  
                $resultado = "&resultado=0&resultado_txt=erro+ao+buscar+cep";  
            }  

            parse_str($resultado, $retorno);   
            
            $obj = new StdClass;
            //$obj->cep      = $param['cep'];
            $obj->endereco = strtoupper( $retorno['tipo_logradouro'].' '.$retorno['logradouro']);
            $obj->bairro  = strtoupper( $retorno['bairro']);
            $obj->cidade   = strtoupper( $retorno['cidade']);
            $obj->estado       = strtoupper( $retorno['uf']); 
            
            /*
            // acha a localizacao pelo endereço
            $geocode = new TGeoCode(utf8_encode($retorno['tipo_logradouro'].' '.$retorno['logradouro'].','.$retorno['cidade']));
            $geocode->request();

            if($geocode->getStatus() == 'OK') {
                //echo $geocode->getLat();
                //echo $geocode->getLng();
                //echo $geocode->getFormattedAddress();
                
                $obj->lat      = $geocode->getLat();
                $obj->long     = $geocode->getLng();
                
                $obj->end_form = $geocode->getFormattedAddress();
                
                // key minha api :  AIzaSyD4JYvk3iEQUjXyDUuyHXgluLnFWo_0evA 
                
                // teste com imagemm estatica --> https://maps.googleapis.com/maps/api/staticmap?center=-9.7509084802915,-36.664387930291&size=800x600&zoom=12&maptype=roadmap&markers=icon:%20http://ijiya.com/images/marker-images/image.png|shadow:true|21.19365498864821,72.8217601776123&sensor=false&key=AIzaSyD4JYvk3iEQUjXyDUuyHXgluLnFWo_0evA
                
                $googleQuery = $geocode->getFormattedAddress();
               
                $url = 'http://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($googleQuery) . '&sensor=false';

                $response = file_get_contents($url);

                $json = json_decode($response,TRUE); //generate array object from the response from the web

                //echo ($json['results'][0]['geometry']['location']['lat'].",".$json['results'][0]['geometry']['location']['lng']);
                $lt = $json['results'][0]['geometry']['location']['lat'];
                $lg = $json['results'][0]['geometry']['location']['lng'];

                $this->pos = $lt.','.$lg;

                $mapElement = new TElement('img');
                $mapElement->generator = 'adianti';
                $mapElement->style = "width:900px;height:750px"; 
                $mapElement->src = "https://maps.googleapis.com/maps/api/staticmap?center=".$this->pos."&zoom=15&size=1024x800&markers=color:red%7Clabel:C%7C".$this->pos."&key=AIzaSyD4JYvk3iEQUjXyDUuyHXgluLnFWo_0evA";
                
                parent::add($mapElement); 
                
                
            } 
            */
            
            // envia dados ao form
            TForm::sendData('form_Pessoa', $obj);
        }

    /**
     * Save form data
     * @param $param Request
     */
    public function onSave( $param )
    {
        try
        {
            TTransaction::open('facilitasmart'); // open a transaction
            
            /**
            // Enable Debug logger for SQL operations inside the transaction
            TTransaction::setLogger(new TLoggerSTD); // standard output
            TTransaction::setLogger(new TLoggerTXT('log.txt')); // file
            **/
            
            $this->form->validate(); // validate form data
            
            $object = new Pessoa;  // create an empty object
            $data = $this->form->getData(); // get form data as array
            $object->fromArray( (array) $data); // load the object with data
            
            //retira caracteres da mascara do CEP
            $object->cep = str_replace('.','',$object->cep);
            $object->cep = str_replace('-','',$object->cep);
            
            if (isset($object->cpf)) {
              $object->cpf = str_replace('.','',$object->cpf);
              $object->cpf = str_replace('-','',$object->cpf);
            }
            
            if (isset($object->cnpj)) {
              $object->cnpj = str_replace('.','',$object->cnpj);
              $object->cnpj = str_replace('-','',$object->cnpj);
              $object->cnpj = str_replace('/','',$object->cnpj);
            }
            
            $object->email = strtolower($object->email);
            $object->nome = strtoupper($object->nome);
            
            // temporário $object->condominio_id = TSession::getValue('id_condominio');
            $object->condominio_id = 10; // monte sinai
            
            $object->endereco = strtoupper($object->endereco);
            $object->bairro = strtoupper($object->bairro);
            $object->cidade = strtoupper($object->cidade);
            $object->estado = strtoupper($object->estado);

                                    
            $object->store(); // save the object
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {
        $this->form->clear(TRUE);
    }
    
    /**
     * Load object to form data
     * @param $param Request
     */
    public function onEdit( $param )
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];  // get the parameter $key
                TTransaction::open('facilitasmart'); // open a transaction
                $object = new Pessoa($key); // instantiates the Active Record
                
                $object->cep = preg_replace('(([0-9]{2,})([0-9]{3,})([0-9]{3,}))','\\1.\\2-\\3',$object->cep);
                
                $this->form->setData($object); // fill the form
                TTransaction::close(); // close the transaction
            }
            else
            {
                $this->form->clear(TRUE);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
}
