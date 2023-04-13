<?php
/**
 * PessoaForm Form
 * @author  <your name here>
 */
class PessoaForm extends TPage
{
    protected $form; // form
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        // creates the form
        $this->form = new TQuickForm('form_Pessoa');
        $this->form->class = 'tform'; // change CSS class
        $this->form = new BootstrapFormWrapper($this->form);
        $this->form->style = 'display: table;width:100%'; // change style
        
        // define the form title
        $this->form->setFormTitle('Pessoa');
        
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
        
        $observacao = new TText('observacao');
        $cep = new TEntry('cep');
        $endereco = new TEntry('endereco');
        $bairro = new TEntry('bairro');
        $cidade = new TEntry('cidade');
        $estado = new TEntry('estado');
        $condominio_id = new THidden('condominio_id');

        // add the fields
        $this->form->addQuickField('Id', $id,  '15%' );
        $this->form->addQuickField('Nome', $nome,  '100%' );
        
        $label_cpf = new TLabel('CPF/CNPJ:');
        $label_cpf->setFontStyle('b');
        $label_cpf->style.=';float:left';
        $this->form->addQuickFields('Tipo Pessoa', array($tipo_pessoa, $label_cpf, $cpf, $cnpj ));
        
        $this->form->addQuickFields( new TLabel('RG :'), array($rg,  new TLabel('Data Nascimento :'), $data_nascimento ));
        //$this->form->addQuickField('CNPJ', $cnpj,  '100%' );
        //$this->form->addQuickField('Data Nascimento', $data_nascimento,  '50%' );
        $this->form->addQuickField('Telefone 1', $telefone1,  '100%' );
        $this->form->addQuickField('Telefone 2', $telefone2,  '100%' );
        $this->form->addQuickField('Telefone 3', $telefone3,  '100%' );
        $this->form->addQuickField('E-mail', $email,  '100%', new TRequiredValidator );
        $this->form->addQuickField('Observação', $observacao,  '100%' );
        $this->form->addQuickField('Cep', $cep,  '100%' );
        $this->form->addQuickField('Endereço', $endereco,  '100%' );
        $this->form->addQuickField('Bairro', $bairro,  '100%' );
        $this->form->addQuickField('Cidade', $cidade,  '100%' );
        $this->form->addQuickField('Estado', $estado,  '100%' );
        
        $this->form->addQuickField('Condomínio', $condominio_id,  '100%' );
        
        $email->addValidation('Email', new TEmailValidator);
        
        //Pablo Dall'Oglio O addquickfield bota um tamanho default. Chama o setsize depois
        $observacao->setSize(650, 80);
        
        // mascaras
        $cpf->setMask('000.000.000-00');
        $cnpj->setMask('00.000.000/0000-00');
        $cep->setMask('99.999-999'); 

        $cpf->setSize('30%');
        $rg->setSize('30%');
        //$telefone1->setSize(200);

        $tipo_pessoa->setChangeAction( new TAction( array($this, 'onChangeTipoPessoa')) );
        self::onChangeTipoPessoa( array('tipo_pessoa'=>1) );
        
        
        // buscar sep
        $buscaCep = new TAction(array($this, 'onCep'));
        $cep->setExitAction($buscaCep); 
        
        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        /** samples
         $this->form->addQuickFields('Date', array($date1, new TLabel('to'), $date2)); // side by side fields
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( 100, 40 ); // set size
         **/
         
        // create the form actions
        $btn = $this->form->addQuickAction(_t('Save'), new TAction(array($this, 'onSave')), 'fa:floppy-o');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addQuickAction(_t('New'),  new TAction(array($this, 'onClear')), 'bs:plus-sign green');
        $this->form->addQuickAction(_t('List'),  new TAction(array('PessoaList','onReload')), 'fa:table blue');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add(TPanelGroup::pack('Pessoa', $this->form));
        
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
            $object->cpf = str_replace('.','',$object->cpf);
            $object->cpf = str_replace('-','',$object->cpf);
            $object->cnpj = str_replace('.','',$object->cnpj);
            $object->cnpj = str_replace('-','',$object->cnpj);
            $object->cnpj = str_replace('/','',$object->cnpj);
            $object->email = strtolower($object->email);
            $object->nome = strtoupper($object->nome);
            
            $object->condominio_id = TSession::getValue('id_condominio');
            
            $object->endereco = strtoupper($object->endereco);
            $object->bairro = strtoupper($object->bairro);
            $object->cidade = strtoupper($object->cidade);
            $object->estado = strtoupper($object->estado);

            // TESTE SE EXISTE UMA PESSOA JÁ CADASTRADA COM ESSES DADOS
            $pessoas = Pessoa::where('condominio_id', '=', $object->condominio_id)->
                                      where('cpf', '=', $object->cpf)->load();
                        
            //default = 1 fechado, não permite nada
            $status = 1;
        
            foreach ($pessoas as $pessoa)
            {
              new TMessage('info', 'Existe uma pessoa cadastrada com esse CPF ' . $pessoa-CPF);
              TTransaction::close(); // close the transaction
              return;
            }
                         
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
